<?php

namespace PedroSancao\Wpsg\Contracts;

interface WithMediaInterface
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getMediaFilters(array $data) : array;

    /**
     * Get property to use as key
     *
     * @return string
     */
    public function getMediaKey() : ?string;

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getMediaValue() : ?string;

    /**
     * Callback to run on each record
     *
     * @param $media
     * @param $index
     * @return string
     */
    public function dataMedia($media, $index);
}
