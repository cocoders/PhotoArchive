<?php

namespace Cocoders\Archive;

class ArchiveFile
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }
}
