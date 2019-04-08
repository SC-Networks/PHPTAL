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
 * Finds template on disk by looking through repositories first
 *
 * @package PHPTAL
 */
class FileSourceResolver implements SourceResolverInterface
{

    /**
     * @var string[]
     */
    private $repositories;

    /**
     * FileSourceResolver constructor.
     * @param array $repositories
     */
    public function __construct(array $repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * @param string $path
     * @return FileSource
     * @throws Exception\IOException
     */
    public function resolve(string $path): ?SourceInterface
    {
        foreach ($this->repositories as $repository) {
            $file = $repository . DIRECTORY_SEPARATOR . $path;
            if (file_exists($file)) {
                return new FileSource($file);
            }
        }

        if (file_exists($path)) {
            return new FileSource($path);
        }

        return null;
    }
}
