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

use PhpTal\PHPTAL;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;
use Tests\Testhelper\TestCodeCache;

class CodeCacheTest extends PhpTalTestCase
{
    /**
     * @var PHPTAL
     */
    private $phptal;

    /**
     * @var string
     */
    private $codeDestination;

    /**
     * @var int
     */
    private $subpathRecursionLevel = 0;

    private function resetPHPTAL()
    {
        $this->phptal = new TestCodeCache();
        $this->phptal->setForceReparse(false);
        static::assertFalse($this->phptal->getForceReparse());

        $tmpdirpath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'temp_output';
        if (!is_dir($tmpdirpath)) {
            mkdir($tmpdirpath);
        }

        static::assertDirectoryExists($tmpdirpath);
        static::assertTrue(is_writable($tmpdirpath));

        $this->phptal->setPhpCodeDestination($tmpdirpath);
        $this->codeDestination = $this->phptal->getPhpCodeDestination();
    }

    private function clearCache()
    {
        $subpath = str_repeat('*/', $this->subpathRecursionLevel);
        static::assertStringContainsString(
            DIRECTORY_SEPARATOR . 'temp_output' . DIRECTORY_SEPARATOR,
            $this->codeDestination
        );
        foreach (glob($this->codeDestination . $subpath . 'tpl_*', GLOB_NOSORT) as $tpl) {
            static::assertTrue(unlink($tpl), "Delete $tpl");
        }
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->resetPHPTAL();
        $this->clearCache();
    }

    public function tearDown(): void
    {
        $this->clearCache();
        parent::tearDown();
    }

    public function testNoParseOnReexecution(): void
    {
        $this->phptal->setSource('<p>hello</p>');
        $this->phptal->execute();

        static::assertTrue($this->phptal->testHasParsed, "Initial parse");

        $this->phptal->testHasParsed = false;
        $this->phptal->execute();

        static::assertFalse($this->phptal->testHasParsed, "No reparse");
    }

    public function testNoParseOnReset(): void
    {
        $this->phptal->setSource('<p>hello2</p>');
        $this->phptal->execute();

        static::assertTrue($this->phptal->testHasParsed, "Initial parse");

        $this->resetPHPTAL();

        $this->phptal->setSource('<p>hello2</p>');
        $this->phptal->execute();

        static::assertFalse($this->phptal->testHasParsed, "No reparse");
    }

    public function testReparseAfterTouch(): void
    {
        if (!is_writable('input/code-cache-01.html')) {
            $this->markTestSkipped();
        }

        $time1 = filemtime('input/code-cache-01.html');
        touch('input/code-cache-01.html', time());
        clearstatcache();
        sleep(1);
        $time2 = filemtime('input/code-cache-01.html');
        $this->assertNotEquals($time1, $time2, "touch() must work");


        $this->phptal->setTemplate('input/code-cache-01.html');
        $this->phptal->execute();
        static::assertTrue($this->phptal->testHasParsed, "Initial parse");

        $this->resetPHPTAL();

        touch('input/code-cache-01.html', $time1);
        clearstatcache();

        $this->phptal->setTemplate('input/code-cache-01.html');
        $this->phptal->execute();

        static::assertTrue($this->phptal->testHasParsed, "Reparse");
    }

    public function testGarbageRemovalWithSubpathRecursion(): void
    {
        $this->executeGarbageRemovalTest(3);
    }

    public function testGarbageRemoval(): void
    {
        $this->executeGarbageRemovalTest(0);
    }

    public function testNested(): void
    {
        $this->phptal->setSource(
            '<div phptal:cache="1m per string: 1"> 1 <div phptal:cache="1h per string: 2"> 2 </div> </div>'
        );

        static::assertSame(
            Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'),
            Helper::normalizeHtml($this->phptal->execute()), "1st run"
        );
        static::assertSame(
            Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'),
            Helper::normalizeHtml($this->phptal->execute()), "2nd run"
        );
        static::assertSame(
            Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'),
            Helper::normalizeHtml($this->phptal->execute()), "3rd run"
        );
        static::assertSame(Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'),
            Helper::normalizeHtml($this->phptal->execute()), "4th run");
    }

    private function executeGarbageRemovalTest(int $subpath_recursion)
    {
        $this->subpathRecursionLevel = $subpath_recursion;

        $src = '<test uniq="' . time() . mt_rand() . '" phptal:cache="1d" />';
        $this->phptal->setSubpathRecursionLevel($this->subpathRecursionLevel);
        $this->phptal->setSource($src);
        $this->phptal->execute();

        static::assertTrue($this->phptal->testHasParsed, "Parse");

        $this->phptal->testHasParsed = false;
        $this->phptal->setSource($src);
        $this->phptal->execute();

        static::assertFalse($this->phptal->testHasParsed, "Reparse!?");

        $subpath = str_repeat('*/', $this->subpathRecursionLevel);
        $files = glob($this->codeDestination . $subpath . 'tpl_*', GLOB_NOSORT);

        static::assertCount(2, $files); // one for template, one for cache
        foreach ($files as $file) {
            $this->assertFileExists($file);
            touch($file, time() - 3600 * 24 * 100);
        }
        clearstatcache();

        $this->phptal->cleanUpGarbage(); // should delete all files

        clearstatcache();

        // can't check for reparse, because PHPTAL uses function_exists() as a shortcut!
        foreach ($files as $file) {
            $this->assertFileNotExists($file);
        }
    }
}
