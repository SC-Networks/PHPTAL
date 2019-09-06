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

use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\TemplateException;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\DummyTranslator;
use Tests\Testhelper\Helper;

class I18NTranslateTest extends PhpTalTestCase
{

    public function testFailsWhenTranslatorNotSet(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-translate-01.html');
        $this->expectException(ConfigurationException::class);
        $tpl->execute();
    }

    public function testStringTranslate(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-translate-01.html');
        $tpl->setTranslator(new DummyTranslator());
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-translate-01.html');
        static::assertSame($exp, $res);
    }

    public function testEvalTranslate(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-translate-02.html');
        $tpl->setTranslator(new DummyTranslator());
        $tpl->message = "my translate key &";
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-translate-02.html');
        static::assertSame($exp, $res);
    }

    public function testStructureTranslate(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator(new DummyTranslator());
        $tpl->setSource('<p i18n:translate="structure \'translate<b>this</b>\'"/>');
        static::assertSame('<p>translate<b>this</b></p>', $tpl->execute());
    }

    public function testStructureTranslate2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator(new DummyTranslator());
        $tpl->setSource('<p i18n:translate="structure">
        translate
        <b class="foo&amp;bar">
        this
        </b>
        </p>');
        static::assertSame('<p>translate <b class="foo&amp;bar"> this </b></p>', $tpl->execute());
    }

    public function testStructureTranslate3(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator($t = new DummyTranslator());
        $t->setTranslation('msg', '<b class="foo&amp;bar">translated&nbsp;key</b>');
        $tpl->var = 'msg';
        $tpl->setSource('<div>
        <p i18n:translate="var"/>
        <p i18n:translate="structure var"/>
        </div>');
        static::assertSame(Helper::normalizeHtml('<div>
        <p>&lt;b class=&quot;foo&amp;amp;bar&quot;&gt;translated&amp;nbsp;key&lt;/b&gt;</p>
        <p><b class="foo&amp;bar">translated&nbsp;key</b></p>
        </div>'), Helper::normalizeHtml($tpl->execute()));
    }


    public function testDomain(): void
    {
        $tpl = $this->newPHPTAL();

        $tpl->bar = 'baz';

        $tpl->setTranslator($t = new DummyTranslator());
        $tpl->t = $t;

        $tpl->setSource('<div i18n:domain="foo${bar}$${quz}">${t/domain}</div>');
        static::assertSame(Helper::normalizeHtml('<div>foobaz${quz}</div>'), Helper::normalizeHtml($tpl->execute()));

    }

    public function testTranslateChain(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator($t = new DummyTranslator());
        $t->setTranslation('bar', '<bar> translated');

        $tpl->setSource('<div i18n:translate="foo | string:bar">not translated</div>');

        static::assertSame('<div>&lt;bar&gt; translated</div>', $tpl->execute());
    }

    public function testTranslateChainString(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator($t = new DummyTranslator());

        $tpl->setSource('<div i18n:translate="foo | string:&lt;bar> translated">not translated</div>');

        static::assertSame('<div>&lt;bar&gt; translated</div>', $tpl->execute());
    }

    public function testTranslateChainExists(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator($t = new DummyTranslator());
        $tpl->foo = '<foo> value';

        $tpl->setSource('<div i18n:translate="foo | string:&lt;bar> translated">not translated</div>');

        static::assertSame('<div>&lt;foo&gt; value</div>', $tpl->execute());
    }

    public function testTranslateChainExistsTranslated(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator($t = new DummyTranslator());
        $t->setTranslation('<foo> value', '<foo> translated');

        $tpl->foo = '<foo> value';

        $tpl->setSource('<div i18n:translate="foo | string:&lt;bar> translated">not translated</div>');

        static::assertSame('<div>&lt;foo&gt; translated</div>', $tpl->execute());
    }

    public function testRejectsEmptyKey(): void
    {
        $this->expectException(TemplateException::class);
        $this->newPHPTAL()->setTranslator(new DummyTranslator())->setSource('<div i18n:translate=""></div>')->execute();
    }

    public function testRejectsEmptyKeyMarkup(): void
    {
        $this->expectException(TemplateException::class);
        $this->newPHPTAL()->setTranslator(new DummyTranslator())->setSource(
            '<div i18n:translate=""> <span tal:content="string:test"> </span> </div>'
        )->execute();
    }


    public function testTranslateChainStructureExistsTranslated(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator($t = new DummyTranslator());
        $t->setTranslation('<foo> value', '<foo> translated');

        $tpl->foo = '<foo> value';

        $tpl->setSource('<div i18n:translate="structure foo | string:&lt;bar> translated">not translated</div>');

        static::assertSame('<div><foo> translated</div>', $tpl->execute());
    }
}
