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

use Tests\Testcase\PhpTalTestCase;

class PhptalUsageTest extends PhpTalTestCase
{
    public function testMultiUse()
    {
        $t = $this->newPHPTAL();
        $t->title = 'hello';
        $t->setTemplate(TAL_TEST_FILES_DIR . 'input/multiuse.01.html');
        $a = $t->execute();
        $t->setTemplate(TAL_TEST_FILES_DIR . 'input/multiuse.02.html');
        $b = $t->execute();
        static::assertNotSame($a, $b, "$a == $b");
        static::assertStringContainsString('hello', $a);
        static::assertStringContainsString('hello', $b);
    }

    public function testSetSourceReset()
    {
        $t = $this->newPHPTAL();
        $t->setSource('<p>Hello</p>');
        $res1 = $t->execute();
        $t->setSource('<p>World</p>');
        $res2 = $t->execute();

        static::assertNotSame($res1, $res2);
    }
}
