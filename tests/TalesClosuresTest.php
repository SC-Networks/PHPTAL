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

use Tests\Testhelper\TestInvocable;

class TalesClosuresTest extends \Tests\Testcase\PhpTal
{
    public function testInvoke()
    {
        $tpl = $this->newPHPTAL();
        $tpl->invoke = new TestInvocable();

        $tpl->setSource("<x tal:content='invoke/testif/works'/>");

        $this->assertEquals("<x>well</x>", $tpl->execute());
    }

    public function testInvokeProperty()
    {
        $tpl = $this->newPHPTAL();
        $tpl->invoke = new TestInvocable();

        $tpl->setSource("<x tal:content='invoke/prop'/>");

        $this->assertEquals("<x>ok</x>", $tpl->execute());
    }
}
