<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker\migrations;

use webdna\spamblocker\SpamBlocker;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
      * @inheritdoc
      */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%spamblocker_patterns}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%spamblocker_patterns}}',
                [
                          'id' => $this->primaryKey(),
                          'dateCreated' => $this->dateTime()->notNull(),
                          'dateUpdated' => $this->dateTime()->notNull(),
                          'uid' => $this->uid(),
                          'name' => $this->string()->notNull()->defaultValue(''),
                          'value' => $this->string()->notNull()->defaultValue(''),
                     ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%spamblocker_patterns}}', ['name', 'value'], true);
        // Additional commands depending on the db driver
        switch ($this->driver) {
                case DbConfig::DRIVER_MYSQL:
                     break;
                case DbConfig::DRIVER_PGSQL:
                     break;
          }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%spamblocker_patterns}}');
    }
}
