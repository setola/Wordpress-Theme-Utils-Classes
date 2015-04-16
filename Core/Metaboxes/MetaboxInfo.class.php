<?php
/**
 * stores MetaboxInfo class definition
 */

namespace WPTU\Core\Metaboxes;
use WPTU\Core\Metaboxes\SimpleMetabox;

/**
 * Manages infos on a single metabox
 */
class MetaboxInfo{
	/**
	 * @var SimpleMetabox stores the metabox
	 */
	private $metabox;

	/**
	 * @var array include and exclude parameter list
	 */
	private $positions;

	/**
	 * Builds a new object
	 * @param $positions array include and exclude parameter list
	 * @param $metabox SimpleMetabox the metabox object
	 */
	public function __construct( $positions, $metabox ) {
		$this->metabox   = $metabox;
		$this->positions = array_merge(
			array( 'include' => '', 'exclude' => '' ),
			$positions
		);
	}

	/**
	 * Magic method to retrieve privates class fields
	 * @param $what the field name
	 * @return mixed the field value
	 */
	public function __get( $what ) {
		return $this->{$what};
	}
}