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

use Tests\Testhelper\PhptalPathTest_DummyClass;

class PhptalPathTest extends \Tests\Testcase\PhpTal
{
    public function testZeroIndex()
    {
        $data   = array(1, 0, 3);
        $result = \PhpTal\Context::path($data, '0');
        $this->assertEquals(1, $result);
    }

    public function testProtectedMethodIgnored()
    {
        $tpl = $this->newPHPTAL();
        $tpl->obj = new PhptalPathTest_DummyClass();
        $tpl->setSource('<test tal:content="obj/protTest"></test>');

        $this->assertEquals('<test>prot-property</test>', $tpl->execute());
    }

    public function testPublicMethodFirst()
    {
        $tpl = $this->newPHPTAL();
        $tpl->obj = new PhptalPathTest_DummyClass();
        $tpl->setSource('<test tal:content="obj/pubTest"></test>');

        $this->assertEquals('<test>pub-method</test>', $tpl->execute());
    }

    public function testNestedArrays()
    {
        $tpl = $this->newPHPTAL();
        $tpl->arr = array('items' => array (
                array (
                    'details' => array()
                )
            ) );
        $tpl->setSource('<test tal:content="arr/items/0/details/0/notfound"></test>');

        try {
            $output = $tpl->execute();
        } catch (\PhpTal\Exception\VariableNotFoundException $E) {
            $this->assertRegExp("/Array 'details' doesn/", $E->getMessage());
            return;
        }

        $this->fail('Execute must throw \PhpTal\Exception\VariableNotFoundException');
    }

    public function testDefinedButNullProperty()
    {
        $src = <<<EOS
<span tal:content="o/foo"/>
<span tal:content="o/foo | string:blah"/>
<span tal:content="o/bar" tal:on-error="string:ok"/>
EOS;
        $exp = <<<EOS
<span></span>
<span>blah</span>
ok
EOS;

        $tpl = $this->newPHPTAL();
        $tpl->setSource($src, __FILE__);
        $tpl->o = new PhptalPathTest_DummyClass();
        $res = $tpl->execute();

        $this->assertEquals($exp, $res);
    }
}
