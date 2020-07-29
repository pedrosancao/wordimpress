<?php

namespace PedroSancao\Wordimpress\BlogData;

trait HasAuthorsTrait
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getAuthorsFilters(array $data) : array
    {
        if (key_exists('posts', $data)) {
            $ids = array_unique(array_column($data['posts'], 'author'));
            return [
                'include'  => $ids,
                'per_page' => count($ids),
            ];
        }
        return [
            'per_page' => 100,
        ];
    }

    /**
     * Get property to use as key
     *
     * @return string
     */
    public function getAuthorKey() : ?string
    {
        return 'id';
    }

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getAuthorValue() : ?string
    {
        return 'name';
    }

    /**
     * Callback to run on each record
     *
     * @param $author
     * @param $index
     * @return string
     */
    public function dataAuthor($author, $index)
    {
        return $author;
    }
}
