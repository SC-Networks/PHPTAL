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
 * @package PHPTAL
 */
interface SourceResolverInterface
{
    /**
     * Returns Source or null.
     *
     * @param string $path
     *
     * @return SourceInterface|null
     */
    public function resolve(string $path): ?SourceInterface;
}
