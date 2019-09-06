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

use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;
use Tests\Testhelper\StupidCacheTrigger;

class TriggerTest extends PhpTalTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!is_writable('.')) {
            $this->markTestSkipped();
        }

        if (file_exists('trigger.10')) {
            unlink('trigger.10');
        }
        if (file_exists('trigger.11')) {
            unlink('trigger.11');
        }
    }

    public function tearDown(): void
    {
        if (file_exists('trigger.10')) {
            unlink('trigger.10');
        }
        if (file_exists('trigger.11')) {
            unlink('trigger.11');
        }

        parent::tearDown();
    }

    public function testSimple(): void
    {
        $trigger = new StupidCacheTrigger();
        $tpl = $this->newPHPTAL('input/trigger.01.html');
        $tpl->addTrigger('someid', $trigger);
        $exp = Helper::normalizeHtmlFile('output/trigger.01.html');

        $tpl->someId = 10;
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame($exp, $res);
        static::assertTrue($trigger->isCaching);
        static::assertSame('trigger.10', $trigger->cachePath);

        $tpl->someId = 10;
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame($exp, $res);
        static::assertFalse($trigger->isCaching);
        static::assertSame('trigger.10', $trigger->cachePath);
    }
}
