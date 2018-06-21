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

class TalesExistTest extends \Tests\Testcase\PhpTal
{
    public function testLevel1()
    {
        $tpl = $this->newPHPTAL('input/tales-exist-01.html');
        $tpl->foo = 1;
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tales-exist-01.html');
        $this->assertEquals($exp, $res, $tpl->getCodePath());
    }

    public function testLevel2()
    {
        $o = new \stdClass();
        $o->foo = 1;
        $tpl = $this->newPHPTAL('input/tales-exist-02.html');
        $tpl->o = $o;
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tales-exist-02.html');
        $this->assertEquals($exp, $res);
    }
}
