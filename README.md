# Wordimpress

![project license](https://img.shields.io/github/license/pedrosancao/wordimpress)
![code size](https://img.shields.io/github/languages/code-size/pedrosancao/wordimpress)
![PHP version](https://img.shields.io/packagist/php-v/pedrosancao/wordimpress)
![packagist version](https://img.shields.io/packagist/v/pedrosancao/wordimpress)
![packagist downloads](https://img.shields.io/packagist/dt/pedrosancao/wordimpress)

Static site generator library that uses Wordpress API as content source. The library still a prototype.

## Key features

- uses Twig as template engine
- convert images to WebP format
- supports markdown to HTML

## Dependencies

This package rely on these PHP extensions:

- GD
- DOM
- Inotify

Make sure you have them installed.

## Usage

Create a class implementing `PedroSancao\Wordimpress\Contracts\SiteInterface`, then implement other
interfaces on the namespace `PedroSancao\Wordimpress\Contracts` to add more capabilities, some of them
have traits that implements the interface (`PedroSancao\Wordimpress\BlogData\Has*Trait`).

Invoke commands on [bin/](./bin) directory passing the classname as parameter.

An example of usage is available on [Bootstrap Template](https://github.com/pedrosancao/wordimpress-bootstrap).

## To do

Check the project's [kanban board](https://github.com/pedrosancao/wordimpress/projects/1).
