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

class CommentFilterTest extends \Tests\Testcase\PhpTal
{
    public function testStripComments()
    {
        $t = $this->newPHPTAL('input/comment-filter-01.html');
        $t->addPreFilter(new \PhpTal\PreFilter\StripComments());
        $res = $t->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/comment-filter-01.html');
        $this->assertEquals($exp, $res);
    }

    public function testPreservesScript()
    {
        $t = $this->newPHPTAL();
        $t->addPreFilter(new \PhpTal\PreFilter\StripComments());
        $t->setSource('<script>//<!--
        alert("1990s called"); /* && */
        //--></script>');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<script>//<![CDATA[
        alert("1990s called"); /* && */
        //]]></script>'), \Tests\Testhelper\Helper::normalizeHtml($t->execute()));
    }

    public function testNamespaceAware()
    {
        $t = $this->newPHPTAL();
        $t->addPreFilter(new \PhpTal\PreFilter\StripComments());
        $t->setSource('<script xmlns="http://example.com/foo">//<!--
        alert("1990s called"); /* && */
        //--></script>');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<script xmlns="http://example.com/foo">//</script>'), \Tests\Testhelper\Helper::normalizeHtml($t->execute()));
    }
}
