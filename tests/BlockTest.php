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

use PhpTal\Exception\ParserException;
use Tests\Testcase\PhpTalTestCase;

class BlockTest extends PhpTalTestCase
{
    public function testTalBlock()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<tal:block content="string:content"></tal:block>');
        $res = $t->execute();
        static::assertSame('content', $res);
    }

    public function testMetalBlock()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<metal:block>foo</metal:block>');
        $res = $t->execute();
        static::assertSame('foo', $res);
    }

    public function testSomeNamespaceBlock()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<foo:block xmlns:foo="http://phptal.example.com">foo</foo:block>');
        $res = $t->execute();
        static::assertSame('<foo:block xmlns:foo="http://phptal.example.com">foo</foo:block>', $res);
    }

    public function testInvalidNamespaceBlock()
    {
        $this->expectException(ParserException::class);
        $t = $this->newPHPTAL();

        $t->setSource('<foo:block>foo</foo:block>');
        $t->execute();
    }
}
