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

class PhptalUsageTest extends \Tests\Testcase\PhpTal
{
    function testMultiUse()
    {
        $t = $this->newPHPTAL();
        $t->title = 'hello';
        $t->setTemplate('../input/multiuse.01.html');
        $a = $t->execute();
        $t->setTemplate('../input/multiuse.02.html');
        $b = $t->execute();
        $this->assertTrue($a != $b, "$a == $b");
        $this->assertContains('hello', $a);
        $this->assertContains('hello', $b);
    }

    function testSetSourceReset()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<p>Hello</p>');
        $res1 = $t->execute();
        $t->setSource('<p>World</p>');
        $res2 = $t->execute();

        $this->assertNotEquals($res1, $res2);
    }
}
