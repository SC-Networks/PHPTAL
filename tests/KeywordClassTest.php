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

class KeywordClassTest extends PhpTalTestCase
{
    public function testOnlyKeywords(): void
    {
        $source = <<<HTML
<tal:block content="">nothing</tal:block>
<tal:block content="nothing">nothing</tal:block>
<tal:block content="default">default</tal:block>
<tal:block content="nonextant|"></tal:block>
<tal:block content="nonextant|nothing"></tal:block>
<tal:block content="nonextant|default">default</tal:block>
<tal:block condition="">false</tal:block>
<tal:block condition="nothing">false</tal:block>
<tal:block condition="default">true</tal:block>
<tal:block repeat="nothing">repeat</tal:block>
<tal:block repeat="default">repeat</tal:block>
HTML;
        $expected = <<<HTML


default


default


true


HTML;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($source);
        static::assertSame($expected, $tpl->execute());
    }
}
