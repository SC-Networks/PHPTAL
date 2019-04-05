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

use Tests\Testhelper\DummyObjectX;

class TalesIssetNullTest extends \Tests\Testcase\PhpTal
{
    function testIt()
    {
        $dummy = new DummyObjectX();
        $dummy->foo = null;

        $res = \Phptal\Context::path($dummy, 'method');
        $this->assertEquals('__call', $res);

        $res = \Phptal\Context::path($dummy, 'foo');
        $this->assertEquals(null, $res);
    }
}
