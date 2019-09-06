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

use PhpTal\Php\Attribute\TAL\Comment;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\State;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\DummyPhpNode;
use Tests\Testhelper\Helper;

class TalTestCaseCommentTest extends PhpTalTestCase
{

    /**
     * @var DummyPhpNode
     */
    private $tag;

    /**
     * @var CodeWriter
     */
    private $gen;

    /**
     * @var Comment
     */
    private $att;

    public function setUp(): void
    {
        parent::setUp();
        $state = new State($this->newPHPTAL());
        $this->gen = new CodeWriter($state);
        $this->tag = new DummyPhpNode();
        $this->tag->codewriter = $this->gen;
    }

    private function newComment(string $expr): void
    {
        $this->att = new Comment($this->tag, $expr);
    }

    public function testComment(): void
    {
        $this->newComment('my dummy comment');
        $this->att->before($this->gen);
        $this->att->after($this->gen);
        $res = $this->gen->getResult();
        static::assertSame(
            Helper::normalizePhpSource('<?php /* my dummy comment */; ?>'),
            Helper::normalizePhpSource($res)
        );
    }

    public function testMultiLineComment(): void
    {
        $comment = "my dummy comment\non more than one\nline";
        $this->newComment($comment);
        $this->att->before($this->gen);
        $this->att->after($this->gen);
        $res = $this->gen->getResult();
        static::assertSame(Helper::normalizePhpSource("<?php /* $comment */; ?>"), Helper::normalizePhpSource($res));
    }

    public function testTrickyComment(): void
    {
        $comment = "my dummy */ comment\non more than one\nline";
        $this->newComment($comment);
        $this->att->before($this->gen);
        $this->att->after($this->gen);
        $res = $this->gen->getResult();
        $comment = str_replace('*/', '* /', $comment);
        static::assertSame(Helper::normalizePhpSource("<?php /* $comment */; ?>"), Helper::normalizePhpSource($res));
    }

    public function testInTemplate(): void
    {
        $tpl = $this->newPHPTAL('input/tal-comment.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-comment.01.html');
        static::assertSame($exp, $res);
    }

    public function testMultilineInTemplate(): void
    {
        $tpl = $this->newPHPTAL('input/tal-comment.02.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-comment.02.html');
        static::assertSame($exp, $res);
    }
}
