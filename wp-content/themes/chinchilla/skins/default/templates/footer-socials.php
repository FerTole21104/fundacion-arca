<?php
/**
 * The template to display the socials in the footer
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.10
 */


// Socials
if ( chinchilla_is_on( chinchilla_get_theme_option( 'socials_in_footer' ) ) ) {
	$chinchilla_output = chinchilla_get_socials_links();
	if ( '' != $chinchilla_output ) {
		?>
		<div class="footer_socials_wrap socials_wrap">
			<div class="footer_socials_inner">
				<?php chinchilla_show_layout( $chinchilla_output ); ?>
			</div>
		</div>
		<?php
	}
}
