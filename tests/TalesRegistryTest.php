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
 * @link     http://phptal.org/
 */

namespace Tests {

    class TalesRegistryTest extends \Tests\Testcase\PhpTal
    {

        public function testRegisterFunction()
        {
            \PhpTal\TalesRegistry::registerPrefix('registry_test', 'registry_test_callback');
            static::assertSame(
                '<p>ok1</p>',
                $this->newPHPTAL()->setSource('<p tal:content="registry_test:number:1"/>')->execute()
            );
        }

        /**
         * @runInSeparateProcess
         * @expectedException \PhpTal\Exception\UnknownModifierException
         */
        public function testUnregisterFunction()
        {
            $test_prefix = 'testprefix';
            \PhpTal\TalesRegistry::registerPrefix($test_prefix, 'registry_test_callback3');
            \PhpTal\TalesRegistry::unregisterPrefix($test_prefix);
            $this->newPHPTAL()->setSource('<p tal:content="' . $test_prefix . ':"/>')->execute();
        }

        /**
         * @expectedException \PhpTal\Exception\ConfigurationException
         */
        public function testCantUnregisterNonRegistered()
        {
            \PhpTal\TalesRegistry::unregisterPrefix('doesnotexist');
        }

        /**
         * @expectedException \PhpTal\Exception\ConfigurationException
         */
        public function testCantRegisterNonExistant()
        {
            \PhpTal\TalesRegistry::registerPrefix('registry_test_2', 'doesnotexist');
        }

        /**
         * @expectedException \PhpTal\Exception\ConfigurationException
         */
        public function testCantRegisterTwice()
        {
            \PhpTal\TalesRegistry::registerPrefix('registry_test_3', 'registry_test_callback');
            \PhpTal\TalesRegistry::registerPrefix('registry_test_3', 'registry_test_callback');
        }

        public function testCanRegisterFallbackTwice()
        {
            \PhpTal\TalesRegistry::registerPrefix('registry_test_4', 'registry_test_callback', true);
            \PhpTal\TalesRegistry::registerPrefix('registry_test_4', 'registry_test_callback', true);
        }

        public function testCanRegisterOverFallback()
        {
            \PhpTal\TalesRegistry::registerPrefix('registry_test_5', 'registry_test_callback', true);
            \PhpTal\TalesRegistry::registerPrefix('registry_test_5', 'registry_test_callback2');
        }

        public function testCanRegisterFallbackOverRegistered()
        {
            \PhpTal\TalesRegistry::registerPrefix('registry_test_6', 'registry_test_callback2');
            \PhpTal\TalesRegistry::registerPrefix('registry_test_6', 'registry_test_callback', true);
        }

        public function testIsRegisteredIsAccessible()
        {
            static::assertFalse(
                \PhpTal\TalesRegistry::isRegistered('some-custom-modifier')
            );
        }

        public function testIsRegisteredReturnsTrueIfAlreadyRegistered()
        {
            $modifier_key = 'registry_test_7';
            \PhpTal\TalesRegistry::registerPrefix($modifier_key, 'registry_test_callback');
            static::assertTrue(
                \PhpTal\TalesRegistry::isRegistered($modifier_key)
            );
        }
    }
}

namespace {

    // a hack to get those things into the global namespace
    // despite registering a different namespace above

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
}
