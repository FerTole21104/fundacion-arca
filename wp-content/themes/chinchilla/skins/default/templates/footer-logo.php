<?php
/**
 * The template to display the site logo in the footer
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.10
 */

// Logo
if ( chinchilla_is_on( chinchilla_get_theme_option( 'logo_in_footer' ) ) ) {
	$chinchilla_logo_image = chinchilla_get_logo_image( 'footer' );
	$chinchilla_logo_text  = get_bloginfo( 'name' );
	if ( ! empty( $chinchilla_logo_image['logo'] ) || ! empty( $chinchilla_logo_text ) ) {
		?>
		<div class="footer_logo_wrap">
			<div class="footer_logo_inner">
				<?php
				if ( ! empty( $chinchilla_logo_image['logo'] ) ) {
					$chinchilla_attr = chinchilla_getimagesize( $chinchilla_logo_image['logo'] );
					echo '<a href="' . esc_url( home_url( '/' ) ) . '">'
							. '<img src="' . esc_url( $chinchilla_logo_image['logo'] ) . '"'
								. ( ! empty( $chinchilla_logo_image['logo_retina'] ) ? ' srcset="' . esc_url( $chinchilla_logo_image['logo_retina'] ) . ' 2x"' : '' )
								. ' class="logo_footer_image"'
								. ' alt="' . esc_attr__( 'Site logo', 'chinchilla' ) . '"'
								. ( ! empty( $chinchilla_attr[3] ) ? ' ' . wp_kses_data( $chinchilla_attr[3] ) : '' )
							. '>'
						. '</a>';
				} elseif ( ! empty( $chinchilla_logo_text ) ) {
					echo '<h1 class="logo_footer_text">'
							. '<a href="' . esc_url( home_url( '/' ) ) . '">'
								. esc_html( $chinchilla_logo_text )
							. '</a>'
						. '</h1>';
				}
				?>
			</div>
		</div>
		<?php
	}
}
