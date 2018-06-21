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

namespace Tests;

class TalesModeTest extends \Tests\Testcase\PhpTal
{
    public function testUnsupportedMode()
    {
        try {
            $tpl = $this->newPHPTAL('input/tales.mode.01.xml');
            $tpl->execute();
            $this->assertTrue(false);
        } catch (\PhpTal\Exception\PhpTalException $e) {
            $this->assertTrue(true);
        }
    }
}
