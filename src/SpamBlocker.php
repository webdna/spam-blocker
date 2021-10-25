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

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['spam-blocker'] = 'spam-blocker/patterns/index';
            $event->rules['spam-blocker/new'] = 'spam-blocker/patterns/edit';
            $event->rules['spam-blocker/edit/<id:\d+>'] = 'spam-blocker/patterns/edit';
        });

        Event::on(Plugins::class, Plugins::EVENT_AFTER_INSTALL_PLUGIN, function (PluginEvent $event) {
            if ($event->plugin === $this) {
            }
        });

        // wheelform
        if (Craft::$app->plugins->isPluginEnabled('wheelform')) {
            Event::on(\wheelform\db\MessageValue::class, \wheelform\db\MessageValue::EVENT_BEFORE_VALIDATE, function (\yii\base\ModelEvent $event) {
                $fieldValues = [$event->sender->getField()->one()->name => $event->sender->getValue()];

                if ($this->_checkPatterns($fieldValues)) {
                    $event->isValid = false;
                }
            });
        }

        //formie requires 1.4.20
        // TODO: version_compare(Craft::$app->getInfo()->version, '3.0', '<')
        if (Craft::$app->plugins->isPluginEnabled('formie')) {
            Event::on(\verbb\formie\services\Submissions::class, \verbb\formie\services\Submissions::EVENT_BEFORE_SPAM_CHECK, function (
                \verbb\formie\events\SubmissionSpamCheckEvent $event
            ) {
                $fieldValues = $this->_getFormieContentAsString($event->submission);

                if ($this->_checkPatterns($fieldValues)) {
                    $event->submission->isSpam = true;
                    $event->submission->spamReason = 'Contains banned keyword';
                }
            });
        }

        Craft::info(Craft::t('spam-blocker', '{name} plugin loaded', ['name' => $this->name]), __METHOD__);
    }

    // Protected Methods
    // =========================================================================

    private function _checkPatterns($values)
    {
        foreach ($this->patterns->getAllPatterns() as $pattern) {
            foreach ($values as $key => $value) {
                // check if string contains
                if (($pattern->name == '*' || $pattern->name == $key) && preg_match('/' . $pattern->value . '/', $value)) {
                    return true;
                }
                /*if (strtolower($pattern->name) && strstr(strtolower($values), strtolower($pattern->name))) {
                    return true;
                }*/
            }
        }

        return false;
    }

    private function _getFormieContentAsString($submission)
    {
        $fieldValues = [];

        if (($fieldLayout = $submission->getFieldLayout()) !== null) {
            foreach ($fieldLayout->getFields() as $field) {
                try {
                    $value = $submission->getFieldValue($field->handle);

                    if ($value instanceof NestedFieldRowQuery) {
                        $values = [];

                        foreach ($value->all() as $row) {
                            $fieldValues[$field->handle] = $this->_getContentAsString($row);
                        }

                        continue;
                    }

                    if ($value instanceof ElementQuery) {
                        $value = $value->one();
                    }

                    if ($value instanceof MultiOptionsFieldData) {
                        $value = implode(
                            ' ',
                            array_map(function ($item) {
                                return $item->value;
                            }, (array) $value)
                        );
                    }

                    $fieldValues[$field->handle] = (string) $value;
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        return $fieldValues;
    }
}
