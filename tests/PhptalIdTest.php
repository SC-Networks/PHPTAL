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
use Tests\Testhelper\MyTrigger;

class PhptalIdTest extends PhpTalTestCase
{
    public function test01(): void
    {
        $trigger = new MyTrigger();

        $exp = Helper::normalizeHtmlFile('output/phptal.id.01.html');
        $tpl = $this->newPHPTAL('input/phptal.id.01.html');
        $tpl->addTrigger('myTable', $trigger);
        $tpl->result = range(0, 3);

        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);

        static::assertSame($exp, $res);
        static::assertFalse($trigger->useCache);

        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);

        static::assertSame($exp, $res);
        static::assertTrue($trigger->useCache);
    }
}
