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

namespace PhpTal\Php;

/**
 * @package PHPTAL
 */
interface TalesChainReaderInterface
{
    public function talesChainNothingKeyword(TalesChainExecutor $executor): void;

    public function talesChainDefaultKeyword(TalesChainExecutor $executor): void;

    public function talesChainPart(TalesChainExecutor $executor, string $expression, bool $islast): void;
}
