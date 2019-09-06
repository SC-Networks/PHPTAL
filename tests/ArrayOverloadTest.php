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
use Tests\Testhelper\MyArray;

class ArrayOverloadTest extends PhpTalTestCase
{
    function testIt()
    {
        $arr = new MyArray();
        for ($i = 0; $i < 20; $i++) {
            $val = new \stdClass();
            $val->foo = "foo value $i";
            $arr->push($val);
        }

        $tpl = $this->newPHPTAL('input/array-overload.01.html');
        $tpl->myobject = $arr;
        $res = $tpl->execute();
        $exp = Helper::normalizeHtmlFile('output/array-overload.01.html');
        $res = Helper::normalizeHtml($res);
        static::assertSame($exp, $res);
    }
}
