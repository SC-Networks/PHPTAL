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

use Tests\Testhelper\OnErrorDummyObject;

class TalOnErrorTest extends \Tests\Testcase\PhpTal
{
    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/tal-on-error.01.html');
        $tpl->dummy = new OnErrorDummyObject();
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-on-error.01.html');
        $this->assertEquals($exp, $res);
        $errors = $tpl->getErrors();
        $this->assertEquals(1, count($errors));
        $this->assertEquals('error thrown', $errors[0]->getMessage());
    }

    public function testEmpty()
    {
        $tpl = $this->newPHPTAL('input/tal-on-error.02.html');
        $tpl->dummy = new OnErrorDummyObject();
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-on-error.02.html');
        $errors = $tpl->getErrors();
        $this->assertEquals(1, count($errors));
        $this->assertEquals('error thrown', $errors[0]->getMessage());
        $this->assertEquals($exp, $res);
    }

    public function testReplaceStructure()
    {
        $tpl = $this->newPHPTAL('input/tal-on-error.03.html');
        $tpl->dummy = new OnErrorDummyObject();
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-on-error.03.html');
        $errors = $tpl->getErrors();
        $this->assertEquals(1, count($errors));
        $this->assertEquals('error thrown', $errors[0]->getMessage());
        $this->assertEquals($exp, $res);
    }
}
