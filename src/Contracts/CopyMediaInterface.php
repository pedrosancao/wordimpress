<?php

namespace PedroSancao\Wordimpress\Contracts;

interface CopyMediaInterface
{
    /**
     * Get media source directory.
     *
     * @return string
     */
    public function getMediaDirectory(): string;
}
