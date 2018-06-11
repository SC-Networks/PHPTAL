<?php

namespace Tests;

/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */


class ReadableErrorTest extends \PHPTAL_TestCase
{
    public function testSimple()
    {
        $this->assertThrowsInLine(2, 'input/error-01.html');
    }

    public function testMacro()
    {
        $this->assertThrowsInLine(2, 'input/error-02.html', 'input/error-02.macro.html');
    }

    public function testAfterMacro()
    {
        $this->assertThrowsInLine(3, 'input/error-03.html');
    }

    public function testParseError()
    {
        $this->assertThrowsInLine(7, 'input/error-04.html');
    }

    public function testMissingVar()
    {
        $this->assertThrowsInLine(5, 'input/error-05.html');
    }

    public function testMissingVarInterpol()
    {
        $this->assertThrowsInLine(3, 'input/error-06.html');
    }

    public function testMissingExpr()
    {
        $this->assertThrowsInLine(6, 'input/error-07.html');
    }

    public function testPHPSyntax()
    {
        $this->assertThrowsInLine(9, 'input/error-08.html');
    }

    public function testTranslate()
    {
        $this->assertThrowsInLine(8, 'input/error-09.html');
    }

    public function testMacroName()
    {
        $this->assertThrowsInLine(4, 'input/error-10.html');
    }

    public function testTALESParse()
    {
        $this->assertThrowsInLine(2, 'input/error-11.html');
    }

    public function testMacroNotExists()
    {
        $this->assertThrowsInLine(3, 'input/error-12.html');
    }

    public function testLocalMacroNotExists()
    {
        $this->assertThrowsInLine(5, 'input/error-13.html');
    }

    /**
     * @param int $line
     * @param string $file
     * @param string $expected_file
     *
     * @return void
     * @throws \PhpTal\Exception\ConfigurationException
     * @throws \PhpTal\Exception\IOException
     * @throws \Throwable
     */
    public function assertThrowsInLine($line, $file, $expected_file = null)
    {
        try {
            $tpl = $this->newPHPTAL($file);
            $tpl->a_number = 1;
            $tpl->execute();
            static::fail('Not thrown');
        } catch (\PhpTal\Exception\TemplateException $e) {
            $msg = $e->getMessage();
            static::assertInternalType('string', $e->srcFile, $msg);
            static::assertContains($expected_file ?: $file, $e->srcFile, $msg);
            static::assertEquals($line, $e->srcLine, 'Wrong line number: ' . $msg . "\n". $tpl->getCodePath());
        }
    }
}
