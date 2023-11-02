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

use PhpTal\Exception\PhpTalException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Id extends Attribute
{
    private ?string $var = null;

    /**
     * Called before element printing.
     *
     *
     */
    public function before(CodeWriter $codewriter): void
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
            '.$this->var.'->start('.$codewriter->str($this->expression).', $tpl) === \PhpTal\TriggerInterface::PROCEED');
    }

    /**
     * Called after element printing.
     *
     *
     * @throws PhpTalException
     */
    public function after(CodeWriter $codewriter): void
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
