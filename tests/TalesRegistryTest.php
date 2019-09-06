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

namespace {

    use PhpTal\Php\TalesInternal;

    function registry_test_callback(string $arg, bool $nothrow): string
    {
        return '"ok" . ' . TalesInternal::compileToPHPExpressions($arg);
    }

    function registry_test_callback2(string $arg, bool $nothrow): string
    {
        return '"ok2" . ' . TalesInternal::compileToPHPExpressions($arg);
    }

    function registry_test_callback3(string $arg, bool $nothrow): string
    {
        return '"ok3" . ' . TalesInternal::compileToPHPExpressions($arg);
    }
}

namespace Tests {

    use PhpTal\Exception\ConfigurationException;
    use PhpTal\Exception\UnknownModifierException;
    use PhpTal\TalesRegistry;
    use Tests\Testcase\PhpTalTestCase;

    class TalesRegistryTest extends PhpTalTestCase
    {

        public function testRegisterFunction(): void
        {
            TalesRegistry::registerPrefix('registry_test', 'registry_test_callback');
            static::assertSame(
                '<p>ok1</p>',
                $this->newPHPTAL()->setSource('<p tal:content="registry_test:number:1"/>')->execute()
            );
        }

        public function testRegisterFunctionAcceptsClosure(): void
        {
            TalesRegistry::registerPrefix('foobar', function (string $arg) {
                return '"ok' . $arg . '"';
            });
            static::assertSame(
                '<p>ok1</p>',
                $this->newPHPTAL()->setSource('<p tal:content="foobar:1"/>')->execute()
            );
        }

        /**
         * @runInSeparateProcess
         */
        public function testUnregisterFunction(): void
        {
            $this->expectException(UnknownModifierException::class);
            $test_prefix = 'testprefix';
            TalesRegistry::registerPrefix($test_prefix, 'registry_test_callback3');
            TalesRegistry::unregisterPrefix($test_prefix);
            $this->newPHPTAL()->setSource('<p tal:content="' . $test_prefix . ':"/>')->execute();
        }

        public function testCantUnregisterNonRegistered(): void
        {
            $this->expectException(ConfigurationException::class);
            TalesRegistry::unregisterPrefix('doesnotexist');
        }

        public function testCantRegisterNonExistant(): void
        {
            $this->expectException(ConfigurationException::class);
            TalesRegistry::registerPrefix('registry_test_2', 'doesnotexist');
        }

        public function testCantRegisterTwice(): void
        {
            $this->expectException(ConfigurationException::class);
            TalesRegistry::registerPrefix('registry_test_3', 'registry_test_callback');
            TalesRegistry::registerPrefix('registry_test_3', 'registry_test_callback');
        }

        public function testCanRegisterFallbackTwice(): void
        {
            TalesRegistry::registerPrefix('registry_test_4', 'registry_test_callback', true);
            TalesRegistry::registerPrefix('registry_test_4', 'registry_test_callback', true);
        }

        public function testCanRegisterOverFallback(): void
        {
            TalesRegistry::registerPrefix('registry_test_5', 'registry_test_callback', true);
            TalesRegistry::registerPrefix('registry_test_5', 'registry_test_callback2');
        }

        public function testCanRegisterFallbackOverRegistered(): void
        {
            TalesRegistry::registerPrefix('registry_test_6', 'registry_test_callback2');
            TalesRegistry::registerPrefix('registry_test_6', 'registry_test_callback', true);
        }

        public function testIsRegisteredIsAccessible(): void
        {
            static::assertFalse(TalesRegistry::isRegistered('some-custom-modifier'));
        }

        public function testIsRegisteredReturnsTrueIfAlreadyRegistered(): void
        {
            $modifier_key = 'registry_test_7';
            TalesRegistry::registerPrefix($modifier_key, 'registry_test_callback');
            static::assertTrue(TalesRegistry::isRegistered($modifier_key));
        }
    }
}
