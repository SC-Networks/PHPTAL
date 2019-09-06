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

use PhpTal\PreFilter\StripComments;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class CommentFilterTest extends PhpTalTestCase
{
    public function testStripComments(): void
    {
        $t = $this->newPHPTAL('input/comment-filter-01.html');
        $t->addPreFilter(new StripComments());
        $res = $t->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/comment-filter-01.html');
        static::assertSame($exp, $res);
    }

    public function testPreservesScript(): void
    {
        $t = $this->newPHPTAL();
        $t->addPreFilter(new StripComments());
        $t->setSource('<script>//<!--
        alert("1990s called"); /* && */
        //--></script>');

        static::assertSame(Helper::normalizeHtml('<script>//<![CDATA[
        alert("1990s called"); /* && */
        //]]></script>'), Helper::normalizeHtml($t->execute()));
    }

    public function testNamespaceAware(): void
    {
        $t = $this->newPHPTAL();
        $t->addPreFilter(new StripComments());
        $t->setSource('<script xmlns="http://example.com/foo">//<!--
        alert("1990s called"); /* && */
        //--></script>');

        static::assertSame(
            Helper::normalizeHtml('<script xmlns="http://example.com/foo">//</script>'),
            Helper::normalizeHtml($t->execute())
        );
    }
}
