<?php

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace Tests;

use PhpTal\Exception\ConfigurationException;
use Tests\Testhelper\DummyTranslator;

class I18NTranslateTest extends \Tests\Testcase\PhpTal
{

    public function testFailsWhenTranslatorNotSet()
    {
        $tpl = $this->newPHPTAL('input/i18n-translate-01.html');
        $this->expectException(ConfigurationException::class);
        $tpl->execute();
    }

    public function testStringTranslate()
    {
        $tpl = $this->newPHPTAL('input/i18n-translate-01.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-translate-01.html');
        $this->assertEquals($exp, $res);
    }

    public function testEvalTranslate()
    {
        $tpl = $this->newPHPTAL('input/i18n-translate-02.html');
        $tpl->setTranslator( new DummyTranslator() );
        $tpl->message = "my translate key &";
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-translate-02.html');
        $this->assertEquals($exp, $res);
    }

    public function testStructureTranslate()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( new DummyTranslator() );
        $tpl->setSource('<p i18n:translate="structure \'translate<b>this</b>\'"/>');
        $this->assertEquals('<p>translate<b>this</b></p>', $tpl->execute());
    }

    public function testStructureTranslate2()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( new DummyTranslator() );
        $tpl->setSource('<p i18n:translate="structure">
        translate
        <b class="foo&amp;bar">
        this
        </b>
        </p>');
        $this->assertEquals('<p>translate <b class="foo&amp;bar"> this </b></p>', $tpl->execute());
    }

    public function testStructureTranslate3()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( $t = new DummyTranslator() );
        $t->setTranslation('msg', '<b class="foo&amp;bar">translated&nbsp;key</b>');
        $tpl->var = 'msg';
        $tpl->setSource('<div>
        <p i18n:translate="var"/>
        <p i18n:translate="structure var"/>
        </div>');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div>
        <p>&lt;b class=&quot;foo&amp;amp;bar&quot;&gt;translated&amp;nbsp;key&lt;/b&gt;</p>
        <p><b class="foo&amp;bar">translated&nbsp;key</b></p>
        </div>'), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }


    public function testDomain()
    {
        $tpl = $this->newPHPTAL();

        $tpl->bar = 'baz';

        $tpl->setTranslator( $t = new DummyTranslator() );
        $tpl->t = $t;

        $tpl->setSource('<div i18n:domain="foo${bar}$${quz}">${t/domain}</div>');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div>foobaz${quz}</div>'), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));

    }

    public function testTranslateChain()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( $t = new DummyTranslator() );
        $t->setTranslation('bar', '<bar> translated');

        $tpl->setSource('<div i18n:translate="foo | string:bar">not translated</div>');

        $this->assertEquals('<div>&lt;bar&gt; translated</div>', $tpl->execute());
    }

    public function testTranslateChainString()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( $t = new DummyTranslator() );

        $tpl->setSource('<div i18n:translate="foo | string:&lt;bar> translated">not translated</div>');

        $this->assertEquals('<div>&lt;bar&gt; translated</div>', $tpl->execute());
    }

    public function testTranslateChainExists()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( $t = new DummyTranslator() );
        $tpl->foo = '<foo> value';

        $tpl->setSource('<div i18n:translate="foo | string:&lt;bar> translated">not translated</div>');

        $this->assertEquals('<div>&lt;foo&gt; value</div>', $tpl->execute());
    }

    public function testTranslateChainExistsTranslated()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( $t = new DummyTranslator() );
        $t->setTranslation('<foo> value', '<foo> translated');

        $tpl->foo = '<foo> value';

        $tpl->setSource('<div i18n:translate="foo | string:&lt;bar> translated">not translated</div>');

        $this->assertEquals('<div>&lt;foo&gt; translated</div>', $tpl->execute());
    }

    /**
     * @expectedException \PhpTal\Exception\TemplateException
     */
    public function testRejectsEmptyKey()
    {
        $this->newPHPTAL()->setTranslator( $t = new DummyTranslator() )->setSource('<div i18n:translate=""></div>')->execute();
    }

    /**
     * @expectedException \PhpTal\Exception\TemplateException
     */
    public function testRejectsEmptyKeyMarkup()
    {
        $this->newPHPTAL()->setTranslator( $t = new DummyTranslator() )->setSource('<div i18n:translate=""> <span tal:content="string:test"> </span> </div>')->execute();
    }


    public function testTranslateChainStructureExistsTranslated()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTranslator( $t = new DummyTranslator() );
        $t->setTranslation('<foo> value', '<foo> translated');

        $tpl->foo = '<foo> value';

        $tpl->setSource('<div i18n:translate="structure foo | string:&lt;bar> translated">not translated</div>');

        $this->assertEquals('<div><foo> translated</div>', $tpl->execute());
    }
}
