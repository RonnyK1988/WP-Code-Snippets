<?php
add_action( 'admin_bar_menu', 'add_popup_templates_to_admin_bar', 100 );

function add_popup_templates_to_admin_bar( $wp_admin_bar ) {
    // Ensure the main "Edit with Bricks" menu exists and we have popup templates
    if ( ! $wp_admin_bar->get_node( 'edit_with_bricks' ) ) {
        return; // Don't add if the parent menu isn't present
    }

    if ( ! isset( \Bricks\Database::$active_templates['popup'] ) || ! is_array( \Bricks\Database::$active_templates['popup'] ) ) {
        return; // No popups to add
    }

    // Get the current post ID for permission checks (similar to the original logic)
    $post_id = get_the_ID();
    if ( ! \Bricks\Capabilities::current_user_can_use_builder( $post_id ) ) {
        return; // User lacks permissions
    }

    // Add a submenu for each popup template
    foreach ( \Bricks\Database::$active_templates['popup'] as $popup_id ) {
        $wp_admin_bar->add_menu( [
            'parent' => 'edit_with_bricks',
            'id'     => 'edit_popup_' . $popup_id,
            'title'  => sprintf( esc_html__( 'Edit popup: %s', 'bricks' ), get_the_title( $popup_id ) ),
            'href'   => \Bricks\Helpers::get_builder_edit_link( $popup_id ),
        ] );
    }
}
