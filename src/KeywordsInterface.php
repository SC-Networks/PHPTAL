<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal;

use Countable;

/**
 * Interface for template keywords
 *
 * @package PHPTAL
 */
interface KeywordsInterface extends Countable
{
    public function __toString(): string;
}
