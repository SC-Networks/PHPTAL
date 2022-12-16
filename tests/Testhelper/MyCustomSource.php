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
    public function __construct(private readonly string $path)
    {
    }

    public function getRealPath(): string
    {
        return '';
    }

    public function getLastModifiedTime(): int
    {
        return $this->path === 'nocache' ? random_int(0, mt_getrandmax()) : 0;
    }

    public function getData(): string
    {
        return '<p class="custom">'.$this->path.' '.random_int(0, mt_getrandmax()).'</p>';
    }
}
