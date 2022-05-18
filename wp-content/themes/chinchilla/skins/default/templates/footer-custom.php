<?php
/**
 * The template to display default site footer
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.10
 */

$chinchilla_footer_id = chinchilla_get_custom_footer_id();
$chinchilla_footer_meta = get_post_meta( $chinchilla_footer_id, 'trx_addons_options', true );
if ( ! empty( $chinchilla_footer_meta['margin'] ) ) {
	chinchilla_add_inline_css( sprintf( '.page_content_wrap{padding-bottom:%s}', esc_attr( chinchilla_prepare_css_value( $chinchilla_footer_meta['margin'] ) ) ) );
}
?>
<footer class="footer_wrap footer_custom footer_custom_<?php echo esc_attr( $chinchilla_footer_id ); ?> footer_custom_<?php echo esc_attr( sanitize_title( get_the_title( $chinchilla_footer_id ) ) ); ?>
						<?php
						$chinchilla_footer_scheme = chinchilla_get_theme_option( 'footer_scheme' );
						if ( ! empty( $chinchilla_footer_scheme ) && ! chinchilla_is_inherit( $chinchilla_footer_scheme  ) ) {
							echo ' scheme_' . esc_attr( $chinchilla_footer_scheme );
						}
						?>
						">
	<?php
	// Custom footer's layout
	do_action( 'chinchilla_action_show_layout', $chinchilla_footer_id );
	?>
</footer><!-- /.footer_wrap -->
