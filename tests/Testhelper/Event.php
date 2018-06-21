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

class Event
{

    /**
     * @var \Countable
     */
    private $artists;

    /**
     * @param \Countable $artists
     */
    public function setArtists(\Countable $artists): void
    {
        $this->artists = $artists;
    }

    public function getArtists(): \Countable
    {
        return $this->artists;
    }
}
