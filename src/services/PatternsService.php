<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker\services;

use webdna\spamblocker\SpamBlocker;
use webdna\spamblocker\models\PatternModel;
use webdna\spamblocker\records\PatternRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;
use Throwable;

/**
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 */
class PatternsService extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function getAllPatterns()
    {
        $patterns = [];

        foreach ($this->_createPatternQuery()->all() as $record) {
            $patterns[] = new PatternModel($record);
        }

        return $patterns;
    }

    public function getPatternById($id)
    {
        $result = $this->_createPatternQuery()->where(['id' => $id])->one();

        if (!$result) {
            return null;
        }

        return new PatternModel($result);
    }

    public function savePattern($model)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            $record = PatternRecord::findOne($model->id);
            if (!$record) {
                $record = new PatternRecord();
            }

            $record->name = $model->name;
            $record->value = $model->value;

            $record->save();

            $transaction->commit();
            return true;
        } catch (Throwable $e) {
            $transaction->rollBack();
            //throw $e;
            return false;
        }
    }

    public function deletePattern($id)
    {
        $record = PatternRecord::findOne($id);

        if (!$record) {
            return;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            $record->delete();
            $transaction->commit();
            return true;
        } catch (Throwable $e) {
            $transaction->rollBack();
            //throw $e;
            return false;
        }
    }


    private function _createPatternQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'name',
                'value',
            ])
            ->from(['{{%spamblocker_patterns}}']);
    }
}
