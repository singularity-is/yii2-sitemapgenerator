# Sitemap Generator for Yii2

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require singularity/yii2-sitemapgenerator
```

or add

```
"singularity/yii2-sitemapgenerator": "~1.0"
```

to the `require` section of your `composer.json` file.

## Usage

Quick example:
```PHP
$generator = new SitemapGenerator([
    'maxURLsPerSitemap' => 20000,
    'basePath' => 'frontend/web',
    'items' => [
        'https://mysite.com',                      // url as string
        ['class' => User::class],                  // array with ['class'] that have getPublicUrl() method
        [
            'class' => Post::class,               // if ['class'] does not have getPublicUrl() method,
            'url' => function (Post $model) {     // then ['url'] must be set
                return ["/post/view/$model->id"]; // ['url'] can be value or callback
            }
        ]
    ]
]);

$count = $generator->generate();
```

## Properties

- baseUrl
- basePath
- items
- sitemapFilename
- sitemapIndexFilename
- robotsFilename
- maxURLsPerSitemap
- fs
- runtime


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Dositej Grbovic](https://dositej-dev.com)
- [All Contributors](https://github.com/singularity-is/yii2-sitemapgenerator/graphs/contributors)

## License

The BSD License (BSD). Please see [License File](LICENSE.md) for more information.


<a href="https://singularity.is"><img src="http://www.gravatar.com/avatar/8663d48ea6093d2ce917217ceeca1cc2.png"></a><br>
<i>#InventTomorrow</i><br>
<a href="https://www.singularity.is">www.singularity.is</a>