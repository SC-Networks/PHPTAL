<?php

namespace Tests;

use PhpTal\Php\TalesInternal;

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


class PhpModeTest extends \PHPTAL_TestCase
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
        $exp = normalize_html_file('output/php-mode.01.xml');
        $res = normalize_html($res);
        static::assertEquals($exp, $res);
    }

    public function testInContent()
    {
        TalesInternal::setFunctionWhitelist(['strtolower']);
        $tpl = $this->newPHPTAL('input/php-mode.02.xml');
        $res = $tpl->execute();
        $exp = normalize_html_file('output/php-mode.02.xml');
        $res = normalize_html($res);
        static::assertEquals($exp, $res);
    }
}
