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

use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;
use Tests\Testhelper\MyPostFilter;
use Tests\Testhelper\MyPostFilter2;

class PostFilterTest extends PhpTalTestCase
{
    public function testIt(): void
    {
        $filter = new MyPostFilter();
        $tpl = $this->newPHPTAL('input/postfilter.01.html');
        $tpl->setPostFilter($filter);
        $tpl->value = 'my value';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/postfilter.01.html');
        static::assertSame($exp, $res);
    }

    public function testMacro(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setPostFilter(new MyPostFilter2());
        $tpl->setSource('<x><y metal:define-macro="macro">test2</y>
        test1
        <z metal:use-macro="macro" />
        </x>
        ');
        static::assertSame(
            Helper::normalizeHtml('<x>test-filtered1<y>test-filtered2</y></x>'),
            Helper::normalizeHtml($tpl->execute())
        );
    }
}
