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
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */



class PhpTransformerTest extends PHPTAL_TestCase
{
    function testBooleanOperators()
    {
        $this->assertEquals('! $a', \PhpTal\Php\Transformer::transform('not a'));
        $this->assertEquals('$a || $b', \PhpTal\Php\Transformer::transform('a or b'));
        $this->assertEquals('($a || $b) && ($z && $x) && (10 < 100)', \PhpTal\Php\Transformer::transform('(a or b) and (z and x) and (10 < 100)'));
    }

    function testPathes()
    {
        $this->assertEquals('$a', \PhpTal\Php\Transformer::transform('a'));
        $this->assertEquals('$a->b', \PhpTal\Php\Transformer::transform('a.b'));
        $this->assertEquals('$a->b->c', \PhpTal\Php\Transformer::transform('a.b.c'));
    }

    function testFunctionAndMethod()
    {
        $this->assertEquals('a()', \PhpTal\Php\Transformer::transform('a()'));
        $this->assertEquals('$a->b()', \PhpTal\Php\Transformer::transform('a.b()'));
        $this->assertEquals('$a->b[$c]()', \PhpTal\Php\Transformer::transform('a.b[c]()'));
        $this->assertEquals('$a->b[$c]->d()', \PhpTal\Php\Transformer::transform('a.b[c].d()'));
    }

    function testArrays()
    {
        $this->assertEquals('$a[0]', \PhpTal\Php\Transformer::transform('a[0]'));
        $this->assertEquals('$a["my key"]', \PhpTal\Php\Transformer::transform('a["my key"]'));
        $this->assertEquals('$a->b[$c]', \PhpTal\Php\Transformer::transform('a.b[c]'));
    }

    function testConcat()
    {
        $this->assertEquals('$a . $c . $b', \PhpTal\Php\Transformer::transform('a . c . b'));
        $this->assertEquals('"". $b', \PhpTal\Php\Transformer::transform('"". b'));
        $this->assertEquals('\'\'.$b', \PhpTal\Php\Transformer::transform('\'\'.b'));
    }

    function testStrings()
    {
        $this->assertEquals('"prout"', \PhpTal\Php\Transformer::transform('"prout"'));
        $this->assertEquals("'prout'", \PhpTal\Php\Transformer::transform("'prout'"));
        $this->assertEquals('"my string\" still in string"',
                            \PhpTal\Php\Transformer::transform('"my string\" still in string"'));
        $this->assertEquals("'my string\' still in string'",
                            \PhpTal\Php\Transformer::transform("'my string\' still in string'"));
    }

    function testStringParams()
    {
        $this->assertEquals('strtolower(\'AAA\')',
                            \PhpTal\Php\Transformer::transform('strtolower(\'AAA\')')
                           );
    }

    function testEvals()
    {
        $this->assertEquals('$prefix->{$prefix->a}', trim(\PhpTal\Php\Transformer::transform('$a', '$prefix->'), '()'));
        $this->assertEquals('$a->{$b}->c', \PhpTal\Php\Transformer::transform('a.$b.c'));
        $this->assertEquals('$prefix->a->{$prefix->x->y}->z', \PhpTal\Php\Transformer::transform('a.{x.y}.z', '$prefix->'));
        $this->assertEquals('$a->{$x->y}()', \PhpTal\Php\Transformer::transform('a.{x.y}()'));
    }

    function testEvals2()
    {
        $this->assertEquals('$prefix->{$prefix->var} + $prefix->{$prefix->var}', trim(\PhpTal\Php\Transformer::transform('${var} + ${var}', '$prefix->'), '()'));
        $this->assertEquals('$prefix->{MyClass::CONSTANT}', trim(\PhpTal\Php\Transformer::transform('${MyClass::CONSTANT}', '$prefix->'), '()'));
    }

    function testOperators()
    {
        $this->assertEquals('$a + 100 / $b == $d', \PhpTal\Php\Transformer::transform('a + 100 / b == d'));
        $this->assertEquals('$a * 10.03', \PhpTal\Php\Transformer::transform('a * 10.03'));
    }

    function testStatics()
    {
        $this->assertEquals('$prefix->x->{MyClass::CONSTANT_UNDER6}', trim(\PhpTal\Php\Transformer::transform('x.${MyClass::CONSTANT_UNDER6}', '$prefix->'), '()'));
        $this->assertEquals('MyClass::method()', \PhpTal\Php\Transformer::transform('MyClass::method()'));
        $this->assertEquals('MyClass::CONSTANT', \PhpTal\Php\Transformer::transform('MyClass::CONSTANT'));
        $this->assertEquals('MyClass::CONSTANT_UNDER', \PhpTal\Php\Transformer::transform('MyClass::CONSTANT_UNDER'));
        $this->assertEquals('MyClass::CONSTANT_UNDER6', \PhpTal\Php\Transformer::transform('MyClass::CONSTANT_UNDER6'));
        $this->assertEquals('MyClass::ConsTant', \PhpTal\Php\Transformer::transform('MyClass::ConsTant'));
        $this->assertEquals('MyClass::$static', \PhpTal\Php\Transformer::transform('MyClass::$static', '$prefix->'));
        $this->assertEquals('MyClass::$static->foo()', \PhpTal\Php\Transformer::transform('MyClass::$static.foo()', '$prefix->'));
    }

    function testStringEval()
    {
        $this->assertEquals('"xxx {$prefix->a->{$prefix->b}->c[$prefix->x]} xxx"', \PhpTal\Php\Transformer::transform('"xxx ${a.$b.c[x]} xxx"', '$prefix->'));
    }

    function testDefines()
    {
        $this->assertEquals('MY_DEFINE . $a->b', \PhpTal\Php\Transformer::transform('@MY_DEFINE . a.b'));
    }

    function testPrefix()
    {
        $this->assertEquals('$C->a->b->c[$C->x]', \PhpTal\Php\Transformer::transform('a.b.c[x]', '$C->'));
        $this->assertEquals('$C->a->{$C->b}->c[$C->x]', \PhpTal\Php\Transformer::transform('a.$b.c[x]', '$C->'));
        $this->assertEquals('"xxx {$C->a->{$C->b}->c[$C->x]} xxx"', \PhpTal\Php\Transformer::transform('"xxx ${a.$b.c[x]} xxx"', '$C->'));
    }

    function testKeywords()
    {
        $this->assertEquals('true != false', \PhpTal\Php\Transformer::transform('true ne false'));
        $this->assertEquals('$test == null', \PhpTal\Php\Transformer::transform('test eq null'));
    }

    function testTernaryOperator()
    {
        $this->assertEquals('($test)?true:false', \PhpTal\Php\Transformer::transform('(test)?true:false'));
    }

    function testinstanceof()
    {
        $this->assertEquals('$test instanceof Foo', \PhpTal\Php\Transformer::transform('test instanceof Foo'));
    }

    function testTransformInString()
    {
        $src = '"do not tranform this ge string lt eq"';
        $this->assertEquals($src, \PhpTal\Php\Transformer::transform($src));
        $src = "'do not tranform this ge string lt eq'";
        $this->assertEquals($src, \PhpTal\Php\Transformer::transform($src));
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    function testCatchesInvalidEvaledFieldName()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:user.$0_invalid_" />');
        $tpl->execute();
        $this->fail();
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    function testCatchesInvalidFieldName()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:user.0_invalid_" />');
        $tpl->execute();
        $this->fail();
    }


    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    function testCatchesInvalidVarName()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:0_invalid_" />');
        $tpl->execute();
        $this->fail();
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    function testCatchesInvalidNumber()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:00..123" />');
        $tpl->execute();
        $this->fail();
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    function testCatchesInvalidNumber2()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="php:0.1.2" />');
        $tpl->execute();
        $this->fail();
    }
}

