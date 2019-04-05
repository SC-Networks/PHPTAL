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

class TalOmitTagTest extends \Tests\Testcase\PhpTal
{
    /**
     * @var int
     */
    private $call_count = 0;


    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/tal-omit-tag.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-omit-tag.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testWithCondition()
    {
        $tpl = $this->newPHPTAL('input/tal-omit-tag.02.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-omit-tag.02.html');
        $this->assertEquals($exp, $res);
    }

    public function callCount()
    {
        $this->call_count++;
    }

    public function testCalledOnlyOnce()
    {
        $this->call_count=0;
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:omit-tag="test/callCount" />');

        $tpl->test = $this;
        $tpl->execute();
        $this->assertEquals(1, $this->call_count);

        $tpl->execute();
        $this->assertEquals(2, $this->call_count);
    }

    public function testNestedConditions()
    {
        $this->call_count=0;
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<span tal:omit-tag="true">a<span tal:omit-tag="false">b<span tal:omit-tag="true">c<span tal:omit-tag="false">d<span tal:omit-tag="false">e<span tal:omit-tag="true">f<span tal:omit-tag="true">g</span>h</span>i</span>j</span>k</span></span></span>');

        $this->assertEquals('a<span>bc<span>d<span>efghi</span>j</span>k</span>', $tpl->execute());
    }
}
