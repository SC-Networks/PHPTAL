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

use PhpTal\Exception\IOException;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\CantFindAThing;
use Tests\Testhelper\Helper;
use Tests\Testhelper\MyCustomSourceResolver;
use Tests\Testhelper\MyTestResolver;

class SourceTest extends PhpTalTestCase
{
    public function testResolver(): void
    {
        $tpl = $this->newPHPTAL()->addSourceResolver(new MyTestResolver())->setTemplate("testing123");
        static::assertSame('<p>found testing123</p>', $tpl->execute());
    }

    public function testResolverCalledEachTime(): void
    {
        $tpl = $this->newPHPTAL()->addSourceResolver($r = new MyTestResolver());
        static::assertSame(0, $r->called);
        $tpl->setTemplate("testing123");
        static::assertSame('<p>found testing123</p>', $tpl->execute());
        static::assertSame(1, $r->called);

        $tpl->setTemplate("testing123");
        static::assertSame('<p>found testing123</p>', $tpl->execute());
        static::assertSame(2, $r->called);
    }

    public function testCustomSource(): void
    {
        $tpl = $this->newPHPTAL()->addSourceResolver($r = new MyCustomSourceResolver());
        $tpl->setTemplate("xyz");

        $res = $tpl->execute();
        static::assertStringContainsString('<p class="custom">xyz ', $res);

        // template should be cached
        static::assertSame($res, $tpl->execute());
        $tpl->setTemplate("xyz");
        static::assertSame($res, $tpl->execute());
    }


    public function testCustomSourceCacheClear(): void
    {
        $tpl = $this->newPHPTAL()->addSourceResolver($r = new MyCustomSourceResolver());
        $tpl->setTemplate("nocache");

        $res = $tpl->execute();
        static::assertStringContainsString('<p class="custom">nocache ', $res);

        // template should not be cached
        static::assertSame($res, $tpl->execute());
        $tpl->setTemplate("nocache");
        static::assertNotSame($res, $tpl->execute());
    }

    public function testFailsIfNotFound(): void
    {
        $this->expectException(IOException::class);
        $tpl = $this->newPHPTAL()->addSourceResolver(new CantFindAThing())->setTemplate("something")->execute();
    }

    public function testFallsBack(): void
    {
        $this
            ->newPHPTAL()
            ->addSourceResolver(new CantFindAThing())
            ->setTemplate(TAL_TEST_FILES_DIR . 'input/phptal.01.html')
            ->execute();
    }

    public function testFallsBack2(): void
    {
        $this
            ->newPHPTAL()
            ->addSourceResolver(new CantFindAThing())
            ->addSourceResolver(new CantFindAThing())
            ->setTemplate(TAL_TEST_FILES_DIR . 'input/phptal.01.html')
            ->execute();
    }

    public function testFallsBack3(): void
    {
        $res = $this
            ->newPHPTAL()
            ->addSourceResolver(new CantFindAThing())
            ->addSourceResolver(new MyTestResolver())
            ->setTemplate('test')
            ->execute();
        static::assertSame('<p>found test</p>', $res);
    }

    public function testFallsBackToResolversFirst(): void
    {
        $res = $this
            ->newPHPTAL()
            ->addSourceResolver(new CantFindAThing())
            ->addSourceResolver(new MyTestResolver())
            ->setTemplate('input/phptal.01.html')
            ->execute();
        static::assertSame('<p>found input/phptal.01.html</p>', Helper::normalizeHtml($res));
    }

    public function testFallsBackToResolversFirst2(): void
    {
        $res = $this
            ->newPHPTAL()
            ->addSourceResolver(new MyTestResolver())
            ->addSourceResolver(new CantFindAThing())
            ->setTemplate('input/phptal.01.html')
            ->execute();
        static::assertSame('<p>found input/phptal.01.html</p>', Helper::normalizeHtml($res));
    }

    public function testOrder(): void
    {
        $res = $this
            ->newPHPTAL()
            ->addSourceResolver(new MyTestResolver())
            ->addSourceResolver(new MyCustomSourceResolver())
            ->setTemplate('test1')
            ->execute();
        static::assertSame('<p>found test1</p>', Helper::normalizeHtml($res));
    }
}
