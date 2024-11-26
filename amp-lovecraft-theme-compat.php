<?php
/**
 * AMP Lovecraft Theme Compat plugin bootstrap.
 *
 * @package   Google\AMP_Lovecraft_Theme_Compat
 * @author    Weston Ruter, Google
 * @license   GPL-2.0-or-later
 * @copyright 2020 Google Inc.
 *
 * @wordpress-plugin
 * Plugin Name: AMP Lovecraft Theme Compat
 * Plugin URI: https://gist.github.com/westonruter/08bea805f2a31e7934dfeeb4e36bb662
 * Description: Plugin to add <a href="https://wordpress.org/plugins/amp/">AMP plugin</a> compatibility to the <a href="https://wordpress.org/themes/lovecraft/">Lovecraft</a> theme by Anders Norén.
 * Version: 0.2
 * Author: Weston Ruter, Google
 * Author URI: https://weston.ruter.net/
 * License: GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Gist Plugin URI: https://gist.github.com/westonruter/08bea805f2a31e7934dfeeb4e36bb662
 */

namespace Google\AMP_Lovecraft_Theme_Compat;

/**
 * Whether the page is AMP.
 *
 * @return bool Is AMP.
 */
function is_amp() {
	return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
}

/**
 * Remove JS that replaces no-js with js class.
 *
 * @see \lovecraft_html_js_class()
 */
function add_hooks() {
	if ( 'lovecraft' === get_template() && is_amp() ) {
		remove_action( 'wp_head', 'lovecraft_html_js_class', 1 );
		add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\override_scripts_and_styles', 11 );
		add_filter( 'amp_content_sanitizers', __NAMESPACE__ . '\filter_sanitizers' );
	}
}
add_action( 'wp', __NAMESPACE__ . '\add_hooks' );

/**
 * Remove enqueued JS.
 *
 * @see lovecraft_load_javascript_files()
 */
function override_scripts_and_styles() {
	wp_dequeue_script( 'lovecraft_global' );
	wp_add_inline_style( 'lovecraft_style', file_get_contents( __DIR__ . '/style.css' ) );
}

/**
 * Add sanitizer to fix up the markup.
 *
 * @param array $sanitizers Sanitizers.
 * @return array Sanitizers.
 */
function filter_sanitizers( $sanitizers ) {
	require_once __DIR__ . '/class-sanitizer.php';
	$sanitizers[ __NAMESPACE__ . '\Sanitizer' ] = [];
	return $sanitizers;
}

/**
 * Bonus improvement: add font-display:swap to the Google Fonts!
 *
 * @param string $src    Stylesheet URL.
 * @param string $handle Style handle.
 * @return string Filtered stylesheet URL.
 */
function filter_font_style_loader_src( $src, $handle ) {
	if ( 'lovecraft_googlefonts' === $handle ) {
		$src = add_query_arg( 'display', 'swap', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', __NAMESPACE__ . '\filter_font_style_loader_src', 10, 2 );
