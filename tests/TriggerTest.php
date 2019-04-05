<?php
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

namespace Tests;

use Tests\Testhelper\StupidCacheTrigger;

class TriggerTest extends \Tests\Testcase\PhpTal
{
    public function setUp()
    {
        parent::setUp();

        if (!is_writable('.')) $this->markTestSkipped();

        if (file_exists('trigger.10')) unlink('trigger.10');
        if (file_exists('trigger.11')) unlink('trigger.11');
    }

    public function tearDown()
    {
        if (file_exists('trigger.10')) unlink('trigger.10');
        if (file_exists('trigger.11')) unlink('trigger.11');

        parent::tearDown();
    }

    public function testSimple()
    {
        $trigger = new StupidCacheTrigger();
        $tpl = $this->newPHPTAL('input/trigger.01.html');
        $tpl->addTrigger('someid', $trigger);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/trigger.01.html');

        $tpl->someId = 10;
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $this->assertEquals($exp, $res);
        $this->assertTrue($trigger->isCaching);
        $this->assertEquals('trigger.10', $trigger->cachePath);

        $tpl->someId = 10;
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $this->assertEquals($exp, $res);
        $this->assertFalse($trigger->isCaching);
        $this->assertEquals('trigger.10', $trigger->cachePath);
    }
}
