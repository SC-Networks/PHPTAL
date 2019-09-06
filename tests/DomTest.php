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

use PhpTal\Dom\Element;
use PhpTal\Dom\XmlnsState;
use Tests\Testcase\PhpTalTestCase;

class DomTest extends PhpTalTestCase
{
    private function newElement(string $name = 'foo', string $ns = ''): Element
    {
        $xmlns = new XmlnsState([], '');
        return new Element($name, $ns, [], $xmlns);
    }

    public function testAppendChild(): void
    {
        $el1 = $this->newElement();
        $el2 = $this->newElement();

        static::assertIsArray($el1->childNodes);
        static::assertNull($el2->parentNode);

        $el1->appendChild($el2);
        static::assertNull($el1->parentNode);
        static::assertSame($el1, $el2->parentNode);
        static::assertCount(1, $el1->childNodes);
        static::assertTrue(isset($el1->childNodes[0]));
        static::assertSame($el2, $el1->childNodes[0]);
    }

    public function testAppendChildChangesParent(): void
    {
        $el1 = $this->newElement();
        $el2 = $this->newElement();

        $ch = $this->newElement();

        $el1->appendChild($ch);

        static::assertTrue(isset($el1->childNodes[0]));
        static::assertSame($ch, $el1->childNodes[0]);

        $el2->appendChild($ch);

        static::assertTrue(isset($el2->childNodes[0]));
        static::assertSame($ch, $el2->childNodes[0]);

        static::assertFalse(isset($el1->childNodes[0]));

        static::assertCount(0, $el1->childNodes);
        static::assertCount(1, $el2->childNodes);
    }

    public function testRemoveChild(): void
    {
        $el1 = $this->newElement();
        $el2 = $this->newElement();
        $el3 = $this->newElement();
        $el4 = $this->newElement();

        $el1->appendChild($el2);
        $el1->appendChild($el3);
        $el1->appendChild($el4);

        static::assertCount(3, $el1->childNodes);
        static::assertTrue(isset($el1->childNodes[2]));
        static::assertFalse(isset($el1->childNodes[3]));

        static::assertSame($el1, $el4->parentNode);

        $el1->removeChild($el4);

        static::assertNull($el4->parentNode);

        static::assertCount(2, $el1->childNodes);
        static::assertTrue(isset($el1->childNodes[1]));
        static::assertFalse(isset($el1->childNodes[2]));
        static::assertSame($el3, end($el1->childNodes));

        $el1->removeChild($el2);

        static::assertCount(1, $el1->childNodes);
        static::assertTrue(isset($el1->childNodes[0]));
        static::assertFalse(isset($el1->childNodes[1]));
    }

    public function testReplaceChild(): void
    {
        $el1 = $this->newElement();
        $el2 = $this->newElement();
        $el3 = $this->newElement();
        $el4 = $this->newElement();

        $r = $this->newElement();

        $el1->appendChild($el2);
        $el1->appendChild($el3);
        $el1->appendChild($el4);

        static::assertCount(3, $el1->childNodes);
        static::assertSame($el3, $el1->childNodes[1]);

        $el1->replaceChild($r, $el3);

        static::assertCount(3, $el1->childNodes);
        static::assertSame($el2, $el1->childNodes[0]);
        static::assertSame($r, $el1->childNodes[1]);
        static::assertSame($el4, $el1->childNodes[2]);

        static::assertNull($el3->parentNode);
        static::assertSame($el1, $r->parentNode);
    }

    public function testSetAttributeNS(): void
    {
        $el = $this->newElement();

        static::assertSame('', $el->getAttributeNS('urn:foons', 'bar'));
        static::assertNull($el->getAttributeNodeNS('urn:foons', 'bar'));
        $el->setAttributeNS('urn:foons', 'bar', 'b\\az&<x>');
        static::assertSame('b\\az&<x>', $el->getAttributeNS('urn:foons', 'bar'));
        static::assertNotNull($el->getAttributeNodeNS('urn:foons', 'bar'));
    }

    public function testSetAttributeNSPrefixed(): void
    {
        $el = $this->newElement();

        $el->setAttributeNS('urn:foons', 'xab:bar', 'b\\az&<x>');
        static::assertSame('b\\az&<x>', $el->getAttributeNS('urn:foons', 'bar'));
        static::assertNotNull($el->getAttributeNodeNS('urn:foons', 'bar'));
    }
}
