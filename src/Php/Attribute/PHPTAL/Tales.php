<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
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
use PhpTal\Php\CodeWriter;

/**
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Tales extends \PhpTal\Php\Attribute
{

    /**
     * @var string
     */
    private $oldMode;

    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws TemplateException
     */
    public function before(CodeWriter $codewriter)
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
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function after(CodeWriter $codewriter)
    {
        $codewriter->setTalesMode($this->oldMode);
    }
}
