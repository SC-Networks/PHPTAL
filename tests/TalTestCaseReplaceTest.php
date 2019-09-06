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

class TalTestCaseReplaceTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.01.html');
        static::assertSame($exp, $res);
    }

    public function testVar(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.02.html');
        $tpl->replace = 'my replace';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.02.html');
        static::assertSame($exp, $res);
    }

    public function testStructure(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.03.html');
        $tpl->replace = '<foo><bar/></foo>';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.03.html');
        static::assertSame($exp, $res);
    }

    public function testNothing(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.04.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.04.html');
        static::assertSame($exp, $res);
    }

    public function testDefault(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.05.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.05.html');
        static::assertSame($exp, $res);
    }

    public function testChain(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.06.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.06.html');
        static::assertSame($exp, $res);
    }

    public function testBlock(): void
    {
        $tpl = $this->newPHPTAL('input/tal-replace.07.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-replace.07.html');
        static::assertSame($exp, $res);
    }

    public function testEmpty(): void
    {
        $src = <<<EOT
<root>
<span tal:replace="nullv | falsev | emptystrv | zerov | default">default</span>
<span tal:replace="nullv | falsev | emptystrv | default">default</span>
</root>
EOT;
        $exp = <<<EOT
<root>
0
<span>default</span>
</root>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src, __FILE__);
        $tpl->nullv = null;
        $tpl->falsev = false;
        $tpl->emptystrv = '';
        $tpl->zerov = 0;
        $res = $tpl->execute();
        static::assertSame(Helper::normalizeHtml($exp), Helper::normalizeHtml($res));
    }
}
