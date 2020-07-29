<?php

namespace PedroSancao\Wpsg\Contracts;

interface WithAuthorsInterface
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getAuthorsFilters(array $data) : array;

    /**
     * Get property to use as key
     *
     * @return string
     */
    public function getAuthorKey() : ?string;

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getAuthorValue() : ?string;

    /**
     * Callback to run on each record
     *
     * @param $author
     * @param $index
     * @return string
     */
    public function dataAuthor($author, $index);
}
