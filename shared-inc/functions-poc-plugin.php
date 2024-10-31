<?php
/**
 * Pinch Of Code plugins functions.
 * 
 * @author  	Pinch Of Code <info@pinchofcode.com>
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // If this file is called directly, abort.

if( !function_exists( 'poc_debug' ) ) {
	/**
	 * Helper function to get formatted arguments prints
	 */
	function poc_debug() {
		$args = func_get_args();

		if( !empty( $args ) ) {
			for( $i = 0; $i < count( $args ); $i++ ) {
				echo '<pre><strong>param ' . $i . '</strong>';
				var_dump( $args[$i] );
				echo '</pre>';
			}
		}
	}
}

if( !function_exists( 'poc_plugin_roles' ) ) {
	/**
	 * Return an array with the roles available in WP.
	 * 
	 * @return array
	 */
	function poc_plugin_roles() {
		global $wp_roles;

        if ( ! isset( $wp_roles ) ) $wp_roles = new WP_Roles();

        $roles = array();
        foreach( $wp_roles->roles as $k=>$role ) {
            $roles[$k] = $role['name'];
        }

        return $roles;
	}
}

add_action( 'wp_ajax_poc_panel_search_products', 'poc_panel_get_all_products' );
if( !function_exists( 'poc_panel_get_all_products' ) ) {
	/**
	 * Search for products.
	 */
	function poc_panel_get_all_products() {

		$term = (string) wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		if ( is_numeric( $term ) ) {

			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__in'       => array(0, $term),
				'fields'         => 'ids'
			);

			$args2 = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post_parent'    => $term,
				'fields'         => 'ids'
			);

			$args3 = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_sku',
						'value'   => $term,
						'compare' => 'LIKE'
					)
				),
				'fields'         => 'ids'
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ) );

		} else {

			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				's'              => $term,
				'fields'         => 'ids'
			);

			$args2 = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
					'key'     => '_sku',
					'value'   => $term,
					'compare' => 'LIKE'
					)
				),
				'fields'         => 'ids'
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

		}

		$found_products = array();

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$product = get_product( $post );

				$found_products[ $post ] = $product->get_formatted_name();
			}
		}

		echo json_encode( $found_products );

		die();
	}
}

/*****
 * Panel options type filters
 */

if( !function_exists( 'poc_panel_filter_email' ) ) {
	/**
	 * Filter email options
	 * @param  array 	$input
	 * @return string 	
	 */
	function poc_panel_filter_email( $input ) {
		return sanitize_email( $input );
	}
}

if( !function_exists( 'poc_panel_filter_url' ) ) {
	/**
	 * Filter url options
	 * @param  array 	$input
	 * @return string 	
	 */
	function poc_panel_filter_url( $input ) {
		return esc_url( $input );
	}
}

if( !function_exists( 'poc_panel_filter_textarea' ) ) {
	/**
	 * Filter textarea options
	 * @param  array 	$input
	 * @return string 	
	 */
	function poc_panel_filter_textarea( $input ) {
		return esc_textarea( $input );
	}
}

if( !function_exists( 'poc_panel_filter_checkboxes' ) ) {
	/**
	 * Filter checkboxes options
	 * @param  array 	$input
	 * @return string 	
	 */
	function poc_panel_filter_checkboxes( $input ) {
		return maybe_serialize( $input );
	}
}

if( !function_exists( 'poc_panel_filter_autocomplete' ) ) {
	/**
	 * Filter autocomplete options
	 * @param  array 	$input
	 * @return string 	
	 */
	function poc_panel_filter_autocomplete( $input ) {
		return maybe_serialize( $input );
	}
}

if( !function_exists( 'poc_panel_filter_products_search' ) ) {
	/**
	 * Filter products search options
	 * @param  array 	$input
	 * @return string 	
	 */
	function poc_panel_filter_products_search( $input ) {
		return maybe_serialize( $input );
	}
}