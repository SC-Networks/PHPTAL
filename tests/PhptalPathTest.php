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

use PhpTal\Context;
use PhpTal\Exception\VariableNotFoundException;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\DummyClass;

class PhptalPathTest extends PhpTalTestCase
{
    public function testZeroIndex(): void
    {
        $data = [1, 0, 3];
        $result = Context::path($data, '0');
        static::assertSame(1, $result);
    }

    public function testProtectedMethodIgnored(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->obj = new DummyClass();
        $tpl->setSource('<test tal:content="obj/protTest"></test>');

        static::assertSame('<test>prot-property</test>', $tpl->execute());
    }

    public function testPublicMethodFirst(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->obj = new DummyClass();
        $tpl->setSource('<test tal:content="obj/pubTest"></test>');

        static::assertSame('<test>pub-method</test>', $tpl->execute());
    }

    public function testNestedArrays(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->arr = [
            'items' => [
                [
                    'details' => []
                ]
            ]
        ];
        $tpl->setSource('<test tal:content="arr/items/0/details/0/notfound"></test>');

        $this->expectException(VariableNotFoundException::class);
        $this->expectExceptionMessage("Array 'details' doesn");
        $tpl->execute();
    }

    public function testDefinedButNullProperty(): void
    {
        $src = <<<EOS
<span tal:content="o/foo"/>
<span tal:content="o/foo | string:blah"/>
<span tal:content="o/bar" tal:on-error="string:ok"/>
EOS;
        $exp = <<<EOS
<span></span>
<span>blah</span>
ok
EOS;

        $tpl = $this->newPHPTAL();
        $tpl->setSource($src, __FILE__);
        $tpl->o = new DummyClass();
        $res = $tpl->execute();

        static::assertSame($exp, $res);
    }
}
