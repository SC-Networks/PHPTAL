<?php
/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiski <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace Tests;

use PhpTal\Exception\ParserException;

class Latin1Test extends \Tests\Testcase\PhpTal
{
    public function testLipsum()
    {
        $tpl = $this->newPHPTAL()->setEncoding('ISO-8859-1')->setSource(rawurldecode('<?xml version="1.0" encoding="UTF-8"?>
             <test>L%f8rem ipsum dolor sit amet, %49%f1%74%eb%72%6e%e2%74%69%f4%6e%e0%6c%69%7a%e6%74%69%f8%6e.</test>'))->execute();
    }

    public function testLow()
    {
        $this->expectException(ParserException::class);
        $tpl = $this->newPHPTAL()->setEncoding('ISO-8859-1')->setSource(rawurldecode('<?xml version="1.0" encoding="UTF-8"?>
             <test>test%03ing</test>'))->execute();
    }

    public function testDead()
    {
        $this->expectException(ParserException::class);
        $tpl = $this->newPHPTAL()->setEncoding('ISO-8859-1')->setSource(rawurldecode('<?xml version="1.0" encoding="UTF-8"?>
             <test>test%88ing</test>'))->execute();
    }
}
