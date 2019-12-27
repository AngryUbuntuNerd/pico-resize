# Image Resize plugin for Pico CMS
[PicoCMS](https://github.com/picocms/Pico) plugin for automatically creating resized images.

Use this plugin when having big, original-sized images that you want to automatically scale down to a given box size.

# Requirements

Either ImageMagick or GD needs to be installed, and the respective PHP extension `imagick` or `gd` is required
to run this plugin. Do not forget to activate in php.ini by having a line like:

``` ini
extension=imagick
```

or

``` ini
extension=gd
```

# Installation / Configuration

Copy `ImageResize.php` to the `plugins` directory of your Pico installation and configure the plugin in your `config/config.yml` file:

``` yaml
ImageResize:
    folder: .resized
    quality: 85
```

# Usage

In your Twig templates, you can use the new `resize(asset, width, height)` function to automatically create resized images of your pictures:

``` html
<img src="{{ resize('assets/example.jpg', 200, 150) }}"/>
``` 

This will create the image and replace the URL linking to the resized version.

Note that the initial creation will take some time on load, but it will be cached in a file afterwards.

You can omit either height or width if you dont want to limit both (the plugin will preserve the ratio).