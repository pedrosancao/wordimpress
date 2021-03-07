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

Add Wordimpress to your project using composer:

```bash
composer require pedrosancao/wordimpress
```

---

Create a class implementing `PedroSancao\Wordimpress\Contracts\SiteInterface`, then implement other
interfaces on the namespace `PedroSancao\Wordimpress\Contracts` to add more capabilities, some of them
have traits that implements the interface (`PedroSancao\Wordimpress\BlogData\Has*Trait`).

---

Invoke wordpress command:

`vendor/bin/wordimpress [options] classname`

`classname` is the full qualified name of the class implementing `SiteInterface`

The available options:

- `-p, --production` generate assets production
- `-w, --watch` watch for changes to recompile
- `--html-only` run only HTML generation (prevents `-w`)

---

An example of usage is available on [Bootstrap Template](https://github.com/pedrosancao/wordimpress-bootstrap).

## To do

Check the project's [kanban board](https://github.com/pedrosancao/wordimpress/projects/1).
