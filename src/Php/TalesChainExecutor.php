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
    final public const CHAIN_BREAK = 1;
    final public const CHAIN_CONT = 2;

    private int $state = 0;

    private bool $chainStarted = false;

    /**
     * @param array<int, string> $chain
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

    public function getCodeWriter(): CodeWriter
    {
        return $this->codewriter;
    }

    /**
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
     * @throws PhpTalException
     */
    public function doElse(): void
    {
        $this->codewriter->doElse();
    }

    public function breakChain(): void
    {
        $this->state = self::CHAIN_BREAK;
    }

    public function continueChain(): void
    {
        $this->state = self::CHAIN_CONT;
    }

    /**
     * @throws PhpTalException
     */
    private function executeChain(): void
    {
        $this->codewriter->noThrow(true);
        $lastkey = array_key_last($this->chain);

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
