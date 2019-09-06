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

use PhpTal\Php\TalesInternal;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class EscapeHTMLTest extends PhpTalTestCase
{

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    private function executeString(string $str, array $params = []): string
    {
        $tpl = $this->newPHPTAL();
        foreach ($params as $k => $v) {
            $tpl->set($k, $v);
        }
        $tpl->setSource($str);
        return $tpl->execute();
    }

    public function testDoesEscapeHTMLContent(): void
    {
        $tpl = $this->newPHPTAL('input/escape.html');
        $exp = Helper::normalizeHtmlFile('output/escape.html');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame($exp, $res);
    }

    public function testEntityDecodingPath1(): void
    {
        $res = $this->executeString('<div title="&quot;" class=\'&quot;\' tal:content="\'&quot; quote character\'" />');
        static::assertStringNotContainsString('&amp;', $res);
    }

    public function testEntityDecodingBeforePHP(): void
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        /* PHP block in attributes gets raw input (that's not XML style, but PHP style) */
        $res = $this->executeString(
            '<div title="${php:strlen(\'&quot;&amp;\')}">' .
            '<tal:block tal:content="php:strlen(\'&quot;&amp;\')" />,${php:strlen(\'&quot;&amp;\')}</div>'
        );
        static::assertSame('<div title="2">2,2</div>', $res);
    }

    public function testEntityEncodingAfterPHP(): void
    {
        TalesInternal::setFunctionWhitelist(['urldecode']);
        $res = $this->executeString(
            '<div title="${php:urldecode(\'%26%22%3C\')}"><tal:block tal:content="php:urldecode(\'%26%22%3C\')" />,${php:urldecode(\'%26%22%3C\')}</div>'
        );
        static::assertSame('<div title="&amp;&quot;&lt;">&amp;&quot;&lt;,&amp;&quot;&lt;</div>', $res);
    }

    public function testNoEntityEncodingAfterStructurePHP(): void
    {
        TalesInternal::setFunctionWhitelist(['urldecode']);
        $res = $this->executeString(
            '<div title="${structure php:urldecode(\'%26%20%3E%27\')}">' .
            '<tal:block tal:content="structure php:urldecode(\'%26%20%3E%22\')" />,${structure php:urldecode(\'%26%20%3E%22\')}</div>'
        );
        static::assertSame(
            '<div title="& >\'">& >",& >"</div>',
            $res
        );
    }

    public function testDecodingBeforeStructure(): void
    {
        $res = $this->executeString('<div tal:content="structure php:\'&amp; quote character\'" />');
        static::assertStringNotContainsString('&amp;', $res);
    }

    public function testEntityDecodingPHP1(): void
    {
        $res = $this->executeString('<div tal:content="php:\'&quot; quote character\'" />');
        static::assertStringNotContainsString('&amp;', $res);
    }

    public function testEntityDecodingPath2(): void
    {
        $res = $this->executeString('<div tal:attributes="title \'&quot; quote character\'" />');
        static::assertStringNotContainsString('&amp;', $res);
    }

    public function testEntityDecodingPHP2(): void
    {
        $res = $this->executeString('<div tal:attributes="title php:\'&quot; quote character\'" />');
        static::assertStringNotContainsString('&amp;', $res);
    }

    public function testEntityDecodingPath3(): void
    {
        $res = $this->executeString('<p>${\'&quot; quote character\'}</p>');
        static::assertStringNotContainsString('&amp;', $res);
    }

    public function testEntityDecodingPHP3(): void
    {
        $res = $this->executeString('<p>${php:\'&quot; quote character\'}</p>');
        static::assertStringNotContainsString('&amp;', $res);
    }


    public function testEntityEncodingPath1(): void
    {
        $res = $this->executeString('<div tal:content="\'&amp; ampersand character\'" />');
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingPHP1(): void
    {
        $res = $this->executeString('<div tal:content="php:\'&amp; ampersand character\'" />');
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingPath2(): void
    {
        $res = $this->executeString('<div tal:attributes="title \'&amp; ampersand character\'" />');
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingVariables(): void
    {
        $res = $this->executeString(
            '<div tal:attributes="title variable; class variable">${variable}${variable}</div>',
            ['variable' => '& = ampersand, " = quote, \' = apostrophe']
        );
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingAttributesDefault1(): void
    {
        $res = $this->executeString(
            '<div tal:attributes="title idontexist | default" title=\'&amp; ampersand character\' />'
        );
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingAttributesDefault2(): void
    {
        $res = $this->executeString('<div tal:attributes="title idontexist | default" title=\'&quot;&apos;\' />');
        static::assertStringNotContainsString('&amp;', $res);
        static::assertStringContainsString('&quot;', $res); // or apos...
    }

    public function testEntityEncodingPHP2(): void
    {
        $res = $this->executeString('<div tal:attributes="title php:\'&amp; ampersand character\'" />');
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingPath3(): void
    {
        $res = $this->executeString('<p>${\'&amp; ampersand character\'}</p>');
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testEntityEncodingPHP3(): void
    {
        $res = $this->executeString('<p>&{php:\'&amp; ampersand character\'}</p>');
        static::assertStringContainsString('&amp;', $res);
        static::assertStringNotContainsString('&amp;amp;', $res);
        static::assertStringNotContainsString('&amp;&amp;', $res);
    }

    public function testSimpleXML(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${x} ${y}</p>');
        $simplexml = new \SimpleXMLElement('<foo title="bar&amp;&lt;" empty="">foo&amp;&lt;</foo>');

        $tpl->x = $simplexml['title'];
        $tpl->y = $simplexml['empty'];
        static::assertSame('<p>bar&amp;&lt; </p>', $tpl->execute());
    }

    public function testStructureSimpleXML(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${structure x} ${structure y}</p>');
        $simplexml = new  \SimpleXMLElement('<foo title="bar&amp;&lt;" empty="">foo&amp;&lt;</foo>');

        $tpl->x = $simplexml['title'];
        $tpl->y = $simplexml['empty'];
        static::assertSame('<p>bar&< </p>', $tpl->execute());
    }

    public function testUnicodeUnescaped(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->World = '${World}'; // a quine! ;)
        $tpl->setSource($src = '<p>Hello “${World}!”</p>');

        static::assertSame($src, $tpl->execute());
    }
}
