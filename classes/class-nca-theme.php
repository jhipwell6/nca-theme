<?php

/**
 * Helper class for theme functions.
 *
 * @class NCATheme
 */

add_action( 'after_setup_theme', 'NCATheme::setup', 10 );

final class NCATheme {
	
	/**
     * @method setup
     */
    static public function setup() {
		
		// Actions
		add_action( 'wp_enqueue_scripts', 						__CLASS__ . '::enqueue_popper', 10 );
		add_action( 'wp_enqueue_scripts', 						__CLASS__ . '::enqueue_scripts', 1000 );
		add_action( 'upload_mimes', 							__CLASS__ . '::add_file_types_to_uploads', 10, 1 );
		add_action( 'pre_get_posts', 							__CLASS__ . '::pre_get_members', 10, 1 );
		add_action( 'pre_get_posts', 							__CLASS__ . '::blog_query_offset', 10, 1 );
		
		// Filters
		add_filter( 'style_loader_src', 						__CLASS__ . '::remove_version', 10, 1 );
		add_filter( 'script_loader_src', 						__CLASS__ . '::remove_version', 10, 1 );
		add_filter( 'wp_prepare_themes_for_js', 				__CLASS__ . '::theme_display_mods' );
		add_filter( 'fl_builder_font_families_system',			__CLASS__ . '::add_fonts', 10, 1 );
		add_filter( 'fl_theme_system_fonts',					__CLASS__ . '::add_fonts', 10, 1 );
		add_filter( 'found_posts', 								__CLASS__ . '::blog_adjust_offset_pagination', 1, 2 );
		add_filter( 'fl_theme_builder_page_archive_get_title',	__CLASS__ . '::blog_archive_get_title', 10, 1 );
		add_filter( 'facetwp_facet_html', 						__CLASS__ . '::fwp_facet_html', 10, 2 );
		add_filter( 'facetwp_is_main_query',					__CLASS__ . '::fwp_is_main_query', 10, 2 );
		add_filter( 'post_type_link', 							__CLASS__ . '::fix_news_links', 10, 4 );
	}
	
	/**
     * @method enqueue_popper
     */
    static public function enqueue_popper() {
		
		wp_enqueue_script( 'nca-popper', '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', array(), '', false );
	}
    
    /**
     * @method enqueue_scripts
	 * loads all theme specific css and js
     */
    static public function enqueue_scripts() {
		
        wp_enqueue_style( 'nca-fonts', NCA_THEME_URL . '/fonts.css' );
		wp_enqueue_style( 'nca-styles', NCA_THEME_URL . '/style.css' );
		wp_register_script( 'nca-scripts', NCA_THEME_URL . '/main.js', array(), '', true );
	
		$global_settings = FLBuilderModel::get_global_settings();
		$data = array(
			'rowWidth' => $global_settings->row_width,
			'moduleMargins' => $global_settings->module_margins
		);
		wp_localize_script( 'nca-scripts', 'site', $data );
		wp_enqueue_script( 'nca-scripts' );
	}
	
	/**
     * @method remove_version
	 * removes the ?ver parameter from stylesheets and scripts
     */
	static public function remove_version( $src ) {
		
		if ( strpos( $src, '?ver=' ) )
			$src = remove_query_arg( 'ver', $src );
		
		return $src;
	}
	
	/**
	 * @method theme_display_mods
	 * admin only - creates theme screenshot from site title
	 */
	static public function theme_display_mods( $themes ) {
		
		if ( $themes['nca-theme'] ) {
			$logo_text = apply_filters( 'fl-logo-text', FLTheme::get_setting( 'fl-logo-text' ) );
			$logo_image = FLTheme::get_setting( 'fl-logo-image' );
			$theme_screenshot = $logo_image ? $logo_image : '//via.placeholder.com/732x550/ffffff/000000/?text=' . $logo_text . ' Theme';
			
			$themes['nca-theme']['name'] = get_bloginfo('name') . ' Theme';
			$themes['nca-theme']['screenshot'][0] = $theme_screenshot;
		}

		return $themes;
	}
	
	/**
	 * @method add_file_types_to_uploads
	 * adds SVG upload support
	 */
	static public function add_file_types_to_uploads( $file_types ) {
		
		$new_filetypes = array();
		$new_filetypes['svg'] = 'image/svg+xml';
		$file_types = array_merge( $file_types, $new_filetypes );
		
		return $file_types;
	}
	
	/**
     * @method add_fonts
	 * loads fonts to be used in the Page Builder
     */
	static public function add_fonts( $system_fonts ) {	
			
		$aleo = array(
			"fallback" => "serif",
			"weights"  => array(
				"400",
				"800",
			)
		);
		
		$system_fonts['Aleo'] = $aleo;
        
        $din = array(
			"fallback" => "sans-serif",
			"weights"  => array(
				"400",
                "600",
				"800",
			)
		);
		
		$system_fonts['DIN'] = $din;
		
        $noway = array(
			"fallback" => "sans-serif",
			"weights"  => array(
				"400",
			)
		);
		
		$system_fonts['Noway'] = $noway;
        
		return $system_fonts;
	}
	
	/**
     * @method pre_get_members
	 * pre sorts the members post type on the member archive page
     */
    static public function pre_get_members( $query ) {
		
		if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive('member') ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'asc' );
			$query->set( 'posts_per_page', '-1' );
		}
	}
	
	/**
     * @method blog_query_offset
	 * offsets the blog posts by 1 post to account for the featured post
     */
    static public function blog_query_offset( $query ) {

		if ( ! $query->is_home() || ! $query->is_main_query() ) {
			return;
		}

		$offset = 1;
		$ppp = get_option('posts_per_page');

		if ( $query->is_paged ) {
			$page_offset = $offset + ( ($query->query_vars['paged'] - 1 ) * $ppp );
			$query->set( 'offset', $page_offset );
		} else {
			$query->set( 'offset', $offset );
		}
	}
	
	/**
     * @method blog_adjust_offset_pagination
	 * offset pagination to account for the featured post
     */
    static public function blog_adjust_offset_pagination( $found_posts, $query ) {

		$offset = 1;

		if ( $query->is_home() && $query->is_main_query() ) {
			return $found_posts - $offset;
		}
		
		return $found_posts;
	}
	
	/**
     * @method blog_archive_get_title
	 * changes the default Blog title from "Posts" to "Blog"
     */
    static public function blog_archive_get_title( $title ) {
		
		if ( is_home() ) {
			$title = __( 'Blog', 'nca-theme' );
		}
		
		return $title;
	}
	
	/**
     * @method fwp_facet_html
	 * modifies the markup for the FacetWP Search facet
     */
    static public function fwp_facet_html( $output, $params ) {
		
		if ( $params['facet']['name'] == 'search' ) {
			
			$output = '<div class="facetwp-search-wrap">
							<div class="input-group" role="search">
								<input type="search" class="facetwp-search form-control" value="' . $params['selected_values'] . '" placeholder="Search members">
								<span class="input-group-append">
									<button type="button" class="facetwp-btn"><span class="fas fa-search"></span></button>
								</span>
							</div>
						</div>';
		}
		
		return $output;
	}
	
	/**
     * @method fwp_is_main_query
	 * fix facetwp not getting the correct posts
     */
    static public function fwp_is_main_query( $is_main_query, $query ) {
		
		if ( '' !== $query->get( 'facetwp' ) ) {
			$is_main_query = (bool) $query->get( 'facetwp' );
		}
		
		return $is_main_query;
	}
	
	/**
	 * @method fix_news_links
	 * automatically redirect news links to third party link if it exists
	 */
	static public function fix_news_links( $url, $post, $leavename, $sample ) {
		
		if ( 'nca_news' == get_post_type( $post ) ) {
			
			if ( is_object( $post ) ) {
				$post_id = $post->ID;
			} else {
				$post_id = $post;
			}
			
			$file = get_post_meta( $post_id, 'file', true );
			
			return wp_get_attachment_url( $file );
		}
		
		return $url;
	}
}