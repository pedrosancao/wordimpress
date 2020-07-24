<?php

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');// extract(get_object_vars(json_decode(file_get_contents('cache/cache.json'))));
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once './vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env' . (file_exists('./.env') ? '' : '.example'));

function loadData($endpoint, $parameters = [])
{
    $baseUrl = $_ENV['WORDPRESS'] . '/wp-json/wp/v2/';
    return json_decode(file_get_contents($baseUrl . $endpoint . '?' . http_build_query($parameters)));
}
function convertImage($url)
{
    $filepath = parse_url($url, PHP_URL_PATH);
    $filename = pathinfo($filepath, PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    $relativeDestination = "media/{$filename}.webp";
    $destination = './build/' . $relativeDestination;
    if (!file_exists($destination)) {
        if (false === $raw = @file_get_contents($url)) {
            echo 'error downloading: ', $url, "\n";
            return false;
        }
        if (in_array($extension, ['svg'])) {
            file_put_contents(str_replace('.webp', '.' . $extension, $destination), $raw);
        } else {
            $image = imagecreatefromstring($raw);
            imagepalettetotruecolor($image);
            if (!imagewebp($image, $destination)) {
                return false;
            }
        }
    }
    return $relativeDestination;
}

$loader = new \Twig\Loader\FilesystemLoader('./templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
    'cache' => './cache',
    'autoescape' => false,
]);
$twig->addExtension(new \Twig\Extra\Intl\IntlExtension());
$twig->addGlobal('locale', $_ENV['LOCALE']);
$twig->addGlobal('base', $_ENV['BASE_URL']);
$twig->addGlobal('title', $_ENV['TITLE']);
$twig->addGlobal('fb_app', $_ENV['FACEBOOK_APP_ID']);

$twig->addRuntimeLoader(new class implements \Twig\RuntimeLoader\RuntimeLoaderInterface {
    public function load($class) {
        if (\Twig\Extra\Markdown\MarkdownRuntime::class === $class) {
            return new \Twig\Extra\Markdown\MarkdownRuntime(new \Twig\Extra\Markdown\DefaultMarkdown());
        }
    }
});
$twig->addExtension(new \Twig\Extra\Markdown\MarkdownExtension());

$cacheFile = 'cache/cache.json';
if (file_exists($cacheFile)) {
    extract(get_object_vars(json_decode(file_get_contents($cacheFile))));
} else {
    $posts = array_map(function ($post) {
        $postData = [
            'id'         => $post->id,
            'date'       => $post->date,
            'slug'       => $post->slug,
            'title'      => trim($post->title->rendered),
            'author'     => $post->author,
            'excerpt'    => trim($post->excerpt->rendered),
            'content'    => trim($post->content->rendered),
            'categories' => $post->categories,
        ];
        if ($post->featured_media) {
            $postImageData = loadData("media/{$post->featured_media}");
            $imageSizes = get_object_vars($postImageData->media_details->sizes);
            usort($imageSizes, function ($image1, $image2) {
                return $image1->width - $image2->width;
            });
            $thumbnail = $medium = $large = null;
            foreach ($imageSizes as $imageSize) {
                if (is_null($thumbnail) && $imageSize->width >= 333) {
                    $thumbnail = $medium = $large = $imageSize->source_url;
                }
                if ($medium === $thumbnail && $imageSize->width >= 635) {
                    $medium = $large = $imageSize->source_url;
                }
                if ($large === $medium && $imageSize->width >= 825) {
                    $large = $imageSize->source_url;
                    break;
                }
            }
            $postData['post_media'] = (object) [
                'thumb'  => $thumbnail,
                'medium' => $medium,
                'large'  => $large,
            ];
            $postData['post_media_alt'] = $postImageData->alt_text ?: strtr($postImageData->title->rendered, '-_', '  ');
        }
        return (object) $postData;
    }, loadData('posts', ['per_page' => 100]));
    
    $authors = array_column(loadData('users', [
        'include'  => array_unique(array_column($posts, 'author')),
        'per_page' => 100,
    ]), 'name', 'id');
    
    $idCategories = array_unique(array_merge(...array_column($posts, 'categories')));
    $categories = array_column(array_map(function ($category) {
        return (object) [
            'id'    => $category->id,
            'name'  => $category->name,
            'slug'  => $category->slug,
        ];
    }, loadData('categories', [
        'include' => $idCategories,
    ])), null, 'id');
    
    libxml_use_internal_errors(true);
    array_walk($posts, function ($post) {
        if (property_exists($post, 'post_media')) {
            foreach (get_object_vars($post->post_media) as $size => $url) {
                $post->post_media->{$size} = convertImage($url);
            }
        }
        $doc = new DOMDocument();
        $data = $doc->loadHTML($post->content);
        $removeAttributes = ['sizes', 'srcset'];
        foreach ($doc->getElementsByTagName('img') as $image) {
            foreach ($removeAttributes as $attributeName) {
                $attribute = $image->attributes->getNamedItem($attributeName);
                if ($attribute) {
                    $post->content = str_replace(" {$attributeName}=\"{$attribute->value}\"", '', $post->content);
                }
            }
            $imageUrl = $image->attributes->getNamedItem('src')->value;
            $post->content = str_replace($imageUrl, convertImage($imageUrl), $post->content);
        }
    });
    
    file_put_contents($cacheFile, json_encode(compact('posts', 'categories', 'authors')));
}

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
}, get_object_vars($categories), array_keys(get_object_vars($categories)));

if (!is_dir('./build/post/')) {
    mkdir('./build/post/');
}
array_map(function ($post) use ($twig, $categories, $authors, $posts) {
    $content = 'sections/post.html';
    if (file_put_contents("./build/post/{$post->slug}.html", $twig->render('post.twig', compact('content', 'post', 'categories', 'authors', 'posts')))) {
        echo "post/{$post->slug}.html generated\n";
    }
}, $posts);
