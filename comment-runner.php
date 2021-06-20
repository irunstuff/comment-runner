<?php

/*
 * Plugin Name:       Comment Runner
 * Plugin URI:        https://irunstuff.com/plugins/comment-runner
 * Description:       Reduce or eliminate comment spam.
 * Version:           1.1.0
 * Author:            IRunStuff.com
 * Author URI:        https://irunstuff.com
 * Text Domain:       comment-runner
 * Domain Path:       /locale
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * License:           GNU General Public License v3 (GPLv3)
{Plugin Name} is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.
{Plugin Name} is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with {Plugin Name}. If not, see {License URI}.
 */


/* prevent direct access to this file; security */
defined( 'ABSPATH' ) or die();


// by default, parameter "$approved" is 0, however we can override this value;
// $approved can be one of 4 values: 0(approved), 1(pending), 'spam', 'trash';
// documentation for comment inputs: https://developer.wordpress.org/reference/functions/wp_insert_comment/
function irunstuff_comment_handler( $approved, $comment_data ) {

    // documentation for "wp_unslash":
    // https://developer.wordpress.org/reference/functions/wp_unslash/
    // description: removes slashes from a string or array of strings;
    // e.g., "Patrick O\'Reilly" becomes "Patrck O'Reilly"
    $data = wp_unslash( $comment_data );

    $comment_author_url = ! isset( $data['comment_author_url'] ) ? '' : $data['comment_author_url'];
    $comment_content    = ! isset( $data['comment_content'] ) ? '' : $data['comment_content'];

    if ( ! empty($comment_author_url) && ! current_user_can('administrator') ) {
        //return new WP_Error( 'spam', 'Comment spam (link in author url) detected!' );
        return 'trash';
    }

    // stristr = (case insensitive) find the first occurrence of a string
    $has_link = stristr( $comment_content, 'https://' ) ?: stristr( $comment_content, 'http://' );
    $has_mailto = stristr( $comment_content, 'mailto:' );

    if ( ( $has_link || $has_mailto ) && ! current_user_can('administrator') ) {
        //return new WP_Error( 'spam', 'Comment spam (link in content) detected!' );
        return 'trash';
    };

    return $approved;
}

// name of filter to hook the callback to, the function to add(the callback), 
// callback priority(lower #s execute earlier, # of args to pass to callback
add_filter( 'pre_comment_approved' , 'irunstuff_comment_handler' , 100, 2 );


function comment_css() {
    wp_enqueue_style( 'comment_css', plugins_url( '/css/comment-runner.css', __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'comment_css' );

?>
