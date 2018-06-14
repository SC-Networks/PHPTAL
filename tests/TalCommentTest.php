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



class DummyPhpNode extends \PhpTal\Dom\Element {
    function __construct() {}
    function generateCode(\PhpTal\Php\CodeWriter $codewriter): void {}
}

class TalCommentTest extends PHPTAL_TestCase
{
    function setUp()
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

    function testComment()
    {
        $this->newComment( 'my dummy comment');
        $this->_att->before($this->_gen);
        $this->_att->after($this->_gen);
        $res = $this->_gen->getResult();
        $this->assertEquals(normalize_phpsource('<?php /* my dummy comment */; ?>'), normalize_phpsource($res));
    }

    function testMultiLineComment()
    {
        $comment = "my dummy comment\non more than one\nline";
        $this->newComment($comment);
        $this->_att->before($this->_gen);
        $this->_att->after($this->_gen);
        $res = $this->_gen->getResult();
        $this->assertEquals(normalize_phpsource("<?php /* $comment */; ?>"), normalize_phpsource($res));
    }

    function testTrickyComment()
    {
        $comment = "my dummy */ comment\non more than one\nline";
        $this->newComment(  $comment);
        $this->_att->before($this->_gen);
        $this->_att->after($this->_gen);
        $res = $this->_gen->getResult();
        $comment = str_replace('*/', '* /', $comment);
        $this->assertEquals(normalize_phpsource("<?php /* $comment */; ?>"), normalize_phpsource($res));
    }

    function testInTemplate()
    {
        $tpl = $this->newPHPTAL('input/tal-comment.01.html');
        $res = normalize_html($tpl->execute());
        $exp = normalize_html_file('output/tal-comment.01.html');
        $this->assertEquals($exp, $res);
    }

    function testMultilineInTemplate()
    {
        $tpl = $this->newPHPTAL('input/tal-comment.02.html');
        $res = normalize_html($tpl->execute());
        $exp = normalize_html_file('output/tal-comment.02.html');
        $this->assertEquals($exp, $res);
    }

    private $_tag;
    private $_gen;
    private $_att;
}
