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

use PhpTal\Exception\ParserException;
use Tests\Testcase\PhpTalTestCase;

class Latin1Test extends PhpTalTestCase
{
    public function testLipsum(): void
    {
        $this->newPHPTAL()->setEncoding('ISO-8859-1')->setSource(
            rawurldecode('<?xml version="1.0" encoding="UTF-8"?>
             <test>L%f8rem ipsum dolor sit amet, %49%f1%74%eb%72%6e%e2%74%69%f4%6e%e0%6c%69%7a%e6%74%69%f8%6e.</test>')
        )->execute();
    }

    public function testLow(): void
    {
        $this->expectException(ParserException::class);
        $this->newPHPTAL()->setEncoding('ISO-8859-1')->setSource(
            rawurldecode('<?xml version="1.0" encoding="UTF-8"?>
             <test>test%03ing</test>')
        )->execute();
    }

    public function testDead(): void
    {
        $this->expectException(ParserException::class);
        $this->newPHPTAL()->setEncoding('ISO-8859-1')->setSource(
            rawurldecode('<?xml version="1.0" encoding="UTF-8"?>
             <test>test%88ing</test>')
        )->execute();
    }
}
