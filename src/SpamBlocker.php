<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker;

use webdna\spamblocker\services\PatternsService;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class SpamBlocker
 *
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 *
 * @property  SpamBlockerServiceService $spamBlockerService
 */
class SpamBlocker extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var SpamBlocker
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '0.0.1';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'patterns' => PatternsService::class,
        ]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['spam-blocker'] = 'spam-blocker/patterns/index';
                $event->rules['spam-blocker/new'] = 'spam-blocker/patterns/edit';
                $event->rules['spam-blocker/edit/<id:\d+>'] = 'spam-blocker/patterns/edit';
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );


        // wheelform
        if (Craft::$app->plugins->isPluginEnabled('wheelform')) {
            Event::on(
                \wheelform\db\MessageValue::class,
                \wheelform\db\MessageValue::EVENT_BEFORE_VALIDATE,
                function (\yii\base\ModelEvent $event) {
                    foreach ($this->patterns->getAllPatterns() as $pattern) {
                        if (($pattern->name == '*' || $pattern->name == $event->sender->getField()->one()->name) && preg_match('/'.$pattern->value.'/', $event->sender->getValue())) {
                            $event->isValid = false;
                        }
                    }
                }
            );
        }


        Craft::info(
            Craft::t(
                'spam-blocker',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
}
