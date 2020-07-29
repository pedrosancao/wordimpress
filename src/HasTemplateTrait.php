<?php

namespace PedroSancao\Wordimpress;

trait HasTemplateTrait
{
    /**
     * @var array
     */
    protected $templates = [];

    /**
     * @var array
     */
    protected $blogData = [];

    /**
     * Add template for page
     *
     * @param string $template Twig template file
     * @param string $exportFile file to export the generated page
     * @param array $data additional data to pass on the template
     */
    public function addTemplate(string $template, string $exportFile, array $data = []) : void
    {
        $this->templates[] = [
            'template' => $template,
            'file'     => ltrim($exportFile, '/'),
            'data'     => $data + $this->blogData,
        ];
    }

    /**
     * Add template for pages
     *
     * @param string $template Twig template file
     * @param string $exportPrefix location prefix to export the generated pages
     * @param array $data additional data to pass on the template
     */
    public function addPageTemplate(string $template, string $exportPrefix = '', array $data = []) : void
    {
        if (key_exists('pages', $this->blogData)) {
            foreach ($this->blogData['pages'] as $slug => $page) {
                $this->addTemplate($template, $exportPrefix . $slug . '.html', $data + compact('page'));
            }
        }
    }

    /**
     * Add template for post pages
     *
     * @param string $template Twig template file
     * @param string $exportPrefix location prefix to export the generated pages
     * @param array $data additional data to pass on the template
     */
    public function addPostTemplate(string $template, string $exportPrefix = 'post/', array $data = []) : void
    {
        if (key_exists('posts', $this->blogData)) {
            foreach ($this->blogData['posts'] as $post) {
                $this->addTemplate($template, $exportPrefix . $post->slug . '.html', $data + compact('post'));
            }
        }
    }

    /**
     * Add template for category pages
     *
     * @param string $template Twig template file
     * @param string $exportPrefix location prefix to export the generated pages
     * @param array $data additional data to pass on the template
     */
    public function addCategoryTemplate(string $template, string $exportPrefix = 'category/', array $data = []) : void
    {
        if (key_exists('categories', $this->blogData)) {
            $posts = key_exists('posts', $this->blogData) ? $this->blogData['posts']: [];
            foreach ($this->blogData['categories'] as $idCategory => $category) {
                $categoryPosts = array_filter($posts, function ($post) use ($idCategory) {
                    return in_array($idCategory, $post->categories);
                });
                $this->addTemplate($template, $exportPrefix . $category->slug . '.html', $data + compact('category', 'categoryPosts'));
            }
        }
    }

    /**
     * @TODO Add template for author pages
     *
     * @param string $template Twig template file
     * @param string $exportPrefix location prefix to export the generated pages
     * @param array $data additional data to pass on the template
     */
    public function addAuthorTemplate(string $template, string $exportPrefix = 'tag/', array $data = []) : void
    {
        throw new \ErrorException('tag template not implemented.');
    }

    /**
     * @TODO Add template for tag pages
     *
     * @param string $template Twig template file
     * @param string $exportPrefix location prefix to export the generated pages
     * @param array $data additional data to pass on the template
     */
    public function addTagTemplate(string $template, string $exportPrefix = 'tag/', array $data = []) : void
    {
        throw new \ErrorException('tag template not implemented.');
    }

}
