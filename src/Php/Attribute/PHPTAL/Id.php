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
class Id extends \PhpTal\Php\Attribute
{
    private $var;
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        // retrieve trigger
        $this->var = $codewriter->createTempVariable();

        $codewriter->doSetVar(
            $this->var,
            '$tpl->getTrigger('.$codewriter->str($this->expression).')'
        );

        // if trigger found and trigger tells to proceed, we execute
        // the node content
        $codewriter->doIf($this->var.' &&
            '.$this->var.'->start('.$codewriter->str($this->expression).', $tpl) === \PhpTal\Trigger::PROCEED');
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        // end of if PROCEED
        $codewriter->doEnd('if');

        // if trigger found, notify the end of the node
        $codewriter->doIf($this->var);
        $codewriter->pushCode(
            $this->var.'->end('.$codewriter->str($this->expression).', $tpl)'
        );
        $codewriter->doEnd('if');
        $codewriter->recycleTempVariable($this->var);
    }
}
