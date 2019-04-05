<?php
/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace Tests;

class BlockTest extends \Tests\Testcase\PhpTal
{
    function testTalBlock()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<tal:block content="string:content"></tal:block>');
        $res = $t->execute();
        $this->assertEquals('content', $res);
    }

    function testMetalBlock()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<metal:block>foo</metal:block>');
        $res = $t->execute();
        $this->assertEquals('foo', $res);
    }

    function testSomeNamespaceBlock()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<foo:block xmlns:foo="http://phptal.example.com">foo</foo:block>');
        $res = $t->execute();
        $this->assertEquals('<foo:block xmlns:foo="http://phptal.example.com">foo</foo:block>', $res);
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    function testInvalidNamespaceBlock()
    {
        $t = $this->newPHPTAL();

        $t->setSource('<foo:block>foo</foo:block>');
        $res = $t->execute();
    }
}
