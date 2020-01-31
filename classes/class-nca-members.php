<?php

/**
 * Helper class for member functions.
 *
 * @class NCAMembers
 */

add_action( 'after_setup_theme', 'NCAMembers::setup', 10 );

final class NCAMembers {
	
	/**
     * @method setup
     */
    static public function setup() {
		
		// Filters
		add_filter( 'login_redirect',							__CLASS__ . '::login_redirect', 10, 3 );
		add_filter( 'cptui_pre_register_taxonomy', 				__CLASS__ . '::pre_register_member_category', 10, 3 );
		add_filter( 'acf/get_valid_field', 						__CLASS__ . '::pre_render_form', 10, 1 );
		add_filter( 'acf/update_value/name=alternate_photo',	__CLASS__ . '::set_member_logo', 10, 3 );
		add_filter( 'show_admin_bar',							__CLASS__ . '::hide_admin_bar' );
		add_filter( 'wp_dropdown_users_args', 					__CLASS__ . '::add_members_to_dropdown', 10, 2 );
	}
	
	/**
     * @method login_redirect
     */
    static public function login_redirect( $redirect_to, $request, $user ) {
		
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			if ( in_array( 'subscriber', $user->roles ) ) {
				$redirect_to = site_url( '/nca-members-only/' );
			} else if ( in_array( 'member', $user->roles ) ) {
				$posts = get_posts( array( 'post_type' => 'member', 'author' => $user->ID ) );
				$redirect_to = ! empty( $posts ) ? get_permalink( $posts[0]->ID ) : site_url( '/members/' );
			}
		}
		
		return $redirect_to;
	}
	
	/**
     * @method pre_register_member_category
     */
    static public function pre_register_member_category( $args, $taxonomy_slug, $taxonomy ) {
		
		if ( $taxonomy_slug == 'member_category' ) {
			$args['capabilities'] = array(
				'assign_terms' => 'edit_members',
			);
		}
		
		return $args;
	}
	
	/**
     * @method pre_render_form
     */
    static public function pre_render_form( $field ) { 
		
		if ( $field['type'] == 'wysiwyg' && ! is_admin() ) { 
			$field['tabs'] = 'visual';
			$field['toolbar'] = 'basic';
			$field['media_upload'] = 0; 
		} 
		
		return $field;
	}
	
	/**
     * @method set_member_logo
     */
    static public function set_member_logo( $value, $post_id, $field ) {
		
		if ( ! is_admin() && is_single() && get_post_type() == 'member' ) {
			update_post_meta( $post_id, '_thumbnail_id', $value );
		}
		
		return $value;
	}
	
	/**
     * @method hide_admin_bar
     */
	static public function hide_admin_bar( $content ) {
		return ( current_user_can( 'manage_options' ) ) ? $content : false;
	}
	
	/**
     * @method add_members_to_dropdown
     */
	static public function add_members_to_dropdown( $query_args, $r ) {
		
		$query_args['who'] = '';
		$query_args['role__in'] = array('administrator', 'editor', 'author', 'member');
		
		return $query_args;
	}
	
}