<?php
/**
 * The template to display the copyright info in the footer
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.10
 */

// Copyright area
?> 
<div class="footer_copyright_wrap
<?php
$chinchilla_copyright_scheme = chinchilla_get_theme_option( 'copyright_scheme' );
if ( ! empty( $chinchilla_copyright_scheme ) && ! chinchilla_is_inherit( $chinchilla_copyright_scheme  ) ) {
	echo ' scheme_' . esc_attr( $chinchilla_copyright_scheme );
}
?>
				">
	<div class="footer_copyright_inner">
		<div class="content_wrap">
			<div class="copyright_text">
			<?php
				$chinchilla_copyright = chinchilla_get_theme_option( 'copyright' );
			if ( ! empty( $chinchilla_copyright ) ) {
				// Replace {{Y}} or {Y} with the current year
				$chinchilla_copyright = str_replace( array( '{{Y}}', '{Y}' ), date( 'Y' ), $chinchilla_copyright );
				// Replace {{...}} and ((...)) on the <i>...</i> and <b>...</b>
				$chinchilla_copyright = chinchilla_prepare_macros( $chinchilla_copyright );
				// Display copyright
				echo wp_kses( nl2br( $chinchilla_copyright ), 'chinchilla_kses_content' );
			}
			?>
			</div>
		</div>
	</div>
</div>
