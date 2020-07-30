<?php

namespace PedroSancao\Wordimpress\Contracts;

interface WatchFilesInterface
{
    /**
     * Custom paths to watch for changes
     *
     * @return array
     */
    public function watchPaths() : array;
}
