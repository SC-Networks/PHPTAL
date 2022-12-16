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

use Phptal\Context;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\DummyObjectX;

class TalesIssetNullTest extends PhpTalTestCase
{
    public function testIt()
    {
        $dummy = new DummyObjectX();
        $dummy->foo = null;

        $res = Context::path($dummy, 'method');
        static::assertSame('__call', $res);

        $res = Context::path($dummy, 'foo');
        static::assertNull($res);
    }
}
