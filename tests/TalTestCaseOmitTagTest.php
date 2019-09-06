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

class TalTestCaseOmitTagTest extends PhpTalTestCase
{
    /**
     * @var int
     */
    private $call_count = 0;


    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/tal-omit-tag.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-omit-tag.01.html');
        static::assertSame($exp, $res);
    }

    public function testWithCondition(): void
    {
        $tpl = $this->newPHPTAL('input/tal-omit-tag.02.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-omit-tag.02.html');
        static::assertSame($exp, $res);
    }

    public function callCount()
    {
        $this->call_count++;
    }

    public function testCalledOnlyOnce(): void
    {
        $this->call_count = 0;
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:omit-tag="test/callCount" />');

        $tpl->test = $this;
        $tpl->execute();
        static::assertSame(1, $this->call_count);

        $tpl->execute();
        static::assertSame(2, $this->call_count);
    }

    public function testNestedConditions(): void
    {
        $this->call_count = 0;
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<span tal:omit-tag="true">a<span tal:omit-tag="false">b<span tal:omit-tag="true">c<span tal:omit-tag="false">d<span tal:omit-tag="false">e<span tal:omit-tag="true">f<span tal:omit-tag="true">g</span>h</span>i</span>j</span>k</span></span></span>'
        );

        static::assertSame('a<span>bc<span>d<span>efghi</span>j</span>k</span>', $tpl->execute());
    }
}
