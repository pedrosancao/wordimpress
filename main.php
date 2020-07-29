<?php

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once './vendor/autoload.php';
require_once './Site.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env' . (file_exists('./.env') ? '' : '.example'));

$site = new Site;
$generator = new PedroSancao\Wpsg\Generator($site);
$generator->prepare();
$generator->generate();

$twig = $generator->getTwig();
extract($generator->getBlogData());


$readme = file_get_contents('./README.md');
if (file_put_contents('./build/index.html', $twig->render('index.twig', compact('readme', 'categories', 'posts')))) {
    echo "index.html generated\n";
}

if (file_put_contents('./build/posts.html', $twig->render('blog.twig', compact('categories', 'posts', 'authors')))) {
    echo "posts.html generated\n";
}

if (!is_dir('./build/category/')) {
    mkdir('./build/category/');
}
array_map(function ($category, $idCategory) use ($twig, $posts, $authors) {
    $categoryPosts = array_filter($posts, function ($post) use ($idCategory) {
        return in_array($idCategory, $post->categories);
    });
    if (file_put_contents("./build/category/{$category->slug}.html", $twig->render('category.twig', compact('category', 'categoryPosts', 'posts', 'authors')))) {
        echo "category/{$category->slug}.html generated\n";
    }
}, $categories, array_keys($categories));

if (!is_dir('./build/post/')) {
    mkdir('./build/post/');
}
array_map(function ($post) use ($twig, $categories, $authors, $posts) {
    $content = 'sections/post.html';
    if (file_put_contents("./build/post/{$post->slug}.html", $twig->render('post.twig', compact('content', 'post', 'categories', 'authors', 'posts')))) {
        echo "post/{$post->slug}.html generated\n";
    }
}, $posts);
