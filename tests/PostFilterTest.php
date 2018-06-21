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

use Tests\Testhelper\MyPostFilter;
use Tests\Testhelper\MyPostFilter2;

class PostFilterTest extends \Tests\Testcase\PhpTal
{
    public function testIt()
    {
        $filter = new MyPostFilter();
        $tpl = $this->newPHPTAL('input/postfilter.01.html');
        $tpl->setPostFilter($filter);
        $tpl->value = 'my value';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/postfilter.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testMacro()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setPostFilter(new MyPostFilter2());
        $tpl->setSource('<x><y metal:define-macro="macro">test2</y>
        test1
        <z metal:use-macro="macro" />
        </x>
        ');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<x>test-filtered1<y>test-filtered2</y></x>'), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }
}
