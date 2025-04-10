<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * Originally developed by Laurent Bedubourg and Kornel Lesiński
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   See contributors list @ github
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 * @link     https://github.com/SC-Networks/PHPTAL
 */

namespace Tests;

use PhpTal\Exception\PhpTalException;
use Tests\Testcase\PhpTalTestCase;

class TalesModeTest extends PhpTalTestCase
{
    public function testUnsupportedMode(): void
    {
        try {
            $tpl = $this->newPHPTAL('input/tales.mode.01.xml');
            $tpl->execute();
            static::assertTrue(false);
        } catch (PhpTalException) {
            static::assertTrue(true);
        }
    }
}
