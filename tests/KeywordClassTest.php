<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Andrew Crites <explosion-pills@aysites.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace Tests;

class KeywordClassTest extends \Tests\Testcase\PhpTal
{
    public function testOnlyKeywords()
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
        $this->assertEquals($expected, $tpl->execute());
    }
}
