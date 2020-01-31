<?php

/**
 * Helper class for form functions.
 *
 * @class NCAForms
 */

if ( ! class_exists('GF_Field_Columns') && class_exists('GF_Field') ) {
	
add_action( 'init', 'NCAForms::setup', 10 );

final class NCAForms {
	
	/**
     * @method setup
     */
    static public function setup() {
		
		GF_Fields::register( new GF_Field_Columns() );
		
		// Actions
		add_action( 'gform_field_standard_settings', 	__CLASS__ . '::add_gf_field_column_settings', 10, 2 );
		
		// Filters
		add_filter( 'gform_get_form_filter', 			__CLASS__ . '::filter_gf_get_form_filter', 10, 2 );
		add_filter( 'gform_field_container', 			__CLASS__ . '::filter_gf_field_column_container', 10, 6 );
		add_filter( 'gform_pre_render', 				__CLASS__ . '::filter_gf_multi_column_pre_render', 10, 3 );
	}
	
	/**
     * @method add_gf_field_column_settings
     */
    static public function add_gf_field_column_settings( $placement, $form_id ) {
		
		if ( $placement == 0 ) {
			$description = 'Column breaks should be placed between fields to split form into separate columns. You do not need to place any column breaks at the beginning or end of the form, only in the middle.';
			
			echo '<li class="column_description field_setting">' . $description . '</li>';
		}
	}
	
	/**
     * @method filter_gf_get_form_filter
     */
    static public function filter_gf_get_form_filter( $form_string, $form ) {
		
		if ( ! is_admin() ) {
			
			$column_count = NCAForms::get_column_count( $form );
			if ( $column_count > 0 ) {
				$form_string = str_replace( "class='gform_fields", "class='gform_fields gf_column", $form_string );
			}
		}
		
		return $form_string;
	}
	
	/**
     * @method filter_gf_field_column_container
     */
    static public function filter_gf_field_column_container( $field_container, $field, $form, $css_class, $style, $field_content ) {
		
		if ( is_admin() ) 
			return $field_container;
		
		if ( $field['type'] == 'column' ) {
			
			$column_index = 2;
			foreach ( $form['fields'] as $form_field ) {
				
				if ( $form_field['id'] == $field['id'] )
					break;
				
				if ( $form_field['type'] == 'column' )
					$column_index++;
			}
			
			return '</ul><ul class="' . GFCommon::get_ul_classes( $form ) . ' gf_column">';
		}
		
		return $field_container;
	}

	/**
     * @method filter_gf_multi_column_pre_render
     */
    static public function filter_gf_multi_column_pre_render( $form, $ajax, $field_values ) {
		
		$column_count = NCAForms::get_column_count( $form );
		
		if ( $column_count > 0 && empty( $prev_page_field ) ) {
			$form['cssClass'] = trim( ( isset( $form['cssClass'] ) ? $form['cssClass'] : '') . ' gform_multi_column gform_column_count_' . ( $column_count + 1 ) );
		} else if ( $column_count > 0 ) {
			$prev_page_field['cssClass'] = trim( ( isset( $prev_page_field['cssClass'] ) ? $prev_page_field['cssClass'] : '') . ' gform_page_multi_column gform_page_column_count_' . ( $column_count + 1 ) );
		}
		
		return $form;
	}
	
	/**
     * @method get_column_count
     */
    static private function get_column_count( $form ) {
		
		$column_count = 0;
		$prev_page_field = null;
		
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] == 'column' ) {
				$column_count++;
			} else if ( $field['type'] == 'page' ) {
				if ( $column_count > 0 && empty( $prev_page_field ) ) {
					$form['firstPageCssClass'] = trim( ( isset( $field['firstPageCssClass'] ) ? $field['firstPageCssClass'] : '') . ' gform_page_multi_column gform_page_column_count_'.( $column_count + 1 ) );
				} else if ( $column_count > 0 ) {
					$prev_page_field['cssClass'] = trim( ( isset( $prev_page_field['cssClass'] ) ? $prev_page_field['cssClass'] : '') . ' gform_page_multi_column gform_page_column_count_' . ( $column_count + 1 ) );
				}
				$prev_page_field = $field;
				$column_count = 0;
			}
		}
		
		return $column_count;
	}
}
	
final class GF_Field_Columns extends GF_Field {
	
	public $type = 'column';

	public function get_form_editor_field_title() {
		return esc_attr__('Column Break', 'gravityforms');
	}

	public function is_conditional_logic_supported() {
		return false;
	}

	function get_form_editor_field_settings() {
		return array(
			'column_description',
			'css_class_setting'
		);
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		return '';
	}

	public function get_field_content( $value, $force_frontend_label, $form ) {

		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor = $this->is_form_editor();
		$is_admin = $is_entry_detail || $is_form_editor;

		if ( $is_admin ) {
			$admin_buttons = $this->get_admin_buttons();
			return $admin_buttons . '<label class=\'gfield_label\'>' . $this->get_form_editor_field_title() . '</label>{FIELD}<hr>';
		}

		return '';
	}
}

}