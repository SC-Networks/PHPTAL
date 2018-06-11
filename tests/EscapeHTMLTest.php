<?php

namespace Tests;

use PhpTal\Php\TalesInternal;

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


class EscapeHTMLTest extends \PHPTAL_TestCase
{

    public function tearDown()
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    private function executeString($str, $params = array())
    {
        $tpl = $this->newPHPTAL();
        foreach ($params as $k => $v) $tpl->set($k, $v);
        $tpl->setSource($str);
        return $tpl->execute();
    }

    public function testDoesEscapeHTMLContent()
    {
        $tpl = $this->newPHPTAL('input/escape.html');
        $exp = normalize_html_file('output/escape.html');
        $res = normalize_html($tpl->execute());
        $this->assertEquals($exp, $res);
    }

    public function testEntityDecodingPath1()
    {
        $res = $this->executeString('<div title="&quot;" class=\'&quot;\' tal:content="\'&quot; quote character\'" />');
        $this->assertNotContains('&amp;', $res);
    }

    public function testEntityDecodingBeforePHP()
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        /* PHP block in attributes gets raw input (that's not XML style, but PHP style) */
        $res = $this->executeString(
            '<div title="${php:strlen(\'&quot;&amp;\')}">'.
            '<tal:block tal:content="php:strlen(\'&quot;&amp;\')" />,${php:strlen(\'&quot;&amp;\')}</div>'
        );
        $this->assertEquals('<div title="2">2,2</div>', $res);
    }

    public function testEntityEncodingAfterPHP()
    {
        TalesInternal::setFunctionWhitelist(['urldecode']);
        $res = $this->executeString('<div title="${php:urldecode(\'%26%22%3C\')}"><tal:block tal:content="php:urldecode(\'%26%22%3C\')" />,${php:urldecode(\'%26%22%3C\')}</div>');
        $this->assertEquals('<div title="&amp;&quot;&lt;">&amp;&quot;&lt;,&amp;&quot;&lt;</div>', $res);
    }

    public function testNoEntityEncodingAfterStructurePHP()
    {
        TalesInternal::setFunctionWhitelist(['urldecode']);
        $res = $this->executeString(
            '<div title="${structure php:urldecode(\'%26%20%3E%27\')}">'.
            '<tal:block tal:content="structure php:urldecode(\'%26%20%3E%22\')" />,${structure php:urldecode(\'%26%20%3E%22\')}</div>'
        );
        $this->assertEquals(
            '<div title="& >\'">& >",& >"</div>',
            $res
        );
    }

    public function testDecodingBeforeStructure()
    {
        $res = $this->executeString('<div tal:content="structure php:\'&amp; quote character\'" />');
        $this->assertNotContains('&amp;', $res);
    }

    public function testEntityDecodingPHP1()
    {
        $res = $this->executeString('<div tal:content="php:\'&quot; quote character\'" />');
        $this->assertNotContains('&amp;', $res);
    }

    public function testEntityDecodingPath2()
    {
        $res = $this->executeString('<div tal:attributes="title \'&quot; quote character\'" />');
        $this->assertNotContains('&amp;', $res);
    }

    public function testEntityDecodingPHP2()
    {
        $res = $this->executeString('<div tal:attributes="title php:\'&quot; quote character\'" />');
        $this->assertNotContains('&amp;', $res);
    }

    public function testEntityDecodingPath3()
    {
        $res = $this->executeString('<p>${\'&quot; quote character\'}</p>');
        $this->assertNotContains('&amp;', $res);
    }

    public function testEntityDecodingPHP3()
    {
        $res = $this->executeString('<p>${php:\'&quot; quote character\'}</p>');
        $this->assertNotContains('&amp;', $res);
    }


    public function testEntityEncodingPath1()
    {
        $res = $this->executeString('<div tal:content="\'&amp; ampersand character\'" />');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testEntityEncodingPHP1()
    {
        $res = $this->executeString('<div tal:content="php:\'&amp; ampersand character\'" />');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testEntityEncodingPath2()
    {
        $res = $this->executeString('<div tal:attributes="title \'&amp; ampersand character\'" />');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testEntityEncodingVariables()
    {
        $res = $this->executeString('<div tal:attributes="title variable; class variable">${variable}${variable}</div>',
                                    array('variable'=>'& = ampersand, " = quote, \' = apostrophe'));
        $this->assertContains('&amp;',$res);
        $this->assertNotContains('&amp;amp;',$res);
        $this->assertNotContains('&amp;&amp;',$res);
    }

    public function testEntityEncodingAttributesDefault1()
    {
        $res = $this->executeString('<div tal:attributes="title idontexist | default" title=\'&amp; ampersand character\' />');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testEntityEncodingAttributesDefault2()
    {
        $res = $this->executeString('<div tal:attributes="title idontexist | default" title=\'&quot;&apos;\' />');
        $this->assertNotContains('&amp;', $res);
        $this->assertContains('&quot;', $res); // or apos...
    }

    public function testEntityEncodingPHP2()
    {
        $res = $this->executeString('<div tal:attributes="title php:\'&amp; ampersand character\'" />');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testEntityEncodingPath3()
    {
        $res = $this->executeString('<p>${\'&amp; ampersand character\'}</p>');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testEntityEncodingPHP3()
    {
        $res = $this->executeString('<p>&{php:\'&amp; ampersand character\'}</p>');
        $this->assertContains('&amp;', $res);
        $this->assertNotContains('&amp;amp;', $res);
        $this->assertNotContains('&amp;&amp;', $res);
    }

    public function testSimpleXML()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${x} ${y}</p>');
        $simplexml = new \SimpleXMLElement('<foo title="bar&amp;&lt;" empty="">foo&amp;&lt;</foo>');

        $tpl->x = $simplexml['title'];
        $tpl->y = $simplexml['empty'];
        $this->assertEquals('<p>bar&amp;&lt; </p>', $tpl->execute());
    }

    public function testStructureSimpleXML()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>${structure x} ${structure y}</p>');
        $simplexml = new  \SimpleXMLElement('<foo title="bar&amp;&lt;" empty="">foo&amp;&lt;</foo>');

        $tpl->x = $simplexml['title'];
        $tpl->y = $simplexml['empty'];
        $this->assertEquals('<p>bar&< </p>', $tpl->execute());
    }

    public function testUnicodeUnescaped()
    {
        $tpl = $this->newPHPTAL();
        $tpl->World = '${World}'; // a quine! ;)
        $tpl->setSource($src = '<p>Hello “${World}!”</p>');

        $this->assertEquals($src, $tpl->execute());
    }
}
