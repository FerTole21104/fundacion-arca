<?php
/**
 * The template to display default site header
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

$chinchilla_header_css   = '';
$chinchilla_header_image = get_header_image();
$chinchilla_header_video = chinchilla_get_header_video();
if ( ! empty( $chinchilla_header_image ) && chinchilla_trx_addons_featured_image_override( is_singular() || chinchilla_storage_isset( 'blog_archive' ) || is_category() ) ) {
	$chinchilla_header_image = chinchilla_get_current_mode_image( $chinchilla_header_image );
}

?><header class="top_panel top_panel_default
	<?php
	echo ! empty( $chinchilla_header_image ) || ! empty( $chinchilla_header_video ) ? ' with_bg_image' : ' without_bg_image';
	if ( '' != $chinchilla_header_video ) {
		echo ' with_bg_video';
	}
	if ( '' != $chinchilla_header_image ) {
		echo ' ' . esc_attr( chinchilla_add_inline_css_class( 'background-image: url(' . esc_url( $chinchilla_header_image ) . ');' ) );
	}
	if ( is_single() && has_post_thumbnail() ) {
		echo ' with_featured_image';
	}
	if ( chinchilla_is_on( chinchilla_get_theme_option( 'header_fullheight' ) ) ) {
		echo ' header_fullheight chinchilla-full-height';
	}
	$chinchilla_header_scheme = chinchilla_get_theme_option( 'header_scheme' );
	if ( ! empty( $chinchilla_header_scheme ) && ! chinchilla_is_inherit( $chinchilla_header_scheme  ) ) {
		echo ' scheme_' . esc_attr( $chinchilla_header_scheme );
	}
	?>
">
	<?php

	// Background video
	if ( ! empty( $chinchilla_header_video ) ) {
		get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/header-video' ) );
	}

	// Main menu
	get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/header-navi' ) );

	// Mobile header
	if ( chinchilla_is_on( chinchilla_get_theme_option( 'header_mobile_enabled' ) ) ) {
		get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/header-mobile' ) );
	}

	// Page title and breadcrumbs area
	if ( ! is_single() ) {
		get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/header-title' ) );
	}

	// Header widgets area
	get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/header-widgets' ) );
	?>
</header>
