<?php
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

namespace Tests;

class SimpleGenerationTest extends \Tests\Testcase\PhpTal
{
    public function testTreeGeneration()
    {
        $tpl = $this->newPHPTAL();

        $parser = new \PhpTal\Dom\SaxXmlParser($tpl->getEncoding());
        $treeGen = $parser->parseFile(new \PhpTal\Dom\PHPTALDocumentBuilder(), '../input/parser.01.xml')->getResult();
        $state     = new \PhpTal\Php\State($tpl);
        $codewriter = new \PhpTal\Php\CodeWriter($state);
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

        $result = \Tests\Testhelper\Helper::normalizePhpSource($result, true);
        $expected = \Tests\Testhelper\Helper::normalizePhpSource($expected, true);


        $this->assertEquals($expected, $result);
    }

    public function testFunctionsGeneration()
    {
        $state = new \PhpTal\Php\State($this->newPHPTAL());
        $codewriter = new \PhpTal\Php\CodeWriter($state);
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
        $res = \Tests\Testhelper\Helper::normalizePhpSource($res, true);
        $exp = \Tests\Testhelper\Helper::normalizePhpSource($exp, true);
        $this->assertEquals($exp, $res);
    }
}
