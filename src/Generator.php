<?php

namespace PedroSancao\Wpsg;

use PedroSancao\Wpsg\BlogData\CacheTrait;
use PedroSancao\Wpsg\BlogData\DataLoadTrait;
use PedroSancao\Wpsg\Contracts\SiteInterface;
use PedroSancao\Wpsg\Exceptions\ImageException;
use PedroSancao\Wpsg\Template\MarkdownLoader;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\Loader\FilesystemLoader;

class Generator
{
    use CacheTrait;
    use DataLoadTrait;
    use HasTemplateTrait;

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
        $this->site->configureTemplates($this);
        $this->generatePages();
    }

    protected function generatePages() : void
    {
        $output = rtrim($this->site->getOutputDir(), '/') . '/';
        $directories = array_diff(array_unique(array_map(function ($template) {
            return dirname($template['file']);
        }, $this->templates)), ['.']);
        foreach ($directories as $directory) {
            if (!is_dir($output . $directory)) {
                mkdir($output . $directory);
            }
        }
        echo "Generating pages: \n";
        foreach ($this->templates as $template) {
            $html = $this->twig->render($template['template'], $template['data'] + $this->blogData);
            if (file_put_contents($output . $template['file'], $html)) {
                echo '- ' ,$template['file'], "\n";
            }
        }
        echo "Done.\n";
    }
}

