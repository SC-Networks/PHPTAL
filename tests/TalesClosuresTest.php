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
use Tests\Testhelper\TestInvocable;

class TalesClosuresTest extends PhpTalTestCase
{
    public function testInvoke(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->invoke = new TestInvocable();

        $tpl->setSource("<x tal:content='invoke/testif/works'/>");

        static::assertSame("<x>well</x>", $tpl->execute());
    }

    public function testInvokeProperty(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->invoke = new TestInvocable();

        $tpl->setSource("<x tal:content='invoke/prop'/>");

        static::assertSame("<x>ok</x>", $tpl->execute());
    }
}
