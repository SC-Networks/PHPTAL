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
use PhpTal\Dom\Text;
use PhpTal\Dom\XmlnsState;
use PhpTal\PreFilter\Normalize;
use Tests\Testcase\PhpTalTestCase;

class PreFilterNormalizeTest extends PhpTalTestCase
{
    public function testBasic(): void
    {
        $tpl = $this->newPHPTAL()->setSource("<p>\t\n\rhello       world</p>");
        $tpl->addPreFilter(new Normalize());

        static::assertSame("<p> hello world</p>", $tpl->execute());
    }

    public function testPreSkipped(): void
    {
        $tpl = $this->newPHPTAL()->setSource("<pre>\r\n foo</pre>");
        $tpl->addPreFilter(new Normalize());

        static::assertSame("<pre>\n foo</pre>", $tpl->execute());
    }

    public function testTextAreaSkipped(): void
    {
        $tpl = $this->newPHPTAL()->setSource(
            "<t:textarea xmlns:t='http://www.w3.org/1999/xhtml'>\r\n foo</t:textarea><p>  </p>"
        );
        $tpl->addPreFilter(new Normalize());

        static::assertSame(
            "<t:textarea xmlns:t=\"http://www.w3.org/1999/xhtml\">\n foo</t:textarea><p> </p>",
            $tpl->execute()
        );
    }

    public function testNormalizesAttrs(): void
    {
        $tpl = $this->newPHPTAL()->setSource("<p title='   foo \r\n bar \t\tbaz '>  </p>");
        $tpl->addPreFilter(new Normalize());

        static::assertSame('<p title="foo bar baz"> </p>', $tpl->execute());
    }

    public function testNormalizesPreAttrs(): void
    {
        $tpl = $this->newPHPTAL()->setSource("<pre title='   foo \r\n bar \t\tbaz '>  </pre>");
        $tpl->addPreFilter(new Normalize());

        static::assertSame('<pre title="foo bar baz">  </pre>', $tpl->execute());
    }

    public function testSkipsXMLSpacePreserve(): void
    {
        $tpl = $this->newPHPTAL()->setSource("<p title='   foo \r\n bar \t\tbaz '>
        <span xml:space='preserve' title=' spa  ced '> \n </span>   </p>");

        $tpl->addPreFilter(new Normalize());

        static::assertSame(
            '<p title="foo bar baz"> <span xml:space="preserve" title=" spa  ced "> ' . "\n" . ' </span> </p>',
            $tpl->execute()
        );
    }

    public function testResumesXMLSpaceDefault(): void
    {
        $tpl = $this->newPHPTAL()->setSource("<p title='   foo \r\n bar \t\tbaz '>
        <span xml:space='preserve' title=' spa  ced '> \n <x xml:space='default' y=' a '>\r\n</x> </span>   </p>");

        $tpl->addPreFilter(new Normalize());

        static::assertSame(
            '<p title="foo bar baz"> <span xml:space="preserve" title=" spa  ced "> ' .
            "\n" . ' <x xml:space="default" y="a"> </x> </span> </p>',
            $tpl->execute()
        );
    }

    private function runFilter(Element $el): void
    {
        $f = new Normalize();

        $f->setPHPTAL($this->newPHPTAL());
        $f->filterDOM($el);
    }

    private function newElement(string $name = 'foo', string $ns = ''): Element
    {
        $xmlns = new XmlnsState([], '');
        return new Element($name, $ns, [], $xmlns);
    }


    public function testNormalizeSpaceRemovesEmpty(): void
    {
        $el = $this->newElement();
        $el->appendChild(new Text('', 'UTF-8'));
        $el->appendChild(new Text('', 'UTF-8'));

        static::assertCount(2, $el->childNodes);

        $this->runFilter($el);

        static::assertCount(0, $el->childNodes);
    }

    public function testNormalizeSpaceMerges(): void
    {
        $el = $this->newElement();
        $el->appendChild(new Text('a', 'UTF-8'));
        $el->appendChild(new Text('b', 'UTF-8'));

        static::assertCount(2, $el->childNodes);

        $this->runFilter($el);

        static::assertCount(1, $el->childNodes);
    }

    public function testNormalizeSpaceSkipsElement(): void
    {
        $el = $this->newElement();
        $el->appendChild(new Text('a', 'UTF-8'));
        $el->appendChild($this->newElement());
        $el->appendChild(new Text('b', 'UTF-8'));

        static::assertCount(3, $el->childNodes);

        $this->runFilter($el);

        static::assertCount(3, $el->childNodes);
    }
}
