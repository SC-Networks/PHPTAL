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

class CommentTest extends \Tests\Testcase\PhpTal
{
    public function testSimple()
    {
        $source = '<html><!-- \${variable} --></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->assertEquals($source, $res);
    }

    public function testNoEntities()
    {
        $source = '<html><!-- <foo> --></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source, __FILE__);
        $res = $tpl->execute();
        $this->assertEquals($source, $res);
    }

    public function testShortComments()
    {
        $source = '<html><!--><--></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->assertEquals($source, $res);
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    public function testNestedComments()
    {
        $source = '<html><!--<!--<!--></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->fail("Ill-formed comment accepted");
    }

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    public function testDashedComment()
    {
        $source = '<html><!--- XML hates you ---></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->fail("Ill-formed comment accepted");
    }


    public function testSkippedComments()
    {
        $source = '<html><!--!
        removed --><!-- left --><!-- !removed --></html>';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        $res = $tpl->execute();
        $this->assertEquals('<html><!-- left --></html>', $res);
    }

    public function testCStyleComments()
    {
        $tpl = $this->newPHPTAL();
        $src = '<script><!--
            // comment
            /* comment <tag> */
            // comment
            --></script>';
        $tpl->setSource($src);
        $this->assertEquals($src, $tpl->execute());
    }
}
