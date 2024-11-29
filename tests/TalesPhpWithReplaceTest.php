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

use PhpTal\Exception\PhpNotAllowedException;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class TalesPhpWithReplaceTest extends PhpTalTestCase
{
    public function testMix(): void
    {
        $tpl = $this->newPHPTAL('input/talesphpwithreplace.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/talesphpwithreplace.01.html');
        static::assertSame($exp, $res);
    }

    public function testPhpModifierDisabledThrowsException(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.12.html');
        $tpl->real = 'real value';
        $tpl->foo = 'real';
        $tpl->disallowPhpModifier();
        $this->expectException(PhpNotAllowedException::class);
        $tpl->execute();
    }
}
