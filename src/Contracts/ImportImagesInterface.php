<?php

namespace PedroSancao\Wordimpress\Contracts;

interface ImportImagesInterface
{
    /**
     * Tells if images should be converted
     */
    public function mustConvertImages() : bool;
}
