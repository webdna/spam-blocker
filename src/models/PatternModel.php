<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker\models;

use craft\base\Model;

/**
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 */
class PatternModel extends Model
{
    // Public Properties
    // =========================================================================

    public string|int|null $id = null;
    public ?string $name = null;
    public ?string $value = null;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['name', 'value'], 'string'];
        $rules[] = [['name', 'value'], 'required'];

        return $rules;
    }
}
