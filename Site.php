<?php

use PedroSancao\Wpsg\Contracts\ImportImagesInterface;
use PedroSancao\Wpsg\Contracts\SiteInterface;
use PedroSancao\Wpsg\Contracts\WithAuthorsInterface;
use PedroSancao\Wpsg\Contracts\WithCategoriesInterface;
use PedroSancao\Wpsg\Contracts\WithMediaInterface;
use PedroSancao\Wpsg\Contracts\WithPagesInterface;
use PedroSancao\Wpsg\Contracts\WithPostsInterface;
use PedroSancao\Wpsg\BlogData\HasAuthorsTrait;
use PedroSancao\Wpsg\BlogData\HasCategoriesTrait;
use PedroSancao\Wpsg\BlogData\HasMediaTrait;
use PedroSancao\Wpsg\BlogData\HasPagesTrait;
use PedroSancao\Wpsg\BlogData\HasPostsTrait;
use PedroSancao\Wpsg\Generator;

class Site implements
    ImportImagesInterface,
    SiteInterface,
    WithAuthorsInterface,
    WithCategoriesInterface,
    WithMediaInterface,
    WithPagesInterface,
    WithPostsInterface
{
    use HasAuthorsTrait;
    use HasCategoriesTrait;
    use HasMediaTrait;
    use HasPagesTrait;
    use HasPostsTrait;

    /**
     * @inheritdoc
     */
    public function getCacheDir(): string
    {
        return __DIR__ . '/cache';
    }

    /**
     * @inheritdoc
     */
    public function getOutputDir(): string
    {
        return __DIR__ . '/build';
    }

    /**
     * @inheritdoc
     */
    public function getTemplatesDir(): string
    {
        return __DIR__ . '/templates';
    }

    /**
     * @inheritdoc
     */
    public function getWordpressUrl(): string
    {
        return $_ENV['WORDPRESS'];
    }

    /**
     * @inheritdoc
     */
    public function mustConvertImages() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeGenerate(Generator $generator): void
    {
        $generator->addTemplateValues([
            'locale' =>  $_ENV['LOCALE'],
            'base'   =>  $_ENV['BASE_URL'],
            'title'  =>  $_ENV['TITLE'],
            'fb_app' =>  $_ENV['FACEBOOK_APP_ID'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function generate(Generator $generator): void
    {
    }

}