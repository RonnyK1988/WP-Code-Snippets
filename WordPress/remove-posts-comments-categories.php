<?php
/**
 * RK Remove Blog Features (Posts, Comments, Categories)
 *
 * Description:
 * Disables and removes WordPress default blog features (Posts, Comments, Categories)
 * from admin UI, admin bar, dashboard, and frontend output.
 *
 * Prefix: rk_
 * Version: 1.0.0
 * Author: Your Name
 *
 * ------------------------------------------------------------------------
 * IMPORTANT
 * ------------------------------------------------------------------------
 * This snippet does NOT delete any data from the database.
 * It only hides and disables access to these features.
 */

/**
 * Remove menu items (Posts, Comments, Categories).
 */
function rk_remove_blog_admin_menu() {
	remove_menu_page( 'edit.php' ); // Posts
	remove_menu_page( 'edit-comments.php' ); // Comments
	remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=category' ); // Categories
}
add_action( 'admin_menu', 'rk_remove_blog_admin_menu', 999 );

/**
 * Clean admin bar (remove posts + comments).
 */
function rk_remove_blog_admin_bar( $wp_admin_bar ) {
	if ( ! is_admin_bar_showing() ) {
		return;
	}

	$wp_admin_bar->remove_node( 'new-post' );
	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'rk_remove_blog_admin_bar', 999 );

/**
 * Remove dashboard widgets related to blog features.
 */
function rk_remove_blog_dashboard_widgets() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'rk_remove_blog_dashboard_widgets' );

/**
 * Redirect access to blocked admin pages.
 */
function rk_remove_blog_admin_redirects() {
	global $pagenow;

	$blocked = array(
		'edit.php',
		'post-new.php',
		'edit-comments.php',
		'comment.php',
	);

	if ( in_array( $pagenow, $blocked, true ) ) {

		// Allow CPTs (e.g. pages)
		if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url() );
		exit;
	}

	// Block categories
	if (
		'edit-tags.php' === $pagenow &&
		isset( $_GET['taxonomy'] ) &&
		'category' === $_GET['taxonomy']
	) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'rk_remove_blog_admin_redirects' );

/**
 * Hide default post type UI.
 */
function rk_remove_blog_post_type_ui() {
	global $wp_post_types;

	if ( isset( $wp_post_types['post'] ) ) {
		$wp_post_types['post']->show_ui           = false;
		$wp_post_types['post']->show_in_menu      = false;
		$wp_post_types['post']->show_in_admin_bar = false;
		$wp_post_types['post']->show_in_nav_menus = false;
	}
}
add_action( 'init', 'rk_remove_blog_post_type_ui', 100 );

/**
 * Hide category taxonomy UI.
 */
function rk_remove_blog_category_ui() {
	global $wp_taxonomies;

	if ( isset( $wp_taxonomies['category'] ) ) {
		$wp_taxonomies['category']->show_ui           = false;
		$wp_taxonomies['category']->show_admin_column = false;
		$wp_taxonomies['category']->show_in_nav_menus = false;
		$wp_taxonomies['category']->show_tagcloud     = false;
	}
}
add_action( 'init', 'rk_remove_blog_category_ui', 100 );

/**
 * Disable comments + trackbacks support globally.
 */
function rk_disable_comments_support() {
	$post_types = get_post_types();

	foreach ( $post_types as $post_type ) {
		remove_post_type_support( $post_type, 'comments' );
		remove_post_type_support( $post_type, 'trackbacks' );
	}
}
add_action( 'init', 'rk_disable_comments_support', 100 );

/**
 * Force comments closed on frontend.
 */
function rk_force_comments_closed() {
	return false;
}
add_filter( 'comments_open', 'rk_force_comments_closed', 20, 2 );
add_filter( 'pings_open', 'rk_force_comments_closed', 20, 2 );

/**
 * Hide existing comments.
 */
function rk_hide_existing_comments( $comments ) {
	return array();
}
add_filter( 'comments_array', 'rk_hide_existing_comments', 10, 2 );

/**
 * Remove Recent Comments widget.
 */
function rk_remove_comments_widget() {
	unregister_widget( 'WP_Widget_Recent_Comments' );
}
add_action( 'widgets_init', 'rk_remove_comments_widget' );

/**
 * Remove default "New" menu and rebuild without Posts.
 */
function rk_admin_bar_cleanup( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'new-content' );
}
add_action( 'admin_bar_menu', 'rk_admin_bar_cleanup', 9999 );

function rk_restore_admin_bar_without_posts( $wp_admin_bar ) {

	if ( ! current_user_can( 'edit_pages' ) && ! current_user_can( 'upload_files' ) ) {
		return;
	}

	$wp_admin_bar->add_node(array(
		'id'    => 'new-content',
		'title' => __( 'New' ),
		'href'  => admin_url(),
	));

	if ( current_user_can( 'upload_files' ) ) {
		$wp_admin_bar->add_node(array(
			'id'     => 'new-media',
			'parent' => 'new-content',
			'title'  => __( 'Media' ),
			'href'   => admin_url( 'media-new.php' ),
		));
	}

	if ( current_user_can( 'edit_pages' ) ) {
		$wp_admin_bar->add_node(array(
			'id'     => 'new-page',
			'parent' => 'new-content',
			'title'  => __( 'Page' ),
			'href'   => admin_url( 'post-new.php?post_type=page' ),
		));
	}
}
add_action( 'admin_bar_menu', 'rk_restore_admin_bar_without_posts', 10000 );
