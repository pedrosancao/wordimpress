<?php

namespace PedroSancao\Wpsg\BlogData;

trait HasCategoriesTrait
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getCategoriesFilters(array $data) : array
    {
        if (key_exists('posts', $data)) {
            $ids = array_unique(array_merge(...array_column($data['posts'], 'categories')));
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
    public function getCategoryKey() : ?string
    {
        return 'id';
    }

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getCategoryValue() : ?string
    {
        return null;
    }

    /**
     * Callback to run on each record
     *
     * @param $category
     * @param $index
     * @return string
     */
    public function dataCategory($category, $index)
    {
        return (object) [
            'name'  => $category->name,
            'slug'  => $category->slug,
        ];
    }
}
