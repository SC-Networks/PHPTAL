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

use Tests\Testhelper\DummyTranslator;

class I18NNameTest extends \Tests\Testcase\PhpTal
{
    public function testSet()
    {
        $tpl = $this->newPHPTAL('input/i18n-name-01.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $this->assertEquals(true, array_key_exists('test', $tpl->getTranslator()->vars));
        $this->assertEquals('test value', $tpl->getTranslator()->vars['test']);
    }

    public function testInterpolation()
    {
        $tpl = $this->newPHPTAL('input/i18n-name-02.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-name-02.html');
        $this->assertEquals($exp, $res);
    }

    public function testMultipleInterpolation()
    {
        $tpl = $this->newPHPTAL('input/i18n-name-03.html');
        $tpl->setTranslator( new DummyTranslator() );
        $tpl->mylogin_var = '<mylogin>';

        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-name-03.html');
        $this->assertEquals($exp, $res, $tpl->getCodePath());
    }

    public function testBlock()
    {
        $tpl = $this->newPHPTAL('input/i18n-name-04.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-name-04.html');
        $this->assertEquals($exp, $res);
    }

    public function testI18NBlock()
    {
        $tpl = $this->newPHPTAL('input/i18n-name-05.html');
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-name-05.html');
        $this->assertEquals($exp, $res);
    }

    public function testNamespace()
    {
        $tpl = $this->newPHPTAL('input/i18n-name-06.html');
        $tpl->username = 'john';
        $tpl->mails = 100;
        $tpl->setTranslator( new DummyTranslator() );
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/i18n-name-06.html');
        $this->assertEquals($exp, $res);
    }
}
