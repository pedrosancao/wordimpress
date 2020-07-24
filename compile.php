<?php

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

function colorPrint($text, $colorCode)
{
    echo "\33[38;5;{$colorCode}m{$text}\33[0m";
}

function compileHtml($arguments)
{
    echo "\nCompiling HTML.\n";
    echo shell_exec('php main.php');
    echo "Done.\n";
}

function compileSass($arguments)
{
    echo "\nCompiling Sass.\n";
    $command = false;
    if (exec('command -v sass')) { // Dart Sass or Ruby Sass
        $command = 'sass --load-path=resources/scss/ --load-path=vendor';
    } elseif (exec('command -v node-sass')) {
        $command = 'node-sass --include-path=resources/scss/ --include-path=vendor --source-map=true';
    } else {
        colorPrint('Sass not installed.', 1);
        echo " Skipping.\n";
        return false;
    }
    if (key_exists('c', $arguments)) {
        $command .= ' --style compressed';
    }
    $command .= ' resources/scss/bundle.scss build/css/styles.css';
    echo shell_exec($command);
    echo "Done.\n";
}

function copyMedia($arguments)
{
    echo "\nCopying media.\n";
    $origin = 'resources/media/';
    $destination = 'build/media/';
    array_map(function ($file) use ($destination) {
        copy($file, $destination . basename($file));
    }, glob($origin . '*'));
    echo "Done.\n";
}

$arguments = getopt('wc');

compileHtml($arguments);
compileSass($arguments);
copyMedia($arguments);
echo "\n";

if (key_exists('w', $arguments)) {
    $pathCallbacks = [
        'README.md'       => 'compileHtml',
        'main.php'        => 'compileHtml',
        'templates'       => 'compileHtml',
        'resources/scss'  => 'compileSass',
        'resources/media' => 'copyMedia',
    ];
    $watchPaths = ['main.php', 'templates', 'resources/scss', 'resources/media'];
    foreach ($pathCallbacks as $path => $callback) {
        if (is_dir($path)) {
            $pathCallbacks += array_fill_keys(glob($path . '/*', GLOB_ONLYDIR), $callback);
        }
    }
    $inotify = inotify_init();
    $watcherCallbacks = [];
    foreach ($pathCallbacks as $path => $callback) {
        $idWatcher = inotify_add_watch($inotify, $path, IN_CLOSE_WRITE);
        $watcherCallbacks[$idWatcher] = $callback;
    }

    echo colorPrint("Watching for changes. Press Ctrl+C to stop.\n", 4);

    while (false !== $events = inotify_read($inotify)) {
        foreach ($events as $event) {
            colorPrint(sprintf("\nChanges detected at %s.\n", date('H:i:s')), 2);
            call_user_func($watcherCallbacks[$event['wd']], $arguments);
        }
    }
}
