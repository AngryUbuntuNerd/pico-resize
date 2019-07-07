<?php
/**
 * Pico ImageResize plugin - extends Twig with image resizing capabilities
 *
 * @author  AngryUbuntuNerd
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 2.0
 */
class ImageResize extends AbstractPicoPlugin
{
    /**
     * API version used by this plugin
     *
     * @var int
     */
    const API_VERSION = 2;

    /**
     * @var string
     */
    private $folder;

    /**
     * @var int
     */
    private $quality;

    /**
     * Triggered after Pico has read its configuration
     *
     * @see Pico::getConfig()
     * @see Pico::getBaseUrl()
     * @see Pico::getBaseThemeUrl()
     * @see Pico::isUrlRewritingEnabled()
     *
     * @param array &$config array of config variables
     *
     * @return void
     */
    public function onConfigLoaded(array &$config)
    {
        $this->folder = '.resized';
        if (isset($config['ImageResize']['folder']))
            $this->folder = $config['ImageResize']['folder'];

        $this->quality = 85;
        if (isset($config['ImageResize']['quality']))
            $this->quality = $config['ImageResize']['quality'];
    }

    /**
     * Triggered when Pico registers the twig template engine
     *
     * @see Pico::getTwig()
     *
     * @param Twig_Environment &$twig Twig instance
     *
     * @return void
     */
    public function onTwigRegistered(Twig_Environment &$twig)
    {
        if (!extension_loaded('imagick'))
            exit('PHP extension "imagick" is not installed, or not enabled in php.ini');

        # TODO: support GD as well

        $twig->addFunction(new Twig_SimpleFunction('resize', array($this, 'resize')));
    }

    /**
     * Resize an image, save it to a temporary folder and return new filename
     * @param string $file
     * @param int $width
     * @param int $height
     * @return string
     */
    public function resize($file, $width = null, $height = null)
    {
        if (is_null($width) && is_null($height)) {
            error_log(new InvalidArgumentException("Width and height can't both be null"));
            return $file;
        }

        // determine resized filename
        $newFile = sprintf('%s/%s/%s-%dx%d.jpg',
            dirname($file),
            $this->folder,
            pathinfo($file, PATHINFO_FILENAME),
            $width,
            $height
        );

        // if we have already resized, just return the existing file
        if (file_exists($newFile))
            return $newFile;

        // load file
        try {
            $image = new Imagick($file);
        } catch (ImagickException $e) {
            error_log($e);
            return $file;
        }

        // calculate the final width and height (keep ratio)
        $originalWidth = $image->getImageWidth();
        $originalHeight = $image->getImageHeight();
        $widthRatio = $originalWidth / ($width ?: 1);
        $heightRatio = $originalHeight / ($height ?: 1);
        if ($widthRatio < 1 || $heightRatio < 1) {
            $resizedWidth = $originalWidth;
            $resizedHeight = $originalHeight;
        } else if ($widthRatio < $heightRatio) {
            $resizedWidth = $width;
            $resizedHeight = round($originalHeight / $widthRatio);
        } else {
            $resizedWidth = round($originalWidth / $heightRatio);
            $resizedHeight = $height;
        }

        // resize and save
        if (!file_exists(pathinfo($newFile, PATHINFO_DIRNAME)))
            mkdir(pathinfo($newFile, PATHINFO_DIRNAME));
        $image->setImageCompressionQuality($this->quality);
        $image->thumbnailImage($resizedWidth, $resizedHeight);
        $image->writeImage($newFile);

        return $newFile;
    }
}
