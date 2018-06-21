<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * Originally developed by Laurent Bedubourg and Kornel Lesiński
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   See contributors list @ github
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 * @link     https://github.com/SC-Networks/PHPTAL
 */

namespace Tests\Testhelper;

use PhpTal\PhpTalInterface;
use PhpTal\TriggerInterface;

class MyTrigger implements TriggerInterface
{
    /**
     * @var bool
     */
    public $useCache = false;

    /**
     * @var string
     */
    private $cache;

    /**
     * @param $id
     * @param PhpTalInterface $tpl
     *
     * @return int
     */
    public function start($id, PhpTalInterface $tpl): int
    {
        if ($this->cache !== null) {
            $this->useCache = true;
            return TriggerInterface::SKIPTAG;
        }

        $this->useCache = false;
        ob_start();
        return TriggerInterface::PROCEED;
    }

    /**
     * @param $id
     * @param PhpTalInterface $tpl
     *
     * @return void
     */
    public function end($id, PhpTalInterface $tpl): void
    {
        if ($this->cache === null) {
            $this->cache = ob_get_contents();
            ob_end_clean();
        }
        echo $this->cache;
    }
}
