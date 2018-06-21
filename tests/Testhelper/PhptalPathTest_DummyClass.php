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

class PhptalPathTest_DummyClass
{
    /**
     * @var null
     */
    public $foo;

    /**
     * @var string
     */
    public $pubTest = 'pub-property';

    /**
     * @var string
     */
    public $protTest = 'prot-property';

    /**
     * @return string
     */
    protected function protTest(): string
    {
        return 'prot-method';
    }

    /**
     * @return string
     */
    public function pubTest(): string
    {
        return 'pub-method';
    }
}
