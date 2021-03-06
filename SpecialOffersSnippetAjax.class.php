<?php 
/**
 * Contains the SpecialOffersSnippetAjax class
 */

/**
 * Hook to WP Ajax system to get the special offers snippet url
 * @author etessore
 * @version 1.0.0
 * @package classes
 *
 */
class SpecialOffersSnippetAjax{
	const ajax_action = 'offer-snippet';
	
	/**
	 * Default constructor
	 */
	public function __construct(){
		$this->hook();
	}
	
	/**
	 * Hooks this feature into wordpress
	 * @return SpecialOffersSnippet $this for chainability
	 */
	public function hook(){
		add_action('wp_ajax_'.self::ajax_action, array(__CLASS__, 'ajax_callback'));
		add_action('wp_ajax_nopriv_'.self::ajax_action, array(__CLASS__, 'ajax_callback'));
		return $this;
	}
	
	/**
	 * Function called by admin-ajax.php
	 */
	public static function ajax_callback(){
		if(empty($_GET['hid'])){ wp_die('hid is required'); }
		unset($_GET['action']);
		die(file_get_contents(SpecialOffersSnippet::calculate_url($_GET)));
	}
}