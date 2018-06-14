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

error_reporting( E_ALL | E_STRICT );
assert_options(ASSERT_ACTIVE, 1);

abstract class PHPTAL_TestCase extends PHPUnit_Framework_TestCase
{
    private $cwd_backup, $buffer_level;

    function setUp()
    {
        $this->assertTrue(\PhpTal\PHPTAL::PHPTAL_VERSION >= '2_0_0');

        $this->buffer_level = ob_get_level();

        // tests rely on cwd being in tests/
        $this->cwd_backup = getcwd();
        chdir(dirname(__FILE__));

        ob_start(); // buffer test's output

        parent::setUp();
    }

    function tearDown()
    {
        parent::tearDown();

        chdir($this->cwd_backup);

        $content = ob_get_clean();

        // ensure that test hasn't left buffering on
        $unflushed = 0;
        while (ob_get_level() > $this->buffer_level) {
            ob_end_flush(); $unflushed++;
        }

        if ($unflushed) throw new \Exception("Unflushed buffers: $unflushed");
    }

    /**
     * backupGlobals is the worst idea ever.
     */
    protected $backupGlobals = false;

    protected function newPHPTAL($tpl = false)
    {
        $p = new \PhpTal\PHPTAL($tpl);
        $p->setForceReparse(true);
        $p->allowPhpModifier(); // many existing tests make use of php modifier
        return $p;
    }

    protected function assertXMLEquals($expect, $test)
    {
        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($expect), "Can load $expect");
        $doc->normalize();
        $expect = $doc->saveXML();

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($test), "Can load $test");
        $doc->normalize();
        $test = $doc->saveXML();

        $this->assertEquals($expect, $test);
    }

    protected function assertHTMLEquals($expect, $test)
    {
        $this->assertEquals(normalize_html($expect), normalize_html($test));
    }
}

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(@date_default_timezone_get());
}

function normalize_html_file($src) {
    return normalize_html(file_get_contents($src));
}

function normalize_html($src) {
    $src = trim($src);
    $src = preg_replace('/\s+/usm', ' ', $src);
    $src = preg_replace('/(?<!]])&gt;/', '>', $src); // > may or may not be escaped, except ]]>
    $src = str_replace('> ', '>', $src);
    $src = str_replace(' <', '<', $src);
    $src = str_replace(' />', '/>', $src);
    return $src;
}

function normalize_phpsource($code, $ignore_newlines = false) {

    // ignore debug
    $code = preg_replace('!<\?php\s+/\* tag ".*?" from line \d+ \*/ ?; \?>!','', $code);
    $code = preg_replace('!/\* tag ".*?" from line \d+ \*/ ?;!','', $code);

    $code = str_replace('<?php use pear2\HTML\Template\PHPTAL as P; ?>', '', $code);

    $lines = explode("\n", $code);
    $code = "";
    foreach ($lines as $line) {
        $line = trim($line);
        $code .= $line;
        if ($ignore_newlines) {
            if (preg_match('/[A-Z0-9_]$/i',$line)) $code .= ' ';
        } else $code .= "\n";
    }

    // ignore some no-ops
    return str_replace(array('<?php ?>', '<?php ; ?>', '{;', ' }'), array('', '', '{', '}'), $code);
}
