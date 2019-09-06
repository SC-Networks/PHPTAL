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

class CommentTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $source = '<html><!-- \${variable} --></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        static::assertSame($source, $res);
    }

    public function testNoEntities(): void
    {
        $source = '<html><!-- <foo> --></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source, __FILE__);
        $res = $tpl->execute();
        static::assertSame($source, $res);
    }

    public function testShortComments(): void
    {
        $source = '<html><!--><--></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        static::assertSame($source, $res);
    }

    public function testNestedComments(): void
    {
        $this->expectException(ParserException::class);
        $source = '<html><!--<!--<!--></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->fail("Ill-formed comment accepted");
    }

    public function testDashedComment(): void
    {
        $this->expectException(ParserException::class);
        $source = '<html><!--- XML hates you ---></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->fail("Ill-formed comment accepted");
    }


    public function testSkippedComments(): void
    {
        $source = '<html><!--!
        removed --><!-- left --><!-- !removed --></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        static::assertSame('<html><!-- left --></html>', $res);
    }

    public function testCStyleComments(): void
    {
        $tpl = $this->newPHPTAL();
        $src = '<script><!--
            // comment
            /* comment <tag> */
            // comment
            --></script>';
        $tpl->setSource($src);
        static::assertSame($src, $tpl->execute());
    }
}
