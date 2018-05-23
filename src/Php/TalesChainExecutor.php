<?php
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

namespace PhpTal\Php;

/**
 * @package PHPTAL
 */
class TalesChainExecutor
{
    const CHAIN_BREAK = 1;
    const CHAIN_CONT = 2;

    /**
     * @var int
     */
    private $state = 0;

    /**
     * @var array
     */
    private $chain;

    /**
     * @var bool
     */
    private $chainStarted = false;

    /**
     * @var CodeWriter
     */
    private $codewriter;

    /**
     * @var TalesChainReaderInterface
     */
    private $reader;


    /**
     * TalesChainExecutor constructor.
     *
     * @param CodeWriter $codewriter
     * @param array $chain
     * @param TalesChainReaderInterface $reader
     *
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function __construct(CodeWriter $codewriter, array $chain, TalesChainReaderInterface $reader)
    {
        $this->chain = $chain;
        $this->codewriter = $codewriter;
        $this->reader = $reader;
        $this->executeChain();
    }

    /**
     * @return CodeWriter
     */
    public function getCodeWriter()
    {
        return $this->codewriter;
    }

    /**
     * @param $condition
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function doIf($condition)
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
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function doElse()
    {
        $this->codewriter->doElse();
    }

    /**
     * @return void
     */
    public function breakChain()
    {
        $this->state = self::CHAIN_BREAK;
    }

    /**
     * @return void
     */
    public function continueChain()
    {
        $this->state = self::CHAIN_CONT;
    }

    /**
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    private function executeChain()
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
                continue;
            }
        }

        $this->codewriter->doEnd('if');
        $this->codewriter->noThrow(false);
    }
}
