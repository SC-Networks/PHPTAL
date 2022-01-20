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
use Tests\Testhelper\Helper;

class ContentEncodingTest extends PhpTalTestCase
{
    public function testSimpleAnyForm(): void
    {
        $tpl = $this->newPHPTAL('input/content-encoding.xml');
        $res = $tpl->execute();
        $exp = html_entity_decode(Helper::normalizeHtmlFile('output/content-encoding.xml'), ENT_QUOTES, 'UTF-8');
        $res = html_entity_decode(Helper::normalizeHtml($res), ENT_QUOTES, 'UTF-8');
        static::assertSame($exp, $res);
    }

    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/content-encoding.xml');
        $res = $tpl->execute();
        $exp = Helper::normalizeHtmlFile('output/content-encoding.xml');
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }

    public function testEchoArray(): void
    {
        $p = $this->newPHPTAL();
        $p->setSource('<p tal:content="foo"/>');
        $p->foo = ['bar' => 'a&aa', '<bbb>', null, -1, false];
        static::assertSame('<p>a&amp;aa, &lt;bbb&gt;, , -1, 0</p>', $p->execute());
    }

    public function testNonUTF8(): void
    {
        // Japanes primary 5 characters just like "ABCDE".
        $text = mb_convert_encoding(rawurldecode("%E3%81%82%E3%81%84%E3%81%86%E3%81%88%E3%81%8A"), 'euc-jp', 'utf-8');

        $source = '<div><p data="' . $text . '">' . $text . '</p><p><![CDATA[' . $text . '"\'&]]></p><p tal:content="text" tal:attributes="data text">here</p></div>';
        $expected = '<div><p data="' . $text . '">' . $text . '</p><p>' . $text . '&quot;&#039;&amp;</p><p data="' . $text . '">' . $text . '</p></div>';

        $p = $this->newPHPTAL();
        $p->setEncoding('euc-jp');
        $p->setSource($source);
        $p->text = $text;
        $output = $p->execute();
        static::assertSame($expected, $output);
    }
}
