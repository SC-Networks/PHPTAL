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
 * Reads template from the filesystem
 *
 * @package PHPTAL
 */
class FileSource implements SourceInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * FileSource constructor.
     * @param string $path
     * @throws Exception\IOException
     */
    public function __construct(string $path)
    {
        $this->path = realpath($path);
        if ($this->path === false) {
            throw new Exception\IOException(
                sprintf('Unable to find real path of file \'%s\' (in %s)', $path, getcwd())
            );
        }
        if (is_dir($this->path)) {
            throw new Exception\IOException(
                sprintf('Path \'%s\' points to a directory', $this->path)
            );
        }
    }

    /**
     * @return string
     */
    public function getRealPath(): string
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getLastModifiedTime(): int
    {
        return filemtime($this->path);
    }

    /**
     * @return string
     * @throws Exception\IOException
     */
    public function getData(): string
    {
        $content = file_get_contents($this->path);

        // file_get_contents returns "" when loading directory!?
        if ($content === false || ($content === '' && is_dir($this->path))) {
            throw new Exception\IOException('Unable to load file ' . $this->path);
        }
        return $content;
    }
}
