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
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */

namespace PhpTal\Php\Attribute\PHPTAL;

/**
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Tales extends \PhpTal\Php\Attribute
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $mode = trim($this->expression);
        $mode = strtolower($mode);

        if ($mode == '' || $mode == 'default')
            $mode = 'tales';

        if ($mode != 'php' && $mode != 'tales') {
            throw new \PhpTal\Exception\TemplateException("Unsupported TALES mode '$mode'",
                $this->phpelement->getSourceFile(), $this->phpelement->getSourceLine());
        }

        $this->_oldMode = $codewriter->setTalesMode($mode);
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->setTalesMode($this->_oldMode);
    }

    private $_oldMode;
}
