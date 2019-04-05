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

class PhptalCacheTest extends \Tests\Testcase\PhpTal
{
    public function setUp()
    {
        parent::setUp();
        $this->PhptalCacheTest_random =  time().mt_rand();
    }

    private function PHPTALWithSource($source)
    {
        global $PhptalCacheTest_random;

        $tpl = new \PhpTal\PHPTAL();
        $tpl->setForceReparse(false);
        $tpl->setSource($source."<!-- {$this->PhptalCacheTest_random} -->"); // avoid cached templates from previous test runs
        return $tpl;
    }

    public function testBasicCache()
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="1h" tal:content="var" />');
        $tpl->var = 'SUCCESS';
        $this->assertContains( "SUCCESS", $tpl->execute() );

        $tpl->var = 'FAIL';
        $res = $tpl->execute();
        $this->assertNotContains( "FAIL", $res );
        $this->assertContains( "SUCCESS", $res );
    }

    /**
     * tal:define is also cached
     */
    public function testDefine()
    {
        $tpl = $this->PHPTALWithSource('<div tal:define="display var" phptal:cache="1h">${display}</div>');
        $tpl->var = 'SUCCESS';
        $this->assertContains( "SUCCESS", $tpl->execute() );

        $tpl->var = 'FAIL';
        $res = $tpl->execute();
        $this->assertNotContains( "FAIL", $res );
        $this->assertContains( "SUCCESS", $res );
    }

    public function testTimedExpiry()
    {

        $tpl = $this->PHPTALWithSource('<div phptal:cache="1s" tal:content="var" />');
        $tpl->var = 'FIRST';
        $this->assertContains( "FIRST", $tpl->execute() );

        sleep(2); // wait for it to expire :)

        $tpl->var = 'SECOND';
        $res = $tpl->execute();
        $this->assertContains( "SECOND", $res );
        $this->assertNotContains( "FIRST", $res );
    }

    public function testCacheInStringSource()
    {
        $source = '<div phptal:cache="1d" tal:content="var" />';
        $tpl = $this->PHPTALWithSource($source);
        $tpl->var = 'FIRST';
        $this->assertContains( "FIRST", $tpl->execute() );

        $tpl = $this->PHPTALWithSource($source);
        $tpl->var = 'SECOND';
        $this->assertContains( "FIRST", $tpl->execute() );
    }

    public function testCleanUpCache()
    {
        $source = '<div phptal:cache="1d" tal:content="var" />';

        $tpl = $this->PHPTALWithSource($source);
        $tpl->cleanUpCache();

        $tpl->var = 'FIRST';
        $this->assertContains( "FIRST", $tpl->execute() );

        $tpl = $this->PHPTALWithSource($source);
        $tpl->var = 'SECOND';
        $res = $tpl->execute();
        $this->assertContains( "FIRST", $res );
        $this->assertNotContains( "SECOND", $res );

        $tpl->cleanUpCache();

        $tpl->var = 'THIRD';
        $res = $tpl->execute();
        $this->assertContains( "THIRD", $res );
        $this->assertNotContains( "SECOND", $res );
        $this->assertNotContains( "FIRST", $res );
    }

    public function testPerExpiry()
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="1d per var" tal:content="var" />');
        $tpl->var = 'FIRST';
        $this->assertContains( "FIRST", $tpl->execute() );
        $tpl->var = 'SECOND';
        $res = $tpl->execute();
        $this->assertContains( "SECOND", $res );
        $this->assertNotContains( "FIRST", $res );
    }

    public function testVersions()
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="40s per version" tal:content="var" />');

        $tpl->var = 'FIRST';
        $tpl->version = '1';
        $this->assertContains( "FIRST", $tpl->execute() );

        $tpl->var = 'FAIL';
        $tpl->version = '1';
        $res = $tpl->execute();
        $this->assertContains( "FIRST", $res );
        $this->assertNotContains( "FAIL", $res );

        $tpl->var = 'THRID';
        $tpl->version = '3';
        $res = $tpl->execute();
        $this->assertContains( "THRID", $res );
        $this->assertNotContains( "SECOND", $res );

        $tpl->var = 'FAIL';
        $tpl->version = '3';
        $res = $tpl->execute();
        $this->assertContains( "THRID", $res );
        $this->assertNotContains( "FAIL", $res );
    }

    public function testVariableExpiry()
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="vartime s" tal:content="var" />');
        $tpl->vartime = 0;
        $tpl->var = 'FIRST';
        $this->assertContains( "FIRST", $tpl->execute() );

        $tpl->var = 'SECOND'; // time is 0 = no cache
        $this->assertContains( "SECOND", $tpl->execute() );

        $tpl->vartime = 60;   // get it to cache it
        $tpl->var = 'SECOND';
        $this->assertContains( "SECOND", $tpl->execute() );

        $tpl->var = 'THRID';
        $res = $tpl->execute();
        $this->assertContains( "SECOND", $res );
        $this->assertNotContains( "THRID", $res ); // should be cached
    }

    public function testVariableExpressionExpiry()
    {
        $tpl = $this->PHPTALWithSource('<div phptal:cache="tales/vartime s" tal:content="var" />');
        $tpl->tales = array('vartime' => 0);
        $tpl->var = 'FIRST';
        $this->assertContains( "FIRST", $tpl->execute() );

        $tpl->var = 'SECOND'; // time is 0 = no cache
        $this->assertContains( "SECOND", $tpl->execute() );

        $tpl->tales = array('vartime' => 60);   // get it to cache it
        $tpl->var = 'SECOND';
        $this->assertContains( "SECOND", $tpl->execute() );

        $tpl->var = 'THRID';
        $res = $tpl->execute();
        $this->assertContains( "SECOND", $res );
        $this->assertNotContains( "THRID", $res ); // should be cached
    }
}
