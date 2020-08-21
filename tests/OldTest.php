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
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class OldTest extends PhpTalTestCase
{
    public function test03(): void
    {
        $tpl = $this->newPHPTAL('input/old-03.html');
        $tpl->title = 'My dynamic title';
        $tpl->content = '<p>my content</p>';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-03.html');
        static::assertSame($exp, $res);
    }

    public function test06(): void
    {
        $tpl = $this->newPHPTAL('input/old-06.html');
        $tpl->title = 'my title';
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-06.html');
        static::assertSame($exp, $res);
    }

    public function test08(): void
    {
        $tpl = $this->newPHPTAL('input/old-08.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-08.html');
        static::assertSame($exp, $res);
    }

    public function test11(): void
    {
        $tpl = $this->newPHPTAL('input/old-11.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-11.html');
        static::assertSame($exp, $res);
    }

    public function test12(): void
    {
        $tpl = $this->newPHPTAL('input/old-12.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-12.html');
        static::assertSame($exp, $res);
    }

    public function test13(): void  // default keyword
    {
        $tpl = $this->newPHPTAL('input/old-13.html');
        $l = new stdClass(); // DummyTag();
        $l->href = "http://www.example.com";
        $l->title = "example title";
        $l->name = "my link content";
        $tpl->a2 = "a value";
        $tpl->link2 = $l;

        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-13.html');
        static::assertSame($exp, $res);
    }

    public function test16(): void // default in attributes
    {
        $tpl = $this->newPHPTAL('input/old-16.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-16.html');
        static::assertSame($exp, $res);
    }

    public function test17(): void // test indents
    {
        $tpl = $this->newPHPTAL('input/old-17.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-17.html');
        static::assertSame($exp, $res);
    }


    public function test19(): void // attribute override
    {
        $tpl = $this->newPHPTAL('input/old-19.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-19.html');
        static::assertSame($exp, $res);
    }


    public function test20(): void // remove xmlns:tal, xmlns:phptal, xmlns:metal, xmlns:i18n
    {
        $tpl = $this->newPHPTAL('input/old-20.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-20.html');
        static::assertSame($exp, $res);
    }


    public function test21(): void // ensure xhtml reduced tags are reduced
    {
        $tpl = $this->newPHPTAL('input/old-21.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-21.html');
        static::assertSame($res, $exp);
    }

    public function test29(): void // test doctype inherited from macro
    {
        $tpl = $this->newPHPTAL('input/old-29.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-29.html');
        static::assertSame($exp, $res);
    }

    public function test30(): void // test blocks
    {
        $tpl = $this->newPHPTAL('input/old-30.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-30.html');
        static::assertSame($exp, $res);
    }

    public function test31(): void // test path evals
    {
        $a = new stdClass;
        $a->fooval = new stdClass;
        $a->fooval->b = new stdClass;
        $a->fooval->b->barval = "it's working";

        $tpl = $this->newPHPTAL('input/old-31.html');
        $tpl->a = $a;
        $tpl->foo = 'fooval';
        $tpl->bar = 'barval';
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/old-31.html');
        static::assertSame($exp, $res);
    }

    public function test32(): void // recursion
    {
        $o = [
            'title' => 'my object',
            'children' => [
                [
                    'title' => 'o.1',
                    'children' => [
                        ['title' => 'o.1.1', 'children' => []],
                        ['title' => 'o.1.2', 'children' => []],
                    ]
                ],
                ['title' => 'o.2', 'children' => []],
            ]
        ];

        $tpl = $this->newPHPTAL('input/old-32.html');
        $tpl->object = $o;
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/old-32.html');
        static::assertSame($exp, $res);
    }
}
