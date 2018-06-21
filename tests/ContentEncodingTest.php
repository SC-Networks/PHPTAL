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

namespace Tests;

class ContentEncodingTest extends \Tests\Testcase\PhpTal
{
    public function testSimpleAnyForm()
    {
        $tpl = $this->newPHPTAL('input/content-encoding.xml');
        $res = $tpl->execute();
        $exp = html_entity_decode(\Tests\Testhelper\Helper::normalizeHtmlFile('output/content-encoding.xml'), ENT_QUOTES, 'UTF-8');
        $res = html_entity_decode(\Tests\Testhelper\Helper::normalizeHtml($res), ENT_QUOTES, 'UTF-8');
        $this->assertEquals($exp, $res);
    }

    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/content-encoding.xml');
        $res = $tpl->execute();
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/content-encoding.xml');
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $this->assertEquals($exp, $res);
    }

    public function testEchoArray()
    {
        $p = $this->newPHPTAL();
        $p->setSource('<p tal:content="foo"/>');
        $p->foo = array('bar'=>'a&aa', '<bbb>', null, -1, false);
        $this->assertEquals('<p>a&amp;aa, &lt;bbb&gt;, , -1, 0</p>', $p->execute());
    }

    public function testNonUTF8()
    {
        if (!function_exists('mb_convert_encoding')) $this->markTestSkipped();

        // Japanes primary 5 characters just like "ABCDE".
        $text     = mb_convert_encoding(rawurldecode("%E3%81%82%E3%81%84%E3%81%86%E3%81%88%E3%81%8A"), 'euc-jp', 'utf-8');

        $source   = '<div><p data="' . $text . '">' . $text . '</p><p><![CDATA[' . $text . '"\'&]]></p><p tal:content="text" tal:attributes="data text">here</p></div>';
        $expected = '<div><p data="' . $text . '">' . $text . '</p><p>' . $text . '&quot;&#039;&amp;</p><p data="' . $text . '">' . $text . '</p></div>';

        $p = $this->newPHPTAL();
        $p->setEncoding('euc-jp');
        $p->setSource($source);
        $p->text = $text;
        $output = $p->execute();
        $this->assertEquals($expected, $output);
    }
}
