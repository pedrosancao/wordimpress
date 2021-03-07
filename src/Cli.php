<?php

namespace PedroSancao\Wordimpress;

use ErrorException;
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
     * @var \PedroSancao\Wordimpress\Contracts\SiteInterface
     */
    private $site;

    /**
     * @var string
     */
    private $wordimpressExecutable;

    /**
     * Create new instance
     *
     * @param string $siteClass
     */
    public function __construct(string $siteClass)
    {
        if (!class_exists($siteClass)) {
            $this->printColor("Please provide a valid class name\n", 1);
            exit(1);
        }

        $interface = SiteInterface::class;
        $reflector = new \ReflectionClass($siteClass);
        if (!$reflector->implementsInterface($interface)) {
            $this->printColor("Provided class must implements {$interface}\n", 1);
            exit(1);
        }

        $this->site = new $siteClass;
    }

    /**
     * Fetch data and generate HTML pages
     */
    public function generateHtml() : void
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
        $this->generateHtml();
        $this->compileSass($forProduction);
        $this->copyMedia();
    }

    /**
     * Watch for file change and recompile
     */
    public function watch() : void
    {
        $this->validateWatcher();
        $callbackWatches = ['invokeBuildHtml' => [
            (new ReflectionClass($this->site))->getFileName(),
            $this->site->getTemplatesDir(),
        ]];
        if ($this->site instanceof WatchFilesInterface) {
            array_push($callbackWatches['invokeBuildHtml'], ...$this->site->watchPaths());
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
                $this->clearScreen();
                $this->printColor(sprintf("Changes detected at %s.\n", date('H:i:s')), 2);
                call_user_func([$this, $watcherCallbacks[$event['wd']]]);
            }
        }
    }

    /**
     * Validate watcher requirements
     */
    protected function validateWatcher() : void
    {
        if (php_sapi_name() !== 'cli') {
            throw new \ErrorException('Watcher can only be used on CLI.');
        }
        global $argv;
        if (basename($argv[0]) !== 'wordimpress') {
            $this->printColor("Cannot find executable, please use wordimpress script.\n", 1);
            exit;
        }
        $this->wordimpressExecutable = $argv[0];
    }

    /**
     * Invoke HTML generator in new process
     */
    protected function invokeBuildHtml() : void
    {
        passthru($this->wordimpressExecutable . ' --html-only ' . get_class($this->site));
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
