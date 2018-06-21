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

class PreFilterNormalizeTest extends \Tests\Testcase\PhpTal
{
    public function testBasic()
    {
        $tpl = $this->newPHPTAL()->setSource("<p>\t\n\rhello       world</p>");
        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals("<p> hello world</p>", $tpl->execute());
    }

    public function testPreSkipped()
    {
        $tpl = $this->newPHPTAL()->setSource("<pre>\r\n foo</pre>");
        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals("<pre>\n foo</pre>", $tpl->execute());
    }

    public function testTextAreaSkipped()
    {
        $tpl = $this->newPHPTAL()->setSource("<t:textarea xmlns:t='http://www.w3.org/1999/xhtml'>\r\n foo</t:textarea><p>  </p>");
        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals("<t:textarea xmlns:t=\"http://www.w3.org/1999/xhtml\">\n foo</t:textarea><p> </p>", $tpl->execute());
    }

    public function testNormalizesAttrs()
    {
        $tpl = $this->newPHPTAL()->setSource("<p title='   foo \r\n bar \t\tbaz '>  </p>");
        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals('<p title="foo bar baz"> </p>', $tpl->execute());
    }

    public function testNormalizesPreAttrs()
    {
        $tpl = $this->newPHPTAL()->setSource("<pre title='   foo \r\n bar \t\tbaz '>  </pre>");
        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals('<pre title="foo bar baz">  </pre>', $tpl->execute());
    }

    public function testSkipsXMLSpacePreserve()
    {
        $tpl = $this->newPHPTAL()->setSource("<p title='   foo \r\n bar \t\tbaz '>
        <span xml:space='preserve' title=' spa  ced '> \n </span>   </p>");

        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals('<p title="foo bar baz"> <span xml:space="preserve" title=" spa  ced "> '.
        "\n".' </span> </p>',$tpl->execute());
    }

    public function testResumesXMLSpaceDefault()
    {
        $tpl = $this->newPHPTAL()->setSource("<p title='   foo \r\n bar \t\tbaz '>
        <span xml:space='preserve' title=' spa  ced '> \n <x xml:space='default' y=' a '>\r\n</x> </span>   </p>");

        $tpl->addPreFilter(new \PhpTal\PreFilter\Normalize());

        $this->assertEquals('<p title="foo bar baz"> <span xml:space="preserve" title=" spa  ced "> '.
        "\n".' <x xml:space="default" y="a"> </x> </span> </p>',$tpl->execute());
    }


    public function runFilter(\PhpTal\Dom\Element $el)
    {
        $f = new \PhpTal\PreFilter\Normalize();

        // assertNull checks for "void" functions
        $this->assertNull($f->setPHPTAL($this->newPHPTAL()));
        $this->assertNull($f->filterDOM($el));
    }

    private function newElement($name = 'foo', $ns = '')
    {
        $xmlns = new \PhpTal\Dom\XmlnsState(array(), '');
        return new \PhpTal\Dom\Element($name, $ns, array(), $xmlns);
    }


    public function testNormalizeSpaceRemovesEmpty()
    {
        $el = $this->newElement();
        $el->appendChild(new \PhpTal\Dom\Text('', 'UTF-8'));
        $el->appendChild(new \PhpTal\Dom\Text('', 'UTF-8'));

        $this->assertEquals(2, count($el->childNodes));

        $this->runFilter($el);

        $this->assertEquals(0, count($el->childNodes));
    }

    public function testNormalizeSpaceMerges()
    {
        $el = $this->newElement();
        $el->appendChild(new \PhpTal\Dom\Text('a', 'UTF-8'));
        $el->appendChild(new \PhpTal\Dom\Text('b', 'UTF-8'));

        $this->assertEquals(2, count($el->childNodes));

        $this->runFilter($el);

        $this->assertEquals(1, count($el->childNodes));
    }

    public function testNormalizeSpaceSkipsElement()
    {
        $el = $this->newElement();
        $el->appendChild(new \PhpTal\Dom\Text('a', 'UTF-8'));
        $el->appendChild($this->newElement());
        $el->appendChild(new \PhpTal\Dom\Text('b', 'UTF-8'));

        $this->assertEquals(3, count($el->childNodes));

        $this->runFilter($el);

        $this->assertEquals(3, count($el->childNodes));
    }
}
