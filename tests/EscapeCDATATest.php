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
use PhpTal\PHPTAL;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class EscapeCDATATest extends PhpTalTestCase
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

    private function executeString(string $str, array $params = []): string
    {
        $tpl = $this->newPHPTAL();
        foreach ($params as $k => $v) {
            $tpl->set($k, $v);
        }
        $tpl->setSource($str);
        $this->last_code_path = $tpl->getCodePath();
        return $tpl->execute();
    }

    /**
     * @return void
     */
    public function testTrimString(): void
    {
        static::assertSame(
            Helper::normalizeHtml('<foo><bar>]]&gt; foo ]> bar</bar></foo>'),
            Helper::normalizeHtml('<foo> <bar>]]&gt; foo ]&gt; bar </bar> </foo>')
        );

        $this->assertNotEquals(
            Helper::normalizeHtml('foo]]>bar'),
            Helper::normalizeHtml('foo]]&gt;bar')
        );
    }

    public function testDoesEscapeHTMLContent(): void
    {
        $tpl = $this->newPHPTAL('input/escape.html');
        $exp = Helper::normalizeHtmlFile('output/escape.html');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame($exp, $res);
    }

    public function testEntityTextInPath(): void
    {
        $res = $this->executeString(
            '<div><![CDATA[${text \'"< & &amp; &quot; &lt;\'},${false | string:"< & &amp; &quot; &lt;}]]></div>'
        );

        // either way is good
        if (false !== strpos($res, '<![CDATA[')) {
            static::assertSame('<div><![CDATA["< & &amp; &quot; &lt;,"< & &amp; &quot; &lt;]]></div>', $res);
        } else {
            static::assertSame(
                '<div>&quot;&lt; &amp; &amp;amp; &amp;quot; &amp;lt;,&quot;&lt; &amp; &amp;amp; &amp;quot; &amp;lt;</div>',
                $res
            );
        }
    }

    public function testEntityStructureInPath(): void
    {
        $res = $this->executeString(
            '<div><![CDATA[${structure \'"< & &amp; &quot; &lt;\'},${structure false | string:"< & &amp; &quot; &lt;}]]></div>'
        );
        static::assertSame('<div><![CDATA["< & &amp; &quot; &lt;,"< & &amp; &quot; &lt;]]></div>', $res);
    }

    public function testEntityInContentPHP(): void
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        $res = $this->executeString('<div><![CDATA[${php:strlen(\'&quot;&amp;&lt;\')},${php:strlen(\'<"&\')}]]></div>');
        static::assertSame('<div>15,3</div>', $res);
    }

    public function testEntityInScriptPHP(): void
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        $res = $this->executeString(
            '<script><![CDATA[${php:strlen(\'&quot;&amp;&lt;\')},${php:strlen(\'<"&\')}]]></script>'
        );
        static::assertSame('<script><![CDATA[15,3]]></script>', $res);
    }

    public function testEntityInPHP2(): void
    {
        TalesInternal::setFunctionWhitelist(['strlen']);
        $res = $this->executeString(
            '<div><![CDATA[${structure php:strlen(\'&quot;&amp;&lt;\')},${structure php:strlen(\'<"&\')}]]></div>'
        );
        static::assertSame('<div><![CDATA[15,3]]></div>', $res);
    }

    public function testEntityInPHP3(): void
    {
        $res = $this->executeString(
            '<div><![CDATA[<?php echo strlen(\'&quot;&amp;&lt;\')?>,<?php echo strlen(\'<"&\') ?>]]></div>'
        );
        static::assertSame(
            '<div><![CDATA[<_ echo strlen(\'&quot;&amp;&lt;\')?>,<_ echo strlen(\'<"&\') ?>]]></div>',
            $res
        );
    }

    public function testNoEncodingAfterPHP(): void
    {
        TalesInternal::setFunctionWhitelist(['urldecode']);
        $res = $this->executeString(
            '<div><![CDATA[${php:urldecode(\'%26%22%3C\')},${structure php:urldecode(\'%26%22%3C\')},<?php echo urldecode(\'%26%22%3C\') ?>]]></div>'
        );
        static::assertSame('<div><![CDATA[&"<,&"<,<_ echo urldecode(\'%26%22%3C\') ?>]]></div>', $res);
    }

    /**
     * normal XML behavior expected
     */
    public function testEscapeCDATAXML(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(PHPTAL::XML);
        $tpl->setSource('<y><![CDATA[${cdata}; ${cdata};]]></y>     <y><![CDATA[${structure cdata}]]></y>');
        $tpl->cdata = ']]></x>';
        $res = $tpl->execute();
        static::assertSame('<y>]]&gt;&lt;/x&gt;; ]]&gt;&lt;/x&gt;;</y>     <y><![CDATA[]]></x>]]></y>', $res);
    }

    /**
     * ugly hybrid between HTML (XHTML as text/html) and XML
     */
    public function testEscapeCDATAXHTML(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(PHPTAL::XHTML);
        $tpl->setSource('<script><![CDATA[${cdata}; ${cdata};]]></script>     <y><![CDATA[${structure cdata}]]></y>');
        $tpl->cdata = ']]></x>';
        $res = $tpl->execute();
        static::assertSame(
            '<script><![CDATA[]]]]><![CDATA[><\/x>; ]]]]><![CDATA[><\/x>;]]></script>     <y><![CDATA[]]></x>]]></y>',
            $res
        );
    }


    public function testEscapeCDATAHTML(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(PHPTAL::HTML5);
        $tpl->setSource('<y><![CDATA[${cdata}; ${cdata};]]></y>     <y><![CDATA[${structure cdata}]]></y>');
        $tpl->cdata = ']]></x>';
        $res = $tpl->execute();
        static::assertSame('<y>]]&gt;&lt;/x&gt;; ]]&gt;&lt;/x&gt;;</y>     <y>]]></x></y>', $res);
    }


    public function testAutoCDATA(): void
    {
        $res = $this->executeString('<script> 1 < 2 </script>');
        static::assertSame('<script>/*<![CDATA[*/ 1 < 2 /*]]>*/</script>', $res, $this->last_code_path);
    }

    public function testAutoCDATA2(): void
    {
        $res = $this->executeString(
            '<xhtmlz:script xmlns:xhtmlz="http://www.w3.org/1999/xhtml"> 1 < 2 ${php:\'&\' . \'&amp;\'} </xhtmlz:script>'
        );
        static::assertSame(
            '<xhtmlz:script xmlns:xhtmlz="http://www.w3.org/1999/xhtml">/*<![CDATA[*/ 1 < 2 && /*]]>*/</xhtmlz:script>',
            $res,
            $this->last_code_path
        );
    }

    public function testNoAutoCDATA(): void
    {
        $res = $this->executeString('<script> "1 \' 2" </script><script xmlns="foo"> 1 &lt; 2 </script>');
        static::assertSame(
            '<script> "1 \' 2" </script><script xmlns="foo"> 1 &lt; 2 </script>',
            $res,
            $this->last_code_path
        );
    }

    public function testNoAutoCDATA2(): void
    {
        $res = $this->executeString(
            '<script> a && ${structure foo} </script><script xmlns="foo"> 1 &lt; 2 </script>',
            ['foo' => '<foo/>']
        );
        static::assertSame(
            '<script> a &amp;&amp; <foo/> </script><script xmlns="foo"> 1 &lt; 2 </script>',
            $res,
            $this->last_code_path
        );
    }

    public function testNoAutoCDATA3(): void
    {
        $res = $this->executeString('<style> html > body </style>');
        static::assertSame('<style> html > body </style>', $res, $this->last_code_path);
    }
}
