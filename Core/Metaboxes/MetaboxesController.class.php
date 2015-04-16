<?php
/**
 * stores MetaboxesController class definition
 */

namespace WPTU\Core\Metaboxes;
use WPTU\Core\TemplateChecker;

/**
 * Generic abstract metaboxes controller: decides which metaboxes are to be shown on the various page of the website
 */
abstract class MetaboxesController {
	/**
	 * @var array stores the list of MetaboxInfo objects
	 */
	protected $metabox_infos;

	/**
	 * @var array list of metaboxes to be shown on the current page
	 */
	protected $enabled_metaboxes = null;

	/**
	 * Build a new object
	 *
	 * @param $metabox_infos array list of MetaboxInfo
	 */
	protected function __construct( $metabox_infos ) {
		$this->metabox_infos = $metabox_infos;
		add_action( 'add_meta_boxes', array( &$this, 'register_admin' ) );
		add_action( 'save_post', array( &$this, 'save_metabox_data' ), 1 );
	}

	/**
	 * Populates the $this->enabled_metaboxes list
	 */
	public function filter_metaboxes() {
		if ( ! is_null( $this->enabled_metaboxes ) ) {
			return;
		}

		$this->enabled_metaboxes = array();
		$template                = get_post_meta( $_GET['post'], '_wp_page_template', true );

		foreach ( $this->metabox_infos as $metabox_info ) {
			$template_checker = new TemplateChecker( $metabox_info->positions['include'], $metabox_info->positions['exclude'] );
			if (
				( $template == 'default' || $template == '' )
				&& (
					get_the_ID() == get_option( 'page_on_front' )
					|| get_option( 'page_on_front' ) == $_GET['post']
					|| get_option( 'page_on_front' ) == $_POST['post_ID']
				)
			) {
				$template = 'front-page.php';
			}

			if ( $template_checker->check( $template ) ) {
				$this->enabled_metaboxes[] = $metabox_info->metabox;
			}
		}
	}

	/**
	 * Register the metaboxes to the WordPress admin page
	 */
	public function register_admin() {
		$this->filter_metaboxes();
		foreach ( $this->enabled_metaboxes as $metabox ) {
			$metabox->register_metaboxes();
		}
	}

	/**
	 * Saves the metabox data on post_save action
	 *
	 * @param $post_id the post ID
	 */
	public function save_metabox_data( $post_id ) {
		$this->filter_metaboxes();
		foreach ( $this->enabled_metaboxes as $metabox ) {
			$metabox->save_metabox_data( $post_id );
		}

	}

	/**
	 * Retrieves the values stored in the given metabox_id post meta
	 *
	 * @param $metabox_id the metabox ID
	 * @param null $post_id the post ID
	 *
	 * @return mixed
	 */
	public function get_value( $metabox_id, $post_id = null ) {
		foreach ( $this->metabox_infos as $metabox_info ) {
			if ( $metabox_info->metabox->metabox['id'] == $metabox_id ) {
				return $metabox_info->metabox->get_meta( $post_id );
			}
		}

	}

	/**
	 * Retrieves a list of values for the metaboxes created by the given $classname class
	 *
	 * @param $classname the name of the class
	 * @param null $post_id the post ID
	 *
	 * @return array
	 */
	public function get_values_by_classname( $classname, $post_id = null ) {
		$toret = array();
		foreach ( $this->metabox_infos as $metabox_info ) {
			if ( get_class( $metabox_info->metabox ) == $classname ) {
				$toret[] = $metabox_info->metabox->get_meta( $post_id );
			}
		}

		return $toret;
	}
}