<?php
/**
 * First Image plugin for Craft CMS 4.x
 *
 * A plugin to get first image from Redactor Field.
 *
 * @link      https://360adaptive.com
 * @copyright Copyright (c) 2018 Bhashkar Yadav
 */

namespace by\firstimage;

use by\firstimage\twigextensions\FirstImageTwigExtension;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

/**
 * Class FirstImage
 *
 * @author    Bhashkar Yadav
 * @package   FirstImage
 * @since     2.0.0
 *
 */
class FirstImage extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var FirstImage
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new FirstImageTwigExtension());

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'first-image',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
