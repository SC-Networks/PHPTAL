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

namespace Tests;

class DoctypeTest extends \Tests\Testcase\PhpTal
{

    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/doctype.01.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/doctype.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testPreservesNewlineAfterDoctype()
    {
        $src = "<!DOCTYPE html>\n\n\n<html></html>";
        $tpl = $this->newPHPTAL()->setSource($src);
        $this->assertEquals($src,$tpl->execute());

        $src = "<!DOCTYPE html>\n<html></html>";
        $tpl->setSource($src);
        $this->assertEquals($src,$tpl->execute());
    }

    public function testMacro()
    {
        $tpl = $this->newPHPTAL('input/doctype.02.user.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/doctype.02.html');
        $this->assertEquals($exp, $res);
    }

    public function testDeepMacro()
    {
        $tpl = $this->newPHPTAL('input/doctype.03.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/doctype.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testDtdInline()
    {
        $tpl = $this->newPHPTAL('input/doctype.04.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/doctype.04.html');
        $this->assertEquals($exp, $res);
    }

    public function testClearedOnReexecution()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><whatever/>');

        $this->assertContains("DOCTYPE html PUBLIC", $tpl->execute());
        $this->assertContains("DOCTYPE html PUBLIC", $tpl->execute());

        $tpl->setSource('<whatever/>');

        $this->assertNotContains("DOCTYPE html PUBLIC", $tpl->execute());
        $this->assertNotContains("DOCTYPE html PUBLIC", $tpl->execute());
    }
}
