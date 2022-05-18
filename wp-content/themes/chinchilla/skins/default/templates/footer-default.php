<?php
/**
 * The template to display default site footer
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.10
 */

?>
<footer class="footer_wrap footer_default
<?php
$chinchilla_footer_scheme = chinchilla_get_theme_option( 'footer_scheme' );
if ( ! empty( $chinchilla_footer_scheme ) && ! chinchilla_is_inherit( $chinchilla_footer_scheme  ) ) {
	echo ' scheme_' . esc_attr( $chinchilla_footer_scheme );
}
?>
				">
	<?php

	// Footer widgets area
	get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/footer-widgets' ) );

	// Logo
	get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/footer-logo' ) );

	// Socials
	get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/footer-socials' ) );

	// Copyright area
	get_template_part( apply_filters( 'chinchilla_filter_get_template_part', 'templates/footer-copyright' ) );

	?>
</footer><!-- /.footer_wrap -->
