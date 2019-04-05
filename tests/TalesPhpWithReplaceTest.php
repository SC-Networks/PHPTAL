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

namespace Test;

use PhpTal\Exception\PhpNotAllowedException;

class TalesPhpWithReplaceTest extends \Tests\Testcase\PhpTal
{
    public function testMix()
    {
        $tpl = $this->newPHPTAL('input/talesphpwithreplace.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/talesphpwithreplace.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testPhpModifierDisabledThrowsException()
    {
        $tpl = $this->newPHPTAL('input/tal-define.12.html');
        $tpl->real = 'real value';
        $tpl->foo = 'real';
        $tpl->disallowPhpModifier();
        $this->expectException(PhpNotAllowedException::class);
        $tpl->execute();
    }
}
