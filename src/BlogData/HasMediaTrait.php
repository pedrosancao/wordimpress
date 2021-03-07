<?php

namespace PedroSancao\Wordimpress\BlogData;

trait HasMediaTrait
{
    /**
     * Get filters for request on Wordpress API.
     *
     * @param array $data
     *
     * @return string
     */
    public function getMediaFilters(array $data): array
    {
        if (key_exists('posts', $data)) {
            $ids = array_unique(array_filter(array_column($data['posts'], 'post_media')));
            return [
                'include' => $ids,
                'per_page' => count($ids),
            ];
        }
        return [
            'per_page' => 100,
        ];
    }

    /**
     * Get property to use as key.
     *
     * @return string
     */
    public function getMediaKey(): ?string
    {
        return 'id';
    }

    /**
     * Get single property to return.
     *
     * @return string
     */
    public function getMediaValue(): ?string
    {
        return null;
    }

    /**
     * Callback to run on each record.
     *
     * @param $media
     * @param $index
     *
     * @return string
     */
    public function dataMedia($media, $index)
    {
        $imageSizes = get_object_vars($media->media_details->sizes);
        usort($imageSizes, function ($image1, $image2) {
            $widthDiff = $image2->width - $image1->width;
            if ($widthDiff === 0) {
                return $image2->height - $image1->height;
            }
            return $widthDiff;
        });

        $namedSizes = $this->getNamedImagesSizes();
        $selectedImages = [];
        foreach($namedSizes as $sizeName => $width) {
            $selectedImages[$sizeName] = $imageSizes[0]->source_url;
            foreach ($imageSizes as $imageSize) {
                if ($imageSize->width < $width) {
                    break;
                }
                $selectedImages[$sizeName] = $imageSize->source_url;
            }
        }
        return (object) [
            'sizes' => (object) $selectedImages,
            'alt' => $media->alt_text ?: strtr($media->title->rendered, '-_', '  '),
        ];
    }

    /**
     * Get images sizes and breakpoints for each one.
     *
     * @return int[]
     */
    protected function getNamedImagesSizes() : array
    {
        return [
            'thumb' => 333,
            'medium' => 635,
            'large' => 825,
            'social' => 1200,
        ];
    }
}
