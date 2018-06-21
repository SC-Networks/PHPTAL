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

use Tests\Testhelper\MyTrigger;

class PhptalIdTest extends \Tests\Testcase\PhpTal
{
    public function test01()
    {
        $trigger = new MyTrigger();

        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/phptal.id.01.html');
        $tpl = $this->newPHPTAL('input/phptal.id.01.html');
        $tpl->addTrigger('myTable', $trigger);
        $tpl->result = range(0, 3);

        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);

        $this->assertEquals($exp, $res);
        $this->assertEquals(false, $trigger->useCache);

        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);

        $this->assertEquals($exp, $res);
        $this->assertEquals(true, $trigger->useCache);
    }
}
