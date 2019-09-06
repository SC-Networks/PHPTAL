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

class DoctypeTest extends PhpTalTestCase
{

    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/doctype.01.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/doctype.01.html');
        static::assertSame($exp, $res);
    }

    public function testPreservesNewlineAfterDoctype(): void
    {
        $src = "<!DOCTYPE html>\n\n\n<html></html>";
        $tpl = $this->newPHPTAL()->setSource($src);
        static::assertSame($src,$tpl->execute());

        $src = "<!DOCTYPE html>\n<html></html>";
        $tpl->setSource($src);
        static::assertSame($src,$tpl->execute());
    }

    public function testMacro(): void
    {
        $tpl = $this->newPHPTAL('input/doctype.02.user.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/doctype.02.html');
        static::assertSame($exp, $res);
    }

    public function testDeepMacro(): void
    {
        $tpl = $this->newPHPTAL('input/doctype.03.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/doctype.03.html');
        static::assertSame($exp, $res);
    }

    public function testDtdInline(): void
    {
        $tpl = $this->newPHPTAL('input/doctype.04.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/doctype.04.html');
        static::assertSame($exp, $res);
    }

    public function testClearedOnReexecution(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><whatever/>');

        static::assertStringContainsString("DOCTYPE html PUBLIC", $tpl->execute());
        static::assertStringContainsString("DOCTYPE html PUBLIC", $tpl->execute());

        $tpl->setSource('<whatever/>');

        static::assertStringNotContainsString("DOCTYPE html PUBLIC", $tpl->execute());
        static::assertStringNotContainsString("DOCTYPE html PUBLIC", $tpl->execute());
    }
}
