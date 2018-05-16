<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  SVN: $Id: $
 * @link     http://phptal.org/
 */


class TalesRegistryTest extends PHPTAL_TestCase
{
    function testInstance()
    {
        $this->assertSame(\PhpTal\TalesRegistry::getInstance(), \PhpTal\TalesRegistry::getInstance());
        $this->assertInstanceOf('\PhpTal\TalesRegistry',\PhpTal\TalesRegistry::getInstance());
    }

    function testRegisterFunction()
    {
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test', 'registry_test_callback');

        $this->assertEquals('<p>ok1</p>', $this->newPHPTAL()->setSource('<p tal:content="registry_test:number:1"/>')->execute());
    }

    /**
     * Define constants after requires/includes
     * @param Text_Template $template
     * @return void
     */
    public function prepareTemplate( Text_Template $template ) {
        $template->setVar( array(
            'constants'    => '',
            'zz_constants' => PHPUnit_Util_GlobalState::getConstantsAsString()
        ));
        parent::prepareTemplate( $template );
    }

    /**
     * @runInSeparateProcess
     * @expectedException \PhpTal\Exception\UnknownModifierException
     */
    function testUnregisterFunction()
    {
        $test_prefix = 'testprefix';
        \PhpTal\TalesRegistry::getInstance()->registerPrefix($test_prefix, 'registry_test_callback3');
        \PhpTal\TalesRegistry::getInstance()->unregisterPrefix($test_prefix);
        $this->newPHPTAL()->setSource('<p tal:content="'.$test_prefix.':"/>')->execute();
    }

    /**
     * @expectedException \PhpTal\Exception\ConfigurationException
     */
    function testCantUnregisterNonRegistered()
    {
        \PhpTal\TalesRegistry::getInstance()->unregisterPrefix('doesnotexist');
    }

    /**
     * @expectedException \PhpTal\Exception\ConfigurationException
     */
    function testCantRegisterNonExistant()
    {
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_2', 'doesnotexist');
    }

    /**
     * @expectedException \PhpTal\Exception\ConfigurationException
     */
    function testCantRegisterTwice()
    {
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_3', 'registry_test_callback');
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_3', 'registry_test_callback');
    }

    function testCanRegisterFallbackTwice()
    {
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_4', 'registry_test_callback', true);
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_4', 'registry_test_callback', true);
    }

    function testCanRegisterOverFallback()
    {
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_5', 'registry_test_callback', true);
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_5', 'registry_test_callback2');
    }

    function testCanRegisterFallbackOverRegistered()
    {
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_6', 'registry_test_callback2');
        \PhpTal\TalesRegistry::getInstance()->registerPrefix('registry_test_6', 'registry_test_callback', true);
    }
}

function registry_test_callback($arg, $nothrow)
{
    return '"ok" . ' . \PhpTal\Php\TalesInternal::compileToPHPExpressions($arg);
}

function registry_test_callback2($arg, $nothrow)
{
    return '"ok2" . ' . \PhpTal\Php\TalesInternal::compileToPHPExpressions($arg);
}

function registry_test_callback3($arg, $nothrow)
{
    return '"ok3" . ' . \PhpTal\Php\TalesInternal::compileToPHPExpressions($arg);
}
