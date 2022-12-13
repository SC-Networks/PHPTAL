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

namespace PhpTal\Php;

use PhpTal\Exception\PhpTalException;

/**
 * @package PHPTAL
 */
class TalesChainExecutor
{
    public const CHAIN_BREAK = 1;
    public const CHAIN_CONT = 2;

    /**
     * @var int
     */
    private $state = 0;

    /**
     * @var bool
     */
    private $chainStarted = false;

    /**
     * @param CodeWriter $codewriter
     * @param array<int, string> $chain
     * @param TalesChainReaderInterface $reader
     *
     * @throws PhpTalException
     */
    public function __construct(
        private readonly CodeWriter $codewriter,
        private array $chain,
        private readonly TalesChainReaderInterface $reader,
    ) {
        $this->chain = $chain;
        $this->executeChain();
    }

    /**
     * @return CodeWriter
     */
    public function getCodeWriter(): CodeWriter
    {
        return $this->codewriter;
    }

    /**
     * @param string $condition
     * @return void
     * @throws PhpTalException
     */
    public function doIf(string $condition): void
    {
        if ($this->chainStarted === false) {
            $this->chainStarted = true;
            $this->codewriter->doIf($condition);
        } else {
            $this->codewriter->doElseIf($condition);
        }
    }

    /**
     * @return void
     * @throws PhpTalException
     */
    public function doElse(): void
    {
        $this->codewriter->doElse();
    }

    /**
     * @return void
     */
    public function breakChain(): void
    {
        $this->state = self::CHAIN_BREAK;
    }

    /**
     * @return void
     */
    public function continueChain(): void
    {
        $this->state = self::CHAIN_CONT;
    }

    /**
     * @return void
     * @throws PhpTalException
     */
    private function executeChain(): void
    {
        $this->codewriter->noThrow(true);

        end($this->chain);
        $lastkey = key($this->chain);

        foreach ($this->chain as $key => $exp) {
            $this->state = 0;

            if ($exp === TalesInternal::NOTHING_KEYWORD) {
                $this->reader->talesChainNothingKeyword($this);
            } elseif ($exp === TalesInternal::DEFAULT_KEYWORD) {
                $this->reader->talesChainDefaultKeyword($this);
            } else {
                $this->reader->talesChainPart($this, $exp, $lastkey === $key);
            }

            if ($this->state === self::CHAIN_BREAK) {
                break;
            }
            if ($this->state === self::CHAIN_CONT) {
                continue; // basically a noop here
            }
        }

        $this->codewriter->doEnd('if');
        $this->codewriter->noThrow(false);
    }
}
