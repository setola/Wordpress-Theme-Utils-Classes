<?php
/**
 * Created by PhpStorm.
 * User: etessore
 * Date: 24/07/2014
 * Time: 15:35
 */


class SimpleMetabox {
	public $metabox;
	public $fields;
	public $metaname;

	/**
	 * Builds the object
	 * @param string $metaname the post meta name where WordPress will store the infos
	 * @param array $metabox metabox properties array('id'=>'','title'=>'', 'post_type'=>'', 'context'=>'', 'priority'=>'')
	 * @param array $fields list of fields array of array('id'=>'changeme', 'label'=>'Change Me', 'type'=>'')
	 */
	public function __construct($metaname, $metabox, $fields){
		$this->metabox = array_merge(
			array(
				'id'        =>  $metaname,
				'title'     =>  '',
				'post_type' =>  '',
				'context'   =>  'advanced',
				'priority'  =>  'default',
			),
			$metabox
		);
		$this->fields = $fields;
		$this->metaname = $metaname;
	}

	/**
	 * Prints the metabox markup
	 * @param $post the current post
	 */
	public function metabox_html($post){
		wp_nonce_field(__FILE__, $this->metaname.'_nonce');
		$values = $this->get_meta($post->ID);

		$rows = '';
		foreach($this->fields as $field){
			$th = HtmlHelper::standard_tag('th', $field['label'], array('class'=>''));
			$td = HtmlHelper::standard_tag(
				'td',
				HtmlHelper::input(
					$this->metaname.'['.$field['id'].']',
					$field['type'],
					array('value'=>$values[$field['id']], 'class'=>'large-text')
				),
				array('class'=>'')
			);
			$tr = HtmlHelper::standard_tag('tr', $th."\n".$td);
			$rows .= $tr;
		}

		echo HtmlHelper::standard_tag('table', HtmlHelper::standard_tag('tbody', $rows), array('class'=>'form-table'));
	}

	/**
	 * Registers the metabox on WordPress
	 */
	public function register_metaboxes(){
		add_meta_box(
			$this->metabox['id'],
			$this->metabox['title'],
			array(&$this, 'metabox_html'),
			$this->metabox['post_type'],
			$this->metabox['context'],
			$this->metabox['priority']
		);
	}

	/**
	 * Saves the metabox data while saving the page
	 */
	public function save_metabox_data($post_id){
		if(!isset($post_id)) return;
		if(!isset($_POST['post_type'])) return;
		// First we need to check if the current user is authorised to do this action.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
		}

		// Secondly we need to check if the user intended to change this value.
		if (
			!isset($_POST[$this->metaname.'_nonce'])
			|| !wp_verify_nonce($_POST[$this->metaname.'_nonce'], __FILE__)
		) {
			return;
		}

		if(is_array($_POST[$this->metaname])){
			foreach($_POST[$this->metaname] as $k => $v){
				$_POST[$this->metaname][$k] = sanitize_text_field($v);
			}
		}

		update_post_meta($post_id, $this->metaname, $_POST[$this->metaname]);
	}

	/**
	 * Retrieves the post meta content.
	 * If a field is not set or empty, the value of the array
	 * element with such key will be an empty string,
	 * just to avoid warnings on non illegal offsets
	 * @param $post_id
	 * @return array unserialized values for the meta
	 */
	public function get_meta($post_id=null){
		if(is_null($post_id)) $post_id = get_the_ID();
		$values = get_post_meta($post_id, $this->metaname, true);

		foreach($this->fields as $field){
			if(empty($values[$field['id']])){
				$values[$field['id']] = '';
			}
		}

		return $values;

		v($this->fields);
		v(get_post_meta($post_id, $this->metaname, true));
		return array_merge($this->fields, (array)get_post_meta($post_id, $this->metaname, true));
	}
} 