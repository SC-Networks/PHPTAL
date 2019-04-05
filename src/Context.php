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

/**
 * This class handles template execution context.
 * Holds template variables and carries state/scope across macro executions.
 *
 */
class Context
{
    /**
     * @var \stdClass
     */
    public $repeat;

    /**
     * n.b. The following variables *must* be prefixed with an underscore '_' for reasons...
     */

    /**
     * @var string
     */
    public $_xmlDeclaration;

    /**
     * @var string
     */
    public $_docType;

    /**
     * @var bool
     */
    private $_nothrow;


    /**
     * @var array TODO What type?
     */
    private $_slots = [];

    /**
     * @var array
     */
    private $_slotsStack = [];

    /**
     * @var Context
     */
    private $_parentContext;

    /**
     * @var \stdClass
     */
    private $_globalContext;

    /**
     * @var bool
     */
    private $_echoDeclarations = false;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->repeat = new \stdClass();
    }

    /**
     * @return void
     */
    public function __clone()
    {
        $this->repeat = clone $this->repeat;
    }

    /**
     * will switch to this context when popContext() is called
     *
     * @param Context $parent
     *
     * @return void
     */
    public function setParent(Context $parent): void
    {
        $this->_parentContext = $parent;
    }

    /**
     * set stdClass object which has property of every global variable
     * It can use __isset() and __get() [none of them or both]
     *
     * @param \stdClass $globalContext
     *
     * @return void
     */
    public function setGlobal(\stdClass $globalContext): void
    {
        $this->_globalContext = $globalContext;
    }

    /**
     * save current execution context
     *
     * @return Context (new)
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
     * @return Context (old)
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
     * @param string $doctype
     * @param bool $called_from_macro will do nothing if _echoDeclarations is also set
     *
     * @return void
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
                throw new Exception\ConfigurationException(
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
     * @param string $xmldec
     * @param bool $called_from_macro will do nothing if _echoDeclarations is also set
     *
     * @return void
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
                throw new Exception\ConfigurationException(
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
     *
     * @param bool $bool
     *
     * @return void
     */
    public function noThrow(bool $bool): void
    {
        $this->_nothrow = $bool;
    }

    /**
     * Returns true if specified slot is filled.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasSlot(string $key): bool
    {
        return isset($this->_slots[$key]) || ($this->_parentContext && $this->_parentContext->hasSlot($key));
    }

    /**
     * Returns the content of specified filled slot.
     *
     * Use echoSlot() whenever you just want to output the slot
     *
     * @param string $key
     *
     * @return string
     */
    public function getSlot(string $key): string
    {
        if (isset($this->_slots[$key])) {
            if (is_string($this->_slots[$key])) {
                return $this->_slots[$key];
            }
            ob_start();
            call_user_func($this->_slots[$key][0], $this->_slots[$key][1], $this->_slots[$key][2]);
            return ob_get_clean();
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
     *
     * @param string $key
     *
     * @return string
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
     *
     * @param string $key
     * @param string $content
     * @return void
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
     * @param string $key
     * @param callable $callback
     * @param PhpTalInterface $_thistpl
     * @param PhpTalInterface $tpl
     *
     * @return void
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
     *
     * @return void
     */
    public function pushSlots(): void
    {
        $this->_slotsStack[] = $this->_slots;
        $this->_slots = [];
    }

    /**
     * Restore filled slots stack.
     *
     * @return void
     */
    public function popSlots(): void
    {
        $this->_slots = array_pop($this->_slotsStack);
    }

    /**
     * Context setter.
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     * @throws Exception\InvalidVariableNameException
     */
    public function set(string $varname, $value): void
    {
        $this->__set($varname, $value);
    }

    /**
     * Context setter.
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     * @throws Exception\InvalidVariableNameException
     */
    public function __set(string $varname, $value): void
    {
        if (preg_match('/^_|\s/', $varname)) {
            throw new Exception\InvalidVariableNameException(
                'Template variable error \'' . $varname . '\' must not begin with underscore or contain spaces'
            );
        }
        $this->$varname = $value;
    }

    /**
     * @param string $varname
     *
     * @return bool
     */
    public function __isset(string $varname): bool
    {
        // it doesn't need to check isset($this->$varname), because PHP does that _before_ calling __isset()
        return isset($this->_globalContext->$varname) || defined($varname);
    }

    /**
     * Context getter.
     * If variable doesn't exist, it will throw an exception, unless noThrow(true) has been called
     *
     * @param string $varname
     *
     * @return mixed
     * @throws Exception\VariableNotFoundException
     */
    public function __get(string $varname)
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

        throw new Exception\VariableNotFoundException("Unable to find variable '$varname' in current scope");
    }

    /**
     * helper method for Context::path()
     *
     * @param mixed $base
     * @param string $path
     * @param string $current
     * @param string $basename
     * @throws Exception\VariableNotFoundException
     */
    private static function pathError($base, string $path, string $current, ?string $basename): void
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
            throw new Exception\VariableNotFoundException(
                "Array {$basename} doesn't have key named '$current' $pathinfo"
            );
        }
        if (is_object($base)) {
            throw new Exception\VariableNotFoundException(
                ucfirst(get_class($base)) .
                " object {$basename} doesn't have method/property named '$current' $pathinfo"
            );
        }
        throw new Exception\VariableNotFoundException(
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
     * @return mixed
     * @throws Exception\VariableNotFoundException
     */
    public static function path($base, string $path, bool $nothrow = null)
    {
        $nothrow = $nothrow ?? false;

        if ($base === null) {
            if ($nothrow) {
                return null;
            }
            static::pathError($base, $path, $path, $path);
        }

        if (is_int($path)) {
            $chunks = [];
        } else {
            $chunks = explode('/', $path);
        }
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

                if ($base instanceof \ArrayAccess && $base->offsetExists($current)) {
                    $base = $base->offsetGet($current);
                    continue;
                }

                if (($current === 'length' || $current === 'size') && $base instanceof \Countable) {
                    $base = count($base);
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
                    } catch (\BadMethodCallException $e) {
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

                static::pathError($base, $path, $current, $prev);
            }

            // array handling
            if (is_array($base)) {
                // key or index
                if (array_key_exists((string)$current, $base)) {
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

                static::pathError($base, $path, $current, $prev);
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

            static::pathError($base, $path, $current, $prev);
        }

        return $base;
    }
}
