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

use DOMElement;
use PhpTal\Dom\Element;

/**
 * Base class for prefilters.
 *
 * You should extend this class and override methods you're interested in.
 *
 * Order of calls is undefined and may change.
 *
 * @package PHPTAL
 */
abstract class PreFilter implements FilterInterface
{
    /**
     * @see getPHPTAL()
     */
    private ?PhpTalInterface $phptal = null;

    /**
     * Receives DOMElement (of PHP5 DOM API) of parsed file (documentElement), or element
     * that has phptal:filter attribute. Should edit DOM in place.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation does nothing. Override it.
     *
     * @param DOMElement $node PHP5 DOM node to modify in place
     */
    public function filterElement(DOMElement $node): void
    {
    }

    /**
     * Receives root PHPTAL DOM node of parsed file and should edit it in place.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation does nothing. Override it.
     *
     * @see \PhpTal\Dom\Element class for methods and fields available.
     *
     * @param Dom\Element $root PHPTAL DOM node to modify in place
     */
    public function filterDOM(Element $root): void
    {
    }

    /**
     * Receives DOM node that had phptal:filter attribute calling this filter.
     * Should modify node in place.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation calls filterDOM(). Override it.
     *
     * @param Dom\Element $node PHPTAL DOM node to modify in place
     */
    public function filterDOMFragment(Element $node): void
    {
        $this->filterDOM($node);
    }

    /**
     * Receives template source code and is expected to return new source.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation does nothing. Override it.
     *
     * @param string $str markup to filter
     */
    public function filter(string $str): string
    {
        return $str;
    }

    /**
     * Returns (any) string that uniquely identifies this filter and its settings,
     * which is used to (in)validate template cache.
     *
     * Unlike other filter methods, this one is called on every execution.
     *
     * Override this method if result of the filter depends on its configuration.
     */
    public function getCacheId(): string
    {
        return static::class;
    }

    /**
     * Returns PHPTAL class instance that is currently using this prefilter.
     * May return NULL if PHPTAL didn't start filtering yet.
     */
    final protected function getPHPTAL(): ?PhpTalInterface
    {
        return $this->phptal;
    }

    /**
     * Set which instance of PHPTAL is using this filter.
     * Must be done before calling any filter* methods.
     *
     * @param PhpTalInterface $phptal instance
     */
    final public function setPHPTAL(PhpTalInterface $phptal): void
    {
        $this->phptal = $phptal;
    }
}
