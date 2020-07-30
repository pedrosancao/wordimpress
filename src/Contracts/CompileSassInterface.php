<?php

namespace PedroSancao\Wordimpress\Contracts;

interface CompileSassInterface
{
    /**
     * Get Sass source file
     *
     * @return string
     */
    public function getSassSourceFile() : string;

    /**
     * Get additional Sass source directories
     *
     * @return string
     */
    public function getSassSourceDirectories() : array;
}
