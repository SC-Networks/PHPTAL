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

class I18NNameTest extends PhpTalTestCase
{
    public function testSet(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-name-01.html');
        $tpl->setTranslator(new DummyTranslator());
        $res = $tpl->execute();
        static::assertTrue(array_key_exists('test', $tpl->getTranslator()->vars));
        static::assertSame('test value', $tpl->getTranslator()->vars['test']);
    }

    public function testInterpolation(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-name-02.html');
        $tpl->setTranslator(new DummyTranslator());
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-name-02.html');
        static::assertSame($exp, $res);
    }

    public function testMultipleInterpolation(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-name-03.html');
        $tpl->setTranslator(new DummyTranslator());
        $tpl->mylogin_var = '<mylogin>';

        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-name-03.html');
        static::assertSame($exp, $res, $tpl->getCodePath());
    }

    public function testBlock(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-name-04.html');
        $tpl->setTranslator(new DummyTranslator());
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-name-04.html');
        static::assertSame($exp, $res);
    }

    public function testI18NBlock(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-name-05.html');
        $tpl->setTranslator(new DummyTranslator());
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-name-05.html');
        static::assertSame($exp, $res);
    }

    public function testNamespace(): void
    {
        $tpl = $this->newPHPTAL('input/i18n-name-06.html');
        $tpl->username = 'john';
        $tpl->mails = 100;
        $tpl->setTranslator(new DummyTranslator());
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/i18n-name-06.html');
        static::assertSame($exp, $res);
    }
}
