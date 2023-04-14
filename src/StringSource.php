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
 * Fake template source that makes PHPTAL->setString() work
 *
 * @package PHPTAL
 */
class StringSource implements SourceInterface
{
    final public const NO_PATH_PREFIX = '<string ';

    private string $realpath;

    public function __construct(
        private string $data,
        ?string $realpath = null
    ) {
        $this->realpath = $realpath ?? self::NO_PATH_PREFIX . md5($data) . '>';
    }

    public function getLastModifiedTime(): int
    {
        $mTime = 0;

        if (!str_starts_with($this->realpath, self::NO_PATH_PREFIX) && file_exists($this->realpath)) {
            $mTime = (int) @filemtime($this->realpath);
        }

        return $mTime;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     * well, this is not always a real path. If it starts with self::NO_PATH_PREFIX, then it's fake.
     */
    public function getRealPath(): string
    {
        return $this->realpath ?? '<string>';
    }
}
