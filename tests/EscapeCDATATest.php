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

class EscapeCDATATest extends \Tests\Testcase\PhpTal
{

    /**
     * @var string
     */
    private $last_code_path;

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    private function executeString($str, $params = array())
    {
        $tpl = $this->newPHPTAL();
        foreach ($params as $k => $v) $tpl->set($k, $v);
        $tpl->setSource($str);
        $this->last_code_path = $tpl->getCodePath();
        return $tpl->execute();
    }

    /**
     * @return void
     */
    public function testTrimString()
    {
        $this->assertEquals(
            \Tests\Testhelper\Helper::normalizeHtml('<foo><bar>]]&gt; foo ]> bar</bar></foo>'),
            \Tests\Testhelper\Helper::normalizeHtml('<foo> <bar>]]&gt; foo ]&gt; bar </bar> </foo>')
        );

        $this->assertNotEquals(
            \Tests\Testhelper\Helper::normalizeHtml('foo]]>bar'),
            \Tests\Testhelper\Helper::normalizeHtml('foo]]&gt;bar')
        );
    }

    public function testDoesEscapeHTMLContent()
    {
        $tpl = $this->newPHPTAL('input/escape.html');
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/escape.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $this->assertEquals($exp, $res);
    }

    public function testEntityTextInPath()
    {
        $res = $this->executeString('<div><![CDATA[${text \'"< & &amp; &quot; &lt;\'},${false | string:"< & &amp; &quot; &lt;}]]></div>');

        // either way is good
        if (false !== strpos($res, '<![CDATA[')) {
            $this->assertEquals('<div><![CDATA["< & &amp; &quot; &lt;,"< & &amp; &quot; &lt;]]></div>', $res);
        } else {
            $this->assertEquals('<div>&quot;&lt; &amp; &amp;amp; &amp;quot; &amp;lt;,&quot;&lt; &amp; &amp;amp; &amp;quot; &amp;lt;</div>', $res);
        }
    }

    public function testEntityStructureInPath()
    {
        $res = $this->executeString('<div><![CDATA[${structure \'"< & &amp; &quot; &lt;\'},${structure false | string:"< & &amp; &quot; &lt;}]]></div>');
        $this->assertEquals('<div><![CDATA["< & &amp; &quot; &lt;,"< & &amp; &quot; &lt;]]></div>', $res);
    }

    public function testEntityInContentPHP()
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        $res = $this->executeString('<div><![CDATA[${php:strlen(\'&quot;&amp;&lt;\')},${php:strlen(\'<"&\')}]]></div>');
        $this->assertEquals('<div>15,3</div>', $res);
    }

    public function testEntityInScriptPHP()
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        $res = $this->executeString('<script><![CDATA[${php:strlen(\'&quot;&amp;&lt;\')},${php:strlen(\'<"&\')}]]></script>');
        $this->assertEquals('<script><![CDATA[15,3]]></script>', $res);
    }

    public function testEntityInPHP2()
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        $res = $this->executeString('<div><![CDATA[${structure php:strlen(\'&quot;&amp;&lt;\')},${structure php:strlen(\'<"&\')}]]></div>');
        $this->assertEquals('<div><![CDATA[15,3]]></div>', $res);
    }

    public function testEntityInPHP3()
    {
        $res = $this->executeString('<div><![CDATA[<?php echo strlen(\'&quot;&amp;&lt;\')?>,<?php echo strlen(\'<"&\') ?>]]></div>');
        $this->assertEquals('<div><![CDATA[<_ echo strlen(\'&quot;&amp;&lt;\')?>,<_ echo strlen(\'<"&\') ?>]]></div>', $res);
    }

    public function testNoEncodingAfterPHP()
    {
        TalesInternal::setFunctionWhitelist(['urldecode']);
        $res = $this->executeString('<div><![CDATA[${php:urldecode(\'%26%22%3C\')},${structure php:urldecode(\'%26%22%3C\')},<?php echo urldecode(\'%26%22%3C\') ?>]]></div>');
        $this->assertEquals('<div><![CDATA[&"<,&"<,<_ echo urldecode(\'%26%22%3C\') ?>]]></div>', $res);
    }

    /**
     * normal XML behavior expected
     */
    public function testEscapeCDATAXML()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(\PhpTal\PHPTAL::XML);
        $tpl->setSource('<y><![CDATA[${cdata}; ${cdata};]]></y>     <y><![CDATA[${structure cdata}]]></y>');
        $tpl->cdata = ']]></x>';
        $res = $tpl->execute();
        $this->assertEquals('<y>]]&gt;&lt;/x&gt;; ]]&gt;&lt;/x&gt;;</y>     <y><![CDATA[]]></x>]]></y>', $res);
    }

    /**
     * ugly hybrid between HTML (XHTML as text/html) and XML
     */
    public function testEscapeCDATAXHTML()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(\PhpTal\PHPTAL::XHTML);
        $tpl->setSource('<script><![CDATA[${cdata}; ${cdata};]]></script>     <y><![CDATA[${structure cdata}]]></y>');
        $tpl->cdata = ']]></x>';
        $res = $tpl->execute();
        $this->assertEquals('<script><![CDATA[]]]]><![CDATA[><\/x>; ]]]]><![CDATA[><\/x>;]]></script>     <y><![CDATA[]]></x>]]></y>', $res);
    }


    public function testEscapeCDATAHTML()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(\PhpTal\PHPTAL::HTML5);
        $tpl->setSource('<y><![CDATA[${cdata}; ${cdata};]]></y>     <y><![CDATA[${structure cdata}]]></y>');
        $tpl->cdata = ']]></x>';
        $res = $tpl->execute();
        $this->assertEquals('<y>]]&gt;&lt;/x&gt;; ]]&gt;&lt;/x&gt;;</y>     <y>]]></x></y>', $res);
    }



    public function testAutoCDATA()
    {
        $res = $this->executeString('<script> 1 < 2 </script>');
        $this->assertEquals('<script>/*<![CDATA[*/ 1 < 2 /*]]>*/</script>', $res, $this->last_code_path);
    }

    public function testAutoCDATA2()
    {
        $res = $this->executeString('<xhtmlz:script xmlns:xhtmlz="http://www.w3.org/1999/xhtml"> 1 < 2 ${php:\'&\' . \'&amp;\'} </xhtmlz:script>');
        $this->assertEquals('<xhtmlz:script xmlns:xhtmlz="http://www.w3.org/1999/xhtml">/*<![CDATA[*/ 1 < 2 && /*]]>*/</xhtmlz:script>', $res, $this->last_code_path);
    }

    public function testNoAutoCDATA()
    {
        $res = $this->executeString('<script> "1 \' 2" </script><script xmlns="foo"> 1 &lt; 2 </script>');
        $this->assertEquals('<script> "1 \' 2" </script><script xmlns="foo"> 1 &lt; 2 </script>', $res, $this->last_code_path);
    }

    public function testNoAutoCDATA2()
    {
        $res = $this->executeString('<script> a && ${structure foo} </script><script xmlns="foo"> 1 &lt; 2 </script>', array('foo'=>'<foo/>'));
        $this->assertEquals('<script> a &amp;&amp; <foo/> </script><script xmlns="foo"> 1 &lt; 2 </script>', $res, $this->last_code_path);
    }

    public function testNoAutoCDATA3()
    {
        $res = $this->executeString('<style> html > body </style>');
        $this->assertEquals('<style> html > body </style>', $res, $this->last_code_path);
    }
}
