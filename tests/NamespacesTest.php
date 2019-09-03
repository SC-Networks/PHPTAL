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
use Tests\Testhelper\Test_PHPTAL_Namespace;

class NamespacesTest extends \Tests\Testcase\PhpTal
{
    public function testTalAlias()
    {
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/namespaces.01.html');
        $tpl = $this->newPHPTAL('input/namespaces.01.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $this->assertEquals($exp, $res);
    }

    public function testInherit()
    {
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/namespaces.02.html');
        $tpl = $this->newPHPTAL('input/namespaces.02.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $this->assertEquals($exp, $res);
    }

    public function testOverwrite()
    {
        $res = $this->newPHPTAL('input/namespaces.03.html')->execute();
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtmlFile('output/namespaces.03.html'), \Tests\Testhelper\Helper::normalizeHtml($res));
    }

    public function testOverwriteBuiltinNamespace()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src='<metal:block xmlns:metal="non-zope" metal:use-macro="just kidding">ok</metal:block>');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml($src), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    public function testNamespaceWithoutPrefix()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<metal:block xmlns:metal="non-zope">
                           <block xmlns="http://xml.zope.org/namespaces/tal" content="string:works" />
                         </metal:block>');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<metal:block xmlns:metal="non-zope"> works </metal:block>'),
                            \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    public function testRedefineBuiltinNamespace()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<metal:block xmlns:metal="non-zope">
                           <foo:block xmlns="x" xmlns:foo="http://xml.zope.org/namespaces/tal" content="string:works" />
                           <metal:block xmlns="http://xml.zope.org/namespaces/i18n" xmlns:metal="http://xml.zope.org/namespaces/tal" metal:content="string:properly" />
                         </metal:block>');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<metal:block xmlns:metal="non-zope"> works properly </metal:block>'),
                            \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    // different kind of namespace

    public function testPHPTALNamespaceClassRejectsEmptyNS()
    {
        $this->expectException(ConfigurationException::class);
        new Test_PHPTAL_Namespace('test', '');
    }

    public function testPHPTALNamespaceClassRejectsEmptyPrefix()
    {
        $this->expectException(ConfigurationException::class);
        new Test_PHPTAL_Namespace('', 'urn:test');
    }
}
