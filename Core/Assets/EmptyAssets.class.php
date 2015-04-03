<?php

namespace WPTU\Core\Assets;

/**
 * Stores the EmptyAssets class definition
 */

/**
 * Creates an asset object without any registered javascript nor css
 *
 * This is useful if you really want to optimize js and css
 * for your template
 */
class EmptyAssets extends Assets {
    public function __construct() {
        parent::__construct(AutomaticAssetsManager::getInstance());
    }

    public function register_custom() {
        return $this;
    }

    public function register_standard() {
        return $this;
    }
}