<?php
declare(strict_types=1);

namespace PhpTal;

use Exception;
use stdClass;

/**
 * Interface: PhpTalInterface
 */
interface PhpTalInterface
{
    /**
     * Set template from file path.
     *
     * @param string $path filesystem path,
     *                     or any path that will be accepted by source resolver
     *
     * @return $this
     */
    public function setTemplate(string $path): self;

    /**
     * Set template from source.
     *
     * Should be used only with temporary template sources.
     * Use setTemplate() or addSourceResolver() whenever possible.
     *
     * @param string $src The phptal template source.
     * @param null|string $path Fake and 'unique' template path.
     *
     * @return $this
     */
    public function setSource(string $src, null|string $path = null): self;

    /**
     * Specify where to look for templates.
     *
     * @param mixed $rep string or Array of repositories
     *
     * @return $this
     */
    public function setTemplateRepository($rep): self;

    /**
     * Get template repositories.
     *
     * @return array<string>
     */
    public function getTemplateRepositories() :array;

    /**
     * Clears the template repositories.
     *
     * @return $this
     */
    public function clearTemplateRepositories(): self;

    /**
     * Specify how to look for templates.
     *
     * @param SourceResolverInterface $resolver instance of resolver
     *
     * @return $this
     */
    public function addSourceResolver(SourceResolverInterface $resolver): self;

    /**
     * Ignore XML/XHTML comments on parsing.
     * Comments starting with <!--! are always stripped.
     *
     * @param bool $bool if true all comments are stripped during parse
     *
     * @return $this
     */
    public function stripComments(bool $bool): self;

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
     */
    public function setOutputMode(int $mode): self;

    /**
     * Get output mode
     * @see setOutputMode()
     *
     * @return int output mode constant
     */
    public function getOutputMode(): int;

    /**
     * Set input and ouput encoding. Encoding is case-insensitive.
     *
     * @param string $enc example: 'UTF-8'
     *
     * @return $this
     */
    public function setEncoding(string $enc): self;

    /**
     * Get input and ouput encoding.
     */
    public function getEncoding(): string;

    /**
     * Set the storage location for intermediate PHP files.
     * The path cannot contain characters that would be interpreted by glob() (e.g. *[]?)
     *
     * @param string $path Intermediate file path.
     */
    public function setPhpCodeDestination(string $path): void;

    /**
     * Get the storage location for intermediate PHP files.
     */
    public function getPhpCodeDestination(): string;

    /**
     * Set the file extension for intermediate PHP files.
     *
     * @param string $extension The file extension.
     *
     * @return $this
     */
    public function setPhpCodeExtension(string $extension): self;

    /**
     * Get the file extension for intermediate PHP files.
     */
    public function getPhpCodeExtension(): string;

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
    public function setForceReparse(bool $bool): self;

    /**
     * Get the value of the force reparse state.
     */
    public function getForceReparse(): bool;

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
    public function setTranslator(TranslationServiceInterface $t): self;

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
    public function addPreFilter(PreFilter $filter): self;

    /**
     * Sets the level of recursion for template cache directories
     *
     *
     * @return $this
     */
    public function setSubpathRecursionLevel(int $recursion_level): self;

    /**
     * Set template post filter.
     * It will be called every time after template generates output.
     *
     * See PHPTAL_PostFilter class.
     *
     * @param FilterInterface $filter filter instance
     */
    public function setPostFilter(FilterInterface $filter): self;

    /**
     * Register a trigger for specified phptal:id.
     *
     * @param string $id phptal:id to look for
     *
     * @return $this
     */
    public function addTrigger(string $id, TriggerInterface $trigger): self;


    /**
     * Returns trigger for specified phptal:id.
     *
     * @param string $id phptal:id
     */
    public function getTrigger(string $id): ?TriggerInterface;

    /**
     * Set a context variable.
     *
     * @see \PhpTal\PHPTAL::__set()
     * @param string $varname name of the variable
     * @param mixed $value value of the variable
     *
     * @return $this
     */
    public function set(string $varname, $value): self;

    /**
     * Execute the template code and return generated markup.
     */
    public function execute(): string;

    /**
     * Execute and echo template without buffering of the output.
     * This function does not allow postfilters nor DOCTYPE/XML declaration.
     */
    public function echoExecute(): void;

    /**
     * This is PHPTAL's internal function that handles
     * execution of macros from templates.
     *
     * $this is caller's context (the file where execution had originally started)
     *
     * @param PhpTalInterface $local_tpl is PHPTAL instance of the file in which macro is defined
     *                          (it will be different from $this if it's external macro call)
     */
    public function executeMacroOfTemplate(string $path, PhpTalInterface $local_tpl): void;


    /**
     * Prepare template without executing it.
     */
    public function prepare(): self;

    /**
     * set how long compiled templates and phptal:cache files are kept
     *
     * @param float $days number of days
     */
    public function setCacheLifetime(float $days): self;

    /**
     * PHPTAL will scan cache and remove old files on every nth compile
     * Set to 0 to disable cleanups
     *
     *
     * @return $this
     */
    public function setCachePurgeFrequency(int $n): self;

    /**
     * Removes all compiled templates from cache that
     * are older than getCacheLifetime() days
     */
    public function cleanUpGarbage(): void;

    /**
     * Removes content cached with phptal:cache for currently set template
     * Must be called after setSource/setTemplate.
     */
    public function cleanUpCache(): void;

    /**
     * Returns the path of the intermediate PHP code file.
     *
     * The returned file may be used to cleanup (unlink) temporary files
     * generated by temporary templates or more simply for debug.
     */
    public function getCodePath(): string;

    /**
     * Returns the generated template function name.
     */
    public function getFunctionName(): string;

    /**
     * Returns template translator.
     */
    public function getTranslator(): ?TranslationServiceInterface;

    /**
     * Returns array of exceptions caught by tal:on-error attribute.
     *
     * @return Exception[]
     */
    public function getErrors(): array;

    /**
     * Public for phptal templates, private for user.
     *
     *
     */
    public function addError(Exception $error): void;

    /**
     * Returns current context object.
     * Use only in Triggers.
     */
    public function getContext(): Context;

    /**
     * only for use in generated template code
     */
    public function getGlobalContext(): stdClass;

    /**
     * only for use in generated template code
     */
    public function pushContext(): Context;

    /**
     * only for use in generated template code
     */
    public function popContext(): Context;

    public function getSource(): SourceInterface;

    /**
     * @return PHPTAL
     */
    public function allowPhpModifier(): self;

    /**
     * @return PHPTAL
     */
    public function disallowPhpModifier(): self;
}
