<?php

namespace PedroSancao\Wordimpress\Contracts;

interface WithPostsInterface
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getPostsFilters(array $data) : array;

    /**
     * Get property to use as key
     *
     * @return string
     */
    public function getPostKey() : ?string;

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getPostValue() : ?string;

    /**
     * Callback to run on each record
     *
     * @param $post
     * @param $index
     * @return string
     */
    public function dataPost($post, $index);
}
