<?php

namespace WPTU;

include_once 'Singleton.class.php';
include_once 'Autoloader/ClassAutoloader.class.php';

use WPTU\Core\Helpers\ThemeHelpers;
use WPTU\Core\Metaboxes\MediaManager;
use WPTU\Core\Singleton;
use WPTU\Core\Autoloader\ClassAutoloader;
use WPTU\Core\DebugUtils;

/**
 * Stores the code for the parent theme setting initialization and management
 */


/**
 * Initializes and manages the parent theme config and constants
 * @author etessore
 * @version 1.0.0
 * @package classes
 */
class ThemeUtils extends Singleton{

	/**
	 * Initializes default settings
	 * Singleton private constructor
	 */
	protected function __construct(){
		self::default_constants();
		//self::enable_autoload_system();
		//self::disable_debug();
		//self::register_main_menu();
		//self::register_bottom_menu();
		//self::register_text_domain();
	}

    public static function enable_feature($featureName){
        switch($featureName){
            case 'Autoload':
                self::enable_autoload_system();
                break;
            case 'MediaManager':
                self::enable_media_manager();
                break;
            case 'Debug':
                self::enable_debug();
                break;
            case 'DummyContent':
                self::enable_dummy_content();
                break;
            default:
                throw new \Exception('Feature not found');
                break;
        }
    }
	
	/**
	 * Register the wtu-framework text domain to WordPress
	 */
	public static function register_text_domain(){
		load_theme_textdomain('wtu_framework', WORDPRESS_THEME_UTILS_PATH.'/languages');
	}

	/**
	 * Initializes the autoloader subsystem
	 */
	protected static function enable_autoload_system(){
        ClassAutoloader::getInstance()
            // First search in the child theme dir
            ->addLoadingTemplate(get_stylesheet_directory().'/%classname%.class.php')
            // then search into the parent theme dir
            ->addLoadingTemplate(get_template_directory().'/%classname%.class.php')
            // temporary only: check the todo-move folder for old code
            ->addLoadingTemplate(get_template_directory().'/WPTU/todo-move/%classname%.class.php');
	}

    protected static function enable_media_manager(){
        MediaManager::enable();
    }

	/**
	 * Registers the Primary Menu to WordPress
	 * @param string $label the label for the menu
	 */
	public static function register_main_menu($label=''){
		register_nav_menu('primary', (empty($label)) ? __('Primary Menu', 'theme') : $label);
	}

	/**
	 * Register the Secondary Menu to WordPress
	 * @param string $label the label for the menu
	 */
	public static function register_bottom_menu($label=''){
		register_nav_menu('secondary', (empty($label)) ? __('Secondary Menu', 'theme'): $label);
	}

	/**
	 * Enables vd()\vc()\v() functions output,
	 * This is generically good for development environments.
	 */
	protected static function enable_debug(){
		$debug = DebugUtils::getInstance();
		$debug->status = true;
	}
	
	/**
	 * Enable the Lorem Ipsum body text on empty pages
	 */
	protected static function enable_dummy_content(){
		$dummy_content = new \LipsumGenerator();
		$dummy_content->init()->save()->hook();
	} 

	/**
	 * Register some constants
	 */
	protected static function default_constants(){
		// initialize constants only once
		if(defined('WORDPRESS_THEME_UTILS_PATH')) return;

		/**
		 * The absolute base path for Wordpress Theme Utils
		 */
		if(!defined('WORDPRESS_THEME_UTILS_PATH'))
			define('WORDPRESS_THEME_UTILS_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

		/**
		 * The main directory name for Wordpress Theme Utils
		 */
		if(!defined('WORDPRESS_THEME_UTILS_DIRNAME'))
			define('WORDPRESS_THEME_UTILS_DIRNAME', basename(WORDPRESS_THEME_UTILS_PATH));

		/**
		 * Set to false to disable registration of Top Menu
		 */
		if(!defined('WORDPRESS_THEME_UTILS_REGISTER_TOP_MENU'))
			define('WORDPRESS_THEME_UTILS_REGISTER_TOP_MENU', true);

		/**
		 * Set to false to disable registration of Bottom Menu
		 */
		if(!defined('WORDPRESS_THEME_UTILS_REGISTER_BOTTOM_MENU'))
			define('WORDPRESS_THEME_UTILS_REGISTER_BOTTOM_MENU', true);

		/**
		 * Relative path for template parts
		 */
		if(!defined('WORDPRESS_THEME_UTILS_PARTIALS_RELATIVE_PATH'))
			define('WORDPRESS_THEME_UTILS_PARTIALS_RELATIVE_PATH', 'partials' . DIRECTORY_SEPARATOR);

		/**
		 * Path for libraries
		 */
		if(!defined('WORDPRESS_THEME_UTILS_LIBRARIES_RELATIVE_PATH'))
			define('WORDPRESS_THEME_UTILS_LIBRARIES_RELATIVE_PATH', 'libraries' . DIRECTORY_SEPARATOR);
		
		if(!defined('WORDPRESS_THEME_UTILS_LIBRARIES_ABSOLUTE_PATH'))
			define(
                'WORDPRESS_THEME_UTILS_LIBRARIES_ABSOLUTE_PATH',
                WORDPRESS_THEME_UTILS_PATH . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR
            );

		/**
		 * Relative path for autoloader class
		 */
		if(!defined('WORDPRESS_THEME_UTILS_AUTOLOADER_RELATIVE_PATH'))
			define(
                'WORDPRESS_THEME_UTILS_AUTOLOADER_RELATIVE_PATH',
                DIRECTORY_SEPARATOR . WORDPRESS_THEME_UTILS_DIRNAME . DIRECTORY_SEPARATOR . 'ClassAutoloader.class.php'
            );
	}
}

