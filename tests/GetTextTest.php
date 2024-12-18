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
use PhpTal\GetTextTranslator;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class GetTextTest extends PhpTalTestCase
{
    private function getTextTranslator(): GetTextTranslator
    {
        return new GetTextTranslator();
    }

    public function testSimple(): void
    {
        $gettext = $this->getTextTranslator();
        $gettext->setLanguage('en_GB', 'en_GB.utf8');
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.01.html');
        $tpl->setTranslator($gettext);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/gettext.01.html');
        static::assertEquals($exp, $res);
    }

    public function testLang(): void
    {
        $gettext = $this->getTextTranslator();
        try {
            $gettext->setLanguage('fr_FR', 'fr_FR@euro', 'fr_FR.utf8');
        } catch (ConfigurationException $e) {
            static::markTestSkipped($e->getMessage());
        }
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.02.html');
        $tpl->setTranslator($gettext);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/gettext.02.html');
        static::assertEquals($exp, $res);
    }

    public function testInterpol(): void
    {
        $gettext = $this->getTextTranslator();
        try {
            $gettext->setLanguage('fr_FR', 'fr_FR@euro', 'fr_FR.utf8');
        } catch (ConfigurationException $e) {
            static::markTestSkipped($e->getMessage());
        }
        $gettext->setEncoding('UTF-8');
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.03.html');
        $tpl->setTranslator($gettext);
        $tpl->login = 'john';
        $tpl->lastCxDate = '2004-12-25';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/gettext.03.html');
        static::assertEquals($exp, $res);
    }

    public function testDomainChange(): void
    {
        $gettext = $this->getTextTranslator();
        $gettext->setEncoding('UTF-8');
        try {
            $gettext->setLanguage('fr_FR', 'fr_FR@euro', 'fr_FR.utf8');
        } catch (ConfigurationException $e) {
            static::markTestSkipped($e->getMessage());
        }
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->addDomain('test2', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.04.html');
        $tpl->setEncoding('UTF-8');
        $tpl->setTranslator($gettext);
        $tpl->login = 'john';
        $tpl->lastCxDate = '2004-12-25';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/gettext.04.html');
        static::assertEquals($exp, $res);
    }

    public function testSpaces(): void
    {
        $gettext = $this->getTextTranslator();
        $gettext->setLanguage('en_GB', 'en_GB.utf8');
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.05.html');
        $tpl->login = 'john smith';
        $tpl->setTranslator($gettext);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/gettext.05.html');
        static::assertEquals($exp, $res);
    }

    public function testAccentuateKeyNonCanonical(): void
    {
        $gettext = $this->getTextTranslator();
        $gettext->setLanguage('en_GB', 'en_GB.utf8');
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.06.html');
        $tpl->setTranslator($gettext);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtml('<root>
  <span>Not accentuated</span>
  <span>Accentuated key without canonicalization</span>
  <span>Accentuated key without canonicalization</span>
</root>
');
        static::assertEquals($exp, $res);
    }

    public function testQuote(): void
    {
        $gettext = $this->getTextTranslator();
        $gettext->setLanguage('en_GB', 'en_GB.utf8');
        $gettext->addDomain('test', TAL_TEST_FILES_DIR . 'locale/');
        $gettext->useDomain('test');

        $tpl = $this->newPHPTAL('input/gettext.07.html');
        $tpl->setTranslator($gettext);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/gettext.07.html');
        static::assertEquals($exp, $res);
    }
}
