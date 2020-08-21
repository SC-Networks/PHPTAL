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

use stdClass;
use TestClosureFromMethod;
use Tests\Testcase\PhpTalTestCase;

class ClosureTalesValueTest extends PhpTalTestCase
{
    public function testClosureVariable(): void
    {

        $source = <<<HTML
<tal:block content="foon"/>
<tal:if condition="false">do not show</tal:if>
<tal:if condition="true">do show</tal:if>
<tal:each repeat="value array"><tal:block content="repeat/value/key"/>:<tal:block content="value"/></tal:each>
<tal:block content="use"/>
<tal:block define="varname inputvar" tal:content="varname"/>
<br class="omitme" tal:omit-tag="omitme"/>
<br class="keepme" tal:omit-tag="keepme"/>
<br tal:replace="structure replacetag"/>
<br tal:replace="keeptag"/>
<span tal:attributes="class classlist; data-empty nonvalue|string:; data-nothing nonextant|"/>
<tal:block on-error="errorhandler" content="nonextant"/>
HTML;
        $expected = <<<HTML
barn

do show
a:1b:2c:3
use
output

<br class="keepme"/>
<hr>

<span class="one two three" data-empty=""></span>
there was an error (but not really)
HTML;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);

        eval(<<<PHP
            \$tpl->foon = function () { return 'barn'; };
            \$false = function () { return false; };
            \$true = function () { return true; };
            \$tpl->false = \$false;
            \$tpl->true = \$true;
            \$tpl->array = function () { return array('a' => 1, 'b' => 2, 'c' => 3); };
            \$use = "use";
            \$tpl->use = function () use (\$use) { return \$use; };
            \$tpl->inputvar = function () { return "output"; };
            \$tpl->omitme = \$true;
            \$tpl->keepme = \$false;
            \$tpl->replacetag = function () { return "<hr>"; };
            \$tpl->keeptag = function () { return ''; };
            \$tpl->classlist = function () { return 'one two three'; };
            \$tpl->nonvalue = function () { return; };
            \$tpl->errorhandler = function () {
                return 'there was an error (but not really)';
            };
PHP
        );
        static::assertSame($expected, $tpl->execute());

        $tpl->foon = 'barn';
        $tpl->false = false;
        $tpl->true = true;
        $tpl->array = ['a' => 1, 'b' => 2, 'c' => 3];
        $tpl->use = "use";
        $tpl->inputvar = "output";
        $tpl->omitme = true;
        $tpl->keepme = false;
        $tpl->replacetag = "<hr>";
        $tpl->keeptag = '';
        $tpl->classlist = 'one two three';
        $tpl->nonvalue = null;
        $tpl->errorhandler = 'there was an error (but not really)';

        static::assertSame($expected, $tpl->execute());
    }

    public function testClosureDeep(): void
    {
        $obj = new stdClass();
        eval("\$obj->foon = function () { return 'hello'; };");

        $objobj = new stdClass();
        $objobj->obj = $obj;

        $arr = ['one' => ['two' => ['three' => $obj]]];

        $source = <<<HTML
<tal:block content="obj/foon"/>
<tal:block content="objobj/obj/foon"/>
<tal:block content="arr/one/two/three/foon"/>
HTML;
        $expected = <<<HTML
hello
hello
hello
HTML;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);

        $tpl->obj = $obj;
        $tpl->objobj = $objobj;
        $tpl->arr = $arr;

        static::assertSame($expected, $tpl->execute());
    }

    public function testNestedClosure(): void
    {

        eval("
            \$closure = function () {
                return function () {
                    return 'hello';
                };
            };
        ");

        $source = <<<HTML
<tal:block content="closure"/>
<br tal:omit-tag="closure"/>
HTML;
        $expected = <<<HTML
hello

HTML;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);

        $tpl->closure = $closure;
        static::assertSame($expected, $tpl->execute());
    }

    public function testClosureFromMethod(): void
    {

        eval(<<<PHP
            class TestClosureFromMethod {
                function closeur() {
                    return function () { return new TestClosureFromMethod; };
                    //return new TestClosureFromMethod;
                }
                function afterCloseur() {
                    return 'hello';
                }
            }
PHP
        );
        $source = <<<HTML
<tal:block content="obj/closeur/afterCloseur"/>
HTML;
        $expected = <<<HTML
hello
HTML;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $tpl->obj = new TestClosureFromMethod();

        static::assertSame($expected, $tpl->execute());
    }
}
