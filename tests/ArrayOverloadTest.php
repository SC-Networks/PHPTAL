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

use Tests\Testhelper\MyArray;

class ArrayOverloadTest extends \Tests\Testcase\PhpTal
{
    function testIt()
    {
        $arr = new MyArray();
        for ($i=0; $i<20; $i++) {
            $val = new \stdClass();
            $val->foo = "foo value $i";
            $arr->push($val);
        }

        $tpl = $this->newPHPTAL('input/array-overload.01.html');
        $tpl->myobject = $arr;
        $res = $tpl->execute();
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/array-overload.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $this->assertEquals($exp, $res);
    }
}
