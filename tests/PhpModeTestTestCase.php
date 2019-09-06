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

use PhpTal\Php\TalesInternal;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class PhpModeTestTestCase extends PhpTalTestCase
{

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testSimple(): void
    {
        TalesInternal::setFunctionWhitelist(['strtolower']);
        $tpl = $this->newPHPTAL('input/php-mode.01.xml');
        $res = $tpl->execute();
        $exp = Helper::normalizeHtmlFile('output/php-mode.01.xml');
        $res = Helper::normalizeHtml($res);
        static::assertEquals($exp, $res);
    }

    public function testInContent(): void
    {
        TalesInternal::setFunctionWhitelist(['strtolower']);
        $tpl = $this->newPHPTAL('input/php-mode.02.xml');
        $res = $tpl->execute();
        $exp = Helper::normalizeHtmlFile('output/php-mode.02.xml');
        $res = Helper::normalizeHtml($res);
        static::assertEquals($exp, $res);
    }
}
