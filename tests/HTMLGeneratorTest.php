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

class HTMLGeneratorTest extends PhpTalTestCase
{

    public function testTalDoesntConsumeNewline(): void
    {
        $res = $this->newPHPTAL()->setSource('<tal:block tal:condition="true">I\'m on a line</tal:block>
<tal:block tal:condition="true">I\'m on a line</tal:block>')->execute();

        static::assertSame('I\'m on a line
I\'m on a line', $res);
    }
}
