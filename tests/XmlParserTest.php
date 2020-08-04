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

use PhpTal\Dom\PHPTALDocumentBuilder;
use PhpTal\Dom\SaxXmlParser;
use PhpTal\Exception\ParserException;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;
use Tests\Testhelper\MyDocumentBuilder;

class XmlParserTest extends PhpTalTestCase
{
    public function testSimpleParse(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseFile($builder = new MyDocumentBuilder(), TAL_TEST_FILES_DIR . 'input/xml.01.xml')->getResult();
        $expected = trim(file_get_contents(TAL_TEST_FILES_DIR . 'input/xml.01.xml'));
        static::assertSame($expected, $builder->result);
        static::assertSame(7, $builder->elementStarts);
        static::assertSame(7, $builder->elementCloses);
    }

    public function testXMLStylesheet(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->baz = 'quz';
        $tpl->setSource('<?xml version="1.0" encoding="utf-8"?>
        <?xml-stylesheet type="text/css" href="/css" ?>
        <?xml-stylesheet type="text/css" href="/${baz}" ?>
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl"/>');

        $this->assertXMLEquals('<?xml version="1.0" encoding="utf-8"?>
        <?xml-stylesheet type="text/css" href="/css" ?>
        <?xml-stylesheet type="text/css" href="/quz" ?>
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl"></html>', $tpl->execute());
    }

    public function testCharactersBeforeBegining(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseFile($builder = new MyDocumentBuilder(), 'input/xml.02.xml')->getResult();
            static::assertTrue(false);
        } catch (\Exception $e) {
            static::assertTrue(true);
        }
    }

    public function testAllowGtAndLtInTextNodes(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseFile($builder = new MyDocumentBuilder(), TAL_TEST_FILES_DIR . 'input/xml.03.xml')->getResult();

        static::assertSame(Helper::normalizeHtmlFile('output/xml.03.xml'), Helper::normalizeHtml($builder->result));
        static::assertSame(3, $builder->elementStarts);
        static::assertSame(3, $builder->elementCloses);
        // a '<' character withing some text data make the parser call 2 times
        // the onElementData() method
        static::assertSame(7, $builder->datas);
    }


    public function testRejectsInvalidAttributes1(): void
    {
        $this->expectException(ParserException::class);
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString($builder = new MyDocumentBuilder(), '<foo bar="bar"baz="baz"/>')->getResult();
        $this->fail($builder->result);
    }

    public function testRejectsInvalidAttributes2(): void
    {
        $this->expectException(ParserException::class);
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString($builder = new MyDocumentBuilder(), '<foo bar;="bar"/>')->getResult();
        $this->fail($builder->result);
    }

    public function testSkipsBom(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString($builder = new MyDocumentBuilder(), "\xef\xbb\xbf<foo/>")->getResult();
        static::assertSame("<foo></foo>", $builder->result);
    }

    public function testAllowsTrickyQnames(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString($builder = new MyDocumentBuilder(), "\xef\xbb\xbf<_.:_ xmlns:_.='tricky'/>")->getResult();
        static::assertSame("<_.:_ xmlns:_.=\"tricky\"></_.:_>", $builder->result);
    }

    public function testRootNS(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString($builder = new MyDocumentBuilder(), "<f xmlns='foo:bar'/>")->getResult();
        static::assertSame('<f xmlns="foo:bar"></f>', $builder->result);
    }

    public function testAllowsXMLStylesheet(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $src = "<foo>
        <?xml-stylesheet href='foo1' ?>
        <?xml-stylesheet href='foo2' ?>
        </foo>";
        $parser->parseString($builder = new MyDocumentBuilder(), $src)->getResult();
        static::assertSame($src, $builder->result);
    }

    public function testFixOrRejectCDATAClose(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $src = '<a> ]]> </a>';
        try {
            $parser->parseString($builder = new MyDocumentBuilder(), $src)->getResult();
            static::assertSame('<a> ]]&gt; </a>', $builder->result);
        } catch (ParserException $e) { /* ok - rejecting is one way to do it */
        }
    }

    public function testSelfClosingSyntaxError(): void
    {
        $this->expectException(ParserException::class);
        $parser = new SaxXmlParser('UTF-8');
        $src = '<a / >';

        $parser->parseString($builder = new MyDocumentBuilder(), $src)->getResult();
    }

    public function testFixOrRejectEntities(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $src = '<a href="?foo=1&bar=baz&copy=true&reg=x"> & ; &#x100; &nbsp; &#10; &--;</a>';
        try {
            $parser->parseString($builder = new MyDocumentBuilder(), $src)->getResult();
            static::assertSame(
                '<a href="?foo=1&amp;bar=baz&amp;copy=true&amp;reg=x"> &amp; ; &#x100; &nbsp; &#10; &amp;--;</a>',
                $builder->result
            );
        } catch (ParserException $e) { /* ok - rejecting is one way to do it */
        }
    }

    public function testLineAccuracy(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(new PHPTALDocumentBuilder(),
                "<x>1

3
 4
<!-- 5 -->
            <x:y/> error in line 6!
            </x>
        ");
            $this->fail("Accepted invalid XML");
        } catch (ParserException $e) {
            static::assertSame(6, $e->srcLine);
        }
    }

    public function testLineAccuracy2(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(new PHPTALDocumentBuilder(),
                "<x foo1='
2'

bar4='baz'

/>
<!------->


");
            $this->fail("Accepted invalid XML");
        } catch (ParserException $e) {
            static::assertSame(7, $e->srcLine);
        }
    }

    public function testLineAccuracy3(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(new PHPTALDocumentBuilder(),
                "

<x foo1='
2'

bar4='baz'

xxxx/>


");
            $this->fail("Accepted invalid XML");
        } catch (ParserException $e) {
            static::assertSame(8, $e->srcLine);
        }
    }

    public function testClosingRoot(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(new PHPTALDocumentBuilder(), "<imrootelement/></ishallnotbeclosed>");
            $this->fail("Accepted invalid XML");
        } catch (ParserException $e) {
            static::assertStringContainsString('ishallnotbeclosed', $e->getMessage());
            static::assertStringNotContainsString('imrootelement', $e->getMessage());
            static::assertStringNotContainsString("documentElement", $e->getMessage());
        }
    }

    public function testNotClosing(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(
                new PHPTALDocumentBuilder(),
                "<element_a><element_b><element_x/><element_c><element_d><element_e>"
            );
            $this->fail("Accepted invalid XML");
        } catch (ParserException $e) {
            static::assertStringNotContainsString("documentElement", $e->getMessage());
            static::assertMatchesRegularExpression("/element_e.*element_d.*element_c.*element_b.*element_a/", $e->getMessage());
        }
    }

    public function testSPRY(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<html>
        <body>
        <div metal:define-macro="SomeMacro" xmlns:spry="http://ns.adobe.com/spry" >
        <div spry:region="someSpryRegion">
          <p spry:if="\'{someRegion::element}\' != \'hello\'">{someSpryRegion::element}</p>
        </div>
        </div>
        </body>
        </html>');
        $tpl->prepare();
    }

    public function testSPRY2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->phptal_var = 'ok';
        $tpl->setSource('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:spry="http://ns.adobe.com/spry"><body><form>
        <input type="text" value="${phptal_var}" spry:if="i == 1" /></form></body></html>');

        $this->assertXMLEquals('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:spry="http://ns.adobe.com/spry"><body><form>
        <input type="text" value="ok" spry:if="i == 1"/></form></body></html>', $tpl->execute());
    }
}
