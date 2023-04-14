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

use PhpTal\Exception\IOException;

/**
 * Reads template from the filesystem
 *
 * @package PHPTAL
 */
class FileSource implements SourceInterface
{
    private string $path;

    /**
     * @throws Exception\IOException
     */
    public function __construct(string $path)
    {
        $realPath = realpath($path);
        if ($realPath === false) {
            throw new IOException(
                sprintf('Unable to find real path of file \'%s\' (in %s)', $path, getcwd())
            );
        }
        if (is_dir($realPath)) {
            throw new IOException(
                sprintf('Path \'%s\' points to a directory', $realPath)
            );
        }

        $this->path = $realPath;
    }

    public function getRealPath(): string
    {
        return $this->path;
    }

    public function getLastModifiedTime(): int
    {
        return (int) filemtime($this->path);
    }

    /**
     * @throws Exception\IOException
     */
    public function getData(): string
    {
        $content = file_get_contents($this->path);

        // file_get_contents returns "" when loading directory!?
        if ($content === false || ($content === '' && is_dir($this->path))) {
            throw new IOException('Unable to load file ' . $this->path);
        }
        return $content;
    }
}
