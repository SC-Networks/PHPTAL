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

use Tests\Testhelper\OverloadTestClass;

class OverloadingTest extends \Tests\Testcase\PhpTal
{
    public function test()
    {
        $tpl = $this->newPHPTAL('input/overloading-01.html');
        $tpl->object = new OverloadTestClass();
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/overloading-01.html');
        $this->assertEquals($exp, $res);
    }
}
