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

namespace Tests\Testcase;

use PhpTal\PhpTalInterface;
use PHPUnit\Framework\TestCase;
use Tests\Testhelper\Helper;

abstract class PhpTal extends TestCase
{

    /**
     * @var string
     */
    private $cwd_backup;

    /**
     * @var int
     */
    private $buffer_level;

    public function setUp(): void
    {
        static::assertTrue(\PhpTal\PHPTAL::PHPTAL_VERSION >= '3_0_0');

        $this->buffer_level = ob_get_level();

        // tests rely on cwd being in tests/
        $this->cwd_backup = getcwd();
        chdir(__DIR__);

        ob_start(); // buffer test's output

        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        chdir($this->cwd_backup);

        ob_get_clean();

        // ensure that test hasn't left buffering on
        $unflushed = 0;
        while (ob_get_level() > $this->buffer_level) {
            ob_end_flush();
            $unflushed++;
        }

        if ($unflushed) {
            throw new \Exception("Unflushed buffers: $unflushed");
        }
    }

    protected function newPHPTAL(?string $tpl = null): PhpTalInterface
    {
        $path = $tpl === null ? null : __DIR__ . '/../' . $tpl;
        $p = new \PhpTal\PHPTAL($path);
        $p->setForceReparse(true);
        $p->allowPhpModifier(); // many existing tests make use of php modifier
        return $p;
    }

    protected function assertXMLEquals(string $expect, string $test): void
    {
        $doc = new \DOMDocument();
        static::assertTrue($doc->loadXML($expect), "Can load $expect");
        $doc->normalize();
        $expect = $doc->saveXML();

        $doc = new \DOMDocument();
        static::assertTrue($doc->loadXML($test), "Can load $test");
        $doc->normalize();
        $test = $doc->saveXML();

        static::assertSame($expect, $test);
    }

    protected function assertHTMLEquals(string $expect, string $test): void
    {
        static::assertSame(Helper::normalizeHtml($expect), Helper::normalizeHtml($test));
    }
}
