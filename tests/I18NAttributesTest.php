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
use Tests\Testhelper\DummyTranslator;
use Tests\Testhelper\Helper;

class I18NAttributesTest extends PhpTalTestCase
{
    public function testSingle(): void
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');

        $tpl = $this->newPHPTAL('input/i18n-attributes-01.html');
        $tpl->setTranslator($t);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/i18n-attributes-01.html');
        static::assertSame($exp, $res);
    }

    public function testTranslateDefault(): void
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');

        $tpl = $this->newPHPTAL('input/i18n-attributes-02.html');
        $tpl->setTranslator($t);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/i18n-attributes-02.html');
        static::assertSame($exp, $res);
    }

    public function testTranslateTalAttribute(): void
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');

        $tpl = $this->newPHPTAL('input/i18n-attributes-03.html');
        $tpl->sometitle = 'my-title';
        $tpl->setTranslator($t);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/i18n-attributes-03.html');
        static::assertSame($exp, $res, $tpl->getCodePath());
    }

    public function testTranslateDefaultAttributeEscape(): void
    {
        $t = new DummyTranslator();
        $t->setTranslation('my\'title', 'mon\'titre');

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div><a title="my\'title" class="my&#039;title" i18n:attributes="class;title">test</a></div>');
        $tpl->sometitle = 'my-title';
        $tpl->setTranslator($t);
        static::assertSame(
            '<div><a title="mon&#039;titre" class="mon&#039;titre">test</a></div>',
            $tpl->execute(),
            $tpl->getCodePath()
        );
    }

    public function testTranslateTalAttributeEscape(): void
    {
        $this->markTestSkipped("Hard to fix bug");

        $t = new DummyTranslator();
        $t->setTranslation('my\'title', 'mon\'titre');

        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<div><a title="foo" tal:attributes="title sometitle; class php:sometitle" i18n:attributes="class;title">test</a></div>'
        );
        $tpl->sometitle = 'my\'title';
        $tpl->setTranslator($t);
        static::assertSame(
            '<div><a title="mon&#039;titre" class="mon&#039;titre">test</a></div>',
            $tpl->execute(),
            $tpl->getCodePath()
        );
    }

    public function testMultiple(): void
    {
        $t = new DummyTranslator();
        $t->setTranslation('my-title', 'mon titre');
        $t->setTranslation('my-dummy', 'mon machin');

        $tpl = $this->newPHPTAL('input/i18n-attributes-04.html');
        $tpl->sometitle = 'my-title';
        $tpl->setTranslator($t);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/i18n-attributes-04.html');
        static::assertSame($exp, $res);
    }

    public function testInterpolation(): void
    {
        $t = new DummyTranslator();
        $t->setTranslation('foo ${someObject/method} bar ${otherObject/method} buz',
            'ok ${someObject/method} ok ${otherObject/method} ok');

        $tpl = $this->newPHPTAL('input/i18n-attributes-05.html');
        $tpl->setTranslator($t);
        $tpl->someObject = ['method' => 'good'];
        $tpl->otherObject = ['method' => 'great'];
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/i18n-attributes-05.html');
        static::assertSame($exp, $res);
    }
}
