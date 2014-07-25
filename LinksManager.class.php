<?php
/*
 * stores the class for LinksManager feature
 */

/**
 * Manages the links subsection
 *
 * Adds support for link image and translations
 * @author etessore
 * @version 1.0.1
 *
 */
class LinksManager extends Singleton {

	const CUSTOM_LINKS_POST_TYPE = 'wtu-custom-links';

	public $metabox;

	/**
	 * Initializes default settings
	 * Singleton private constructor
	 */
	protected function __construct() {
		$this->metabox = new SimpleMetabox(
			'_link_details',
			array(
				//'id'        =>  $id,
				'title'     => __( 'Link Details', 'wtu_framework' ),
				'post_type' => self::CUSTOM_LINKS_POST_TYPE
			),
			array(
				array(
					'id'    => 'title',
					'label' => __( 'Title', 'wtu_framework' ),
					'type'  => 'text'
				),
				array(
					'id'    => 'label',
					'label' => __( 'Label', 'wtu_framework' ),
					'type'  => 'text'
				),
				array(
					'id'    => 'open_new_tab',
					'label' => __( 'Open in new tab', 'wtu_framework' ),
					'type'  => 'checkbox'
				),
				array(
					'id'    => 'noindex',
					'label' => __( 'Noindex', 'wtu_framework' ),
					'type'  => 'text'
				),
			)
		);

		add_action( 'init', array( &$this, 'init' ) );
		add_filter( 'gettext', array( &$this, 'custom_title' ) );
		add_action( 'add_meta_boxes', array( &$this->metabox, 'register_metaboxes' ) );
		add_action( 'add_meta_boxes', array( &$this, 'remove_useless_metaboxes' ), 99 );
		add_action( 'save_post', array( &$this->metabox, 'save_metabox_data' ) );
	}

	/**
	 * Called by WordPress on init
	 */
	public function init() {
		$this->register_custom_types();
		add_post_type_support( self::CUSTOM_LINKS_POST_TYPE, 'thumbnail' );
		if ( is_admin() ) {

		} else {

		}
	}

	/**
	 * Callback to modify the title field in the admin panel
	 *
	 * @param unknown $input
	 *
	 * @return string|unknown
	 */
	public function custom_title( $input ) {

		global $post_type;

		if ( is_admin() && 'Enter title here' == $input && self::CUSTOM_LINKS_POST_TYPE == $post_type ) {
			return __( 'Enter URL here', 'wtu_framework' );
		}

		return $input;
	}

	/**
	 * Register some custom post types and categories
	 */
	public function register_custom_types() {
		register_post_type(
			self::CUSTOM_LINKS_POST_TYPE,
			array(
				'label'               => 'Links',
				'description'         => __( 'A translable link with image', 'wtu_framework' ),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'rewrite'             => array( 'slug' => '' ),
				'query_var'           => true,
				'exclude_from_search' => true,
				'supports'            => array( 'title', 'thumbnail', 'author' ),
				'taxonomies'          => array( 'category', ),
				'labels'              => array(
					'name'               => __( 'Links', 'wtu_framework' ),
					'singular_name'      => __( 'Link', 'wtu_framework' ),
					'menu_name'          => __( 'Links', 'wtu_framework' ),
					'add_new'            => __( 'Add Link', 'wtu_framework' ),
					'add_new_item'       => __( 'Add New Link', 'wtu_framework' ),
					'edit'               => __( 'Edit', 'wtu_framework' ),
					'edit_item'          => __( 'Edit Link', 'wtu_framework' ),
					'new_item'           => __( 'New Link', 'wtu_framework' ),
					'view'               => __( 'View Link', 'wtu_framework' ),
					'view_item'          => __( 'View Link', 'wtu_framework' ),
					'search_items'       => __( 'Search Links', 'wtu_framework' ),
					'not_found'          => __( 'No Links Found', 'wtu_framework' ),
					'not_found_in_trash' => __( 'No Links Found in Trash', 'wtu_framework' ),
					'parent'             => __( 'Parent Link', 'wtu_framework' ),
				),
			)
		);
	}

	/**
	 * Register the link details metabox and removes some useless one
	 */
	public static function remove_useless_metaboxes() {
		remove_meta_box( 'fbseo', self::CUSTOM_LINKS_POST_TYPE, 'normal' );
		remove_meta_box( 'wpseo_meta', self::CUSTOM_LINKS_POST_TYPE, 'normal' );
	}

	/**
	 * Retrieves a list of links
	 *
	 * @param array $args
	 *
	 * @return array links list
	 */
	public static function get_links( $args = array() ) {
		$defaults = array(
			'post_type' => self::CUSTOM_LINKS_POST_TYPE
		);
		$args     = wp_parse_args( $args, $defaults );

		return get_posts( $args );
	}
}