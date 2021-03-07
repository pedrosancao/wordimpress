<?php

namespace PedroSancao\Wordimpress\BlogData;

trait HasPagesTrait
{
    /**
     * Get filters for request on Wordpress API.
     *
     * @param array $data
     *
     * @return string
     */
    public function getPagesFilters(array $data): array
    {
        return [
            'per_page' => 100,
        ];
    }

    /**
     * Get property to use as key.
     *
     * @return string
     */
    public function getPageKey(): ?string
    {
        return 'slug';
    }

    /**
     * Get single property to return.
     *
     * @return string
     */
    public function getPageValue(): ?string
    {
        return 'content';
    }

    /**
     * Callback to run on each record.
     *
     * @param $content
     * @param $index
     *
     * @return string
     */
    public function dataPage($content, $index)
    {
        return $content->rendered;
    }
}
