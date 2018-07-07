<?php
/**
 * First Image plugin for Craft CMS 3.x
 *
 * A plugin to get first image from Redactor Field.
 *
 * @link      http://sidd3.com
 * @copyright Copyright (c) 2018 Bhashkar Yadav
 */

namespace by\firstimage\twigextensions;

use by\firstimage\FirstImage;
use by\firstimage\libs\HtmlDomParser;

use Craft;

/**
 * @author    Bhashkar Yadav
 * @package   FirstImage
 * @since     1.0.0
 */
class FirstImageTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'FirstImage';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('getFirstImage', [$this, 'get_first_image']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getFirstImage', [$this, 'get_first_image']),
        ];
    }
 
    /**
     * @param null $text
     *
     * @return string
     */
    public function get_first_image($text = null)
    {
        // get DOM from URL or file
        $post_html = HtmlDomParser::str_get_html($text);
        // find all image with full tag
        $first_img = $post_html->find('img', 0);

        if($first_img !== null) {
            return $first_img->src;
        }
    }

}
