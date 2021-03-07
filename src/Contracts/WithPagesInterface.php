<?php

namespace PedroSancao\Wordimpress\Contracts;

interface WithPagesInterface
{
    /**
     * Get filters for request on Wordpress API.
     *
     * @param array $data
     *
     * @return string
     */
    public function getPagesFilters(array $data): array;

    /**
     * Get property to use as key.
     *
     * @return string
     */
    public function getPageKey(): ?string;

    /**
     * Get single property to return.
     *
     * @return string
     */
    public function getPageValue(): ?string;

    /**
     * Callback to run on each record.
     *
     * @param $page
     * @param $index
     *
     * @return string
     */
    public function dataPage($page, $index);
}
