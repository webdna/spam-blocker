<?php
/**
 * Spam Blocker plugin for Craft CMS 3.x
 *
 * Add regex tests to form field validation
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2021 webdna
 */

namespace webdna\spamblocker\assetbundles\indexcpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    webdna
 * @package   SpamBlocker
 * @since     0.0.1
 */
class IndexCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@webdna/spamblocker/assetbundles/indexcpsection/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Index.js',
        ];

        $this->css = [
            'css/Index.css',
        ];

        parent::init();
    }
}
