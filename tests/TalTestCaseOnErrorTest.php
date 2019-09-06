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
use Tests\Testhelper\OnErrorDummyObject;

class TalTestCaseOnErrorTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/tal-on-error.01.html');
        $tpl->dummy = new OnErrorDummyObject();
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-on-error.01.html');
        static::assertSame($exp, $res);
        $errors = $tpl->getErrors();
        static::assertCount(1, $errors);
        static::assertSame('error thrown', $errors[0]->getMessage());
    }

    public function testEmpty(): void
    {
        $tpl = $this->newPHPTAL('input/tal-on-error.02.html');
        $tpl->dummy = new OnErrorDummyObject();
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-on-error.02.html');
        $errors = $tpl->getErrors();
        static::assertCount(1, $errors);
        static::assertSame('error thrown', $errors[0]->getMessage());
        static::assertSame($exp, $res);
    }

    public function testReplaceStructure(): void
    {
        $tpl = $this->newPHPTAL('input/tal-on-error.03.html');
        $tpl->dummy = new OnErrorDummyObject();
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-on-error.03.html');
        $errors = $tpl->getErrors();
        static::assertCount(1, $errors);
        static::assertSame('error thrown', $errors[0]->getMessage());
        static::assertSame($exp, $res);
    }
}
