<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
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
     * @param $id
     * @param PhpTalInterface $tpl
     *
     * @return mixed
     */
    public function start($id, PhpTalInterface $tpl);

    /**
     * @param $id
     * @param PhpTalInterface $tpl
     *
     * @return mixed
     */
    public function end($id, PhpTalInterface $tpl);
}
