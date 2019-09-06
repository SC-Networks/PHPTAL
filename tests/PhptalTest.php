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
use PhpTal\Exception\IOException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Exception\TemplateException;
use PhpTal\PHPTAL;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class PhptalTest extends PhpTalTestCase
{
    public function test01(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.01.html');
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        static::assertSame('<dummy/>', $res);
    }

    public function testXmlHeader(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.02.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/phptal.02.html');
        static::assertSame($exp, $res);
    }

    public function testExceptionNoEcho(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.03.html');
        ob_start();
        try {
            $res = $tpl->execute();
        } catch (\Exception $e) {
        }
        $c = ob_get_contents();
        ob_end_clean();
        static::assertSame('', $c);
    }

    public function testRepositorySingle(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.01.html');
        $tpl->setTemplateRepository('input');
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        static::assertSame('<dummy/>', $res);
    }

    public function testRepositorySingleWithSlash(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.01.html');
        $tpl->setTemplateRepository('input/');
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        static::assertSame('<dummy/>', $res);
    }

    public function testRepositoryMuliple(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.01.html');
        $tpl->setTemplateRepository(['bar', 'input/']);
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        static::assertSame('<dummy/>', $res);
    }

    public function testSetTemplate(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplateRepository(['bar', TAL_TEST_FILES_DIR . 'input/']);
        $tpl->setOutputMode(PHPTAL::XML);
        $tpl->setTemplate('phptal.01.html');
        $res = $tpl->execute();
        static::assertSame('<dummy/>', $res);
    }

    public function testXmlMode(): void
    {
        $tpl = $this->newPHPTAL('input/xml.04.xml');
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        $exp = file_get_contents(TAL_TEST_FILES_DIR . 'input/xml.04.xml');
        $this->assertXMLEquals($exp, $res);
    }

    public function testSource(): void
    {
        $source = '<span tal:content="foo"/>';
        $tpl = $this->newPHPTAL();
        $tpl->foo = 'foo value';
        $tpl->setSource($source);
        $res = $tpl->execute();
        static::assertSame('<span>foo value</span>', $res);

        static::assertRegExp('/^tpl_\d{8}_/', $tpl->getFunctionName());
        static::assertStringContainsString('string', $tpl->getFunctionName());
        static::assertStringNotContainsString(PHPTAL::PHPTAL_VERSION, $tpl->getFunctionName());
    }

    public function testSourceWithPath(): void
    {
        $source = '<span tal:content="foo"/>';
        $tpl = $this->newPHPTAL();
        $tpl->foo = 'foo value';
        $tpl->setSource($source, $fakename = 'abc12345');
        $res = $tpl->execute();
        static::assertSame('<span>foo value</span>', $res);
        static::assertRegExp('/^tpl_\d{8}_/', $tpl->getFunctionName());
        static::assertStringContainsString($fakename, $tpl->getFunctionName());
        static::assertStringNotContainsString(PHPTAL::PHPTAL_VERSION, $tpl->getFunctionName());
    }

    public function testStripComments(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.04.html');
        $exp = Helper::normalizeHtmlFile('output/phptal.04.html');
        $tpl->stripComments(true);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }

    public function testStripCommentsReset(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.04.html');
        $exp = Helper::normalizeHtmlFile('output/phptal.04.html');
        $tpl->stripComments(false);
        $tpl->stripComments(true);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }

    public function testStripCommentsUnset(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.04.html');
        $exp = Helper::normalizeHtmlFile('input/phptal.04.html');
        $tpl->stripComments(true);
        $tpl->stripComments(false);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }

    public function testUnknownOutputMode(): void
    {
        try {
            $tpl = $this->newPHPTAL();
            $tpl->setOutputMode(999);
            static::assertTrue(false);
        } catch (PhpTalException $e) {
            static::assertTrue(true);
        }
    }

    public function testZeroedContent(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.05.html');
        $res = $tpl->execute();
        $exp = Helper::normalizeHtmlFile('input/phptal.05.html');
        static::assertSame($exp, $res);
    }

    public function testOnlineExpression(): void
    {
        $tpl = $this->newPHPTAL('input/phptal.06.html');
        $tpl->foo = '<p>hello</p>';
        $res = $tpl->execute();
        $exp = Helper::normalizeHtmlFile('output/phptal.06.html');
        static::assertSame($exp, $res);
    }

    public function testDirAsTemplate(): void
    {
        $tpl = $this->newPHPTAL(__DIR__);
        $this->expectException(IOException::class);
        $tpl->execute();
    }

    public function testEncodingUppercase(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setEncoding('utf-8');
        static::assertSame('UTF-8', $tpl->getEncoding());
    }

    public function testPHPParseErrorDoesNotStopPHPTAL2(): void
    {
        $this->expectException(TemplateException::class);
        $tpl = $this->newPHPTAL()->setSource('<x tal:content="php:\'deliberate parse\' \'error test\'"/>');

        ob_start();
        echo "\n" . __CLASS__ . "::testPHPParseErrorDoesNotStopPHPTAL2 failed\n";
        try {
            @$tpl->execute(); // if test dies for no apparent reason, the reason is '@'
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        ob_end_clean();
    }

    public function testThrowsIfNoTemplate(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->newPHPTAL()->execute();
    }

    public function testDoctypeWithClone(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplate(TAL_TEST_FILES_DIR . 'input/phptal.07.html');
        $tpl->execute();

        $tpl2 = clone $tpl;

        $tpl2->setTemplate(TAL_TEST_FILES_DIR . 'input/phptal.09.html');
        $res = $tpl2->execute();

        static::assertStringContainsString('DOCTYPE', $res);
    }

    public function testDoctypeWithoutClone(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplate(TAL_TEST_FILES_DIR . 'input/phptal.07.html');
        $tpl->execute();

        $tpl->setTemplate(TAL_TEST_FILES_DIR . 'input/phptal.09.html');
        $res = $tpl->execute();

        static::assertStringContainsString('DOCTYPE', $res);
    }
}
