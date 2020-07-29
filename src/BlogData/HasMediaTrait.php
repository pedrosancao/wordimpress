<?php

namespace PedroSancao\Wpsg\BlogData;

trait HasMediaTrait
{
    /**
     * Get filters for request on Wordpress API
     *
     * @param array $data
     * @return string
     */
    public function getMediaFilters(array $data) : array
    {
        if (key_exists('posts', $data)) {
            $ids = array_unique(array_filter(array_column($data['posts'], 'post_media')));
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
    public function getMediaKey() : ?string
    {
        return 'id';
    }

    /**
     * Get single property to return
     *
     * @return string
     */
    public function getMediaValue() : ?string
    {
        return null;
    }

    /**
     * Callback to run on each record
     *
     * @param $media
     * @param $index
     * @return string
     */
    public function dataMedia($media, $index)
    {
        $imageSizes = get_object_vars($media->media_details->sizes);
        usort($imageSizes, function ($image1, $image2) {
            return $image1->width - $image2->width;
        });

        $minWidthThumbnail = $this->getMinWidthThumbnail();
        $minWidthMedium = $this->getMinWidthMedium();
        $minWidthLarge = $this->getMinWidthLarge();
        $thumbnail = $medium = $large = null;
        foreach ($imageSizes as $imageSize) {
            if (is_null($thumbnail) && $imageSize->width >= $minWidthThumbnail) {
                $thumbnail = $medium = $large = $imageSize->source_url;
            }
            if ($medium === $thumbnail && $imageSize->width >= $minWidthMedium) {
                $medium = $large = $imageSize->source_url;
            }
            if ($large === $medium && $imageSize->width >= $minWidthLarge) {
                $large = $imageSize->source_url;
                break;
            }
        }
        return (object) [
            'sizes' => (object) [
                'thumb'  => $thumbnail,
                'medium' => $medium,
                'large'  => $large,
            ],
            'alt' => $media->alt_text ?: strtr($media->title->rendered, '-_', '  '),
        ];
    }

    protected function getMinWidthThumbnail() : int
    {
        return 333;
    }

    protected function getMinWidthMedium() : int
    {
        return 635;
    }

    protected function getMinWidthLarge() : int
    {
        return 825;
    }
}
