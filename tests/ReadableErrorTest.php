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

use PhpTal\Exception\TemplateException;
use Tests\Testcase\PhpTalTestCase;

class ReadableErrorTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $this->assertThrowsInLine(2, 'input/error-01.html');
    }

    public function testMacro(): void
    {
        $this->assertThrowsInLine(2, 'input/error-02.html', 'input/error-02.macro.html');
    }

    public function testAfterMacro(): void
    {
        $this->assertThrowsInLine(3, 'input/error-03.html');
    }

    public function testParseError(): void
    {
        $this->assertThrowsInLine(7, 'input/error-04.html');
    }

    public function testMissingVar(): void
    {
        $this->assertThrowsInLine(5, 'input/error-05.html');
    }

    public function testMissingVarInterpol(): void
    {
        $this->assertThrowsInLine(3, 'input/error-06.html');
    }

    public function testMissingExpr(): void
    {
        $this->assertThrowsInLine(6, 'input/error-07.html');
    }

    public function testPHPSyntax(): void
    {
        $this->assertThrowsInLine(9, 'input/error-08.html');
    }

    public function testTranslate(): void
    {
        $this->assertThrowsInLine(8, 'input/error-09.html');
    }

    public function testMacroName(): void
    {
        $this->assertThrowsInLine(4, 'input/error-10.html');
    }

    public function testTALESParse(): void
    {
        $this->assertThrowsInLine(2, 'input/error-11.html');
    }

    public function testMacroNotExists(): void
    {
        $this->assertThrowsInLine(3, 'input/error-12.html');
    }

    public function testLocalMacroNotExists(): void
    {
        $this->assertThrowsInLine(5, 'input/error-13.html');
    }

    public function assertThrowsInLine(int $line, string $file, string $expected_file = null): void
    {
        try {
            $tpl = $this->newPHPTAL($file);
            $tpl->a_number = 1;
            $tpl->execute();
            static::fail('Not thrown');
        } catch (TemplateException $e) {
            $msg = $e->getMessage();
            echo $msg.PHP_EOL.$e->srcFile;
            static::assertIsString($e->srcFile, $msg);
            static::assertStringContainsString($expected_file ?: $file, $e->srcFile, $msg);
            static::assertEquals($line, $e->srcLine, 'Wrong line number: ' . $msg . "\n" . $tpl->getCodePath());
        }
    }
}
