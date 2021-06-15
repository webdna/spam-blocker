<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker\records;

use webdna\spamblocker\SpamBlocker;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 */
class PatternRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%spamblocker_patterns}}';
    }
}
