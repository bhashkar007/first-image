<?php
/**
 * First Image plugin for Craft CMS 5.x
 *
 * Get the first image from a rich text (Redactor / CKEditor) field.
 *
 * @link      https://uxi360.com
 */

namespace uxi360\firstimage;

use Craft;
use craft\base\Plugin;
use uxi360\firstimage\twigextensions\FirstImageTwigExtension;

/**
 * @author    UXI360 Team
 * @since     3.0.0
 */
class FirstImage extends Plugin
{
    /**
     * @inheritdoc
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        Craft::$app->getView()->registerTwigExtension(new FirstImageTwigExtension());
    }
}
