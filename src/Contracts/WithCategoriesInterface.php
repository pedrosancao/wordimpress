<?php

namespace PedroSancao\Wordimpress\Contracts;

interface WithCategoriesInterface
{
    /**
     * Get filters for request on Wordpress API.
     *
     * @param array $data
     *
     * @return string
     */
    public function getCategoriesFilters(array $data): array;

    /**
     * Get property to use as key.
     *
     * @return string
     */
    public function getCategoryKey(): ?string;

    /**
     * Get single property to return.
     *
     * @return string
     */
    public function getCategoryValue(): ?string;

    /**
     * Callback to run on each record.
     *
     * @param $category
     * @param $index
     *
     * @return string
     */
    public function dataCategory($category, $index);
}
