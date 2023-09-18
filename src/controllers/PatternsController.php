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

use yii\web\Response;

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
     * @inheritdoc
     */
    protected array|bool|int $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionIndex(): Response
    {
        $patterns = SpamBlocker::$plugin->patterns->getAllPatterns();

        return $this->renderTemplate('spam-blocker/index', compact('patterns'));
    }

    /**
     * @return Response
     */
    public function actionEdit(int $id = null, PatternModel $pattern = null): Response
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
     * @return Response
     */
    public function actionSave(): Response
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
     * @return Response
     */
    public function actionDelete(): Response
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
