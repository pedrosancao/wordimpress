<?php

namespace PedroSancao\Wpsg\BlogData;

trait HasPostsTrait
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getPostsFilters(array $data) : array
    {
        return [
            'per_page' => 100,
        ];
    }

    /**
     * Get property to use as key
     *
     * @return string
     */
    public function getPostKey() : ?string
    {
        return null;
    }

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getPostValue() : ?string
    {
        return null;
    }

    /**
     * Callback to run on each record
     *
     * @param $post
     * @param $index
     * @return string
     */
    public function dataPost($post, $index)
    {
        return (object) [
            'id'         => $post->id,
            'date'       => $post->date,
            'slug'       => $post->slug,
            'title'      => trim($post->title->rendered),
            'author'     => $post->author,
            'excerpt'    => trim($post->excerpt->rendered),
            'content'    => trim($post->content->rendered),
            'categories' => $post->categories,
            'post_media' => $post->featured_media,
        ];
    }
}
