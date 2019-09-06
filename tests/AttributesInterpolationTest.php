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

class AttributesInterpolationTest extends PhpTalTestCase
{
    public function testInterpol(): void
    {
        $src = <<<EOT
<span title="\${foo}"></span>
EOT;
        $exp = <<<EOT
<span title="foo value"></span>
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
<span title="\${foo2} x \${structure foo} y \${foo}\${structure foo2}"></span><img/>
EOT;
        $exp = <<<EOT
<span title="{foo2 &lt;img /&gt;} x foo value y foo value{foo2 <img />}"></span><img/>
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
<span title="\${foo}\${foo}1"></span>
<span tal:attributes="title string:\${foo}\${foo}2"></span>
<span tal:attributes="title '\${foo}\${foo}3'"></span>
EOT;
        $exp = <<<EOT
<span title="foo valuefoo value1"></span>
<span title="foo valuefoo value2"></span>
<span title="foo valuefoo value3"></span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testInterpol3a(): void
    {
        $src = <<<EOT
<span tal:attributes="title php:'\${foo}\${foo}'"></span>
EOT;
        $exp = <<<EOT
<span title="\${foo}\${foo}"></span>
EOT;
        $tpl = $this->newPHPTAL()->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testNoInterpol(): void
    {
        $src = <<<EOT
<span title="$\${foo}"></span>
EOT;
        $exp = <<<EOT
<span title="\${foo}"></span>
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
<span title="$$\${foo}"></span>
EOT;
        $exp = <<<EOT
<span title="\$foo value"></span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = 'foo value';
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }
}
