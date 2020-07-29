<?php

namespace PedroSancao\Wpsg\Contracts;

interface ImportImagesInterface
{
    /**
     * Tells if images should be converted
     */
    public function mustConvertImages() : bool;
}
