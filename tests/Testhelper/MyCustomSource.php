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

use PhpTal\SourceInterface;

class MyCustomSource implements SourceInterface
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getRealPath(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getLastModifiedTime(): int
    {
        return $this->path === 'nocache' ? mt_rand() : 0;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return '<p class="custom">'.$this->path.' '.mt_rand().'</p>';
    }
}
