<?php

include_once 'Core/ThemeUtils.class.php';

/**
 * Initializes the WordPress Theme Utils.
 * Use this in your child theme functions.php
 */
function wtu_init(){
    \WPTU\ThemeUtils::getInstance();

    /**
     * Register some standard assets
     *
     * overload the global $assets variable in your child theme functions.php if you need customization on this.
     * @see DefaultAssets for adding or remove assets
     */
    /*global $assets;
    if(empty($assets)){
        $assets = new DefaultAssetsCDN();
    }*/

    /**
     * Register runtime infos, useful for javascript
     *
     * Overload the global $runtime_infos in your child theme functions.php if you need customization on this.
     * @see RuntimeInfos for more details
     */
    /*global $runtime_infos;
    if(empty($runtime_infos)){
        $runtime_infos = new RuntimeInfos();
        $runtime_infos->hook();
    }*/
}