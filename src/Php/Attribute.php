<?php
declare(strict_types=1);

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

namespace PhpTal\Php;

use PhpTal\Dom\Element;

/**
 * Base class for all PHPTAL attributes.
 *
 * Attributes are first ordered by PHPTAL then called depending on their
 * priority before and after the element printing.
 *
 * An attribute must implements start() and end().
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
abstract class Attribute
{
    final public const ECHO_TEXT = 'text';
    final public const ECHO_STRUCTURE = 'structure';

    /**
     * @var string
     */
    protected $echoType = Attribute::ECHO_TEXT;

    /**
     * Attribute value specified by the element.
     * @var string
     */
    protected $expression;

    /**
     * Element using this attribute (PHPTAL's counterpart of XML node)
     * @var Element
     */
    protected $phpelement;

    /**
     * Called before element printing.
     *
     *
     */
    abstract public function before(CodeWriter $codewriter): void;

    /**
     * Called after element printing.
     *
     *
     */
    abstract public function after(CodeWriter $codewriter): void;

    /**
     * Attribute constructor.
     */
    public function __construct(Element $phpelement, string $expression)
    {
        $this->expression = $expression;
        $this->phpelement = $phpelement;
    }

    /**
     * Remove structure|text keyword from expression and stores it for later
     * doEcho() usage.
     *
     * $expression = 'stucture my/path';
     * $expression = $this->extractEchoType($expression);
     *
     * ...
     *
     * $this->doEcho($code);
     */
    protected function extractEchoType(string $expression): string
    {
        $echoType = self::ECHO_TEXT;
        $expression = trim($expression);
        if (preg_match('/^(text|structure)\s+(.*?)$/ism', $expression, $m)) {
            [, $echoType, $expression] = $m;
        }
        $this->echoType = strtolower($echoType);
        return trim($expression);
    }

    protected function doEchoAttribute(CodeWriter $codewriter, string $code): void
    {
        if ($this->echoType === self::ECHO_TEXT) {
            $codewriter->doEcho($code);
        } else {
            $codewriter->doEchoRaw($code);
        }
    }

    /**
     * @return array{
     *  0: string,
     *  1: null|string
     * }
     */
    protected function parseSetExpression(string $exp): array
    {
        $exp = trim($exp);
        // (dest) (value)
        if (preg_match('/^([a-z0-9:\-_]+)\s+(.*?)$/si', $exp, $m)) {
            return [$m[1], trim($m[2])];
        }
        // (dest)
        return [$exp, null];
    }
}
