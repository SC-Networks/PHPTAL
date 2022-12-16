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

class StupidCacheTrigger implements TriggerInterface
{
    /**
     * @var bool
     */
    public $isCaching = false;

    /**
     * @var string
     */
    public $cachePath = '';

    /**
     * @param $id
     *
     */
    public function start($id, PhpTalInterface $tpl): int
    {
        $this->cachePath = 'trigger.' . $tpl->getContext()->someId;

        // if already cached, read the cache and tell PHPTAL to
        // ignore the tag content
        if (file_exists($this->cachePath)) {
            $this->isCaching = false;
            readfile($this->cachePath);
            return self::SKIPTAG;
        }

        // no cache, we start and output buffer and tell
        // PHPTAL to proceed (ie: execute the tag content)
        $this->isCaching = true;
        ob_start();
        return self::PROCEED;
    }

    /**
     * @param $id
     *
     */
    public function end($id, PhpTalInterface $tpl): void
    {
        // end of tag, if cached file used, do nothing
        if (!$this->isCaching) {
            return;
        }

        // otherwise, get the content of the output buffer
        // and write it into the cache file for later usage
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;

        $f = fopen($this->cachePath, 'w');
        fwrite($f, $content);
        fclose($f);
    }
}
