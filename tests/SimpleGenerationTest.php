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

use PhpTal\Dom\PHPTALDocumentBuilder;
use PhpTal\Dom\SaxXmlParser;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\State;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class SimpleGenerationTest extends PhpTalTestCase
{
    public function testTreeGeneration(): void
    {
        $tpl = $this->newPHPTAL();

        $parser = new SaxXmlParser($tpl->getEncoding());
        $treeGen = $parser->parseFile(
            new PHPTALDocumentBuilder(),
            TAL_TEST_FILES_DIR . 'input/parser.01.xml'
        )->getResult();
        $state = new State($tpl);
        $codewriter = new CodeWriter($state);
        $codewriter->doFunction('test', '$tpl');
        $treeGen->generateCode($codewriter);
        $codewriter->doEnd('function');
        $result = $codewriter->getResult();

        $expected = <<<EOS
<?php
function test(\$tpl) {
\$ctx->setXmlDeclaration('<?xml version="1.0"?>',false) ;?>
<html>
  <head>
    <title>test document</title>
  </head>
  <body>
    <h1>test document</h1>
    <a href="http://phptal.sf.net">phptal</a>
  </body>
</html><?php
}

 ?>
EOS;

        $result = Helper::normalizePhpSource($result, true);
        $expected = Helper::normalizePhpSource($expected, true);

        static::assertSame($expected, $result);
    }

    public function testFunctionsGeneration(): void
    {
        $state = new State($this->newPHPTAL());
        $codewriter = new CodeWriter($state);
        $codewriter->doFunction('test1', '$tpl');
        $codewriter->pushHTML($codewriter->interpolateHTML('test1'));
        $codewriter->doFunction('test2', '$tpl');
        $codewriter->pushHTML('test2');
        $codewriter->doEnd();
        $codewriter->pushHTML('test1');
        $codewriter->doEnd();
        $res = $codewriter->getResult();
        $exp = <<<EOS
<?php function test2(\$tpl) {?>test2<?php}?>
<?php function test1(\$tpl) {?>test1test1<?php}?>
EOS;
        $res = Helper::normalizePhpSource($res, true);
        $exp = Helper::normalizePhpSource($exp, true);
        static::assertSame($exp, $res);
    }
}
