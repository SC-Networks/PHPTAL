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

use Tests\Testhelper\PHPTAL_TestCodeCache;

class CodeCacheTest extends \Tests\Testcase\PhpTal
{
    private $phptal;
    private $codeDestination;
    private $subpathRecursionLevel = 0;

    private function resetPHPTAL()
    {
        $this->phptal = new PHPTAL_TestCodeCache();
        $this->phptal->setForceReparse(false);
        $this->assertFalse($this->phptal->getForceReparse());

        $tmpdirpath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'temp_output';
        if (!is_dir($tmpdirpath)) mkdir($tmpdirpath);

        $this->assertTrue(is_dir($tmpdirpath));
        $this->assertTrue(is_writable($tmpdirpath));

        $this->phptal->setPhpCodeDestination($tmpdirpath);
        $this->codeDestination = $this->phptal->getPhpCodeDestination();
    }

    private function clearCache()
    {
        $subpath = str_repeat('*/', $this->subpathRecursionLevel);
        $this->assertStringContainsString(DIRECTORY_SEPARATOR.'temp_output'.DIRECTORY_SEPARATOR, $this->codeDestination);
        foreach (glob($this->codeDestination . $subpath . 'tpl_*', GLOB_NOSORT) as $tpl) {
            $this->assertTrue(unlink($tpl), "Delete $tpl");
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

    public function testNoParseOnReexecution()
    {
        $this->phptal->setSource('<p>hello</p>');
        $this->phptal->execute();

        $this->assertTrue($this->phptal->testHasParsed, "Initial parse");

        $this->phptal->testHasParsed = false;
        $this->phptal->execute();

        $this->assertFalse($this->phptal->testHasParsed, "No reparse");
    }

    public function testNoParseOnReset()
    {
        $this->phptal->setSource('<p>hello2</p>');
        $this->phptal->execute();

        $this->assertTrue($this->phptal->testHasParsed, "Initial parse");

        $this->resetPHPTAL();

        $this->phptal->setSource('<p>hello2</p>');
        $this->phptal->execute();

        $this->assertFalse($this->phptal->testHasParsed, "No reparse");
    }

    public function testReparseAfterTouch()
    {
        if (!is_writable('input/code-cache-01.html')) $this->markTestSkipped();

        $time1 = filemtime('input/code-cache-01.html');
        touch('input/code-cache-01.html', time());
        clearstatcache();
        sleep(1);
        $time2 = filemtime('input/code-cache-01.html');
        $this->assertNotEquals($time1, $time2, "touch() must work");


        $this->phptal->setTemplate('input/code-cache-01.html');
        $this->phptal->execute();
        $this->assertTrue($this->phptal->testHasParsed, "Initial parse");

        $this->resetPHPTAL();

        touch('input/code-cache-01.html', $time1);
        clearstatcache();

        $this->phptal->setTemplate('input/code-cache-01.html');
        $this->phptal->execute();

        $this->assertTrue($this->phptal->testHasParsed, "Reparse");
    }

    public function testGarbageRemovalWithSubpathRecursion()
    {
        $this->executeGarbageRemovalTest(3);
    }

    public function testGarbageRemoval()
    {
        $this->executeGarbageRemovalTest(0);
    }

    public function testNested()
    {
        $this->phptal->setSource('<div phptal:cache="1m per string: 1"> 1 <div phptal:cache="1h per string: 2"> 2 </div> </div>');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'), \Tests\Testhelper\Helper::normalizeHtml($this->phptal->execute()), "1st run");
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'), \Tests\Testhelper\Helper::normalizeHtml($this->phptal->execute()), "2nd run");
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'), \Tests\Testhelper\Helper::normalizeHtml($this->phptal->execute()), "3rd run");
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div> 1 <div> 2 </div> </div>'), \Tests\Testhelper\Helper::normalizeHtml($this->phptal->execute()), "4th run");
    }

    private function executeGarbageRemovalTest($subpath_recursion)
    {
        $this->subpathRecursionLevel = $subpath_recursion;

        $src = '<test uniq="'.time().mt_rand().'" phptal:cache="1d" />';
        $this->phptal->setSubpathRecursionLevel($this->subpathRecursionLevel);
        $this->phptal->setSource($src);
        $this->phptal->execute();

        $this->assertTrue($this->phptal->testHasParsed, "Parse");

        $this->phptal->testHasParsed = false;
        $this->phptal->setSource($src);
        $this->phptal->execute();

        $this->assertFalse($this->phptal->testHasParsed, "Reparse!?");

        $subpath = str_repeat('*/', $this->subpathRecursionLevel);
        $files = glob($this->codeDestination . $subpath . 'tpl_*', GLOB_NOSORT);

        $this->assertEquals(2, count($files)); // one for template, one for cache
        foreach ($files as $file) {
            $this->assertFileExists($file);
            touch($file, time() - 3600*24*100);
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
