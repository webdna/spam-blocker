<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker\controllers;

use webdna\spamblocker\SpamBlocker;
use webdna\spamblocker\models\PatternModel;

use Craft;
use craft\web\Controller;

/**
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 */
class PatternsController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $patterns = SpamBlocker::$plugin->patterns->getAllPatterns();

        return $this->renderTemplate('spam-blocker/index', compact('patterns'));
    }

    /**
     * @return mixed
     */
    public function actionEdit(int $id = null, PatternModel $pattern = null)
    {
        if (!$pattern) {
            if ($id) {
                $pattern = SpamBlocker::$plugin->patterns->getPatternById($id);
            } else {
                $pattern = new PatternModel();
            }
        }

        return $this->renderTemplate('spam-blocker/_edit', compact('pattern'));
    }

    /**
     * @return mixed
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $id = Craft::$app->getRequest()->getBodyParam('id');

        $pattern = SpamBlocker::$plugin->patterns->getPatternById($id);

        if (!$pattern) {
            $pattern = new PatternModel();
        }

        $pattern->name = Craft::$app->getRequest()->getBodyParam('name');
        $pattern->value = Craft::$app->getRequest()->getBodyParam('value');

        if (SpamBlocker::$plugin->patterns->savePattern($pattern)) {
            Craft::$app->getSession()->setNotice('Pattern saved!');
            $this->redirectToPostedUrl($pattern);
        } else {
            Craft::$app->getSession()->setError("Couldn't save the pattern, this combination might already exist");
            Craft::$app->getUrlManager()->setRouteParams(compact('pattern'));
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function actionDelete()
    {
        $this->requireAcceptsJson();
        $id = Craft::$app->getRequest()->getRequiredParam('id');

        if (SpamBlocker::$plugin->patterns->deletePattern($id)) {
            return $this->asJson(['success' => true]);
        } else {
            return $this->asJson(['error' => "Couldn't delete the pattern"]);
        }
    }
}
