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

class PhptalCacheTest extends PhpTalTestCase
{
    private $cacheTestRnd;

    public function setUp(): void
    {
        parent::setUp();
        $this->cacheTestRnd = time() . mt_rand();
    }

    private function PHPTALWithSource(string $source): PHPTAL
    {
        $tpl = new PHPTAL();
        $tpl->setForceReparse(false);
        $tpl->setSource($source . "<!-- {$this->cacheTestRnd} -->"); // avoid cached templates from previous test runs
        return $tpl;
    }

    public function testBasicCache(): void
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="1h" tal:content="var" />');
        $tpl->var = 'SUCCESS';
        static::assertStringContainsString("SUCCESS", $tpl->execute());

        $tpl->var = 'FAIL';
        $res = $tpl->execute();
        static::assertStringNotContainsString("FAIL", $res);
        static::assertStringContainsString("SUCCESS", $res);
    }

    /**
     * tal:define is also cached
     */
    public function testDefine(): void
    {
        $tpl = $this->PHPTALWithSource('<div tal:define="display var" phptal:cache="1h">${display}</div>');
        $tpl->var = 'SUCCESS';
        static::assertStringContainsString("SUCCESS", $tpl->execute());

        $tpl->var = 'FAIL';
        $res = $tpl->execute();
        static::assertStringNotContainsString("FAIL", $res);
        static::assertStringContainsString("SUCCESS", $res);
    }

    public function testTimedExpiry(): void
    {

        $tpl = $this->PHPTALWithSource('<div phptal:cache="1s" tal:content="var" />');
        $tpl->var = 'FIRST';
        static::assertStringContainsString("FIRST", $tpl->execute());

        sleep(2); // wait for it to expire :)

        $tpl->var = 'SECOND';
        $res = $tpl->execute();
        static::assertStringContainsString("SECOND", $res);
        static::assertStringNotContainsString("FIRST", $res);
    }

    public function testCacheInStringSource(): void
    {
        $source = '<div phptal:cache="1d" tal:content="var" />';
        $tpl = $this->PHPTALWithSource($source);
        $tpl->var = 'FIRST';
        static::assertStringContainsString("FIRST", $tpl->execute());

        $tpl = $this->PHPTALWithSource($source);
        $tpl->var = 'SECOND';
        static::assertStringContainsString("FIRST", $tpl->execute());
    }

    public function testCleanUpCache(): void
    {
        $source = '<div phptal:cache="1d" tal:content="var" />';

        $tpl = $this->PHPTALWithSource($source);
        $tpl->cleanUpCache();

        $tpl->var = 'FIRST';
        static::assertStringContainsString("FIRST", $tpl->execute());

        $tpl = $this->PHPTALWithSource($source);
        $tpl->var = 'SECOND';
        $res = $tpl->execute();
        static::assertStringContainsString("FIRST", $res);
        static::assertStringNotContainsString("SECOND", $res);

        $tpl->cleanUpCache();

        $tpl->var = 'THIRD';
        $res = $tpl->execute();
        static::assertStringContainsString("THIRD", $res);
        static::assertStringNotContainsString("SECOND", $res);
        static::assertStringNotContainsString("FIRST", $res);
    }

    public function testPerExpiry(): void
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="1d per var" tal:content="var" />');
        $tpl->var = 'FIRST';
        static::assertStringContainsString("FIRST", $tpl->execute());
        $tpl->var = 'SECOND';
        $res = $tpl->execute();
        static::assertStringContainsString("SECOND", $res);
        static::assertStringNotContainsString("FIRST", $res);
    }

    public function testVersions(): void
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="40s per version" tal:content="var" />');

        $tpl->var = 'FIRST';
        $tpl->version = '1';
        static::assertStringContainsString("FIRST", $tpl->execute());

        $tpl->var = 'FAIL';
        $tpl->version = '1';
        $res = $tpl->execute();
        static::assertStringContainsString("FIRST", $res);
        static::assertStringNotContainsString("FAIL", $res);

        $tpl->var = 'THRID';
        $tpl->version = '3';
        $res = $tpl->execute();
        static::assertStringContainsString("THRID", $res);
        static::assertStringNotContainsString("SECOND", $res);

        $tpl->var = 'FAIL';
        $tpl->version = '3';
        $res = $tpl->execute();
        static::assertStringContainsString("THRID", $res);
        static::assertStringNotContainsString("FAIL", $res);
    }

    public function testVariableExpiry(): void
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="vartime s" tal:content="var" />');
        $tpl->vartime = 0;
        $tpl->var = 'FIRST';
        static::assertStringContainsString("FIRST", $tpl->execute());

        $tpl->var = 'SECOND'; // time is 0 = no cache
        static::assertStringContainsString("SECOND", $tpl->execute());

        $tpl->vartime = 60;   // get it to cache it
        $tpl->var = 'SECOND';
        static::assertStringContainsString("SECOND", $tpl->execute());

        $tpl->var = 'THRID';
        $res = $tpl->execute();
        static::assertStringContainsString("SECOND", $res);
        static::assertStringNotContainsString("THRID", $res); // should be cached
    }

    public function testVariableExpressionExpiry(): void
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="tales/vartime s" tal:content="var" />');
        $tpl->tales = ['vartime' => 0];
        $tpl->var = 'FIRST';
        static::assertStringContainsString("FIRST", $tpl->execute());

        $tpl->var = 'SECOND'; // time is 0 = no cache
        static::assertStringContainsString("SECOND", $tpl->execute());

        $tpl->tales = ['vartime' => 60];   // get it to cache it
        $tpl->var = 'SECOND';
        static::assertStringContainsString("SECOND", $tpl->execute());

        $tpl->var = 'THRID';
        $res = $tpl->execute();
        static::assertStringContainsString("SECOND", $res);
        static::assertStringNotContainsString("THRID", $res); // should be cached
    }
}
