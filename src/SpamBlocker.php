<?php
/**
 * Spam Blocker plugin for Craft CMS 5.x
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
    public static ?Plugin $plugin = null;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '0.0.1';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * @var bool
     */
    public bool $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
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
            Event::on(\wheelform\db\MessageValue::class,
            \wheelform\db\MessageValue::EVENT_BEFORE_VALIDATE,
            function (\yii\base\ModelEvent $event) {
                $fieldValues = [$event->sender->getField()->one()->name => $event->sender->getValue()];

                if ($this->_checkPatterns($fieldValues)) {
                    $event->isValid = false;
                }
            });
        }

        // Formie
        if (Craft::$app->plugins->isPluginEnabled('formie')) {
            if (class_exists(\verbb\formie\services\SubmissionWorkflow::class)) {
                // Formie 4+: spam checks run as the `screen.evaluateSpam` workflow task. Marking the
                // submission as spam before that task runs makes Formie skip its own checks and
                // handle it as spam, the same as the old `beforeSpamCheck` event.
                Event::on(\verbb\formie\services\SubmissionWorkflow::class,
                \verbb\formie\services\SubmissionWorkflow::EVENT_BEFORE_TASK,
                function (\verbb\formie\events\SubmissionWorkflowTaskEvent $event) {
                    if ($event->task !== 'screen.evaluateSpam' || !$event->command) {
                        return;
                    }

                    if ($event->command->navigation !== \verbb\formie\enums\NavigationIntent::ADVANCE) {
                        return;
                    }

                    $this->_checkFormieSubmission($event->command->submission);
                });
            } else {
                // Formie 2/3
                Event::on(\verbb\formie\services\Submissions::class,
                \verbb\formie\services\Submissions::EVENT_BEFORE_SPAM_CHECK,
                function (\verbb\formie\events\SubmissionSpamCheckEvent $event) {
                    $this->_checkFormieSubmission($event->submission);
                });
            }
        }

        Craft::info(Craft::t('spam-blocker', '{name} plugin loaded', ['name' => $this->name]), __METHOD__);
    }

    // Protected Methods
    // =========================================================================

    private function _checkPatterns(array $values): bool
    {
        foreach ($this->patterns->getAllPatterns() as $pattern) {
            foreach ($values as $key => $value) {
                // check if string contains
                if (!is_string($value)) {
                    continue;
                }
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

    private function _checkFormieSubmission(?\verbb\formie\elements\Submission $submission): void
    {
        if (!$submission || $submission->isSpam) {
            return;
        }

        if ($this->_checkPatterns($this->_getFormieContent($submission))) {
            $submission->isSpam = true;
            $submission->spamReason = 'Contains banned keyword';
        }
    }

    private function _getFormieContent(\verbb\formie\elements\Submission $submission): array
    {
        $fieldValues = [];

        foreach ($submission->getFields() as $field) {
            try {
                $value = $submission->getFieldValue($field->handle);

                // Let Formie flatten its own value types (options, groups, repeaters, elements, etc.)
                if (method_exists($field, 'getValueAsString')) {
                    $value = $field->getValueAsString($value, $submission);
                }

                $fieldValues[$field->handle] = $this->_valueToString($value);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $fieldValues;
    }

    private function _valueToString(mixed $value): string
    {
        if (is_array($value) || $value instanceof \Traversable) {
            $parts = [];

            foreach ($value as $item) {
                $parts[] = $this->_valueToString($item);
            }

            return implode(' ', array_filter($parts, fn($part) => $part !== ''));
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        return '';
    }
}
