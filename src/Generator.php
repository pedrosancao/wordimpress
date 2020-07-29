<?php

namespace PedroSancao\Wpsg;

use DOMDocument;
use PedroSancao\Wpsg\BlogData\CacheTrait;
use PedroSancao\Wpsg\Contracts\ImportImagesInterface;
use PedroSancao\Wpsg\Contracts\SiteInterface;
use PedroSancao\Wpsg\Contracts\WithAuthorsInterface;
use PedroSancao\Wpsg\Contracts\WithCategoriesInterface;
use PedroSancao\Wpsg\Contracts\WithMediaInterface;
use PedroSancao\Wpsg\Contracts\WithPagesInterface;
use PedroSancao\Wpsg\Contracts\WithPostsInterface;
use PedroSancao\Wpsg\Exceptions\ImageException;
use PedroSancao\Wpsg\Template\MarkdownLoader;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Loader\FilesystemLoader;

class Generator
{
    use CacheTrait;

    /**
     * @var \PedroSancao\Wpsg\Contracts\SiteInterface
     */
    protected $site;

    /**
     * @var \PedroSancao\Wpsg\ApiClient
     */
    protected $apiClient;

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $blogData = [];

    /**
     * @oaram PedroSancao\Wpsg\Contracts\SiteInterface $site
     */
    public function __construct(SiteInterface $site)
    {
        $this->site = $site;
        $this->apiClient = new ApiClient($site->getWordpressUrl());
        $this->initTwig($site);
    }

    /**
     * Initialize Twig template engine
     *
     * @oaram PedroSancao\Wpsg\Contracts\SiteInterface $site
     */
    protected function initTwig() : void
    {
        $loader = new FilesystemLoader($this->site->getTemplatesDir());
        $this->twig = new Environment($loader, [
            'debug' => true,
            'cache' => $this->site->getCacheDir(),
            'autoescape' => false,
        ]);
        $this->twig->addExtension(new IntlExtension());
        $this->twig->addRuntimeLoader(new MarkdownLoader());
        $this->twig->addExtension(new MarkdownExtension());

    }

    /**
     * Add a global variable to twig
     *
     * @param string $name
     * @param type $value
     */
    public function addTemplateValues(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->twig->addGlobal($name, $value);
        }
    }

    /**
     * Get the twig instance
     *
     * @return Twig\Environment
     */
    public function getTwig() : Environment
    {
        return $this->twig;
    }

    /**
     * Get all the blog data loaded
     *
     * @return array
     */
    public function getBlogData() : array
    {
        return $this->blogData;
    }

    /**
     * Prepare generation fetching all data from API
     */
    public function prepare() : void
    {
        if (!$this->loadCache()) {
            $this->loadPages();
            $this->loadPosts();
            $this->loadMedia();
            $this->loadAuthors();
            $this->loadCategories();
            $this->importImages();
            $this->saveCache();
        }
    }

    /**
     * Prepare generation fetching all data from API
     */
    public function generate() : void
    {
        $this->site->beforeGenerate($this);
        $this->site->generate($this);
    }

    /**
     * Load data from API for a resource
     *
     * @param string $endpoint
     * @param array $parameters
     * @param \PedroSancao\Wpsg\callable $callback
     * @param string $value
     * @param string $key
     * @return array
     */
    protected function loadData(
        string $endpoint,
        array $parameters,
        callable $callback,
        string $value = null,
        string $key = null
    ) : array
    {
        $data = $this->apiClient->loadData($endpoint, $parameters);
        if (empty($data)) {
            return [];
        }
        if ($value || $key) {
            $data = array_column($data, $value, $key);
        }

        $values = array_map($callback, $data, range(0, count($data) - 1));
        return array_combine(array_keys($data), $values);
    }

    /**
     * Load pages data if the site enabled it
     */
    protected function loadPages() : void
    {
        if ($this->site instanceof WithPagesInterface) {
            $this->blogData['pages'] = $this->loadData(
                'pages',
                $this->site->getPagesFilters($this->blogData),
                [$this->site, 'dataPage'],
                $this->site->getPageValue(),
                $this->site->getPageKey()
            );
        }
    }

    /**
     * Load posts data if the site enabled it
     */
    protected function loadPosts() : void
    {
        if ($this->site instanceof WithPostsInterface) {
            $this->blogData['posts'] = $this->loadData(
                'posts',
                $this->site->getPostsFilters($this->blogData),
                [$this->site, 'dataPost'],
                $this->site->getPostValue(),
                $this->site->getPostKey()
            );
        }
    }

    /**
     * Load media data if the site enabled it
     */
    protected function loadMedia() : void
    {
        if ($this->site instanceof WithMediaInterface) {
            $this->blogData['media'] = $this->loadData(
                'media',
                $this->site->getMediaFilters($this->blogData),
                [$this->site, 'dataMedia'],
                $this->site->getMediaValue(),
                $this->site->getMediaKey()
            );
        }
    }

    /**
     * Load authors data if the site enabled it
     */
    protected function loadAuthors() : void
    {
        if ($this->site instanceof WithAuthorsInterface) {
            $this->blogData['authors'] = $this->loadData(
                'users',
                $this->site->getAuthorsFilters($this->blogData),
                [$this->site, 'dataAuthor'],
                $this->site->getAuthorValue(),
                $this->site->getAuthorKey()
            );
        }
    }

    /**
     * Load categories data if the site enabled it
     */
    protected function loadCategories() : void
    {
        if ($this->site instanceof WithCategoriesInterface) {
            $this->blogData['categories'] = $this->loadData(
                'categories',
                $this->site->getCategoriesFilters($this->blogData),
                [$this->site, 'dataCategory'],
                $this->site->getCategoryValue(),
                $this->site->getCategoryKey()
            );
        }
    }

    /**
     * Import images if the site enabled it
     */
    protected function importImages() : void
    {
        $hasMedia = count(array_intersect(array_keys($this->blogData), ['media', 'posts'])) > 0;
        if ($hasMedia && $this->site instanceof ImportImagesInterface) {
            $imageTools = new ImageTools($this->site->mustConvertImages());
            $destination = rtrim($this->site->getOutputDir(), '/') . '/media';

            if (key_exists('media', $this->blogData)) {
                array_walk($this->blogData['media'], function ($media) use ($imageTools, $destination) {
                    foreach (get_object_vars($media->sizes) as $size => $url) {
                        try {
                            $media->sizes->{$size} = 'media/' . $imageTools->import($url, $destination);
                        } catch(ImageException $exception) {
                            echo $exception->getMessage(), "\n";
                        }
                    }
                });
            }
            if (key_exists('posts', $this->blogData)) {
                libxml_use_internal_errors(true);
                array_walk($this->blogData['posts'], function ($post) use ($imageTools, $destination) {
                    $doc = new DOMDocument();
                    $doc->loadHTML($post->content);
                    $removeAttributes = ['sizes', 'srcset'];
                    foreach ($doc->getElementsByTagName('img') as $image) {
                        foreach ($removeAttributes as $attributeName) {
                            $attribute = $image->attributes->getNamedItem($attributeName);
                            if ($attribute) {
                                $post->content = str_replace(" {$attributeName}=\"{$attribute->value}\"", '', $post->content);
                            }
                        }
                        $imageUrl = $image->attributes->getNamedItem('src')->value;
                        try {
                            $newUrl = 'media/' . $imageTools->import($imageUrl, $destination);
                            $post->content = str_replace($imageUrl, $newUrl, $post->content);
                        } catch(ImageException $exception) {
                            echo $exception->getMessage(), "\n";
                        }
                    }
                });
            }
        }
    }
}
