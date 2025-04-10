<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal;

use ParseError;
use PhpTal\Exception\TemplateException;
use PhpTal\TalNamespace\Builtin;
use Throwable;

class ExceptionHandler
{
    /**
     * ExceptionHandler constructor.
     */
    public function __construct(private readonly string $encoding)
    {
    }

    /**
     * PHP's default exception handler allows error pages to be indexed and can reveal too much information,
     * so if possible PHPTAL sets up its own handler to fix this.
     *
     * Doesn't change exception handler if non-default one is set.
     *
     * @param Throwable $e exception to re-throw and display
     *
     * @throws TemplateException
     * @throws Throwable
     */
    public static function handleException(Throwable $e, string $encoding): void
    {
        // PHPTAL's handler is only useful on fresh HTTP response
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            $old_exception_handler = set_exception_handler([
                new ExceptionHandler($encoding),
                'defaultExceptionHandler'
            ]);

            if ($old_exception_handler !== null) {
                restore_exception_handler(); // if there's user's exception handler, let it work
            }
        }

        if ($e::class === ParseError::class) {
            $e = new TemplateException($e->getMessage());
        }
        throw $e; // throws instead of outputting immediately to support user's try/catch
    }


    /**
     * Generates simple error page. Sets appropriate HTTP status to prevent page being indexed.
     *
     * @param Throwable $e exception to display
     */
    public function defaultExceptionHandler(Throwable $e): void
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 PHPTAL Exception');
            header('Content-Type:text/html;charset=' . $this->encoding);
        }

        $line = $e->getFile();
        if ($e->getLine()) {
            $line .= ' line ' . $e->getLine();
        }

        if (ini_get('display_errors')) {
            $title = $e::class . ': ' . htmlspecialchars($e->getMessage(), ENT_COMPAT);
            $body = "<p><strong>\n" . htmlspecialchars($e->getMessage(), ENT_COMPAT) . '</strong></p>' .
                '<p>In ' . htmlspecialchars($line, ENT_COMPAT) . "</p><pre>\n" .
                htmlspecialchars($e->getTraceAsString(), ENT_COMPAT) . '</pre>';
        } else {
            $title = 'PHPTAL Exception';
            $body = '<p>This page cannot be displayed.</p><hr/>' .
                '<p><small>Enable <code>display_errors</code> to see detailed message.</small></p>';
        }

        echo "<!DOCTYPE html><html xmlns='" . Builtin::NS_XHTML . "'><head><style>body{font-family:sans-serif}</style><title>\n";
        echo $title . '</title></head><body><h1>PHPTAL Exception</h1>' . $body;
        error_log($e->getMessage() . ' in ' . $line);
        echo '</body></html>' . str_repeat('    ', 100) . "\n"; // IE won't display error pages < 512b
        exit(1);
    }
}
