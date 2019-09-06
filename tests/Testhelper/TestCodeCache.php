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

use PhpTal\PHPTAL;

class TestCodeCache extends PHPTAL
{
    /**
     * @var bool
     */
    public $testHasParsed = false;

    /**
     * Parse currently set template, prefilter and generate PHP code.
     *
     * @return string (compiled PHP code)
     */
    public function parse(): string
    {
        $this->testHasParsed = true;
        return parent::parse();
    }
}
