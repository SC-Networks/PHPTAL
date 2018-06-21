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

class TalReplaceTest extends \Tests\Testcase\PhpTal
{
    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testVar()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.02.html');
        $tpl->replace = 'my replace';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.02.html');
        $this->assertEquals($exp, $res);
    }

    public function testStructure()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.03.html');
        $tpl->replace = '<foo><bar/></foo>';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testNothing()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.04.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.04.html');
        $this->assertEquals($exp, $res);
    }

    public function testDefault()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.05.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.05.html');
        $this->assertEquals($exp, $res);
    }

    public function testChain()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.06.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.06.html');
        $this->assertEquals($exp, $res);
    }

    public function testBlock()
    {
        $tpl = $this->newPHPTAL('input/tal-replace.07.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-replace.07.html');
        $this->assertEquals($exp, $res);
    }

    public function testEmpty()
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
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml($exp), \Tests\Testhelper\Helper::normalizeHtml($res));
    }
}
