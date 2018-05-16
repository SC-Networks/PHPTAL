<?php

/**
 * Interface: PhpTalInterface
 */
interface PhpTalInterface {

    /**
     * Set template from file path.
     *
     * @param string $path filesystem path,
     *                     or any path that will be accepted by source resolver
     *
     * @return $this
     */
    public function setTemplate($path);

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
    public function setSource($src, $path = null);

    /**
     * Specify where to look for templates.
     *
     * @param mixed $rep string or Array of repositories
     *
     * @return $this
     */
    public function setTemplateRepository($rep);

    /**
     * Get template repositories.
     *
     * @return array
     */
    public function getTemplateRepositories();

    /**
     * Clears the template repositories.
     *
     * @return $this
     */
    public function clearTemplateRepositories();

    /**
     * Specify how to look for templates.
     *
     * @param PHPTAL_SourceResolver $resolver instance of resolver
     *
     * @return $this
     */
    public function addSourceResolver(PHPTAL_SourceResolver $resolver);

    /**
     * Ignore XML/XHTML comments on parsing.
     * Comments starting with <!--! are always stripped.
     *
     * @param bool $bool if true all comments are stripped during parse
     *
     * @return $this
     */
    public function stripComments($bool);

    /**
     * Set output mode
     * XHTML output mode will force elements like <link/>, <meta/> and <img/>, etc.
     * to be empty and threats attributes like selected, checked to be
     * boolean attributes.
     *
     * XML output mode outputs XML without such modifications
     * and is neccessary to generate RSS feeds properly.
     *
     * @param int $mode (PHPTAL::XML, PHPTAL::XHTML or PHPTAL::HTML5).
     *
     * @return $this
     */
    public function setOutputMode($mode);

    /**
     * Get output mode
     * @see setOutputMode()
     *
     * @return int output mode constant
     */
    public function getOutputMode();

    /**
     * Set input and ouput encoding. Encoding is case-insensitive.
     *
     * @param string $enc example: 'UTF-8'
     *
     * @return $this
     */
    public function setEncoding($enc);

    /**
     * Get input and ouput encoding.
     *
     * @param string $enc example: 'UTF-8'
     *
     * @return string
     */
    public function getEncoding();

    /**
     * Set the storage location for intermediate PHP files.
     * The path cannot contain characters that would be interpreted by glob() (e.g. *[]?)
     *
     * @param string $path Intermediate file path.
     *
     * @return $this
     */
    public function setPhpCodeDestination($path);

    /**
     * Get the storage location for intermediate PHP files.
     *
     * @return string
     */
    public function getPhpCodeDestination();

    /**
     * Set the file extension for intermediate PHP files.
     *
     * @param string $extension The file extension.
     *
     * @return $this
     */
    public function setPhpCodeExtension($extension);

    /**
     * Get the file extension for intermediate PHP files.
     *
     * @return string
     */
    public function getPhpCodeExtension();

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
    public function setForceReparse($bool);

    /**
     * Get the value of the force reparse state.
     *
     * @return bool
     */
    public function getForceReparse();

    /**
     * Set I18N translator.
     *
     * This sets encoding used by the translator, so be sure to use encoding-dependent
     * features of the translator (e.g. addDomain) _after_ calling setTranslator.
     *
     * @param PHPTAL_TranslationService $t instance
     *
     * @return $this
     */
    public function setTranslator(PHPTAL_TranslationService $t);

    /**
     * Add new prefilter to filter chain.
     * Prefilters are called only once template is compiled.
     *
     * PreFilters must inherit PHPTAL_PreFilter class.
     * (in future this method will allow string with filter name instead of object)
     *
     * @param mixed $filter PHPTAL_PreFilter object or name of prefilter to add
     *
     * @return $this
     */
    public function addPreFilter($filter);

    /**
     * Sets the level of recursion for template cache directories
     *
     * @param int $recursion_level
     *
     * @return $this
     */
    public function setSubpathRecursionLevel($recursion_level);

    /**
     * Set template post filter.
     * It will be called every time after template generates output.
     *
     * See PHPTAL_PostFilter class.
     *
     * @param PHPTAL_Filter $filter filter instance
     */
    public function setPostFilter(PHPTAL_Filter $filter);

    /**
     * Register a trigger for specified phptal:id.
     *
     * @param string $id phptal:id to look for
     *
     * @return $this
     */
    public function addTrigger($id, PHPTAL_Trigger $trigger);

    /**
     * Returns trigger for specified phptal:id.
     *
     * @param string $id phptal:id
     *
     * @return PHPTAL_Trigger|null
     */
    public function getTrigger($id);

    /**
     * Set a context variable.
     * Use it by setting properties on PHPTAL object.
     *
     * @param string $varname
     * @param mixed $value
     *
     * @return void
     */
    public function __set($varname, $value);

    /**
     * Set a context variable.
     *
     * @see PHPTAL::__set()
     * @param string $varname name of the variable
     * @param mixed $value value of the variable
     *
     * @return $this
     */
    public function set($varname, $value);

    /**
     * Execute the template code and return generated markup.
     *
     * @return string
     */
    public function execute();

    /**
     * Execute and echo template without buffering of the output.
     * This function does not allow postfilters nor DOCTYPE/XML declaration.
     *
     * @return void
     */
    public function echoExecute();

    /**
     * This is PHPTAL's internal function that handles
     * execution of macros from templates.
     *
     * $this is caller's context (the file where execution had originally started)
     *
     * @param PhpTalInterface $local_tpl is PHPTAL instance of the file in which macro is defined
     *                          (it will be different from $this if it's external macro call)
     */
    public function _executeMacroOfTemplate($path, PhpTalInterface $local_tpl);


    /**
     * Prepare template without executing it.
     *
     * @return void
     */
    public function prepare();

    /**
     * get how long compiled templates and phptal:cache files are kept, in days
     *
     * @return float
     */
    public function getCacheLifetime();

    /**
     * set how long compiled templates and phptal:cache files are kept
     *
     * @param float $days number of days
     */
    public function setCacheLifetime($days);

    /**
     * PHPTAL will scan cache and remove old files on every nth compile
     * Set to 0 to disable cleanups
     *
     * @param int $n
     *
     * @return $this
     */
    public function setCachePurgeFrequency($n);

    /**
     * how likely cache cleaning can happen
     * @see self::setCachePurgeFrequency()
     *
     * @return int
     */
    public function getCachePurgeFrequency();

    /**
     * Removes all compiled templates from cache that
     * are older than getCacheLifetime() days
     *
     * @return void
     */
    public function cleanUpGarbage();

    /**
     * Removes content cached with phptal:cache for currently set template
     * Must be called after setSource/setTemplate.
     *
     * @return void
     */
    public function cleanUpCache();

    /**
     * Returns the path of the intermediate PHP code file.
     *
     * The returned file may be used to cleanup (unlink) temporary files
     * generated by temporary templates or more simply for debug.
     *
     * @return string
     */
    public function getCodePath();

    /**
     * Returns the generated template function name.
     *
     * @return string
     */
    public function getFunctionName();

    /**
     * Returns template translator.
     *
     * @return PHPTAL_TranslationService
     */
    public function getTranslator();

    /**
     * Returns array of exceptions caught by tal:on-error attribute.
     *
     * @return \Exception[]]
     */
    public function getErrors();

    /**
     * Public for phptal templates, private for user.
     *
     * @param \Exception $error
     *
     * @return void
     */
    public function addError(\Exception $error);

    /**
     * Returns current context object.
     * Use only in Triggers.
     *
     * @return PHPTAL_Context
     */
    public function getContext();

    /**
     * only for use in generated template code
     *
     * @return \stdClass
     */
    public function getGlobalContext();

    /**
     * only for use in generated template code
     *
     * @return PHPTAL_Context
     */
    public function pushContext();

    /**
     * only for use in generated template code
     *
     * @return PHPTAL_Context
     */
    public function popContext();
}