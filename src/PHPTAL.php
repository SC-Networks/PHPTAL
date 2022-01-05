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

use PhpTal\Php\TalesInternal;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * PHPTAL template entry point.
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */
class PHPTAL implements PhpTalInterface
{
    public const PHPTAL_VERSION = '3_0_2';

    /**
     * constants for output mode
     * @see setOutputMode()
     */
    public const XHTML = 11;
    public const XML   = 22;
    public const HTML5 = 55;

    /**
     * @see getPreFilters()
     *
     * @var FilterInterface[]
     */
    protected $prefilters = [];

    /**
     * The postfilter which will get called on every run
     *
     * @var FilterInterface
     */
    protected $postfilter;

    /**
     *  list of template source repositories given to file source resolver
     *
     * @var string[]
     */
    protected $repositories = [];

    /**
     *  template path (path that has been set, not necessarily loaded)
     *
     * @var string|null
     */
    protected $path;

    /**
     *  template source resolvers (classes that search for templates by name)
     *
     *  @var SourceResolverInterface[]
     */
    protected $resolvers = [];

    /**
     *  template source (only set when not working with file)
     *
     * @var SourceInterface|null
     */
    protected $source;

    /**
     * destination of PHP intermediate file
     *
     * @var string|null
     */
    protected $codeFile;

    /**
     * php function generated for the template
     *
     * @var string|null
     */
    protected $functionName;

    /**
     * set to true when template is ready for execution
     *
     * @var bool
     */
    protected $prepared = false;

    /**
     * associative array of phptal:id => \PhpTal\TriggerInterface
     *
     * @var TriggerInterface[]
     */
    protected $triggers = [];

    /**
     * i18n translator
     *
     * @var TranslationServiceInterface|null
     */
    protected $translator;

    /**
     * global execution context
     *
     * @var stdClass
     */
    protected $globalContext;

    /**
     * current execution context
     *
     * @var Context
     */
    protected $context;

    /**
     * list of on-error caught exceptions
     *
     * @var \Exception[]
     */
    protected $errors = [];

    /**
     * encoding used throughout
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * type of syntax used in generated templates
     *
     * @var int
     */
    protected $outputMode = self::XHTML;

    // configuration properties

    /**
     * don't use code cache
     *
     * @var bool
     */
    protected $forceReparse = false;

    /**
     * directory where code cache is
     *
     * @var string
     */
    private $phpCodeDestination;

    /**
     * @var string
     */
    private $phpCodeExtension = 'php';

    /**
     * number of days
     *
     * @var float
     */
    private $cacheLifetime = 30.;

    /**
     * 1/x
     *
     * @var int
     */
    private $cachePurgeFrequency = 30;

    /**
     * speeds up calls to external templates
     *
     * @var PhpTalInterface[]
     */
    private $externalMacroTemplatesCache = [];

    /**
     * @var int
     */
    private $subpathRecursionLevel = 0;

    /**
     * @param string $path Template file path.
     */
    public function __construct(?string $path = null)
    {
        $this->path = $path;
        $this->globalContext = new stdClass();
        $this->context = new Context();
        $this->context->setGlobal($this->globalContext);

        $this->setPhpCodeDestination(sys_get_temp_dir());
    }

    /**
     * Clone template state and context.
     *
     * @return void
     */
    public function __clone()
    {
        $this->context = $this->context->pushContext();
    }

    /**
     * Set template from file path.
     *
     * @param string $path filesystem path,
     *                     or any path that will be accepted by source resolver
     *
     * @return $this
     */
    public function setTemplate(?string $path): PhpTalInterface
    {
        $this->prepared = false;
        $this->functionName = null;
        $this->codeFile = null;
        $this->path = $path;
        $this->source = null;
        $this->context->_docType = null;
        $this->context->_xmlDeclaration = null;
        return $this;
    }

    /**
     * Set template from source.
     *
     * Should be used only with temporary template sources.
     * Use setTemplate() or addSourceResolver() whenever possible.
     *
     * @param string $src The phptal template source.
     * @param string $path Fake and 'unique' template path.
     *
     * @return $this
     */
    public function setSource(string $src, ?string $path = null): PhpTalInterface
    {
        $this->prepared = false;
        $this->functionName = null;
        $this->codeFile = null;
        $this->source = new StringSource($src, $path);
        $this->path = $this->source->getRealPath();
        $this->context->_docType = null;
        $this->context->_xmlDeclaration = null;
        return $this;
    }

    /**
     * Specify where to look for templates.
     *
     * @param mixed $rep string or Array of repositories
     *
     * @return $this
     */
    public function setTemplateRepository($rep): PhpTalInterface
    {
        if (is_array($rep)) {
            $this->repositories = $rep;
        } else {
            $this->repositories[] = $rep;
        }
        return $this;
    }

    /**
     * Get template repositories.
     *
     * @return array
     */
    public function getTemplateRepositories(): array
    {
        return $this->repositories;
    }

    /**
     * Clears the template repositories.
     *
     * @return $this
     */
    public function clearTemplateRepositories(): PhpTalInterface
    {
        $this->repositories = [];
        return $this;
    }

    /**
     * Specify how to look for templates.
     *
     * @param SourceResolverInterface $resolver instance of resolver
     *
     * @return $this
     */
    public function addSourceResolver(SourceResolverInterface $resolver): PhpTalInterface
    {
        $this->resolvers[] = $resolver;
        return $this;
    }

    /**
     * Ignore XML/XHTML comments on parsing.
     * Comments starting with <!--! are always stripped.
     *
     * @param bool $bool if true all comments are stripped during parse
     *
     * @return $this
     */
    public function stripComments(bool $bool): PhpTalInterface
    {
        $this->resetPrepared();

        if ($bool) {
            $this->prefilters['_phptal_strip_comments_'] = new PreFilter\StripComments();
        } else {
            unset($this->prefilters['_phptal_strip_comments_']);
        }
        return $this;
    }

    /**
     * Set output mode
     * XHTML output mode will force elements like <link/>, <meta/> and <img/>, etc.
     * to be empty and threats attributes like selected, checked to be
     * boolean attributes.
     *
     * XML output mode outputs XML without such modifications
     * and is neccessary to generate RSS feeds properly.
     *
     * @param int $mode (\PhpTal\PHPTAL::XML, \PhpTal\PHPTAL::XHTML or \PhpTal\PHPTAL::HTML5).
     *
     * @return $this
     * @throws Exception\ConfigurationException
     */
    public function setOutputMode(int $mode): PhpTalInterface
    {
        $this->resetPrepared();

        if (!in_array($mode, [static::XHTML, static::XML, static::HTML5], true)) {
            throw new Exception\ConfigurationException('Unsupported output mode ' . $mode);
        }
        $this->outputMode = $mode;
        return $this;
    }

    /**
     * Get output mode
     * @see setOutputMode()
     *
     * @return int output mode constant
     */
    public function getOutputMode(): int
    {
        return $this->outputMode;
    }

    /**
     * Set input and ouput encoding. Encoding is case-insensitive.
     *
     * @param string $enc example: 'UTF-8'
     *
     * @return $this
     */
    public function setEncoding(string $enc): PhpTalInterface
    {
        $enc = strtoupper($enc);
        if ($enc !== $this->encoding) {
            $this->encoding = $enc;
            if ($this->translator) {
                $this->translator->setEncoding($enc);
            }

            $this->resetPrepared();
        }
        return $this;
    }

    /**
     * Get input and ouput encoding.
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Set the storage location for intermediate PHP files.
     * The path cannot contain characters that would be interpreted by glob() (e.g. *[]?)
     *
     * @param string $path Intermediate file path.
     *
     * @return void
     */
    public function setPhpCodeDestination(string $path): void
    {
        $this->phpCodeDestination = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->resetPrepared();
    }

    /**
     * Get the storage location for intermediate PHP files.
     *
     * @return string
     */
    public function getPhpCodeDestination(): string
    {
        return $this->phpCodeDestination;
    }

    /**
     * Set the file extension for intermediate PHP files.
     *
     * @param string $extension The file extension.
     *
     * @return $this
     */
    public function setPhpCodeExtension(string $extension): PhpTalInterface
    {
        $this->phpCodeExtension = $extension;
        $this->resetPrepared();
        return $this;
    }

    /**
     * Get the file extension for intermediate PHP files.
     */
    public function getPhpCodeExtension(): string
    {
        return $this->phpCodeExtension;
    }

    /**
     * Flags whether to ignore intermediate php files and to
     * reparse templates every time (if set to true).
     *
     * DON'T USE IN PRODUCTION - this makes PHPTAL many times slower.
     *
     * @param bool $bool Forced reparse state.
     *
     * @return $this
     */
    public function setForceReparse(bool $bool): PhpTalInterface
    {
        $this->forceReparse = $bool;
        return $this;
    }

    /**
     * Get the value of the force reparse state.
     *
     * @return bool
     */
    public function getForceReparse(): bool
    {
        return $this->forceReparse;
    }

    /**
     * Set I18N translator.
     *
     * This sets encoding used by the translator, so be sure to use encoding-dependent
     * features of the translator (e.g. addDomain) _after_ calling setTranslator.
     *
     * @param TranslationServiceInterface $t instance
     *
     * @return $this
     */
    public function setTranslator(TranslationServiceInterface $t): PhpTalInterface
    {
        $this->translator = $t;
        $t->setEncoding($this->getEncoding());
        return $this;
    }

    /**
     * Add new prefilter to filter chain.
     * Prefilters are called only once template is compiled.
     *
     * PreFilters must inherit PreFilter class.
     * (in future this method will allow string with filter name instead of object)
     *
     * @param PreFilter $filter PreFilter object or name of prefilter to add
     *
     * @return $this
     */
    final public function addPreFilter(PreFilter $filter): PhpTalInterface
    {
        $this->resetPrepared();
        $this->prefilters[] = $filter;
        return $this;
    }

    /**
     * Sets the level of recursion for template cache directories
     *
     * @param int $recursion_level
     *
     * @return self
     */
    public function setSubpathRecursionLevel(int $recursion_level): PhpTalInterface
    {
        $this->subpathRecursionLevel = $recursion_level;
        return $this;
    }

    /**
     * Array with all prefilter objects *or strings* that are names of prefilter classes.
     * (the latter is not implemented in 1.2.1)
     *
     * Array keys may be non-numeric!
     *
     * @return FilterInterface[]
     */
    protected function getPreFilters(): array
    {
        return $this->prefilters;
    }

    /**
     * Returns string that is unique for every different configuration of prefilters.
     * Result of prefilters may be cached until this string changes.
     *
     * You can override this function.
     *
     * @return string
     */
    private function getPreFiltersCacheId(): string
    {
        $cacheid = '';
        foreach ($this->getPreFilters() as $key => $prefilter) {
            if ($prefilter instanceof PreFilter) {
                $cacheid .= $key . $prefilter->getCacheId();
            } else {
                $cacheid .= $key . get_class($prefilter);
            }
        }
        return $cacheid;
    }

    /**
     * Instantiate prefilters
     *
     * @return FilterInterface[]
     */
    private function getPreFilterInstances(): array
    {
        $prefilters = $this->getPreFilters();

        foreach ($prefilters as $prefilter) {
            if ($prefilter instanceof PreFilter) {
                $prefilter->setPHPTAL($this);
            }
        }
        return $prefilters;
    }

    /**
     * Set template post filter.
     * It will be called every time after template generates output.
     *
     * See PHPTAL_PostFilter class.
     *
     * @param FilterInterface $filter filter instance
     *
     * @return $this
     */
    public function setPostFilter(FilterInterface $filter): PhpTalInterface
    {
        $this->postfilter = $filter;
        return $this;
    }

    /**
     * Register a trigger for specified phptal:id.
     *
     * @param string $id phptal:id to look for
     * @param TriggerInterface $trigger
     *
     * @return $this
     */
    public function addTrigger(string $id, TriggerInterface $trigger): PhpTalInterface
    {
        $this->triggers[$id] = $trigger;
        return $this;
    }

    /**
     * Returns trigger for specified phptal:id.
     *
     * @param string $id phptal:id
     *
     * @return TriggerInterface|null
     */
    public function getTrigger(string $id): ?TriggerInterface
    {
        return $this->triggers[$id] ?? null;
    }

    /**
     * Set a context variable.
     * Use it by setting properties on PHPTAL object.
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     * @throws Exception\InvalidVariableNameException
     */
    public function __set($varname, $value)
    {
        $this->context->set($varname, $value);
    }

    /**
     * Set a context variable.
     *
     * @see \PhpTal\PHPTAL::__set()
     * @param string $varname name of the variable
     * @param mixed $value value of the variable
     *
     * @return $this
     * @throws Exception\InvalidVariableNameException
     */
    public function set(string $varname, $value): PhpTalInterface
    {
        $this->context->set($varname, $value);
        return $this;
    }

    /**
     * Execute the template code and return generated markup.
     *
     * @return string
     * @throws Exception\TemplateException
     * @throws Throwable
     */
    public function execute(): string
    {
        $res = '';

        try {
            if (!$this->prepared) {
                // includes generated template PHP code
                $this->prepare();
            }
            $this->context->echoDeclarations(false);

            $templateFunction = $this->getFunctionName();

            try {
                ob_start();
                $templateFunction($this, $this->context);
                $res = ob_get_clean();
            } catch (Throwable $e) {
                ob_end_clean();
                throw $e;
            }

            // unshift doctype
            if ($this->context->_docType) {
                $res = $this->context->_docType . $res;
            }

            // unshift xml declaration
            if ($this->context->_xmlDeclaration) {
                $res = $this->context->_xmlDeclaration . "\n" . $res;
            }

            if ($this->postfilter !== null) {
                return $this->postfilter->filter($res);
            }
        } catch (Throwable $e) {
            ExceptionHandler::handleException($e, $this->getEncoding());
        }

        return $res;
    }

    /**
     * Execute and echo template without buffering of the output.
     * This function does not allow postfilters nor DOCTYPE/XML declaration.
     *
     * @return void
     * @throws Exception\TemplateException
     * @throws Throwable
     */
    public function echoExecute(): void
    {
        try {
            if (!$this->prepared) {
                // includes generated template PHP code
                $this->prepare();
            }

            if ($this->postfilter !== null) {
                throw new Exception\ConfigurationException('echoExecute() does not support postfilters');
            }

            $this->context->echoDeclarations(true);

            $templateFunction = $this->getFunctionName();
            $templateFunction($this, $this->context);
        } catch (Throwable $e) {
            ExceptionHandler::handleException($e, $this->getEncoding());
        }
    }

    /**
     * This is PHPTAL's internal function that handles
     * execution of macros from templates.
     *
     * $this is caller's context (the file where execution had originally started)
     *
     * @param string $path
     * @param PhpTalInterface $local_tpl is PHPTAL instance of the file in which macro is defined
     *                          (it will be different from $this if it's external macro call)
     *
     * @throws Exception\IOException
     * @throws Exception\MacroMissingException
     * @throws Exception\TemplateException
     * @throws Throwable
     */
    final public function executeMacroOfTemplate(string $path, PhpTalInterface $local_tpl): void
    {
        // extract macro source file from macro name, if macro path does not
        // contain filename, then the macro is assumed to be local

        if (preg_match('/^(.*?)\/([a-z0-9_-]*)$/i', $path, $m)) {
            [, $file, $macroName] = $m;

            if (isset($this->externalMacroTemplatesCache[$file])) {
                $tpl = $this->externalMacroTemplatesCache[$file];
            } else {
                $tpl = clone $this;
                array_unshift($tpl->repositories, dirname($this->source->getRealPath()));
                $tpl->setTemplate($file);
                $tpl->prepare();

                // keep it small (typically only 1 or 2 external files are used)
                if (count($this->externalMacroTemplatesCache) > 10) {
                    $this->externalMacroTemplatesCache = [];
                }
                $this->externalMacroTemplatesCache[$file] = $tpl;
            }

            $fun = $tpl->getFunctionName() . '_' . str_replace('-', '_', $macroName);
            if (!function_exists($fun)) {
                throw new Exception\MacroMissingException(
                    "Macro '$macroName' is not defined in $file",
                    $this->getSource()->getRealPath()
                );
            }

            $fun($tpl, $this);
        } else {
            // call local macro
            $fun = $local_tpl->getFunctionName() . '_' . str_replace('-', '_', $path);
            if (!function_exists($fun)) {
                throw new Exception\MacroMissingException(
                    "Macro '$path' is not defined",
                    $local_tpl->getSource()->getRealPath()
                );
            }
            $fun($local_tpl, $this);
        }
    }

    /**
     * ensure that getCodePath will return up-to-date path
     *
     * @return void
     * @throws Exception\ConfigurationException
     * @throws Exception\IOException
     */
    private function setCodeFile(): void
    {
        $this->findTemplate();
        $this->codeFile = $this->getPhpCodeDestination() . $this->getSubPath() . '/'  . $this->getFunctionName()
            . '.' . $this->getPhpCodeExtension();
    }

    /**
     * Generate a subpath structure depending on the config
     *
     * @return string
     */
    private function getSubPath(): string
    {
        $real_path = md5($this->getFunctionName());
        $path = '';
        for ($i = 0; $i < $this->subpathRecursionLevel; $i++) {
            $path .= '/' . $real_path[$i];
        }
        if (!file_exists($this->getPhpCodeDestination() . $path) &&
            !mkdir($concurrentDirectory = $this->getPhpCodeDestination() . $path, 0777, true) &&
            !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        return $path;
    }

    /**
     * @return void
     */
    protected function resetPrepared(): void
    {
        $this->prepared = false;
        $this->functionName = null;
        $this->codeFile = null;
    }

    /**
     * Prepare template without executing it.
     *
     * @return self
     * @throws Exception\ConfigurationException
     * @throws Exception\IOException
     * @throws Exception\TemplateException
     * @throws Throwable
     */
    public function prepare(): PhpTalInterface
    {
        // clear just in case settings changed and cache is out of date
        $this->externalMacroTemplatesCache = [];

        // find the template source file and update function name
        $this->setCodeFile();

        if (!function_exists($this->getFunctionName())) {
            // parse template if php generated code does not exists or template
            // source file modified since last generation or force reparse is set
            if ($this->getForceReparse() || !file_exists($this->getCodePath())) {
                // i'm not sure where that belongs, but not in normal path of execution
                // because some sites have _a lot_ of files in temp
                if ($this->getCachePurgeFrequency() && mt_rand() % $this->getCachePurgeFrequency() === 0) {
                    $this->cleanUpGarbage();
                }

                $result = $this->parse();

                if (!file_put_contents($this->getCodePath(), $result)) {
                    throw new Exception\IOException('Unable to open '.$this->getCodePath().' for writing');
                }

                // the awesome thing about eval() is that parse errors don't stop PHP.
                // when PHP dies during eval, fatal error is printed and
                // can be captured with output buffering
                ob_start();
                try {
                    eval("?>\n".$result);
                } catch (Throwable $e) {
                    ob_end_clean();
                    throw $e;
                }

                if (!function_exists($this->getFunctionName())) {
                    $msg = str_replace('eval()\'d code', $this->getCodePath(), ob_get_clean());

                    // greedy .* ensures last match
                    $line = preg_match('/.*on line (\d+)$/m', $msg, $m) ? $m[1] : 0;
                    throw new Exception\TemplateException(trim($msg), $this->getCodePath(), $line);
                }
                ob_end_clean();
            } else {
                // eval trick is used only on first run,
                // just in case it causes any problems with opcode accelerators
                require $this->getCodePath();
            }
        }

        $this->prepared = true;
        return $this;
    }

    /**
     * get how long compiled templates and phptal:cache files are kept, in days
     *
     * @return float
     */
    private function getCacheLifetime(): float
    {
        return $this->cacheLifetime;
    }

    /**
     * set how long compiled templates and phptal:cache files are kept
     *
     * @param float $days number of days
     *
     * @return $this
     */
    public function setCacheLifetime(float $days): PhpTalInterface
    {
        $this->cacheLifetime = max(0.5, $days);
        return $this;
    }

    /**
     * PHPTAL will scan cache and remove old files on every nth compile
     * Set to 0 to disable cleanups
     *
     * @param int $n
     *
     * @return $this
     */
    public function setCachePurgeFrequency(int $n): PhpTalInterface
    {
        $this->cachePurgeFrequency = $n;
        return $this;
    }

    /**
     * how likely cache cleaning can happen
     * @see self::setCachePurgeFrequency()
     *
     * @return int
     */
    private function getCachePurgeFrequency(): int
    {
        return $this->cachePurgeFrequency;
    }


    /**
     * Removes all compiled templates from cache that
     * are older than getCacheLifetime() days
     *
     * @return void
     */
    public function cleanUpGarbage(): void
    {
        $cacheFilesExpire = (int) (time() - $this->getCacheLifetime() * 3600 * 24);

        // relies on templates sorting order being related to their modification dates
        $upperLimit = $this->getPhpCodeDestination() . $this->getFunctionNamePrefix($cacheFilesExpire) . '_';
        $lowerLimit = $this->getPhpCodeDestination() . $this->getFunctionNamePrefix();

        // last * gets phptal:cache
        $cacheFiles = glob(sprintf(
            '%s%stpl_????????_*.%s*',
            $this->getPhpCodeDestination(),
            str_repeat('*/', $this->subpathRecursionLevel),
            $this->getPhpCodeExtension()
        ), GLOB_NOSORT);

        if ($cacheFiles) {
            foreach ($cacheFiles as $index => $file) {
                // comparison here skips filenames that are certainly too new
                if (strcmp($file, $upperLimit) <= 0 || strpos($file, $lowerLimit) === 0) {
                    $time = filemtime($file);
                    if ($time && $time < $cacheFilesExpire) {
                        @unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Removes content cached with phptal:cache for currently set template
     * Must be called after setSource/setTemplate.
     *
     * @return void
     * @throws Exception\ConfigurationException
     * @throws Exception\IOException
     */
    public function cleanUpCache(): void
    {
        $filename = $this->getCodePath();
        $cacheFiles = glob($filename . '?*', GLOB_NOSORT);
        if ($cacheFiles) {
            foreach ($cacheFiles as $file) {
                if (strpos($file, $filename) !== 0) {
                    continue;
                } // safety net
                @unlink($file);
            }
        }
        $this->prepared = false;
    }

    /**
     * Returns the path of the intermediate PHP code file.
     *
     * The returned file may be used to cleanup (unlink) temporary files
     * generated by temporary templates or more simply for debug.
     *
     * @return string
     * @throws Exception\ConfigurationException
     * @throws Exception\IOException
     */
    public function getCodePath(): string
    {
        if (!$this->codeFile) {
            $this->setCodeFile();
        }
        return $this->codeFile;
    }

    /**
     * Returns the generated template function name.
     *
     * @return string
     */
    public function getFunctionName(): string
    {
        // function name is used as base for caching, so it must be unique for
        // every combination of settings that changes code in compiled template

        if (!$this->functionName) {
            // just to make tempalte name recognizable
            $basename = preg_replace('/\.[a-z]{3,5}$/', '', basename($this->source->getRealPath()));
            $basename = substr(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $basename), '_'), 0, 20);

            $hash = md5(
                static::PHPTAL_VERSION . PHP_VERSION
                . $this->source->getRealPath()
                . $this->getEncoding()
                . $this->getPreFiltersCacheId()
                . $this->getOutputMode(),
                true
            );

            // uses base64 rather than hex to make filename shorter.
            // there is loss of some bits due to name constraints and case-insensivity,
            // but that's still over 110 bits in addition to basename and timestamp.
            $hash = strtr(rtrim(base64_encode($hash), '='), '+/=', '_A_');

            $this->functionName = $this->getFunctionNamePrefix($this->source->getLastModifiedTime()) .
                                   $basename . '__' . $hash;
        }
        return $this->functionName;
    }

    /**
     * Returns prefix used for function name.
     * Function name is also base name for the template.
     *
     * @param int|null $timestamp unix timestamp with template modification date
     *
     * @return string
     */
    private function getFunctionNamePrefix(?int $timestamp = null): string
    {
        // tpl_ prefix and last modified time must not be changed,
        // because cache cleanup relies on that
        return 'tpl_' . sprintf('%08x', $timestamp ?? 0) . '_';
    }

    /**
     * Returns template translator.
     *
     * @return TranslationServiceInterface|null
     */
    public function getTranslator(): ?TranslationServiceInterface
    {
        return $this->translator;
    }

    /**
     * Returns array of exceptions caught by tal:on-error attribute.
     *
     * @return \Exception[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Public for phptal templates, private for user.
     *
     * @param \Exception $error
     *
     * @return void
     */
    public function addError(\Exception $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Returns current context object.
     * Use only in Triggers.
     *
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * only for use in generated template code
     *
     * @return stdClass
     */
    public function getGlobalContext(): stdClass
    {
        return $this->globalContext;
    }

    /**
     * only for use in generated template code
     *
     * @return Context
     */
    final public function pushContext(): Context
    {
        $this->context = $this->context->pushContext();
        return $this->context;
    }

    /**
     * only for use in generated template code
     *
     * @return Context
     */
    final public function popContext(): Context
    {
        $this->context = $this->context->popContext();
        return $this->context;
    }

    /**
     * Parse currently set template, prefilter and generate PHP code.
     *
     * @return string (compiled PHP code)
     * @throws Exception\ConfigurationException
     * @throws Exception\ParserException
     * @throws Exception\TemplateException
     * @throws Exception\PhpTalException
     */
    protected function parse(): string
    {
        $data = $this->source->getData();

        $prefilters = $this->getPreFilterInstances();
        foreach ($prefilters as $prefilter) {
            $data = $prefilter->filter($data);
        }

        $realpath = $this->source->getRealPath();
        $parser = new Dom\SaxXmlParser($this->encoding);

        $builder = new Dom\PHPTALDocumentBuilder();
        $tree = $parser->parseString($builder, $data, $realpath)->getResult();

        foreach ($prefilters as $prefilter) {
            if ($prefilter instanceof PreFilter) {
                $prefilter->filterDOM($tree);
            }
        }

        $state = new Php\State($this);

        $codewriter = new Php\CodeWriter($state);
        $codewriter->doTemplateFile($this->getFunctionName(), $tree);

        return $codewriter->getResult();
    }

    /**
     * Search template source location.
     *
     * @return void
     * @throws Exception\ConfigurationException
     * @throws Exception\IOException
     */
    protected function findTemplate(): void
    {
        if ($this->path === null) {
            throw new Exception\ConfigurationException('No template file specified');
        }

        if ($this->source !== null) {
            return;
        }

        if ($this->resolvers === [] && !$this->repositories) {
            $this->source = new FileSource($this->path);
        } else {
            foreach ($this->resolvers as $resolver) {
                $source = $resolver->resolve($this->path);
                if ($source !== null) {
                    $this->source = $source;
                    return;
                }
            }

            $resolver = new FileSourceResolver($this->repositories);
            $this->source = $resolver->resolve($this->path);
        }

        if (!$this->source) {
            throw new Exception\IOException('Unable to locate template file '.$this->path);
        }
    }

    /**
     * @return SourceInterface
     */
    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    /**
     * @return PHPTAL
     */
    public function allowPhpModifier(): PhpTalInterface
    {
        TalesInternal::setPhpModifierAllowed(true);
        return $this;
    }

    /**
     * @return PHPTAL
     */
    public function disallowPhpModifier(): PhpTalInterface
    {
        TalesInternal::setPhpModifierAllowed(false);
        return $this;
    }
}
