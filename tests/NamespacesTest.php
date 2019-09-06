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
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;
use Tests\Testhelper\TalNamespace;

class NamespacesTest extends PhpTalTestCase
{
    public function testTalAlias(): void
    {
        $exp = Helper::normalizeHtmlFile('output/namespaces.01.html');
        $tpl = $this->newPHPTAL('input/namespaces.01.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }

    public function testInherit(): void
    {
        $exp = Helper::normalizeHtmlFile('output/namespaces.02.html');
        $tpl = $this->newPHPTAL('input/namespaces.02.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }

    public function testOverwrite(): void
    {
        $res = $this->newPHPTAL('input/namespaces.03.html')->execute();
        static::assertSame(Helper::normalizeHtmlFile('output/namespaces.03.html'), Helper::normalizeHtml($res));
    }

    public function testOverwriteBuiltinNamespace(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src = '<metal:block xmlns:metal="non-zope" metal:use-macro="just kidding">ok</metal:block>');
        static::assertSame(Helper::normalizeHtml($src), Helper::normalizeHtml($tpl->execute()));
    }

    public function testNamespaceWithoutPrefix(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<metal:block xmlns:metal="non-zope">
                           <block xmlns="http://xml.zope.org/namespaces/tal" content="string:works" />
                         </metal:block>');
        static::assertSame(
            Helper::normalizeHtml('<metal:block xmlns:metal="non-zope"> works </metal:block>'),
            Helper::normalizeHtml($tpl->execute())
        );
    }

    public function testRedefineBuiltinNamespace(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<metal:block xmlns:metal="non-zope">
                           <foo:block xmlns="x" xmlns:foo="http://xml.zope.org/namespaces/tal" content="string:works" />
                           <metal:block xmlns="http://xml.zope.org/namespaces/i18n" xmlns:metal="http://xml.zope.org/namespaces/tal" metal:content="string:properly" />
                         </metal:block>');
        static::assertSame(
            Helper::normalizeHtml('<metal:block xmlns:metal="non-zope"> works properly </metal:block>'),
            Helper::normalizeHtml($tpl->execute())
        );
    }

    // different kind of namespace

    public function testPHPTALNamespaceClassRejectsEmptyNS(): void
    {
        $this->expectException(ConfigurationException::class);
        new TalNamespace('test', '');
    }

    public function testPHPTALNamespaceClassRejectsEmptyPrefix(): void
    {
        $this->expectException(ConfigurationException::class);
        new TalNamespace('', 'urn:test');
    }
}
