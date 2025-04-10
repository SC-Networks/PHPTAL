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
 * You can implement this interface to load templates from various sources (see SourceResolver)
 *
 * @package PHPTAL
 */
interface SourceInterface
{
    /**
     * unique path identifying the template source.
     * must not be empty. must be as unique as possible.
     *
     * it doesn't have to be path on disk.
     */
    public function getRealPath(): string;

    /**
     * template source last modified time (unix timestamp)
     * Return 0 if unknown.
     *
     * If you return 0:
     *  • PHPTAL won't know when to reparse the template,
     *    unless you change realPath whenever template changes.
     *  • clearing of cache will be marginally slower.
     */
    public function getLastModifiedTime(): int;

    /**
     * the template source
     */
    public function getData(): string;
}
