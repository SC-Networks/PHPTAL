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

use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Debug extends Attribute
{
    /**
     * @var bool
     */
    private $oldMode;

    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function before(CodeWriter $codewriter): void
    {
        $this->oldMode = $codewriter->setDebug(true);
    }

    /**
     * Called after element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function after(CodeWriter $codewriter): void
    {
        $codewriter->setDebug($this->oldMode);
    }
}
