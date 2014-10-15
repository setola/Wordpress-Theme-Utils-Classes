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
		public function __construct($metaname, $metabox, $fields) {
			$this->metabox = array_merge(
				array(
					'id'        => $metaname,
					'title'     => '',
					'post_type' => '',
					'context'   => 'normal',
					'priority'  => 'high',
				), $metabox
			);
			$this->fields = $fields;
			$this->metaname = $metaname;

			wp_register_script(
				'wpu-media-manager-2', get_template_directory_uri() . '/js/media-manager-2.js', array(
					'jquery',
					'media-editor'
				), '1.0.0', true
			);
			add_action(
				'admin_print_scripts', array(
					__CLASS__,
					'enqueue_assets'
				)
			);
		}

		public static function enqueue_assets() {
			wp_enqueue_script('wpu-media-manager-2');
		}

		/**
		 * Prints the metabox markup
		 * @param $post the current post
		 * TODO: support for other input types and select :D
		 */
		public function metabox_html($post) {
			wp_nonce_field($this->metaname, $this->metaname . '_nonce');

			$values = $this->get_meta($post->ID);

			$rows = '';
			foreach ($this->fields as $field) {
				$th = HtmlHelper::standard_tag('th', $field['label'], array('class' => ''));

				switch ($field['type']) {
					case 'callback':
						if (is_callable($field['options']['callback'])) {
							$box = isset($field['options']['box']) ? $field['options']['box'] : array();
							$input = call_user_func_array(
								$field['options']['callback'], array(
									$post,
									$box
								)
							);
						}
						break;

					case 'select':
						$input = HtmlHelper::select(
							$this->metaname . '[' . $field['id'] . ']', $field['options'],
							array('value' => $values[$field['id']])
						);
						break;

					case 'single-attachment':
						$thumb = '';
						if (is_numeric($values[$field['id']])) {
                            $thumb = wp_get_attachment_link($values[$field['id']], 'thumbnail', false, true, false);
						}

                        $button_text = isset($field['parms']['data-button-text']) ? $field['parms']['data-button-text'] : __('Select from Media');
						$input = $thumb . HtmlHelper::input(
								'', 'button', array_merge(
									array(
										'value'                     => $button_text,
										'class'                     => 'media-single-attachment',
										'data-target-id'            => $this->metaname . '-' . $field['id'],
										'data-thumb-id'             => $this->metaname . '-' . $field['id'] . '-thumb'
									), (array)$field['parms']
								)
							) . HtmlHelper::input(
								$this->metaname . '[' . $field['id'] . ']', 'hidden', //todo: change to hidden?
								array_merge(
									array(
										'id'    => $this->metaname . '-' . $field['id'],
										'value' => $values[$field['id']],
										'class' => 'large-text'
									), (array)$field['parms']
								)
							);
						break;

					case 'chebox-list':
						$input = '';
						if (count($field['options'])) {
							foreach ($field['options'] as $key => $option) {
								$input .= HtmlHelper::input(
										$this->metaname . '[' . $field['id'] . '][' . $key . ']', 'checkbox', array(
											'id'      => 'chebox-list-' . $field['id'] . '-' . $key,
											'class'   => '',
											'checked' => empty($values[$field['id']][$key]) ? '' : 'checked'
										)
									) . PHP_EOL . HtmlHelper::label(
										$option, 'chebox-list-' . $field['id'] . '-' . $key
									) . PHP_EOL . HtmlHelper::br();
							}
						}
						break;

					case 'checkbox':
						$input = HtmlHelper::input(
							$this->metaname . '[' . $field['id'] . ']', $field['type'], array(
								'class'   => '',
								'checked' => empty($values[$field['id']]) ? '' : 'checked'
							)
						);
						break;

					case 'text':
					default:
						$input = HtmlHelper::input(
							$this->metaname . '[' . $field['id'] . ']', $field['type'], array_merge(
								array(
									'value' => $values[$field['id']],
									'class' => 'large-text'
								), (array)$field['parms']
							)
						);
						break;
				}

				if (!empty($field['description'])) {
					$input .= HtmlHelper::br() . HtmlHelper::paragraph(
							$field['description'], array('class' => 'description')
						);
				}

				$td = HtmlHelper::standard_tag('td', $input, array('class' => ''));
				$tr = HtmlHelper::standard_tag('tr', $th . "\n" . $td);
				$rows .= $tr;
			}

			echo HtmlHelper::standard_tag(
				'table', HtmlHelper::standard_tag('tbody', $rows), array('class' => 'form-table')
			);
		}

		/**
		 * Registers the metabox on WordPress
		 */
		public function register_metaboxes() {
			add_meta_box(
				$this->metabox['id'], $this->metabox['title'], array(
					&$this,
					'metabox_html'
				), $this->metabox['post_type'], $this->metabox['context'], $this->metabox['priority']
			);
		}

		/**
		 * Saves the metabox data while saving the page
		 */
		public function save_metabox_data($post_id) {
			if (!isset($post_id))
				return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
				return $post_id;
			if (!isset($_POST['post_type']))
				return $post_id;
			// First we need to check if the current user is authorised to do this action.
			if ('page' == $_POST['post_type']) {
				if (!current_user_can('edit_page', $post_id))
					return;
			}
			else {
				if (!current_user_can('edit_post', $post_id))
					return;
			}

			// Secondly we need to check if the user intended to change this value.
			wp_verify_nonce($_POST[$this->metaname . '_nonce'], $this->metaname);
			// TODO: understand how to make this check working :|
			//check_admin_referer($this->metaname, $this->metaname . '_nonce');

			array_walk_recursive(
				$_POST[$this->metaname], array(
					__CLASS__,
					'sanitize_r'
				)
			);

			update_post_meta($post_id, $this->metaname, $_POST[$this->metaname]);
		}

		public static function sanitize_r(&$val, $key) {
			$val = sanitize_text_field($val);
		}

		/**
		 * Retrieves the post meta content.
		 * If a field is not set or empty, the value of the array
		 * element with such key will be an empty string,
		 * just to avoid warnings on non illegal offsets
		 * @param $post_id
		 * @return array unserialized values for the meta
		 */
		public function get_meta($post_id = null) {
			if (is_null($post_id))
				$post_id = get_the_ID();
			$values = get_post_meta($post_id, $this->metaname, true);

			foreach ($this->fields as $field) {
				if (empty($values[$field['id']])) {
					$values[$field['id']] = '';
				}
			}

			return $values;
		}
	}
