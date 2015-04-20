<?php

namespace WPTU\Core;

include_once 'Singleton.class.php';
include_once 'Autoloader/ClassAutoloader.class.php';

use WPTU\Core\Assets\Assets;
use WPTU\Core\Helpers\LoremIpsum\LipsumGenerator;
use WPTU\Core\Helpers\ThemeHelpers;
use WPTU\Core\Metaboxes\MediaManager;
use WPTU\Core\Singleton;
use WPTU\Core\Autoloader\ClassAutoloader;
use WPTU\Core\Helpers\DevUtils\DebugUtils;

/**
 * Stores the code for the parent theme setting initialization and management
 */


/**
 * Initializes and manages the parent theme config and constants
 * @property Assets asset_manager stores the asset manager
 * @property Array enabled_features stores a list of already enabled features
 * @author etessore
 * @version 1.0.0
 * @package classes
 */
class ThemeUtils extends Singleton {
    const FEATURE_AUTOLOAD = 1;
    const FEATURE_MEDIAMANAGER = 2;
    const FEATURE_DEBUG = 3;
    const FEATURE_DUMMYCONTENT = 4;

    private $enabled_features;
    private $assets_manager;

    /**
     * Initializes default settings
     * Singleton private constructor
     */
    protected function __construct() {
        $this->enabled_features = array();
        $this
            ->default_constants()
            ->register_text_domain();
        //self::enable_autoload_system();
        //self::disable_debug();
        //self::register_main_menu();
        //self::register_bottom_menu();
        //self::register_text_domain();
    }

    /**
     * Enables the given feature
     * @param $feature_name int one of the class FEATURE_* constants
     * @return $this ThemeUtils for chainability
     * @throws \Exception if the given feature is already enabled
     */
    public function enable_feature($feature_name) {

        if (in_array($feature_name, $this->enabled_features))
            throw new \Exception(sprintf('Feature "%s" is already enabled', $feature_name));

        if(is_array($feature_name)){
            foreach($feature_name as $fn){
                $this->_enable_feature($fn);
            }
        } else {
            $this->_enable_feature($feature_name);
        }

        return $this;
    }

    /**
     * @param $feature_name
     * @throws \Exception if $featureName is not recognized
     */
    protected function _enable_feature($feature_name) {
        switch ($feature_name) {
            case self::FEATURE_AUTOLOAD:
                $this->enable_autoload_system();
                break;
            case self::FEATURE_MEDIAMANAGER:
                $this->enable_media_manager();
                break;
            case self::FEATURE_DEBUG:
                $this->enable_debug();
                break;
            case self::FEATURE_DUMMYCONTENT:
                $this->enable_dummy_content();
                break;
            default:
                throw new \Exception('Feature not found');
                break;
        }
    }

    /**
     * Sets the assets manager
     * @param Assets $assets_manager
     * @return $this ThemeUtils for chainability
     */
    public function set_assets_manager(Assets $assets_manager){
        $this->assets_manager = $assets_manager;

        return $this;
    }

    /**
     * Retrieves the assets manager for this theme
     * @return Assets the assets manager for this theme
     */
    public function get_assets_manager(){
        return $this->assets_manager;
    }

    /**
     * Register the wtu-framework text domain to WordPress
     */
    public function register_text_domain() {
        load_theme_textdomain(WORDPRESS_THEME_UTILS_TEXTDOMAIN, WORDPRESS_THEME_UTILS_PATH . '/languages');

        return $this;
    }

    /**
     * Initializes the autoloader subsystem
     */
    protected function enable_autoload_system() {
        $this->enabled_features[] = self::FEATURE_AUTOLOAD;
        ClassAutoloader::getInstance()
            // First search in the child theme dir
            ->addLoadingTemplate(get_stylesheet_directory() . '/%classname%.class.php')
            // then search into the parent theme dir
            ->addLoadingTemplate(get_template_directory() . '/%classname%.class.php')
            // temporary only: check the todo-move folder for old code
            ->addLoadingTemplate(get_template_directory() . '/WPTU/todo-move/%classname%.class.php');

        return $this;
    }

    /**
     * Enables the Media Manager feature
     * @return $this ThemeUtils for chainability
     */
    protected function enable_media_manager() {
        $this->enabled_features[] = self::FEATURE_MEDIAMANAGER;
        MediaManager::enable();
        return $this;
    }

    /**
     * Enables vd()\vc()\v() functions output,
     * This is generically good for development environments.
     * @return $this ThemeUtils for chainability
     */
    protected function enable_debug() {
        $this->enabled_features[] = self::FEATURE_DEBUG;
        $debug = DebugUtils::getInstance();
        $debug->status = true;
        return $this;
    }

    /**
     * Enable the Lorem Ipsum body text on empty pages
     * @return $this ThemeUtils for chainability
     */
    protected function enable_dummy_content() {
        $this->enabled_features[] = self::FEATURE_DUMMYCONTENT;
        $dummy_content = new LipsumGenerator();
        $dummy_content->init()->save()->hook();

        return $this;
    }

    /**
     * Register some constants
     * @return $this ThemeUtils for chainability
     */
    protected function default_constants() {
        // initialize constants only once
        if (defined('WORDPRESS_THEME_UTILS_PATH')) return $this;

        /**
         * The absolute base path for Wordpress Theme Utils
         */
        if (!defined('WORDPRESS_THEME_UTILS_PATH'))
            define('WORDPRESS_THEME_UTILS_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

        /**
         * The main directory name for Wordpress Theme Utils
         */
        if (!defined('WORDPRESS_THEME_UTILS_DIRNAME'))
            define('WORDPRESS_THEME_UTILS_DIRNAME', basename(WORDPRESS_THEME_UTILS_PATH));

        /**
         * Relative path for template parts
         */
        if (!defined('WORDPRESS_THEME_UTILS_PARTIALS_RELATIVE_PATH'))
            define('WORDPRESS_THEME_UTILS_PARTIALS_RELATIVE_PATH', 'partials' . DIRECTORY_SEPARATOR);

        /**
         * Path for libraries
         */
        if (!defined('WORDPRESS_THEME_UTILS_LIBRARIES_RELATIVE_PATH'))
            define('WORDPRESS_THEME_UTILS_LIBRARIES_RELATIVE_PATH', 'libraries' . DIRECTORY_SEPARATOR);

        if (!defined('WORDPRESS_THEME_UTILS_LIBRARIES_ABSOLUTE_PATH'))
            define(
            'WORDPRESS_THEME_UTILS_LIBRARIES_ABSOLUTE_PATH',
                WORDPRESS_THEME_UTILS_PATH . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR
            );

        /**
         * Relative path for autoloader class
         */
        if (!defined('WORDPRESS_THEME_UTILS_AUTOLOADER_RELATIVE_PATH'))
            define(
            'WORDPRESS_THEME_UTILS_AUTOLOADER_RELATIVE_PATH',
                DIRECTORY_SEPARATOR . WORDPRESS_THEME_UTILS_DIRNAME . DIRECTORY_SEPARATOR . 'ClassAutoloader.class.php'
            );

        if (!defined('WORDPRESS_THEME_UTILS_TEXTDOMAIN'))
            define('WORDPRESS_THEME_UTILS_TEXTDOMAIN', 'wtu_framework');

        return $this;
    }
}

