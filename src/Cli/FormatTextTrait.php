<?php

namespace PedroSancao\Wordimpress\Cli;

trait FormatTextTrait
{
    /**
     * Clear terminal screen
     */
    public function clearScreen() : void
    {
        echo "\33c";
    }

    /**
     * Print colored text
     *
     * @param string $text
     * @param int $colorCode
     */
    public function printColor(string $text, int $colorCode) :  void
    {
        echo "\33[38;5;{$colorCode}m{$text}\33[0m";
    }
}
