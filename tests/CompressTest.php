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
use PhpTal\PHPTAL;
use PhpTal\PreFilter\Compress;
use Tests\Testcase\PhpTalTestCase;

class CompressTest extends PhpTalTestCase
{
    private function assertStrips(string $expect, string $source, bool $html5 = false)
    {
        $tpl = $this->newPHPTAL();
        if ($html5) {
            $tpl->setOutputMode(PHPTAL::HTML5);
        }
        $tpl->addPreFilter(new Compress());

        $tpl->setSource($source);

        static::assertSame($expect, $tpl->execute());
    }

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testConsecutiveSpace(): void
    {
        $this->assertStrips(
            "<p>foo bar baz</p>",
            "<p>foo     \t bar\n\n\n baz</p>"
        );
    }

    public function testPreservesPre(): void
    {
        $this->assertStrips(
            "<p>foo bar</p><pre>  \tfoo\t   \nbar   </pre>",
            "<p>foo   \t\n bar</p><pre>  \tfoo\t   \nbar   </pre>"
        );
    }

    public function testCase1(): void
    {
        $this->assertStrips('<p>foo <a href="">bar </a>baz</p>', '<p>foo <a href=""> bar </a> baz  </p>');
    }

    public function testCase2(): void
    {
        $this->assertStrips('<p>foo <a href="">bar </a>baz</p>', ' <p>foo <a href=""> bar </a>baz </p>');
    }

    public function testCase3(): void
    {
        $this->assertStrips('<p>foo<a href=""> bar </a>baz</p>', ' <p> foo<a href=""> bar </a>baz </p>  ');
    }

    public function testCase4(): void
    {
        $this->assertStrips('<p>foo <a href="">bar</a> baz</p>', ' <p> foo <a href="">bar</a> baz</p>');
    }

    public function testLastEmptyChild1(): void
    {
        $this->assertStrips('<p>foo<br/></p>', '<p>foo <br/></p>');
    }

    public function testLastEmptyChild2(): void
    {
        $this->assertStrips('<p>foo<span></span></p>', '<p>foo <span></span></p>');
    }

    public function testLastEmptyChild3(): void
    {
        $this->assertStrips('<p>foo<span></span></p>', '<p>foo <span></span> </p>');
    }

    public function testLastEmptyChild4(): void
    {
        $this->assertStrips('<p>foo<!-- --></p>', '<p>foo <!-- --> </p>');
    }

    public function testRespectsNamespace(): void
    {
        $this->assertStrips(
            '<div>z<p xmlns="not:xhtml"> ke <br></br>ep </p>z</div>',
            '<div> z<p xmlns="not:xhtml"> ke <br></br> ep </p> z </div>'
        );
    }

    public function testTalRepeatBlock(): void
    {
        TalesInternal::setFunctionWhitelist(['range']);
        $this->assertStrips(
            "<div>a<div>x</div><div>x</div><div>x</div>b</div>",
            "<div>a <div tal:repeat='x php:range(1,3)'> x </div> b</div>"
        );
    }

    public function testTrimsSpaceBeforeBlockSibling(): void
    {
        $this->assertStrips(
            "<div>a<div>b</div>c<div>d</div>e</div>",
            "<div>a <div>b</div> c <div> d </div> e </div>"
        );
    }

    public function testPreservesSpaceAroundImages(): void
    {
        $this->assertStrips("<div><img/></div>", "<div> <img/> </div>");
        $this->assertStrips("<div>x <img/></div>", "<div> x <img/> </div>");
        $this->assertStrips("<div>x <img/> y</div>", "<div> x <img/> y </div>");
        $this->assertStrips("<div><img/> y</div>", "<div><img/> y </div>");
    }

    public function testPreservesSpaceAroundButtons(): never
    {
        $this->assertStrips("<div><button>Z</button></div>", "<div> <button>Z</button> </div>");
        $this->assertStrips("<div>x <button>Z</button></div>", "<div> x <button>Z</button> </div>");
        $this->assertStrips("<div>x <button>Z</button> y</div>", "<div> x <button>Z</button> y </div>");
        $this->assertStrips("<div><button>Z</button> y</div>", "<div><button>Z</button> y </div>");

        $this->markTestIncomplete();

        $this->assertStrips("<div><button>Z</button></div>", "<div> <button> Z </button> </div>");
        $this->assertStrips("<div>x <button>Z</button></div>", "<div> x <button> Z </button> </div>");
        $this->assertStrips("<div>x <button>Z</button> y</div>", "<div> x <button> Z </button> y </div>");
        $this->assertStrips("<div><button>Z</button> y</div>", "<div><button> Z </button> y </div>");
    }

    public function testKeepsNewlinesInScript(): void
    {
        $this->assertStrips("<script>//foo\nbar()</script>", "<script>//foo\nbar()</script>");
    }

    public function testTalRepeatInline(): void
    {
        TalesInternal::setFunctionWhitelist(['range']);
        $this->assertStrips(
            "<div><a>x </a><a>x </a><a>x </a></div>",
            "<div><a tal:repeat='x php:range(1,3)'> x </a></div>"
        );
    }

    public function testStripsAllInHead(): void
    {
        $this->assertStrips(
            '<html><head><title></title><link/><script>" ";</script><script></script><meta/><style></style></head></html>',
            '<html >
            <head > <title > </title > <link /> <script >" ";</script> <script/>
             <meta /> <style
              > </style >
               </head > </html>'
        );
    }

    public function testAdjacentBlocks(): void
    {
        $this->assertStrips(
            '<div><p>test 123</p><p>456</p><ul><li>x</li></ul></div>',
            '<div> <p> test 123 </p> <p> 456 </p> <ul> <li>x</li> </ul> </div>'
        );
    }

    public function testAdjacentBlocksPre(): void
    {
        $this->assertStrips(
            '<div><p>test 123</p><pre> 456 </pre><p>x</p></div>',
            '<div> <p> test 123 </p> <pre> 456 </pre> <p> x </p> </div>'
        );
    }

    public function testTalReplacedElementsAreText(): void
    {
        $this->assertStrips('<div>a x b</div>', '<div> a <p tal:replace="string:x"/> b </div>');
    }

    /**
     * It's common to display list items with display:inline in horizontal menus
     */
    public function testListItemsAreInline(): void
    {
        $this->assertStrips(
            '<div><ul><li><a>a </a></li><li>b </li><li>c</li></ul></div>',
            '<div> <ul> <li> <a> a </a> </li> <li> b </li> <li> c </li> </ul> </div>'
        );
    }

    public function testPreservesXMLSpace(): void
    {
        $this->assertStrips(
            '<p>foo<span xml:space="preserve"> foo bar  baz </span> bla</p>',
            '<p>  foo<span xml:space="preserve"> foo bar  baz </span> bla </p>'
        );
    }

    public function testRemovesInterelement(): void
    {
        $this->assertStrips(
            '<table>x<tr>x<td>foo</td>x</tr>x</table>',
            '<table> x <tr> x <td> foo </td> x </tr> x </table>'
        );
        $this->assertStrips(
            '<select>x<option></option>x<optgroup>x<option></option>x</optgroup>x</select>',
            '<select> x <option> </option> x <optgroup> x <option> </option> x </optgroup> x </select> '
        );
    }

    public function testOrdersAttributes(): void
    {
        $this->assertStrips(
            '<img src="foo" width="10" height="5" alt="x"/>',
            '<img width="10" height="5" src="foo" alt="x" />'
        );
    }

    public function testSortsUnknownAttributes(): void
    {
        $this->assertStrips('<img alpha="1" beta="2" gamma="3"/>', '<img gamma="3" alpha="1" beta="2" />');
    }

    public function testPreFirstLine(): void
    {
        $this->assertStrips("<pre>\n\n\ntest</pre>", "<pre>\n\n\ntest</pre>");
        $this->assertStrips("<pre>test</pre>", "<pre>\ntest</pre>");
    }

    public function testDoesNotShortenXHTMLMeta(): void
    {
        $this->assertStrips(
            '<meta content="text/plain;charset=UTF-8" http-equiv="Content-Type"/>',
            "<meta http-equiv='Content-Type' content='text/plain;charset=UTF-8'/>"
        );
    }

    public function testShortensHTML5Meta(): void
    {
        $this->assertStrips(
            "<meta charset=utf-8>",
            "<meta http-equiv='Content-Type' content='text/plain;charset=UTF-8'/>",
            true
        );
    }

    public function testShortensHTML5Types(): void
    {
        $this->assertStrips(
            "<script></script><style></style>",
            "<script type='text/javascript ;charset=utf-8'
            language='javascript'></script><style type='text/css'></style>",
            true
        );
    }

    public function testShortensHTML5TypesSafely(): void
    {
        $this->assertStrips(
            '<script type="text/javascript;e4x=1"></script><script type=text/hack></script>',
            '<script type="text/javascript;e4x=1"></script><script type="text/hack"></script>',
            true
        );
    }

    public function testTalBlockInInlineIsInline(): void
    {
        $this->assertStrips("<p><span>foo bar</span></p>", "<p><span> <tal:block> foo </tal:block> bar </span></p>");
    }

    public function testTalBlockInListIsInline(): void
    {
        $this->assertStrips("<ul><li>foo bar</li></ul>", "<ul><li> <tal:block> foo </tal:block> bar </li></ul>");
    }

    public function testPreservesSpaceBeforePI(): void
    {
        $this->assertStrips("<p>foo <_ echo 'bar'; ?></p>", "<p>foo <?php echo 'bar'; ?></p>");
    }

    public function testTalContent(): void
    {
        $this->assertStrips(
            '<h1 class="title"><a href="a">a</a> » <a href="b">b</a> » </h1>',
            '<h1 class="title" tal:condition="not:exists:blah">
                     <tal:block tal:repeat="b php:array(\'a\',\'b\')"><a href=" ${b} " tal:content="b" /> » </tal:block>
                            <tal:block tal:content="blah | nothing"/>
                        </h1>
        '
        );
    }

    public function testConditionalBR(): void
    {
        $this->assertStrips("<div>foo bar</div>", '<div>foo<br tal:condition="false"/> bar</div>');
        $this->assertStrips("<div>foo bar</div>", '<div>foo<span tal:condition="false"/> bar</div>');
        $this->assertStrips("<div>foo bar</div>", '<div>foo<div tal:condition="false"/> bar</div>');
    }

    public function testAll(): void
    {
        $this->assertStrips(
            "<html><head><title>Foo</title></head><body><p><a href=\"test\" title=\"x\">x </a>xu</p><br/>foo</body></html><!-- bla -->",
            '<html> <head> <title> Foo </title> </head>
        <body>
        <p>
        <a title="   x " href=" test "> x </a> xu
        </p>
        <br/>
        foo</body> </html>  <!-- bla -->'
        );
    }
}
