<?php

namespace WPTU\Core\Assets;

/**
 * Class Assets
 * Manages registration and enqueing of javascript and css
 * Useful if you like to choose which assets use in a template part
 * @see AutomaticAssetsManager
 * @package WPTU\Core\Assets
 */
abstract class Assets{
    protected $assets_manager;

    public function __construct($asset_manager){
        $this
            ->set_assets_manager($asset_manager)
            ->register_standard()
            ->register_custom();
    }

    /**
     * Registers some standard use assets
     * Use this for css\js libraries for ex. jQuery, Bootstrap and so on
     * @return Assets $this for chainability
     */
    abstract function register_standard();

    /**
     * Registers some custom assets
     * Use this for register your js and css
     * @return Assets $this for chainability
     */
    abstract function register_custom();

    /**
     * Sets the given manager as the current assets manager
     * @param $manager the assets manager
     * @return Assets $this for chainability
     */
    public function set_assets_manager($manager){
        $this->assets_manager = $manager;
        return $this;
    }

    /**
     * Adds a javascript to the current set
     * @see @link http://codex.wordpress.org/Function_Reference/wp_register_script
     * @param string $handle Script name
     * @param string $src Script url
     * @param array $deps (optional) Array of script names on which this script depends
     * @param string|bool $ver (optional) Script version (used for cache busting), set to NULL to disable
     * @param bool $in_footer (optional) Whether to enqueue the script before </head> or before </body>
     * @return DefaultAssets $this for chainability
     */
    public function add_js($handle, $src, $deps = array(), $ver = null, $in_footer = false){
        $this->assets_manager->add_js($handle, $src, $deps, $ver, $in_footer);
        return $this;
    }

    /**
     * Adds a css to the current set
     * @see @link http://codex.wordpress.org/Function_Reference/wp_register_style
     * @param string $handle Name of the stylesheet.
     * @param string|bool $src Path to the stylesheet from the root directory of WordPress. Example: '/css/mystyle.css'.
     * @param array $deps Array of handles of any stylesheet that this stylesheet depends on.
     *  (Stylesheets that must be loaded before this stylesheet.) Pass an empty array if there are no dependencies.
     * @param string|bool $ver String specifying the stylesheet version number. Set to NULL to disable.
     *  Used to ensure that the correct version is sent to the client regardless of caching.
     * @param string $media The media for which this stylesheet has been defined.
     * @return DefaultAssets $this for chainability
     */
    public function add_css($handle, $src, $deps = array(), $ver = null, $media = false){
        $this->assets_manager->add_css($handle, $src, $deps, $ver, $media);
        return $this;
    }
}