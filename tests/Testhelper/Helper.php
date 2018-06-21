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

namespace Tests\Testhelper;

class Helper
{
    public static function normalizeHtmlFile($src)
    {
        return static::normalizeHtml(file_get_contents(__DIR__ . '/../' . $src));
    }

    public static function normalizeHtml($src)
    {
        $src = trim($src);
        $src = preg_replace('/\s+/usm', ' ', $src);
        $src = preg_replace('/(?<!]])&gt;/', '>', $src); // > may or may not be escaped, except ]]>
        $src = str_replace('> ', '>', $src);
        $src = str_replace(' <', '<', $src);
        $src = str_replace(' />', '/>', $src);
        return $src;
    }

    public static function normalizePhpSource($code, $ignore_newlines = false)
    {

        // ignore debug
        $code = preg_replace('!<\?php\s+/\* tag ".*?" from line \d+ \*/ ?; \?>!', '', $code);
        $code = preg_replace('!/\* tag ".*?" from line \d+ \*/ ?;!', '', $code);

        $code = str_replace('<?php use pear2\HTML\Template\PHPTAL as P; ?>', '', $code);

        $lines = explode("\n", $code);
        $code = "";
        foreach ($lines as $line) {
            $line = trim($line);
            $code .= $line;
            if ($ignore_newlines) {
                if (preg_match('/[A-Z0-9_]$/i', $line)) {
                    $code .= ' ';
                }
            } else {
                $code .= "\n";
            }
        }

        // ignore some no-ops
        return str_replace(array('<?php ?>', '<?php ; ?>', '{;', ' }'), array('', '', '{', '}'), $code);
    }
}
