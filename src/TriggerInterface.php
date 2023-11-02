<?php
/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal;

/**
 * Interface for Triggers (phptal:id)
 *
 * @package PHPTAL
 */
interface TriggerInterface
{
    public const SKIPTAG = 1;
    public const PROCEED = 2;

    /**
     * @return mixed
     */
    public function start(mixed $id, PhpTalInterface $tpl);

    /**
     * @return mixed
     */
    public function end(mixed $id, PhpTalInterface $tpl);
}
