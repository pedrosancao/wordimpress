# Wordpress static site generator

Static site generator using Wordpress API as content source. The project still a proof of concept.
This implementation retrieves content from [wordpress.org/news](https://wordpress.org/news).

## Key features

- uses Twig as template engine
- supports Sass (using Bootstrap Sass via composer)
- support Facebook comment plugin
- built-in watcher to compile as files change
- convert images to WebP format
- supports markdown to HTML

## Usage

Copy ".env.example" to ".env" and adjust the properties.

To compile templates, Sass and copy media once run `php compile.php`.

To compile then watch for changes add `-w` parameter: `php compile.php -w`.

The parameter `-m` add compression (currently only Sass has support).

## To do list

The following tasks are my current priorities:

- 🔝 Convert procedural code to OOP;
- Add paginations for all posts and category posts;
