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

use DOMNode;
use Exception;
use PhpTal\Dom\PHPTALDocumentBuilder;
use PhpTal\Dom\SaxXmlParser;
use PhpTal\Exception\PhpTalException;
use Tests\Testcase\PhpTalTestCase;

class ParserTest extends PhpTalTestCase
{
    public function testParseSimpleDocument(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $tree = $parser->parseFile(
            new PHPTALDocumentBuilder(),
            TAL_TEST_FILES_DIR . 'input/parser.01.xml'
        )->getResult();

        if ($tree instanceof DOMNode) {
            $this->markTestSkipped();
        }

        $children = $tree->childNodes;
        static::assertCount(3, $children);
        static::assertCount(5, $children[2]->childNodes);
    }

    public function testByteOrderMark(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseFile(
                new PHPTALDocumentBuilder(),
                TAL_TEST_FILES_DIR . 'input/parser.02.xml'
            )->getResult();
            static::assertTrue(true);
        } catch (Exception $e) {
            static::assertTrue(false);
        }
    }

    public function testBadAttribute(): void
    {
        try {
            $parser = new SaxXmlParser('UTF-8');
            $parser->parseFile(new PHPTALDocumentBuilder(), TAL_TEST_FILES_DIR . 'input/parser.03.xml')->getResult();
        } catch (Exception $e) {
            static::assertStringContainsString('href', $e->getMessage());
            static::assertStringContainsString('quote', $e->getMessage());
        }
    }

    public function testLegalElementNames(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString(
            new PHPTALDocumentBuilder(),
            '<?xml version="1.0" encoding="UTF-8"?>
        <t1 xmlns:foo..._-ą="http://foo.example.com"><foo..._-ą:test-element_name /><t---- /><t___ /><oóźżćń /><d.... /></t1>'
        )->getResult();
    }

    public function testXMLNS(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        $parser->parseString(new PHPTALDocumentBuilder(), '<?xml version="1.0" encoding="UTF-8"?>
         <t1 xml:lang="foo" xmlns:bla="xx"></t1>')->getResult();
    }

    public function testIllegalElementNames1(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(new PHPTALDocumentBuilder(), '<?xml version="1.0" encoding="UTF-8"?>
            <t><1element /></t>')->getResult();

            $this->fail("Accepted invalid element name starting with a number");
        } catch (PhpTalException $e) {
        }
    }

    public function testIllegalElementNames2(): void
    {
        $parser = new SaxXmlParser('UTF-8');
        try {
            $parser->parseString(new PHPTALDocumentBuilder(), '<t><element~ /></t>');
            $this->fail('Accepted invalid element name');
        } catch (PhpTalException $e) {
        }
    }
}
