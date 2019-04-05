<?php

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace Tests;

use Tests\Testhelper\CantFindAThing;
use Tests\Testhelper\MyCustomSourceResolver;
use Tests\Testhelper\MyTestResolver;

class SourceTest extends \Tests\Testcase\PhpTal
{
    public function testResolver()
    {
        $tpl = $this->newPHPTAL()->addSourceResolver(new MyTestResolver())->setTemplate("testing123");
        $this->assertEquals('<p>found testing123</p>', $tpl->execute());
    }

    public function testResolverCalledEachTime()
    {
        $tpl = $this->newPHPTAL()->addSourceResolver($r = new MyTestResolver());
        $this->assertEquals(0,$r->called);
        $tpl->setTemplate("testing123");
        $this->assertEquals('<p>found testing123</p>', $tpl->execute());
        $this->assertEquals(1,$r->called);

        $tpl->setTemplate("testing123");
        $this->assertEquals('<p>found testing123</p>', $tpl->execute());
        $this->assertEquals(2,$r->called);
    }

    public function testCustomSource()
    {
        $tpl = $this->newPHPTAL()->addSourceResolver($r = new MyCustomSourceResolver());
        $tpl->setTemplate("xyz");

        $res = $tpl->execute();
        $this->assertContains('<p class="custom">xyz ', $res);

        // template should be cached
        $this->assertEquals($res, $tpl->execute());
        $tpl->setTemplate("xyz");
        $this->assertEquals($res, $tpl->execute());
    }


    public function testCustomSourceCacheClear()
    {
        $tpl = $this->newPHPTAL()->addSourceResolver($r = new MyCustomSourceResolver());
        $tpl->setTemplate("nocache");

        $res = $tpl->execute();
        $this->assertContains('<p class="custom">nocache ', $res);

        // template should not be cached
        $this->assertEquals($res, $tpl->execute());
        $tpl->setTemplate("nocache");
        $this->assertNotEquals($res, $tpl->execute());
    }

    /**
     * @expectedException \PhpTal\Exception\IOException
     */
    public function testFailsIfNotFound()
    {
        $tpl = $this->newPHPTAL()->addSourceResolver(new CantFindAThing())->setTemplate("something")->execute();
    }

    public function testFallsBack()
    {
        $this->newPHPTAL()->addSourceResolver(new CantFindAThing())->setTemplate('../input/phptal.01.html')->execute();
    }

    public function testFallsBack2()
    {
        $this->newPHPTAL()->addSourceResolver(new CantFindAThing())->addSourceResolver(new CantFindAThing())->setTemplate('../input/phptal.01.html')->execute();
    }

    public function testFallsBack3()
    {
        $res = $this->newPHPTAL()->addSourceResolver(new CantFindAThing())->addSourceResolver(new MyTestResolver())->setTemplate('test')->execute();
        $this->assertEquals('<p>found test</p>', $res);
    }

    public function testFallsBackToResolversFirst()
    {
        $res = $this->newPHPTAL()->addSourceResolver(new CantFindAThing())->addSourceResolver(new MyTestResolver())->setTemplate('input/phptal.01.html')->execute();
        $this->assertEquals('<p>found input/phptal.01.html</p>', \Tests\Testhelper\Helper::normalizeHtml($res));
    }

    public function testFallsBackToResolversFirst2()
    {
        $res = $this->newPHPTAL()->addSourceResolver(new MyTestResolver())->addSourceResolver(new CantFindAThing())->setTemplate('input/phptal.01.html')->execute();
        $this->assertEquals('<p>found input/phptal.01.html</p>', \Tests\Testhelper\Helper::normalizeHtml($res));
    }

    public function testOrder()
    {
        $res = $this->newPHPTAL()->addSourceResolver(new MyTestResolver())->addSourceResolver(new MyCustomSourceResolver())->setTemplate('test1')->execute();
        $this->assertEquals('<p>found test1</p>', \Tests\Testhelper\Helper::normalizeHtml($res));
    }
}
