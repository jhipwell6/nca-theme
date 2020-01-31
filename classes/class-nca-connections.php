<?php

/**
 * Helper class for field connections.
 *
 * @class NCAConnections
 */

add_action( 'after_setup_theme', 'NCAConnections::setup', 10 );

final class NCAConnections {
	
	/**
     * @method setup
     */
    static public function setup() {
		
		// Actions
		add_action( 'fl_page_data_add_properties', __CLASS__ . '::add_post_properties', 10 );
	}
	
	/**
     * @method add_post_properties
     */
	static public function add_post_properties() {
		
		if ( class_exists('FLPageData') ) {
			
			FLPageData::add_site_property( 'first_post', array(
				'label'       => __( 'First Post', 'nca-theme' ),
				'group'       => 'site',
				'type'        => 'all',
				'getter'      => 'NCAConnections::fc_get_first_post',
			) );
			
			FLPageData::add_site_property_settings_fields( 'first_post', array(
				'field'          =>  array(
					'type'         => 'select',
					'label'         => __( 'Field', 'nca-theme' ),
					'default'       => 'title',
					'options'       => array(
						'title'		    => __( 'Title', 'nca-theme' ),
						'excerpt'		=> __( 'Excerpt', 'nca-theme' ),
						'url'			=> __( 'URL', 'nca-theme' ),
						'image'			=> __( 'Featured Image', 'nca-theme' ),
					),
					'toggle'  => array(
						'excerpt' => array(
							'fields' => array( 'length', 'more' ),
						),
					),
				),
				'length' => array(
					'type'        => 'text',
					'label'       => __( 'Length', 'nca-theme' ),
					'default'     => '55',
					'size'        => '5',
					'description' => __( 'Words', 'nca-theme' ),
					'placeholder' => '55',
				),
				'more'   => array(
					'type'        => 'text',
					'label'       => __( 'More Text', 'nca-theme' ),
					'placeholder' => '...',
				),
			) );
			
			FLPageData::add_post_property( 'member_form', array(
				'label'       => __( 'Member Form', 'nca-theme' ),
				'group'       => 'posts',
				'type'        => 'all',
				'getter'      => 'NCAConnections::fc_get_member_form',
			) );
		}
	}
	
	/**
     * @method fc_get_first_post
     */
	static public function fc_get_first_post( $settings ) {
		
		$output = '';
		$posts = get_posts( array( 'posts_per_page' => 1 ) );
		$post = ! empty( $posts ) ? $posts[0] : null;
		
		if ( is_null( $post ) ) {
			return;
		}
		
		setup_postdata( $post );
		
		switch( $settings->field ) {
			
			case 'excerpt':
				$output = FLPageDataPost::get_excerpt( $settings );
				break;
				
			case 'url':
				$output = get_permalink( $post->ID );
				break;
				
			case 'image':
				$output = get_the_post_thumbnail_url( $post->ID, 'full' );
				break;
				
			default:
				$output = get_the_title( $post->ID );
		}
		
		wp_reset_postdata();
		
		return $output;
	}
	
	/**
     * @method fc_get_member_form
     */
	static public function fc_get_member_form() {
		
		global $post;
		$output = '';
		
		ob_start();
		acf_form( array(
			'post_id'			=> get_the_ID(),
			'post_title'		=> false,
			'post_content'		=> true,
			'updated_message'	=> __('Profile updated', 'acf'),
			'uploader'			=> 'basic',
			'submit_value'		=> 'Update profile'
		));
		$output = ob_get_clean();
		
		return $output;
	}
}