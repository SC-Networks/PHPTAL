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

use PhpTal\Exception\ConfigurationException;
use PhpTal\PHPTAL;
use PhpTal\PhpTalInterface;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class EchoExecuteTest extends PhpTalTestCase
{
    private function echoExecute(PhpTalInterface $tpl)
    {
        try {
            ob_start();
            $tpl->echoExecute();
            $res = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        $res2 = $tpl->execute();
        $res3 = $tpl->execute();

        static::assertSame($res2, $res3, "Multiple runs should give same result");

        static::assertSame($res2, $res, "Execution with and without buffering should give same result");

        return Helper::normalizeHtml($res);
    }

    public function testEchoExecute(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<hello/>');

        static::assertSame("<hello></hello>", $this->echoExecute($tpl));
    }

    public function testEchoExecuteDecls(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<?xml version="1.0"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><hello/>');

        static::assertSame(
            Helper::normalizeHtml('<?xml version="1.0"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><hello></hello>'),
            $this->echoExecute($tpl)
        );
    }

    public function testEchoExecuteDeclsMacro(): void
    {
        try {
            $tpl = $this->newPHPTAL();
            $tpl->setSource('<?xml version="1.0"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><hello><m metal:define-macro="test">test</m><x metal:use-macro="test"/></hello>');

            static::assertSame(
                Helper::normalizeHtml('<?xml version="1.0"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><hello><m>test</m></hello>'),
                $this->echoExecute($tpl)
            );
        } catch (ConfigurationException $e) {
            // this is fine. Combination of macros and echoExecute is not supported yet (if it were, the test above is valid)
            static::assertStringContainsString("echoExecute", $e->getMessage());
        }
    }
}
