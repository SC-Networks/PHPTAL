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

class TemplateRepositoryTest extends PhpTalTestCase
{
    public function testLooksInRepo(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplateRepository(TAL_TEST_FILES_DIR . '/input');
        $tpl->setTemplate('phptal.01.html');
        $tpl->execute();
    }

    public function testSkipsNotFound(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplateRepository(__DIR__ . '/invalid');
        $tpl->setTemplateRepository(TAL_TEST_FILES_DIR . '/input');
        $tpl->setTemplateRepository(__DIR__ . '/bogus');
        $tpl->setTemplate('phptal.02.html');
        $tpl->execute();
    }

    public function testFailsIfNoneMatch(): void
    {
        $this->expectException(IOException::class);
        $tpl = $this->newPHPTAL();
        $tpl->setTemplateRepository(__DIR__ . '/invalid');
        $tpl->setTemplateRepository(__DIR__ . '/error');
        $tpl->setTemplateRepository(__DIR__ . '/bogus');
        $tpl->setTemplate('phptal.01.html');
        $tpl->execute();
    }

    public function testRepositoriesAreStrings(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setTemplateRepository('/footest');
        $tpl->setTemplateRepository('bartest');
        $tpl->setTemplateRepository('testbaz/');

        $repos = $tpl->getTemplateRepositories();
        static::assertIsArray($repos);
        $this->assertCount(3, $repos);

        foreach ($repos as $repo) {
            $this->assertIsString($repo);
            static::assertStringContainsString('test', $repo);
        }
    }

    public function testRepositoryClear(): void
    {
        $tpl = $this->newPHPTAL();
        static::assertCount(0, $tpl->getTemplateRepositories());

        $tpl->setTemplateRepository(['foo', 'bar']);
        static::assertCount(2, $tpl->getTemplateRepositories());

        $tpl->clearTemplateRepositories();
        static::assertCount(0, $tpl->getTemplateRepositories());
    }
}
