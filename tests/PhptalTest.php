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
 * @link     http://phptal.org/
 */

declare(strict_types=1);

class PhptalTest extends PHPTAL_TestCase
{
    function test01()
    {
        $tpl = $this->newPHPTAL('input/phptal.01.html');
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $res = $tpl->execute();
        $this->assertEquals('<dummy/>', $res);
    }

    function testXmlHeader()
    {
        $tpl = $this->newPHPTAL('input/phptal.02.html');
        $res = normalize_html($tpl->execute());
        $exp = normalize_html_file('output/phptal.02.html');
        $this->assertEquals($exp, $res);
    }

    function testExceptionNoEcho()
    {
        $tpl = $this->newPHPTAL('input/phptal.03.html');
        ob_start();
        try {
            $res = $tpl->execute();
        }
        catch (Exception $e)
        {
        }
        $c = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('', $c);
    }

    function testRepositorySingle()
    {
        $tpl = $this->newPHPTAL('phptal.01.html');
        $tpl->setTemplateRepository('input');
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $res = $tpl->execute();
        $this->assertEquals('<dummy/>', $res);
    }

    function testRepositorySingleWithSlash()
    {
        $tpl = $this->newPHPTAL('phptal.01.html');
        $tpl->setTemplateRepository('input/');
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $res = $tpl->execute();
        $this->assertEquals('<dummy/>', $res);
    }

    function testRepositoryMuliple()
    {
        $tpl = $this->newPHPTAL('phptal.01.html');
        $tpl->setTemplateRepository(array('bar', 'input/'));
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $res = $tpl->execute();
        $this->assertEquals('<dummy/>', $res);
    }

    function testSetTemplate()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplateRepository(array('bar', 'input/'));
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $tpl->setTemplate('phptal.01.html');
        $res = $tpl->execute();
        $this->assertEquals('<dummy/>', $res);
    }

    function testXmlMode()
    {
        $tpl = $this->newPHPTAL('input/xml.04.xml');
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $res = $tpl->execute();
        $exp = file_get_contents('input/xml.04.xml');
        $this->assertXMLEquals($exp, $res);
    }

    function testSource()
    {
        $source = '<span tal:content="foo"/>';
        $tpl = $this->newPHPTAL();
        $tpl->foo = 'foo value';
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->assertEquals('<span>foo value</span>', $res);

        $this->assertRegExp('/^tpl_\d{8}_/', $tpl->getFunctionName());
        $this->assertContains('string', $tpl->getFunctionName());
        $this->assertNotContains(\PhpTal\PHPTAL::PHPTAL_VERSION, $tpl->getFunctionName());
    }

    /**
     * @todo: write it :)
     */
    function testFunctionNameChangesWhenSettingsChange()
    {
        $this->markTestIncomplete();
    }

    function testSourceWithPath()
    {
        $source = '<span tal:content="foo"/>';
        $tpl = $this->newPHPTAL();
        $tpl->foo = 'foo value';
        $tpl->setSource($source, $fakename = 'abc12345');
        $res = $tpl->execute();
        $this->assertEquals('<span>foo value</span>', $res);
        $this->assertRegExp('/^tpl_\d{8}_/', $tpl->getFunctionName());
        $this->assertContains($fakename, $tpl->getFunctionName());
        $this->assertNotContains(\PhpTal\PHPTAL::PHPTAL_VERSION, $tpl->getFunctionName());
    }

    function testStripComments()
    {
        $tpl = $this->newPHPTAL('input/phptal.04.html');
        $exp = normalize_html_file('output/phptal.04.html');
        $tpl->stripComments(true);
        $res = $tpl->execute();
        $res = normalize_html($res);
        $this->assertEquals($exp, $res);
    }

    function testStripCommentsReset()
    {
        $tpl = $this->newPHPTAL('input/phptal.04.html');
        $exp = normalize_html_file('output/phptal.04.html');
        $tpl->stripComments(false);
        $tpl->stripComments(true);
        $res = $tpl->execute();
        $res = normalize_html($res);
        $this->assertEquals($exp, $res);
    }

    function testStripCommentsUnset()
    {
        $tpl = $this->newPHPTAL('input/phptal.04.html');
        $exp = normalize_html_file('input/phptal.04.html');
        $tpl->stripComments(true);
        $tpl->stripComments(false);
        $res = $tpl->execute();
        $res = normalize_html($res);
        $this->assertEquals($exp, $res);
    }

    function testUnknownOutputMode()
    {
        try {
            $tpl = $this->newPHPTAL();
            $tpl->setOutputMode(999);
            $this->assertTrue(false);
        }
        catch (\PhpTal\Exception\PhpTalException $e)
        {
            $this->assertTrue(true);
        }
    }

    function testZeroedContent()
    {
        $tpl = $this->newPHPTAL('input/phptal.05.html');
        $res = $tpl->execute();
        $exp = normalize_html_file('input/phptal.05.html');
        $this->assertEquals($exp, $res);
    }

    function testOnlineExpression()
    {
        $tpl = $this->newPHPTAL('input/phptal.06.html');
        $tpl->foo = '<p>hello</p>';
        $res = $tpl->execute();
        $exp = normalize_html_file('output/phptal.06.html');
        $this->assertEquals($exp, $res);
    }

    function testDirAsTemplate()
    {
            $tpl = $this->newPHPTAL(__DIR__);
            $this->expectException(\PhpTal\Exception\IOException::class);
            $tpl->execute();
    }

    function testEncodingUppercase()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setEncoding('utf-8');
        $this->assertEquals('UTF-8', $tpl->getEncoding());
    }

    /**
     * @expectedException \PhpTal\Exception\TemplateException
     */
    function testPHPParseErrorDoesNotStopPHPTAL2()
    {
        $tpl = $this->newPHPTAL()->setSource('<x tal:content="php:\'deliberate parse\' \'error test\'"/>');

        ob_start();
        echo "\n".__CLASS__."::testPHPParseErrorDoesNotStopPHPTAL2 failed\n";
        try {
            @$tpl->execute(); // if test dies for no apparent reason, the reason is '@'
        }
        catch(\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        ob_end_clean();
    }

    /**
     * @expectedException \PhpTal\Exception\ConfigurationException
     */
    function testThrowsIfNoTemplate()
    {
        $this->newPHPTAL()->execute();
    }

    function testDoctypeWithClone()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplate('input/phptal.07.html');
        $tpl->execute();

        $tpl2 = clone $tpl;

        $tpl2->setTemplate('input/phptal.09.html');
        $res = $tpl2->execute();

        $this->assertContains('DOCTYPE',$res);
    }

    function testDoctypeWithoutClone()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplate('input/phptal.07.html');
        $tpl->execute();

        $tpl->setTemplate('input/phptal.09.html');
        $res = $tpl->execute();

        $this->assertContains('DOCTYPE',$res);
    }
}
