<?php

namespace PedroSancao\Wpsg\Template;

use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Extra\Markdown\DefaultMarkdown;

class MarkdownLoader implements RuntimeLoaderInterface
{
    public function load($class)
    {
        if (MarkdownRuntime::class === $class) {
            return new MarkdownRuntime(new DefaultMarkdown());
        }
    }
}
