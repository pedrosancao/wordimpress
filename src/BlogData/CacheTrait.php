<?php

namespace PedroSancao\Wordimpress\BlogData;

trait CacheTrait
{
    /**
     *  Gets the path for cache file.
     *
     * @return string
     */
    protected function getCacheFile(): string
    {
        return $this->site->getCacheDir() . '/blog-data';
    }

    /**
     * Save data retrieved from API to a cache file.
     */
    protected function saveCache(): void
    {
        file_put_contents($this->getCacheFile(), serialize($this->blogData));
    }

    /**
     * Load cache if available.
     *
     * @return bool true if cache were loaded
     */
    protected function loadCache(): bool
    {
        $cacheFile = $this->getCacheFile();
        if (file_exists($cacheFile)) {
            $this->blogData = unserialize(file_get_contents($cacheFile));
            return true;
        }
        return false;
    }
}
