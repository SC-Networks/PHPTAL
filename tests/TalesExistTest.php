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

class TalesExistTest extends PhpTalTestCase
{
    public function testLevel1(): void
    {
        $tpl = $this->newPHPTAL('input/tales-exist-01.html');
        $tpl->foo = 1;
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tales-exist-01.html');
        static::assertSame($exp, $res, $tpl->getCodePath());
    }

    public function testLevel2(): void
    {
        $o = new \stdClass();
        $o->foo = 1;
        $tpl = $this->newPHPTAL('input/tales-exist-02.html');
        $tpl->o = $o;
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tales-exist-02.html');
        static::assertSame($exp, $res);
    }
}
