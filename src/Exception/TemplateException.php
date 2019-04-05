<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal\Exception;

use PhpTal\PHPTAL;

/**
 * Exception that is related to location within a template.
 * You can check srcFile and srcLine to find source of the error.
 *
 * @package PHPTAL
 */
class TemplateException extends PhpTalException
{
    /**
     * @var string
     */
    public $srcFile;

    /**
     * @var int
     */
    public $srcLine;

    /**
     * @var bool
     */
    private $is_src_accurate;

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @param string $msg
     * @param string $srcFile
     * @param int $srcLine
     */
    public function __construct(string $msg, string $srcFile = '', int $srcLine = 0)
    {
        parent::__construct($msg);

        if ($srcFile && $srcLine) {
            $this->srcFile = $srcFile;
            $this->srcLine = $srcLine;
            $this->is_src_accurate = true;
        } else {
            $this->is_src_accurate = $this->setTemplateSource();
        }

        if ($this->is_src_accurate) {
            $this->file = $this->srcFile;
            $this->line = (int)$this->srcLine;
        }
    }

    /**
     * @return string the string representation of the exception.
     */
    public function __toString(): string
    {
        if (!$this->srcFile || $this->is_src_accurate) {
            return parent::__toString();
        }
        return "From {$this->srcFile} around line {$this->srcLine}\n" . parent::__toString();
    }

    /**
     * Set new TAL source file/line if it isn't known already
     * @param string $srcFile
     * @param int $srcLine
     */
    public function hintSrcPosition(string $srcFile, int $srcLine): void
    {
        if ($srcFile && $srcLine) {
            if (!$this->is_src_accurate) {
                $this->srcFile = $srcFile;
                $this->srcLine = $srcLine;
                $this->is_src_accurate = true;
            } elseif ($this->srcLine <= 1 && $this->srcFile === $srcFile) {
                $this->srcLine = $srcLine;
            }
        }

        if ($this->is_src_accurate) {
            $this->file = $this->srcFile;
            $this->line = (int)$this->srcLine;
        }
    }

    /**
     * @param string $path
     *
     * @return false|int
     */
    private function isTemplatePath(string $path): int
    {
        return preg_match('/[\\\\\/]tpl_[0-9a-f]{8}_[^\\\\]+$/', $path);
    }

    /**
     * @return array
     */
    private function findFileAndLine(): array
    {
        if ($this->isTemplatePath($this->file)) {
            return [$this->file, $this->line];
        }

        $eval_line = 0;
        $eval_path = null;

        // searches backtrace to find template file
        foreach ($this->getTrace() as $tr) {
            if (!isset($tr['file'], $tr['line'])) {
                continue;
            }

            if ($this->isTemplatePath($tr['file'])) {
                return [$tr['file'], $tr['line']];
            }

            // PHPTAL.php uses eval() on first run to catch fatal errors. This makes template path invisible.
            // However, function name matches template path and eval() is visible in backtrace.
            if (strpos($tr['file'], 'eval()') !== false) {
                $eval_line = $tr['line'];
            } elseif ($eval_line && isset($tr['function'], $tr['args'][0]) &&
                $this->isTemplatePath('/' . $tr['function'] . '.php') && $tr['args'][0] instanceof PHPTAL) {
                return [$tr['args'][0]->getCodePath(), $eval_line];
            }
        }

        return [null, null];
    }

    /**
     * sets srcLine and srcFile to template path and source line
     * by checking error backtrace and scanning PHP code file
     *
     * @return bool true if found accurate data
     */
    private function setTemplateSource(): bool
    {
        // not accurate, but better than null
        $this->srcFile = $this->file;
        $this->srcLine = $this->line;

        [$file, $line] = $this->findFileAndLine();

        if ($file === null) {
            return false;
        }

        // this is not accurate yet, hopefully will be overwritten later
        $this->srcFile = $file;
        $this->srcLine = $line;

        $lines = @file($file);
        if (!$lines) {
            return false;
        }

        $found_line = false;
        $found_file = false;

        // scan lines backwards looking for "from line" comments
        $end = min(count($lines), $line) - 1;
        for ($i = $end; $i >= 0; $i--) {
            if (preg_match('/tag "[^"]*" from line (\d+)/', $lines[$i], $m)) {
                $this->srcLine = (int)$m[1];
                $found_line = true;
                break;
            }
        }

        foreach (preg_grep('/Generated by PHPTAL from/', $lines) as $line) {
            if (preg_match('/Generated by PHPTAL from (.*) \(/', $line, $m)) {
                $this->srcFile = $m[1];
                $found_file = true;
                break;
            }
        }

        return $found_line && $found_file;
    }
}
