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

namespace PhpTal\Php\Attribute\PHPTAL;

use PhpTal\Exception\TemplateException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Tales extends Attribute
{
    private ?string $oldMode = null;

    /**
     * Called before element printing.
     *
     *
     * @throws TemplateException
     */
    public function before(CodeWriter $codewriter): void
    {
        $mode = strtolower(trim($this->expression));

        if ($mode === '' || $mode === 'default') {
            $mode = 'tales';
        }

        if ($mode !== 'php' && $mode !== 'tales') {
            throw new TemplateException(
                "Unsupported TALES mode '$mode'",
                $this->phpelement->getSourceFile(),
                $this->phpelement->getSourceLine()
            );
        }

        $this->oldMode = $codewriter->setTalesMode($mode);
    }

    /**
     * Called after element printing.
     *
     *
     */
    public function after(CodeWriter $codewriter): void
    {
        $codewriter->setTalesMode($this->oldMode);
    }
}
