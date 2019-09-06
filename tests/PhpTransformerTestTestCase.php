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
use PhpTal\Php\Transformer;
use Tests\Testcase\PhpTalTestCase;

class PhpTransformerTestTestCase extends PhpTalTestCase
{
    public function testBooleanOperators(): void
    {
        static::assertSame('! $a', Transformer::transform('not a'));
        static::assertSame('$a || $b', Transformer::transform('a or b'));
        static::assertSame(
            '($a || $b) && ($z && $x) && (10 < 100)',
            Transformer::transform('(a or b) and (z and x) and (10 < 100)')
        );
    }

    public function testPathes(): void
    {
        static::assertSame('$a', Transformer::transform('a'));
        static::assertSame('$a->b', Transformer::transform('a.b'));
        static::assertSame('$a->b->c', Transformer::transform('a.b.c'));
    }

    public function testFunctionAndMethod(): void
    {
        static::assertSame('a()', Transformer::transform('a()'));
        static::assertSame('$a->b()', Transformer::transform('a.b()'));
        static::assertSame('$a->b[$c]()', Transformer::transform('a.b[c]()'));
        static::assertSame('$a->b[$c]->d()', Transformer::transform('a.b[c].d()'));
    }

    public function testArrays(): void
    {
        static::assertSame('$a[0]', Transformer::transform('a[0]'));
        static::assertSame('$a["my key"]', Transformer::transform('a["my key"]'));
        static::assertSame('$a->b[$c]', Transformer::transform('a.b[c]'));
    }

    public function testConcat(): void
    {
        static::assertSame('$a . $c . $b', Transformer::transform('a . c . b'));
        static::assertSame('"". $b', Transformer::transform('"". b'));
        static::assertSame('\'\'.$b', Transformer::transform('\'\'.b'));
    }

    public function testStrings(): void
    {
        static::assertSame('"prout"', Transformer::transform('"prout"'));
        static::assertSame("'prout'", Transformer::transform("'prout'"));
        static::assertSame(
            '"my string\" still in string"',
            Transformer::transform('"my string\" still in string"')
        );
        static::assertSame(
            "'my string\' still in string'",
            Transformer::transform("'my string\' still in string'")
        );
    }

    public function testStringParams(): void
    {
        static::assertSame('strtolower(\'AAA\')', Transformer::transform('strtolower(\'AAA\')'));
    }

    public function testEvals(): void
    {
        static::assertSame('$prefix->{$prefix->a}', trim(Transformer::transform('$a', '$prefix->'), '()'));
        static::assertSame('$a->{$b}->c', Transformer::transform('a.$b.c'));
        static::assertSame('$prefix->a->{$prefix->x->y}->z', Transformer::transform('a.{x.y}.z', '$prefix->'));
        static::assertSame('$a->{$x->y}()', Transformer::transform('a.{x.y}()'));
    }

    public function testEvals2(): void
    {
        static::assertSame(
            '$prefix->{$prefix->var} + $prefix->{$prefix->var}',
            trim(Transformer::transform('${var} + ${var}', '$prefix->'), '()')
        );
        static::assertSame(
            '$prefix->{MyClass::CONSTANT}',
            trim(Transformer::transform('${MyClass::CONSTANT}', '$prefix->'), '()')
        );
    }

    public function testOperators(): void
    {
        static::assertSame('$a + 100 / $b == $d', Transformer::transform('a + 100 / b == d'));
        static::assertSame('$a * 10.03', Transformer::transform('a * 10.03'));
    }

    public function testStatics(): void
    {
        static::assertSame(
            '$prefix->x->{MyClass::CONSTANT_UNDER6}',
            trim(Transformer::transform('x.${MyClass::CONSTANT_UNDER6}', '$prefix->'), '()')
        );
        static::assertSame('MyClass::method()', Transformer::transform('MyClass::method()'));
        static::assertSame('MyClass::CONSTANT', Transformer::transform('MyClass::CONSTANT'));
        static::assertSame('MyClass::CONSTANT_UNDER', Transformer::transform('MyClass::CONSTANT_UNDER'));
        static::assertSame('MyClass::CONSTANT_UNDER6', Transformer::transform('MyClass::CONSTANT_UNDER6'));
        static::assertSame('MyClass::ConsTant', Transformer::transform('MyClass::ConsTant'));
        static::assertSame('MyClass::$static', Transformer::transform('MyClass::$static', '$prefix->'));
        static::assertSame('MyClass::$static->foo()', Transformer::transform('MyClass::$static.foo()', '$prefix->'));
    }

    public function testStringEval(): void
    {
        static::assertSame(
            '"xxx {$prefix->a->{$prefix->b}->c[$prefix->x]} xxx"',
            Transformer::transform('"xxx ${a.$b.c[x]} xxx"', '$prefix->')
        );
    }

    public function testDefines(): void
    {
        static::assertSame('MY_DEFINE . $a->b', Transformer::transform('@MY_DEFINE . a.b'));
    }

    public function testPrefix(): void
    {
        static::assertSame('$C->a->b->c[$C->x]', Transformer::transform('a.b.c[x]', '$C->'));
        static::assertSame('$C->a->{$C->b}->c[$C->x]', Transformer::transform('a.$b.c[x]', '$C->'));
        static::assertSame(
            '"xxx {$C->a->{$C->b}->c[$C->x]} xxx"',
            Transformer::transform('"xxx ${a.$b.c[x]} xxx"', '$C->')
        );
    }

    public function testKeywords(): void
    {
        static::assertSame('true != false', Transformer::transform('true ne false'));
        static::assertSame('$test == null', Transformer::transform('test eq null'));
    }

    public function testTernaryOperator(): void
    {
        static::assertSame('($test)?true:false', Transformer::transform('(test)?true:false'));
    }

    public function testinstanceof(): void
    {
        static::assertSame('$test instanceof Foo', Transformer::transform('test instanceof Foo'));
    }

    public function testTransformInString(): void
    {
        $src = '"do not tranform this ge string lt eq"';
        static::assertSame($src, Transformer::transform($src));
        $src = "'do not tranform this ge string lt eq'";
        static::assertSame($src, Transformer::transform($src));
    }

    public function testCatchesInvalidEvaledFieldName(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:false.$0true" />');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testCatchesInvalidFieldName(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:false.0true" />');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }


    public function testCatchesInvalidVarName(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:0false" />');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testCatchesInvalidNumber(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:00..123" />');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testCatchesInvalidNumber2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:0.1.2" />');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }
}
