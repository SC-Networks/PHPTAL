<?php

namespace Tests\Testhelper;

use PhpTal\SourceInterface;

/**
 * Class MyCustomSource
 * @package Testhelper
 */
class MyCustomSource implements SourceInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * MyCustomSource constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getRealPath(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getLastModifiedTime(): int
    {
        return $this->path === 'nocache' ? mt_rand() : 0;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return '<p class="custom">'.$this->path.' '.mt_rand().'</p>';
    }
}
