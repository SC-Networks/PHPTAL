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
     * @var string
     */
    public $xmlDeclaration;

    /**
     * @var string
     */
    public $docType;

    /**
     * @var bool
     */
    private $nothrow;


    /**
     * @var array TODO What type?
     */
    private $slots = [];

    /**
     * @var array
     */
    private $slotsStack = [];

    /**
     * @var Context
     */
    private $parentContext;

    /**
     * @var \stdClass
     */
    private $globalContext;

    /**
     * @var bool
     */
    private $echoDeclarations = false;

    /**
     * Context constructor.
     */
    public function __construct()
    {
        $this->repeat = new \stdClass();
    }

    /**
     * @return void
     * @todo ????
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
    public function setParent(Context $parent)
    {
        $this->parentContext = $parent;
    }

    /**
     * set stdClass object which has property of every global variable
     * It can use __isset() and __get() [none of them or both]
     *
     * @param \stdClass $globalContext
     *
     * @return void
     */
    public function setGlobal(\stdClass $globalContext)
    {
        $this->globalContext = $globalContext;
    }

    /**
     * save current execution context
     *
     * @return Context (new)
     */
    public function pushContext()
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
    public function popContext()
    {
        return $this->parentContext;
    }

    /**
     * @param bool $tf true if DOCTYPE and XML declaration should be echoed immediately, false if buffered
     */
    public function echoDeclarations($tf)
    {
        $this->echoDeclarations = $tf;
    }

    /**
     * Set output document type if not already set.
     *
     * This method ensure PHPTAL uses the first DOCTYPE encountered (main
     * template or any macro template source containing a DOCTYPE.
     *
     * @param bool $called_from_macro will do nothing if _echoDeclarations is also set
     *
     * @return void
     * @throws Exception\ConfigurationException
     */
    public function setDocType($doctype, $called_from_macro)
    {
        // FIXME: this is temporary workaround for problem of DOCTYPE disappearing in cloned
        // FIXME: PHPTAL object (because clone keeps _parentContext)
        if (!$this->docType) {
            $this->docType = $doctype;
        }

        if ($this->parentContext) {
            $this->parentContext->setDocType($doctype, $called_from_macro);
        } elseif ($this->echoDeclarations) {
            if (!$called_from_macro) {
                echo $doctype;
            } else {
                throw new Exception\ConfigurationException(
                    'Executed macro in file with DOCTYPE when using echoExecute(). This is not supported yet. ' .
                    'Remove DOCTYPE or use PHPTAL->execute().'
                );
            }
        } elseif (!$this->docType) {
            $this->docType = $doctype;
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
    public function setXmlDeclaration($xmldec, $called_from_macro)
    {
        // FIXME
        if (!$this->xmlDeclaration) {
            $this->xmlDeclaration = $xmldec;
        }

        if ($this->parentContext) {
            $this->parentContext->setXmlDeclaration($xmldec, $called_from_macro);
        } elseif ($this->echoDeclarations) {
            if (!$called_from_macro) {
                echo $xmldec . "\n";
            } else {
                throw new Exception\ConfigurationException(
                    'Executed macro in file with XML declaration when using echoExecute(). This is not supported yet.' .
                    ' Remove XML declaration or use PHPTAL->execute().'
                );
            }
        } elseif (!$this->xmlDeclaration) {
            $this->xmlDeclaration = $xmldec;
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
    public function noThrow($bool)
    {
        $this->nothrow = $bool;
    }

    /**
     * Returns true if specified slot is filled.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasSlot($key)
    {
        return isset($this->slots[$key]) || ($this->parentContext && $this->parentContext->hasSlot($key));
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
    public function getSlot($key)
    {
        if (isset($this->slots[$key])) {
            if (is_string($this->slots[$key])) {
                return $this->slots[$key];
            }
            ob_start();
            call_user_func($this->slots[$key][0], $this->slots[$key][1], $this->slots[$key][2]);
            return ob_get_clean();
        }

        if ($this->parentContext) {
            return $this->parentContext->getSlot($key);
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
    public function echoSlot($key)
    {
        if (isset($this->slots[$key])) {
            if (is_string($this->slots[$key])) {
                echo $this->slots[$key];
            } else {
                call_user_func($this->slots[$key][0], $this->slots[$key][1], $this->slots[$key][2]);
            }
        } elseif ($this->parentContext) {
            return $this->parentContext->echoSlot($key);
        }

        return '';
    }

    /**
     * Fill a macro slot.
     *
     * @param string $key
     * @param string $content TODO Really string?
     * @return void
     */
    public function fillSlot($key, $content)
    {
        $this->slots[$key] = $content;
        if ($this->parentContext) {
            // Works around bug with tal:define popping context after fillslot
            $this->parentContext->slots[$key] = $content;
        }
    }

    /**
     * @param string $key
     * @param callable $callback
     * @param string $_thistpl
     * @param string $tpl
     *
     * @return void
     */
    public function fillSlotCallback($key, callable $callback, $_thistpl, $tpl)
    {
        $this->slots[$key] = [$callback, $_thistpl, $tpl];
        if ($this->parentContext) {
            // Works around bug with tal:define popping context after fillslot
            $this->parentContext->slots[$key] = [$callback, $_thistpl, $tpl];
        }
    }

    /**
     * Push current filled slots on stack.
     *
     * @return void
     */
    public function pushSlots()
    {
        $this->slotsStack[] = $this->slots;
        $this->slots = [];
    }

    /**
     * Restore filled slots stack.
     *
     * @return void
     */
    public function popSlots()
    {
        $this->slots = array_pop($this->slotsStack);
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
    public function __set($varname, $value)
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
    public function __isset($varname)
    {
        // it doesn't need to check isset($this->$varname), because PHP does that _before_ calling __isset()
        return isset($this->globalContext->$varname) || defined($varname);
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
    public function __get($varname)
    {
        // PHP checks public properties first, there's no need to support them here

        // must use isset() to allow custom global contexts with __isset()/__get()
        if (isset($this->globalContext->$varname)) {
            return $this->globalContext->$varname;
        }

        if (defined($varname)) {
            return constant($varname);
        }

        if ($this->nothrow) {
            return null;
        }

        throw new Exception\VariableNotFoundException('Unable to find variable \'$varname\' in current scope');
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
    private static function pathError($base, $path, $current, $basename)
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
    public static function path($base, $path, $nothrow = false)
    {
        if ($base === null) {
            if ($nothrow) {
                return null;
            }
            static::pathError($base, $path, $path, $path);
        }

        $chunks = explode('/', $path);
        $current = null;
        $prev = null;
        for ($i = 0, $iMax = count($chunks); $i < $iMax; $i++) {
            if ($current != $chunks[$i]) { // if not $i-- before continue;
                $prev = $current;
                $current = $chunks[$i];
            }

            // object handling
            if (is_object($base)) {
                // look for method. Both method_exists and is_callable are required
                // because of __call() and protected methods
                if (method_exists($base, $current) && is_callable(array($base, $current))) {
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
