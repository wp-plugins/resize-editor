<?php
/*
Plugin Name: Resize Editor
Plugin URI: http://www.creationlabs.net/resize-editor
Description: Resize post editor width.
Text Domain: resize-editor
Domain Path: /languages/
Author: Hiroshi Sawai
Author URI: http://www.info-town.jp
Version: 1.1.0
*/

/*  Copyright 2013  Hiroshi Sawai (email : info@info-town.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * Defines plugin constant values
 */
if ( ! defined( 'RESIZE_EDITOR_DIR' ) ) {
	define( 'RESIZE_EDITOR_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'RESIZE_EDITOR_STYLE' ) ) {
	define( 'RESIZE_EDITOR_STYLE', RESIZE_EDITOR_DIR . '/' . 'style.css' );
}

/*
 * Set up plugin
 */
add_action( 'init', 'resize_editor_init' );
function resize_editor_init()
{
	// load text domin
	load_plugin_textdomain( 'resize-editor', false, 'resize-editor/languages' );
	// register deactivation function
	if ( function_exists( 'register_deactivation_hook' ) ) {
		register_deactivation_hook( __FILE__, 'deactivation_resize_editor' );
	}
	// register uninstall function
	if ( function_exists( 'register_uninstall_hook' ) ) {
		register_uninstall_hook( __FILE__, 'uninstall_resize_editor' );
	}
}

/*
 * Deactivate Resize Editor plugin
 *
 * delete Resize Editor css(/style.css)
 * delete added option(postdivrichwidth) from wp_options
 */
function deactivation_resize_editor()
{
	// delete options that has been added by Resize Editor
	delete_option( 'postdivrichwidth' );
	// delete css that has been added by Resize Eitor
	if ( true === file_exists( RESIZE_EDITOR_STYLE ) ) {
		unlink( RESIZE_EDITOR_STYLE );
	}
}

/*
 *  Uninstall Resize Editor plugin
 *
 * delete Resize Editor css(/style.css)
 * delete added option(postdivrichwidth) from wp_options
 */
function uninstall_resize_editor()
{
	// delete options that has been added by Resize Editor
	delete_option( 'postdivrichwidth' );
	// delete css that has been added by Resize Eitor
	if ( true === file_exists( RESIZE_EDITOR_STYLE ) ) {
		unlink( RESIZE_EDITOR_STYLE );
	}
}

/*
 * Add resize editor css(style.css)
 */
add_action( 'admin_init', 'add_resize_editor_style' );
function add_resize_editor_style()
{
	wp_register_style( 'resize_editor', plugins_url( '', __FILE__ ) . '/style.css' );
	wp_enqueue_style( 'resize_editor' );
}

/*
 * Add Resize Editor setting page
 */
add_action( 'admin_menu', 'resize_editor' );
function resize_editor()
{
	add_options_page(
		'Resize Editor Options',
		'Resize Editor',
		'manage_options',
		'resize-editor',
		'resize_editor_options'
	);
}

/*
 * Display setting page
 */
function resize_editor_options()
{
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page' ) );
	}
	if ( isset( $_POST['_wpnonce'] ) && $_POST['_wpnonce'] ) {
		if ( check_admin_referer( 'safe-resize', '_wpnonce' ) ) {
			if ( isset( $_POST['postdivrichwidth'] ) && absint( $_POST['postdivrichwidth'] ) ) {
				$width = absint( $_POST['postdivrichwidth'] );
				// update option
				update_option( 'postdivrichwidth', $width );
				// create style.css
				$css = '@charset "utf-8";' . PHP_EOL
					. '#postdivrich { width:' . $width . 'px; overflow: hidden; }';
				$fp = fopen( RESIZE_EDITOR_STYLE, 'w' ) or die( __( 'Can not open file' ) );
				flock( $fp, LOCK_EX );
				fputs( $fp, $css );
				flock( $fp, LOCK_UN );
				fclose( $fp );
			} else {
				delete_option( 'postdivrichwidth' );
				if ( true === file_exists( RESIZE_EDITOR_STYLE ) ) {
					unlink( RESIZE_EDITOR_STYLE );
				}
			}
		}
	}

	// options setting form
	echo '<div class="wrap">' . PHP_EOL;
	echo '<h2>Resize Editor</h2>' . PHP_EOL;
	echo '<form method="post" action="">' . PHP_EOL;
	echo wp_nonce_field( 'safe-resize', '_wpnonce' ) . PHP_EOL;
	echo '<table class="form-table">' . PHP_EOL;
	echo '<tr><th>Rich Editor Width</th><td><input type="text" name="postdivrichwidth" '
		. 'value="' . esc_html( get_option( 'postdivrichwidth' ) ) . '" size="6"/>&nbsp;px<br>'
		. __( 'Empty or 0 reset existing value', 'resize-editor' ) . '</td></tr>' . PHP_EOL;
	echo '</table>' . PHP_EOL;
	echo '<p class="submit"><input type="submit" class="button-primary" value="' . __(
			'Save changes',
			'resize-editor'
		) . '" /></p>' . PHP_EOL;
	echo '</form>' . PHP_EOL;
	echo '</div>';
}

?>
