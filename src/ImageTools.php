<?php

namespace PedroSancao\Wordimpress;

use PedroSancao\Wordimpress\Exceptions\ImageException;

class ImageTools
{
    /**
     * @var bool
     */
    private $shouldConvert;

    /**
     * @var array
     */
    private $convertTypes = [
        'gif',
        'jpeg',
        'jpg',
        'png',
    ];

    /**
     * Create new instance.
     *
     * @param bool $shouldConvert
     */
    public function __construct($shouldConvert)
    {
        $this->shouldConvert = $shouldConvert;
    }

    /**
     * Update image files extension to convert.
     *
     * @param array $types
     */
    public function setConvertTypes(array $types): void
    {
        $this->convertTypes = $types;
    }

    /**
     * Checks if image should be converted.
     *
     * @param string $filepath
     *
     * @return bool
     */
    protected function shouldConvert(string $filepath): bool
    {
        if ($this->shouldConvert) {
            $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
            return in_array($extension, $this->convertTypes);
        }
        return false;
    }

    /**
     * Retrieve file contents from URL.
     *
     * @param string $url
     *
     * @throws \PedroSancao\Wordimpress\Exceptions\ImageException
     *
     * @return string
     */
    protected function getFileContents(string $url): string
    {
        if (false === $file = @file_get_contents($url)) {
            throw new ImageException('error downloading: ' . $url);
        }
        return $file ?? '';
    }

    /**
     * Import image from URL to destination folder.
     *
     * @param string $url
     * @param string $destination
     *
     * @throws \PedroSancao\Wordimpress\Exceptions\ImageException
     *
     * @return string
     */
    public function import(string $url, string $destination): string
    {
        $filepath = parse_url($url, PHP_URL_PATH);
        $shouldConvert = $this->shouldConvert($filepath);
        $filename = $shouldConvert ? pathinfo($filepath, PATHINFO_FILENAME) . '.webp' : basename($filepath);
        $destinationImage = rtrim($destination, '/') . '/' . $filename;
        if (!file_exists($destinationImage)) {
            call_user_func([$this, $shouldConvert ? 'downloadAsWebp' : 'downloadAsIs'], $url, $destinationImage);
        }
        return $filename;
    }

    /**
     * Download file without modifying.
     *
     * @param string $url
     * @param string $destination
     */
    public function downloadAsIs(string $url, string $destination): void
    {
        $raw = $this->getFileContents($url);
        file_put_contents($destination, $raw);
    }

    /**
     * Download file and convert to WebP.
     *
     * @param string $url
     * @param string $destination
     *
     * @throws \PedroSancao\Wordimpress\Exceptions\ImageException
     */
    public function downloadAsWebp(string $url, string $destination): void
    {
        $raw = $this->getFileContents($url);
        if (false === $image = imagecreatefromstring($raw)) {
            throw new ImageException('unable to create image: ' . $url);
        }
        imagepalettetotruecolor($image);
        if (!imagewebp($image, $destination)) {
            throw new ImageException('unable to export as WebP: ' . $url);
        }
    }
}
