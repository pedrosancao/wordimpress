<?php

namespace PedroSancao\Wordimpress;

use PedroSancao\Wordimpress\Cli\FormatTextTrait;
use PedroSancao\Wordimpress\Contracts\CompileSassInterface;
use PedroSancao\Wordimpress\Contracts\CopyMediaInterface;
use PedroSancao\Wordimpress\Contracts\SiteInterface;
use PedroSancao\Wordimpress\Contracts\WatchFilesInterface;
use ReflectionClass;

class Cli
{
    use FormatTextTrait;

    /**
     * @var string
     */
    private $binDir;

    /**
     * @var \PedroSancao\Wordimpress\Contracts\SiteInterface
     */
    private $site;

    /**
     * Create new instance
     *
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $this->binDir = dirname($argv[0]);
        if (count($argv) < 2 || !class_exists($argv[1])) {
            $this->printColor("Please provide a valid class name\n", 1);
            exit(1);
        }

        $this->site = new $argv[1];
        if (!$this->site instanceof SiteInterface) {
            $interface = SiteInterface::class;
            $this->printColor("Provided class must implements {$interface}\n", 1);
            exit(1);
        }
    }

    /**
     * Fetch data and generate HTML pages
     */
    public function generate() : void
    {
        $generator = new Generator($this->site);
        $generator->prepare();
        $generator->generate();
    }

    /**
     * Compile all resources: HTML, Sass and media
     *
     * @param bool $forProduction
     */
    public function run(bool $forProduction = false) : void
    {
        $this->compileHtml();
        $this->compileSass($forProduction);
        $this->copyMedia();
    }

    /**
     * Watch for file change and recompile
     */
    public function watch() : void
    {
        $callbackWatches = ['compileHtml' => [
            (new ReflectionClass($this->site))->getFileName(),
            $this->site->getTemplatesDir(),
        ]];
        if ($this->site instanceof WatchFilesInterface) {
            array_push($callbackWatches['compileHtml'], ...$this->site->watchPaths());
        }
        if ($this->site instanceof CompileSassInterface) {
            $callbackWatches['compileSass'] = [dirname($this->site->getSassSourceFile())];
        }
        if ($this->site instanceof CopyMediaInterface) {
            $callbackWatches['copyMedia'] = [$this->site->getMediaDirectory()];
        }

        $inotify = inotify_init();
        $watcherCallbacks = [];
        foreach ($callbackWatches as $callback => $paths) {
            while (false !== $path = current($paths)) {
                if (is_dir($path)) {
                    array_push($paths, ...glob($path . '/*', GLOB_ONLYDIR));
                }
                $idWatcher = inotify_add_watch($inotify, $path, IN_CLOSE_WRITE);
                $watcherCallbacks[$idWatcher] = $callback;
                next($paths);
            }
        }

        echo $this->printColor("Watching for changes. Press Ctrl+C to stop.\n", 4);

        while (false !== $events = inotify_read($inotify)) {
            foreach ($events as $event) {
                $this->printColor(sprintf("\nChanges detected at %s.\n", date('H:i:s')), 2);
                call_user_func([$this, $watcherCallbacks[$event['wd']]]);
            }
        }
    }

    /**
     * Invoke HTML generator
     */
    protected function compileHtml() : void
    {
        echo shell_exec($this->binDir . '/wordimpress-generate ' . get_class($this->site));
    }

    /**
     * Invoke Sass compiler
     *
     * @param bool $forProduction
     */
    protected function compileSass(bool $forProduction = false) : void
    {
        if ($this->site instanceof CompileSassInterface) {
            echo "Compiling Sass.\n";

            $command = $includeTerm = '';
            $source = $this->site->getSassSourceFile();
            $destination = $this->site->getOutputDir() . '/css/styles.css';
            $include = array_merge([dirname($source)], $this->site->getSassSourceDirectories());

            if (exec('command -v sass')) { // Dart Sass or Ruby Sass
                $command = 'sass';
                $includeTerm = 'load-path';
            } elseif (exec('command -v node-sass')) {
                $command = 'node-sass --source-map=true';
                $includeTerm = 'include-path';
            } else {
                $this->printColor("Sass not installed.\n", 1);
                echo "Skipping.\n";
                return;
            }

            foreach ($include as $directory) {
                $command .= " --{$includeTerm}={$directory}";
            }
            if ($forProduction) {
                $command .= ' --style compressed';
            }
            $command .= " {$source} {$destination}";

            echo shell_exec($command);
            echo "Done.\n";
        }
    }

    /**
     * Copy media files
     */
    protected function copyMedia() : void
    {
        if ($this->site instanceof CopyMediaInterface) {
            echo "Copying media.\n";
            $source = $this->site->getMediaDirectory();
            $destination = $this->site->getOutputDir() . '/media/';
            array_map(function ($file) use ($destination) {
                copy($file, $destination . basename($file));
            }, glob($source . '/*'));
            echo "Done.\n";
        }
    }
}
