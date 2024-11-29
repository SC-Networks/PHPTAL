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

namespace PhpTal;

use AllowDynamicProperties;
use ArrayAccess;
use BadMethodCallException;
use Countable;
use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\InvalidVariableNameException;
use PhpTal\Exception\VariableNotFoundException;
use stdClass;

/**
 * This class handles template execution context.
 * Holds template variables and carries state/scope across macro executions.
 */
#[AllowDynamicProperties]
class Context
{
    public stdClass $repeat;

    /**
     * n.b. The following variables *must* be prefixed with an underscore '_' for reasons...
     */

    public ?string $_xmlDeclaration = null;

    public ?string $_docType = null;

    private bool $_nothrow = false;

    /**
     * @var array<string, string|array{
     *  0: callable-string|callable():string,
     *  1: PhpTalInterface,
     *  2: PhpTalInterface
     * }>
     */
    private array $_slots = [];

    /**
     * @var array<array<string, string|array{
     *  0: callable-string|callable():string,
     *  1: PhpTalInterface,
     *  2: PhpTalInterface
     * }>>
     */
    private array $_slotsStack = [];

    private ?Context $_parentContext = null;

    private ?stdClass $_globalContext = null;

    private bool $_echoDeclarations = false;

    public function __construct()
    {
        $this->repeat = new stdClass();
    }

    public function __clone(): void
    {
        $this->repeat = clone $this->repeat;
    }

    /**
     * will switch to this context when popContext() is called
     */
    public function setParent(Context $parent): void
    {
        $this->_parentContext = $parent;
    }

    /**
     * set stdClass object which has property of every global variable
     * It can use __isset() and __get() [none of them or both]
     */
    public function setGlobal(stdClass $globalContext): void
    {
        $this->_globalContext = $globalContext;
    }

    /**
     * save current execution context
     *
     * @return Context Cloned context
     */
    public function pushContext(): Context
    {
        $res = clone $this;
        $res->setParent($this);
        return $res;
    }

    /**
     * get previously saved execution context
     *
     * @return Context Parent context
     */
    public function popContext(): Context
    {
        return $this->_parentContext;
    }

    /**
     * @param bool $tf true if DOCTYPE and XML declaration should be echoed immediately, false if buffered
     */
    public function echoDeclarations(bool $tf): void
    {
        $this->_echoDeclarations = $tf;
    }

    /**
     * Set output document type if not already set.
     *
     * This method ensure PHPTAL uses the first DOCTYPE encountered (main
     * template or any macro template source containing a DOCTYPE.
     *
     * @param bool $called_from_macro will do nothing if _echoDeclarations is also set
     *
     * @throws Exception\ConfigurationException
     */
    public function setDocType(string $doctype, bool $called_from_macro): void
    {
        // FIXME: this is temporary workaround for problem of DOCTYPE disappearing in cloned
        // FIXME: PHPTAL object (because clone keeps _parentContext)
        if (!$this->_docType) {
            $this->_docType = $doctype;
        }

        if ($this->_parentContext) {
            $this->_parentContext->setDocType($doctype, $called_from_macro);
        } elseif ($this->_echoDeclarations) {
            if ($called_from_macro) {
                throw new ConfigurationException(
                    'Executed macro in file with DOCTYPE when using echoExecute(). This is not supported yet. ' .
                    'Remove DOCTYPE or use PHPTAL->execute().'
                );
            }
            echo $doctype;
        } elseif (!$this->_docType) {
            $this->_docType = $doctype;
        }
    }

    /**
     * Set output document xml declaration.
     *
     * This method ensure PHPTAL uses the first xml declaration encountered
     * (main template or any macro template source containing an xml
     * declaration)
     *
     * @param bool $called_from_macro will do nothing if _echoDeclarations is also set
     *
     * @throws Exception\ConfigurationException
     */
    public function setXmlDeclaration(string $xmldec, bool $called_from_macro): void
    {
        // FIXME
        if (!$this->_xmlDeclaration) {
            $this->_xmlDeclaration = $xmldec;
        }

        if ($this->_parentContext) {
            $this->_parentContext->setXmlDeclaration($xmldec, $called_from_macro);
        } elseif ($this->_echoDeclarations) {
            if ($called_from_macro) {
                throw new ConfigurationException(
                    'Executed macro in file with XML declaration when using echoExecute(). This is not supported yet.' .
                    ' Remove XML declaration or use PHPTAL->execute().'
                );
            }
            echo $xmldec . "\n";
        } elseif (!$this->_xmlDeclaration) {
            $this->_xmlDeclaration = $xmldec;
        }
    }

    /**
     * Activate or deactivate exception throwing during unknown path
     * resolution.
     */
    public function noThrow(bool $bool): void
    {
        $this->_nothrow = $bool;
    }

    /**
     * Returns true if specified slot is filled.
     */
    public function hasSlot(string $key): bool
    {
        return isset($this->_slots[$key]) || ($this->_parentContext && $this->_parentContext->hasSlot($key));
    }

    /**
     * Returns the content of specified filled slot.
     *
     * Use echoSlot() whenever you just want to output the slot
     */
    public function getSlot(string $key): string
    {
        if (isset($this->_slots[$key])) {
            if (is_string($this->_slots[$key])) {
                return $this->_slots[$key];
            }
            ob_start();
            call_user_func($this->_slots[$key][0], $this->_slots[$key][1], $this->_slots[$key][2]);
            return (string) ob_get_clean();
        }

        if ($this->_parentContext) {
            return $this->_parentContext->getSlot($key);
        }

        return '';
    }

    /**
     * Immediately echoes content of specified filled slot.
     *
     * Equivalent of echo $this->getSlot();
     */
    public function echoSlot(string $key): string
    {
        if (isset($this->_slots[$key])) {
            if (is_string($this->_slots[$key])) {
                echo $this->_slots[$key];
            } else {
                call_user_func($this->_slots[$key][0], $this->_slots[$key][1], $this->_slots[$key][2]);
            }
        } elseif ($this->_parentContext) {
            return $this->_parentContext->echoSlot($key);
        }

        return '';
    }

    /**
     * Fill a macro slot.
     */
    public function fillSlot(string $key, string $content): void
    {
        $this->_slots[$key] = $content;
        if ($this->_parentContext) {
            // Works around bug with tal:define popping context after fillslot
            $this->_parentContext->_slots[$key] = $content;
        }
    }

    /**
     * @param callable():string $callback
     */
    public function fillSlotCallback(
        string $key,
        callable $callback,
        PhpTalInterface $_thistpl,
        PhpTalInterface $tpl
    ): void {
        $this->_slots[$key] = [$callback, $_thistpl, $tpl];
        if ($this->_parentContext) {
            // Works around bug with tal:define popping context after fillslot
            $this->_parentContext->_slots[$key] = [$callback, $_thistpl, $tpl];
        }
    }

    /**
     * Push current filled slots on stack.
     */
    public function pushSlots(): void
    {
        $this->_slotsStack[] = $this->_slots;
        $this->_slots = [];
    }

    /**
     * Restore filled slots stack.
     */
    public function popSlots(): void
    {
        $this->_slots = array_pop($this->_slotsStack);
    }

    /**
     * Context setter.
     *
     * @throws Exception\InvalidVariableNameException
     */
    public function set(string $varname, mixed $value): void
    {
        $this->__set($varname, $value);
    }

    /**
     * Context setter.
     *
     * @throws Exception\InvalidVariableNameException
     */
    public function __set(string $varname, mixed $value): void
    {
        if (preg_match('/^_|\s/', $varname)) {
            throw new InvalidVariableNameException(
                'Template variable error \'' . $varname . '\' must not begin with underscore or contain spaces'
            );
        }
        $this->$varname = $value;
    }

    public function __isset(string $varname): bool
    {
        // it doesn't need to check isset($this->$varname), because PHP does that _before_ calling __isset()
        return isset($this->_globalContext->$varname) || defined($varname);
    }

    /**
     * Context getter.
     * If variable doesn't exist, it will throw an exception, unless noThrow(true) has been called
     *
     * @throws Exception\VariableNotFoundException
     */
    public function __get(string $varname): mixed
    {
        // PHP checks public properties first, there's no need to support them here

        // must use isset() to allow custom global contexts with __isset()/__get()
        if (isset($this->_globalContext->$varname)) {
            return $this->_globalContext->$varname;
        }

        if (defined($varname)) {
            return constant($varname);
        }

        if ($this->_nothrow) {
            return null;
        }

        throw new VariableNotFoundException("Unable to find variable '$varname' in current scope");
    }

    /**
     * helper method for Context::path()
     *
     * @throws Exception\VariableNotFoundException
     */
    private static function pathError(mixed $base, string $path, string $current, ?string $basename): void
    {
        if ($current !== $path) {
            $pathinfo = " (in path '.../$path')";
        } else {
            $pathinfo = '';
        }

        if (!empty($basename)) {
            $basename = "'" . $basename . "'";
        }

        if (is_array($base)) {
            throw new VariableNotFoundException(
                "Array {$basename} doesn't have key named '$current' $pathinfo"
            );
        }
        if (is_object($base)) {
            throw new VariableNotFoundException(
                ucfirst($base::class) .
                " object {$basename} doesn't have method/property named '$current' $pathinfo"
            );
        }
        throw new VariableNotFoundException(
            trim("Attempt to read property '$current' $pathinfo from " . gettype($base) . " value {$basename}")
        );
    }

    /**
     * Resolve TALES path starting from the first path element.
     * The TALES path : object/method1/10/method2
     * will call : $ctx->path($ctx->object, 'method1/10/method2')
     *
     * This function is very important for PHPTAL performance.
     *
     * This function will become non-static in the future
     *
     * @param mixed $base first element of the path ($ctx)
     * @param string $path rest of the path
     * @param bool $nothrow is used by phptal_exists(). Prevents this function from
     * throwing an exception when a part of the path cannot be resolved, null is
     * returned instead.
     *
     * @throws Exception\VariableNotFoundException
     */
    public static function path(mixed $base, string $path, bool $nothrow = false): mixed
    {
        if ($base === null) {
            if ($nothrow) {
                return null;
            }
            self::pathError($base, $path, $path, $path);
        }

        $chunks = explode('/', $path);
        $current = null;
        $prev = null;
        for ($i = 0, $iMax = count($chunks); $i < $iMax; $i++) {
            if ($current !== $chunks[$i]) { // if not $i-- before continue;
                $prev = $current;
                $current = $chunks[$i];
            }

            // object handling
            if (is_object($base)) {
                // look for method. Both method_exists and is_callable are required
                // because of __call() and protected methods
                if (method_exists($base, $current) && is_callable([$base, $current])) {
                    $base = $base->$current();
                    continue;
                }

                // look for property
                if (property_exists($base, $current)) {
                    $base = $base->$current;
                    continue;
                }

                if ($base instanceof ArrayAccess && $base->offsetExists($current)) {
                    $base = $base->offsetGet($current);
                    continue;
                }

                if (($current === 'length' || $current === 'size') && $base instanceof Countable) {
                    $base = $base->count();
                    continue;
                }

                // look for isset (priority over __get)
                if (method_exists($base, '__isset')) {
                    if ($base->__isset($current)) {
                        $base = $base->$current;
                        continue;
                    }
                } // ask __get and discard if it returns null
                elseif (method_exists($base, '__get')) {
                    $tmp = $base->$current;
                    if ($tmp !== null) {
                        $base = $tmp;
                        continue;
                    }
                }

                // magic method call
                if (method_exists($base, '__call')) {
                    try {
                        $base = $base->__call($current, []);
                        continue;
                    } catch (BadMethodCallException) {
                        // noop
                    }
                }

                if (is_callable($base)) {
                    $base = Helper::phptal_unravel_closure($base);
                    $i--;
                    continue;
                }

                if ($nothrow) {
                    return null;
                }

                self::pathError($base, $path, $current, $prev);
            }

            // array handling
            if (is_array($base)) {
                // key or index
                if (array_key_exists($current, $base)) {
                    $base = $base[$current];
                    continue;
                }

                // virtual methods provided by phptal
                if ($current === 'length' || $current === 'size') {
                    $base = count($base);
                    continue;
                }

                if ($nothrow) {
                    return null;
                }

                self::pathError($base, $path, $current, $prev);
            }

            // string handling
            if (is_string($base)) {
                // virtual methods provided by phptal
                if ($current === 'length' || $current === 'size') {
                    $base = strlen($base);
                    continue;
                }

                // access char at index
                if (is_numeric($current)) {
                    $base = $base[$current];
                    continue;
                }
            }

            // if this point is reached, then the part cannot be resolved

            if ($nothrow) {
                return null;
            }

            self::pathError($base, $path, $current, $prev);
        }

        return $base;
    }
}
