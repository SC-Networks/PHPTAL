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
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */



class TalesClosuresTest extends PHPTAL_TestCase
{
    function testInvoke()
    {
        $tpl = $this->newPHPTAL();
        $tpl->invoke = new TestInvocable;

        $tpl->setSource("<x tal:content='invoke/testif/works'/>");

        $this->assertEquals("<x>well</x>", $tpl->execute());
    }

    function testInvokeProperty()
    {
        $tpl = $this->newPHPTAL();
        $tpl->invoke = new TestInvocable;

        $tpl->setSource("<x tal:content='invoke/prop'/>");

        $this->assertEquals("<x>ok</x>", $tpl->execute());
    }
}

class TestInvocable
{
    function __invoke()
    {
        return array('testif'=>array('works'=>'well'));
    }

    public $prop = 'ok';
}
