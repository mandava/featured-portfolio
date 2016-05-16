<?php

/**
 * Plugin Name: Featured Portfolio
 * Plugin URI: http://themesquare.com/featured-portfolio/
 * Description: Adds portfolio post type and a widget to display portfolio items with thumbnails. Requires Genesis Framework.
 * Version: 1.0
 * Author: ThemeSquare
 * Author URI: http://themesquare.com/
 * Text Domain: featured-portfolio
 * License: GPL2 (or Later)
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * 
 * This is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * This is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
*/

defined( 'WPINC' ) or die;

register_activation_hook( __FILE__, 'tsfp_activation_check' );
/**
 * Checks if Genesis (version 2.0 or later) is active else deactivate the plugin
 */
function tsfp_activation_check() {
	$version = '2.0';
	$active_theme = wp_get_theme( 'genesis' );

	if ( ('genesis' != basename( TEMPLATEPATH )) || ( version_compare( $active_theme['Version'], $version, '<' ) ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate plugin
		wp_die( sprintf( __( 'Sorry, you can\'t activate %1$sGenesis Featured Portfolio%2$s unless you have installed the %3$sGenesis Framework%4$s. Go back to the %5$sPlugins Page%4$s.', 'featured-portfolio' ), '<b>', '</b>', '<a href="http://themesquare.com/ref/genesis" target="_blank">', '</a>', '<a href="javascript:history.back()">' ) );
	}

	tsfp_portfolio_post_type();
	tsfp_portfolio_taxonomy_category();
	tsfp_portfolio_taxonomy_tags();
	
	flush_rewrite_rules(); // Flush rewrite rules
}

//* Flush rewrite rules after deactivating the plugin
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action('admin_init', 'tsfp_deactivate_check');
/**
 * Checks if Genesis is active else deactivate the plugin
 */
function tsfp_deactivate_check() {
    if ( ! function_exists('genesis_pre') ) {
		deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate plugin
    }
}

add_action( 'init', 'tsfp_load_text_domain' );
/**
 * Loads plugin translation files
 */
function tsfp_load_text_domain() {
	$text_domain = 'featured-portfolio';
	$plugin_locale = apply_filters( 'plugin_locale', get_locale(), $text_domain );
	load_textdomain( $text_domain, trailingslashit( WP_LANG_DIR ) . $text_domain . '/' . $text_domain . '-' . $plugin_locale . '.mo' );
	load_plugin_textdomain( $text_domain, false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/**
 * Include Widget Class file
 */
include_once dirname( __FILE__ ) . '/includes/widget-class.php';

add_action( 'widgets_init', 'tsfp_register_widget' );
/**
 * Register portfolio widget
 */
function tsfp_register_widget() {
	register_widget( 'Featured_Portfolio' );
}

add_action( 'init', 'tsfp_portfolio_post_type' );
/**
 * Register portfolio post type
 */
function tsfp_portfolio_post_type() {

	$labels = array(
		'name'               => __( 'Portfolio', 'featured-portfolio' ),
		'singular_name'      => __( 'Portfolio Item', 'featured-portfolio' ),
		'menu_name'          => _x( 'Portfolio', 'admin menu', 'featured-portfolio' ),
		'name_admin_bar'     => _x( 'Portfolio Item', 'add new on admin bar', 'featured-portfolio' ),
		'add_new'            => __( 'Add New Item', 'featured-portfolio' ),
		'add_new_item'       => __( 'Add New Portfolio Item', 'featured-portfolio' ),
		'new_item'           => __( 'Add New Portfolio Item', 'featured-portfolio' ),
		'edit_item'          => __( 'Edit Portfolio Item', 'featured-portfolio' ),
		'view_item'          => __( 'View Item', 'featured-portfolio' ),
		'all_items'          => __( 'All Portfolio Items', 'featured-portfolio' ),
		'search_items'       => __( 'Search Portfolio', 'featured-portfolio' ),
		'parent_item_colon'  => __( 'Parent Portfolio Item:', 'featured-portfolio' ),
		'not_found'          => __( 'No portfolio items found', 'featured-portfolio' ),
		'not_found_in_trash' => __( 'No portfolio items found in trash', 'featured-portfolio' ),
	);

	$supports = array(
		'title',
		'editor',
		'author',
		'thumbnail',
		'excerpt',
		'trackbacks',
		'custom-fields',
		//'comments',
		'revisions',
		'page-attributes',
		'post-formats',
		'genesis-layouts',
		'genesis-seo',
		'genesis-scripts',
		'genesis-cpt-archives-settings'
	);

	//* Add portfolio labels filter
	$labels = apply_filters( 'tsfp_filter_portfolio_labels', $labels );
	
	//* Add portfolio supports filter
	$supports = apply_filters( 'tsfp_filter_portfolio_supports', $supports );

	$args = array(
		'labels'          => $labels,
		'supports'        => $supports,
		'public'          => true,
		'capability_type' => 'post',
		'rewrite'         => array( 'slug' => 'portfolio', ), // Permalinks format
		'menu_position'   => 5,
		'menu_icon'       => ( version_compare( $GLOBALS['wp_version'], '3.8', '>=' ) ) ? 'dashicons-portfolio' : false ,
		'has_archive'     => true
	);

	//* Add portfolio args filter
	$args = apply_filters( 'tsfp_filter_portfolio_args', $args );

	register_post_type( 'portfolio', $args );
}


add_action( 'init', 'tsfp_portfolio_taxonomy_category' );
/**
 * Register portfolio category taxonomy
 */
function tsfp_portfolio_taxonomy_category() {

	$labels = array(
		'name'                       => __( 'Portfolio Categories', 'featured-portfolio' ),
		'singular_name'              => __( 'Portfolio Category', 'featured-portfolio' ),
		'menu_name'                  => __( 'Portfolio Categories', 'featured-portfolio' ),
		'edit_item'                  => __( 'Edit Portfolio Category', 'featured-portfolio' ),
		'update_item'                => __( 'Update Portfolio Category', 'featured-portfolio' ),
		'add_new_item'               => __( 'Add New Portfolio Category', 'featured-portfolio' ),
		'new_item_name'              => __( 'New Portfolio Category Name', 'featured-portfolio' ),
		'parent_item'                => __( 'Parent Portfolio Category', 'featured-portfolio' ),
		'parent_item_colon'          => __( 'Parent Portfolio Category:', 'featured-portfolio' ),
		'all_items'                  => __( 'All Portfolio Categories', 'featured-portfolio' ),
		'search_items'               => __( 'Search Portfolio Categories', 'featured-portfolio' ),
		'popular_items'              => __( 'Popular Portfolio Categories', 'featured-portfolio' ),
		'separate_items_with_commas' => __( 'Separate portfolio categories with commas', 'featured-portfolio' ),
		'add_or_remove_items'        => __( 'Add or remove portfolio categories', 'featured-portfolio' ),
		'choose_from_most_used'      => __( 'Choose from the most used portfolio categories', 'featured-portfolio' ),
		'not_found'                  => __( 'No portfolio categories found.', 'featured-portfolio' ),
	);

	//* Add portfolio labels filter
	$labels = apply_filters( 'tsfp_filter_portfolio_category_labels', $labels );
	
	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_tagcloud'     => true,
		'hierarchical'      => true,
		'rewrite'           => array( 'slug' => 'portfolio_category' ),
		'show_admin_column' => true,
		'query_var'         => true,
	);

	//* Add portfolio args filter
	$args = apply_filters( 'tsfp_filter_portfolio_category_args', $args );

	register_taxonomy( 'portfolio_category', 'portfolio', $args );

}

add_action( 'init', 'tsfp_portfolio_taxonomy_tags' );
/**
 * Register portfolio tags taxonomy
 */
function tsfp_portfolio_taxonomy_tags() {
	$labels = array(
		'name'                       => __( 'Portfolio Tags', 'featured-portfolio' ),
		'singular_name'              => __( 'Portfolio Tag', 'featured-portfolio' ),
		'menu_name'                  => __( 'Portfolio Tags', 'featured-portfolio' ),
		'edit_item'                  => __( 'Edit Portfolio Tag', 'featured-portfolio' ),
		'update_item'                => __( 'Update Portfolio Tag', 'featured-portfolio' ),
		'add_new_item'               => __( 'Add New Portfolio Tag', 'featured-portfolio' ),
		'new_item_name'              => __( 'New Portfolio Tag Name', 'featured-portfolio' ),
		'parent_item'                => __( 'Parent Portfolio Tag', 'featured-portfolio' ),
		'parent_item_colon'          => __( 'Parent Portfolio Tag:', 'featured-portfolio' ),
		'all_items'                  => __( 'All Portfolio Tags', 'featured-portfolio' ),
		'search_items'               => __( 'Search Portfolio Tags', 'featured-portfolio' ),
		'popular_items'              => __( 'Popular Portfolio Tags', 'featured-portfolio' ),
		'separate_items_with_commas' => __( 'Separate portfolio tags with commas', 'featured-portfolio' ),
		'add_or_remove_items'        => __( 'Add or remove portfolio tags', 'featured-portfolio' ),
		'choose_from_most_used'      => __( 'Choose from the most used portfolio tags', 'featured-portfolio' ),
		'not_found'                  => __( 'No portfolio tags found.', 'featured-portfolio' ),
	);

	//* Add portfolio labels filter
	$labels = apply_filters( 'tsfp_filter_portfolio_tag_labels', $labels );
	
	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_tagcloud'     => true,
		'hierarchical'      => false,
		'rewrite'           => array( 'slug' => 'portfolio_tag' ),
		'show_admin_column' => true,
		'query_var'         => true,
	);

	//* Add portfolio args filter
	$args = apply_filters( 'tsfp_filter_portfolio_tag_args', $args );

	register_taxonomy( 'portfolio_tag', 'portfolio', $args );

}