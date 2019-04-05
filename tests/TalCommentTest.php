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

use Tests\Testhelper\DummyPhpNode;

class TalCommentTest extends \Tests\Testcase\PhpTal
{

    private $_tag;
    private $_gen;
    private $_att;

    public function setUp()
    {
        parent::setUp();
        $state = new \PhpTal\Php\State($this->newPHPTAL());
        $this->_gen = new \PhpTal\Php\CodeWriter($state);
        $this->_tag = new DummyPhpNode();
        $this->_tag->codewriter = $this->_gen;
    }

    private function newComment($expr)
    {
        return $this->_att = new \PhpTal\Php\Attribute\TAL\Comment($this->_tag, $expr);
    }

    public function testComment()
    {
        $this->newComment( 'my dummy comment');
        $this->_att->before($this->_gen);
        $this->_att->after($this->_gen);
        $res = $this->_gen->getResult();
        $this->assertEquals(\Tests\Testhelper\Helper::normalizePhpSource('<?php /* my dummy comment */; ?>'), \Tests\Testhelper\Helper::normalizePhpSource($res));
    }

    public function testMultiLineComment()
    {
        $comment = "my dummy comment\non more than one\nline";
        $this->newComment($comment);
        $this->_att->before($this->_gen);
        $this->_att->after($this->_gen);
        $res = $this->_gen->getResult();
        $this->assertEquals(\Tests\Testhelper\Helper::normalizePhpSource("<?php /* $comment */; ?>"), \Tests\Testhelper\Helper::normalizePhpSource($res));
    }

    public function testTrickyComment()
    {
        $comment = "my dummy */ comment\non more than one\nline";
        $this->newComment(  $comment);
        $this->_att->before($this->_gen);
        $this->_att->after($this->_gen);
        $res = $this->_gen->getResult();
        $comment = str_replace('*/', '* /', $comment);
        $this->assertEquals(\Tests\Testhelper\Helper::normalizePhpSource("<?php /* $comment */; ?>"), \Tests\Testhelper\Helper::normalizePhpSource($res));
    }

    public function testInTemplate()
    {
        $tpl = $this->newPHPTAL('input/tal-comment.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-comment.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testMultilineInTemplate()
    {
        $tpl = $this->newPHPTAL('input/tal-comment.02.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-comment.02.html');
        $this->assertEquals($exp, $res);
    }
}
