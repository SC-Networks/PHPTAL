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

use PhpTal\Php\TalesInternal;

class PhpModeTest extends \Tests\Testcase\PhpTal
{

    public function tearDown()
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testSimple()
    {
        TalesInternal::setFunctionWhitelist(['strtolower']);
        $tpl = $this->newPHPTAL('input/php-mode.01.xml');
        $res = $tpl->execute();
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/php-mode.01.xml');
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        static::assertEquals($exp, $res);
    }

    public function testInContent()
    {
        TalesInternal::setFunctionWhitelist(['strtolower']);
        $tpl = $this->newPHPTAL('input/php-mode.02.xml');
        $res = $tpl->execute();
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/php-mode.02.xml');
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        static::assertEquals($exp, $res);
    }
}
