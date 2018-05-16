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
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */

namespace PhpTal\Php;

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
    const ECHO_TEXT = 'text';
    const ECHO_STRUCTURE = 'structure';

    /** Attribute value specified by the element. */
    protected $expression;

    /** Element using this attribute (PHPTAL's counterpart of XML node) */
    protected $phpelement;

    /**
     * Called before element printing.
     */
    abstract function before(CodeWriter $codewriter);

    /**
     * Called after element printing.
     */
    abstract function after(CodeWriter $codewriter);

    function __construct(\PhpTal\Dom\Element $phpelement, $expression)
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
    protected function extractEchoType($expression)
    {
        $echoType = self::ECHO_TEXT;
        $expression = trim($expression);
        if (preg_match('/^(text|structure)\s+(.*?)$/ism', $expression, $m)) {
            list(, $echoType, $expression) = $m;
        }
        $this->_echoType = strtolower($echoType);
        return trim($expression);
    }

    protected function doEchoAttribute(CodeWriter $codewriter, $code)
    {
        if ($this->_echoType === self::ECHO_TEXT)
            $codewriter->doEcho($code);
        else
            $codewriter->doEchoRaw($code);
    }

    protected function parseSetExpression($exp)
    {
        $exp = trim($exp);
        // (dest) (value)
        if (preg_match('/^([a-z0-9:\-_]+)\s+(.*?)$/si', $exp, $m)) {
            return array($m[1], trim($m[2]));
        }
        // (dest)
        return array($exp, null);
    }

    protected $_echoType = Attribute::ECHO_TEXT;
}

