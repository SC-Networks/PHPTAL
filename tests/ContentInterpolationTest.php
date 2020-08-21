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

use PhpTal\Exception\ParserException;
use PhpTal\Exception\VariableNotFoundException;
use PhpTal\Php\TalesInternal;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class ContentInterpolationTest extends PhpTalTestCase
{

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testInterpol(): void
    {
        $src = <<<EOT
<span>\${foo}</span>
EOT;
        $exp = <<<EOT
<span>foo value</span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testInterpol2(): void
    {
        $src = <<<EOT
<span>\${foo2} x \${structure foo} y \${foo}\${structure foo2}</span><img/>
EOT;
        $exp = <<<EOT
<span>{foo2 &lt;img /&gt;} x foo value y foo value{foo2 <img />}</span><img/>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $tpl->foo2 = '{foo2 <img />}';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testInterpol3(): void
    {
        $src = <<<EOT
<span>\${foo}\${foo}</span>
EOT;
        $exp = <<<EOT
<span>foo valuefoo value</span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testNoInterpol(): void
    {
        $src = <<<EOT
<span>$\${foo}</span>
EOT;
        $exp = <<<EOT
<span>\${foo}</span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testInterpolAdv(): void
    {
        $src = <<<EOT
<span>$$\${foo}</span>
EOT;
        $exp = <<<EOT
<span>\$foo value</span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testUnescape(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->var = 'val<';

        $tpl->setSource('<p>
            ${var}

            $${var}

            $$${var}

            $$$${var}
        </p>');

        static::assertSame(Helper::normalizeHtml('<p>
            val&lt;

            ${var}

            $val&lt;

            $${var}
        </p>'), Helper::normalizeHtml($tpl->execute()));
    }

    public function testUnescapeString(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->var = 'val<';

        $tpl->setSource('<p tal:content="string:
             ${var}

             $${var}

             $$${var}

             $$$${var}
         "/>');

        static::assertSame(Helper::normalizeHtml('<p>
             val&lt;

             ${var}

             $val&lt;

             $${var}
         </p>'), Helper::normalizeHtml($tpl->execute()));
    }

    public function testUnescapeStructure(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->var = 'val<x/>';

        $tpl->setSource('<p>
            ${structure var}

            $${structure var}

            $$${structure var}

            $$$${structure var}
        </p>');

        static::assertSame(Helper::normalizeHtml('<p>
            val<x/>

            ${structure var}

            $val<x/>

            $${structure var}
        </p>'), Helper::normalizeHtml($tpl->execute()));
    }

    public function testUnescapeCDATA(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->var = 'val<';

        $tpl->setSource('<script><![CDATA[<
            ${text var}

            $${text var}

            $$${var}

            $$$${var}
        ]]></script>');

        static::assertSame(Helper::normalizeHtml('<script><![CDATA[<
            val<

            ${text var}

            $val<

            $${var}
        ]]></script>'), Helper::normalizeHtml($tpl->execute()));
    }

    public function testUnescapeCDATAStructure(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->var = 'val<';

        $tpl->setSource('<script><![CDATA[<
            ${structure var}

            $${structure var}

            $$${structure var}

            $$$${structure var}
        ]]></script>');

        static::assertSame(Helper::normalizeHtml('<script><![CDATA[<
            val<

            ${structure var}

            $val<

            $${structure var}
        ]]></script>'), Helper::normalizeHtml($tpl->execute()));
    }

    public function testUnescapePHPTales(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->var = '1';

        $tpl->setSource('<p phptal:tales="php">
            ${var+1}

            $${var+1}

            $$${var+1}

            $$$${var+1}
        </p>');

        static::assertSame(Helper::normalizeHtml('<p>
            2

            ${var+1}

            $2

            $${var+1}
        </p>'), Helper::normalizeHtml($tpl->execute()));
    }

    public function testPHPBlock(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>test<?php echo "<x>"; ?>test<?php print("&amp;") ?>test</p>');
        static::assertSame('<p>test<_ echo "<x>"; ?>test<_ print("&amp;") ?>test</p>', $tpl->execute());
    }

    public function testPHPBlock54(): void
    {

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>test<? print("<x>"); ?>test<?= "&amp;" ?>test</p>');
        try
        {
            // PHP 5.4: short tag <?= is always enabled.
            static::assertSame('<p>test<? print("<x>"); ?>test<_ "&amp;" ?>test</p>', $tpl->execute());
        }
        catch(ParserException $e) {/* xml ill-formedness error is ok too */}
        ini_restore('short_open_tag');
    }

    public function testErrorsThrow(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${error}</p>');
        $this->expectException(VariableNotFoundException::class);
        $tpl->execute();
    }

    public function testErrorsThrow2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${error | error}</p>');
        $this->expectException(VariableNotFoundException::class);
        $tpl->execute();
    }

    public function testErrorsThrow3(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->foo = array();
        $tpl->setSource('<p>${foo/error | foo/error}</p>');
        $this->expectException(VariableNotFoundException::class);
        $tpl->execute();
    }

    public function testErrorsSilenced(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${error | nothing}</p>');
        static::assertSame('<p></p>', $tpl->execute());
    }

    public function testZeroIsNotEmpty(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->zero = '0';
        $tpl->setSource('<p>${zero | error}</p>');
        static::assertSame('<p>0</p>', $tpl->execute());
    }

    public function testPreservesNewline(): void
    {
        $tpl = $this->newPHPTAL()->setSource('<body>
${variable1 | string:Line 1}
<tal:block tal:content="variable2 | string:Line 2"></tal:block>
Line 3
</body>');

        static::assertSame('<body>
Line 1
Line 2
Line 3
</body>', $tpl->execute(), $tpl->getCodePath());
    }

    public function testMultilineInterpolationPHP(): void
    {
        $res = $this->newPHPTAL()->setSource('<p>${php:\'foo
        bar\'}</p>')->execute();

        static::assertSame('<p>foo
        bar</p>', $res);

        TalesInternal::setFunctionWhitelist(['substr']);

        $res = $this->newPHPTAL()->setSource('<p>${php:\'foo\' .
        substr(\'barz\' ,
        0,3)}</p>')->execute();

        static::assertSame('<p>foobar</p>', $res);
    }


    public function testMultilineInterpolation(): void
    {
        $res = $this->newPHPTAL()->setSource('<p>${string:foo
        bar}</p>')->execute();

        static::assertSame('<p>foo
        bar</p>', $res);

        $res = $this->newPHPTAL()->setSource('<p>${structure string:foo
        bar}</p>')->execute();

        static::assertSame('<p>foo
        bar</p>', $res);
    }

    public function testTagsBreakTALES(): void
    {
        $res = $this->newPHPTAL()->setSource('<p>${foo<br/>bar}</p>')->execute();

        static::assertSame('<p>${foo<br/>bar}</p>', $res);
    }

    public function testEscapedTagsDontBreakTALES(): void
    {
        $res = $this->newPHPTAL()->setSource('<p>${structure string:foo&lt;br  />bar}</p>')->execute();

        static::assertSame('<p>foo<br  />bar</p>', $res);
    }
}
