<?php

namespace PedroSancao\Wpsg\Contracts;

use PedroSancao\Wpsg\Generator;

interface SiteInterface
{
    /**
     * Get Wordpress base URL
     *
     * @return string
     */
    public function getWordpressUrl() : string;

    /**
     * Get location of templates do use
     *
     * @return string
     */
    public function getTemplatesDir() : string;

    /**
     * Get directory to store cache
     *
     * @return string
     */
    public function getCacheDir() : string;

    /**
     * Get directory to put generated and imported files
     *
     * @return string
     */
    public function getOutputDir() : string;

    /**
     * Callback executed before generation start
     *
     * @param \PedroSancao\Wpsg\Generator $generator
     */
    public function beforeGenerate(Generator $generator) : void;

    /**
     * Configure the templates that must be used to generate pages
     *
     * @param \PedroSancao\Wpsg\Generator $generator
     */
    public function configureTemplates(Generator $generator) : void;
}
