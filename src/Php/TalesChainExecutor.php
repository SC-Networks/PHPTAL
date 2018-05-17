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
    const CHAIN_CONT  = 2;


    private $_state = 0;
    private $_chain;
    private $_chainStarted = false;
    private $codewriter = null;


    public function __construct(\PhpTal\Php\CodeWriter $codewriter, array $chain, TalesChainReaderInterface $reader)
    {
        $this->_chain = $chain;
        $this->codewriter = $codewriter;
        $this->_reader = $reader;
        $this->_executeChain();
    }

    public function getCodeWriter()
    {
        return $this->codewriter;
    }

    public function doIf($condition)
    {
        if ($this->_chainStarted == false) {
            $this->_chainStarted = true;
            $this->codewriter->doIf($condition);
        } else {
            $this->codewriter->doElseIf($condition);
        }
    }

    public function doElse()
    {
        $this->codewriter->doElse();
    }

    public function breakChain()
    {
        $this->_state = self::CHAIN_BREAK;
    }

    public function continueChain()
    {
        $this->_state = self::CHAIN_CONT;
    }

    private function _executeChain()
    {
        $this->codewriter->noThrow(true);

        end($this->_chain); $lastkey = key($this->_chain);

        foreach ($this->_chain as $key => $exp) {
            $this->_state = 0;

            if ($exp == TalesInternal::NOTHING_KEYWORD) {
                $this->_reader->talesChainNothingKeyword($this);
            } elseif ($exp == TalesInternal::DEFAULT_KEYWORD) {
                $this->_reader->talesChainDefaultKeyword($this);
            } else {
                $this->_reader->talesChainPart($this, $exp, $lastkey === $key);
            }

            if ($this->_state == self::CHAIN_BREAK)
                break;
            if ($this->_state == self::CHAIN_CONT)
                continue;
        }

        $this->codewriter->doEnd('if');
        $this->codewriter->noThrow(false);
    }
}
