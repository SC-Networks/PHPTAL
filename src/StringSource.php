<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * PHP Version 5
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
    public const NO_PATH_PREFIX = '<string ';

    /**
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $realpath;


    /**
     * StringSource constructor.
     *
     * @param string $data
     * @param string $realpath
     */
    public function __construct($data, ?string $realpath = null)
    {
        $this->data = $data;
        $this->realpath = $realpath ?: self::NO_PATH_PREFIX . md5($data) . '>';
    }

    /**
     * @return int
     */
    public function getLastModifiedTime(): int
    {
        if (strpos($this->realpath, self::NO_PATH_PREFIX) !== 0 && file_exists($this->realpath)) {
            return @filemtime($this->realpath);
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * well, this is not always a real path. If it starts with self::NO_PATH_PREFIX, then it's fake.
     *
     * @return string
     */
    public function getRealPath(): string
    {
        if ($this->realpath === null) {
            return '<string>';
        }
        return $this->realpath;
    }
}
