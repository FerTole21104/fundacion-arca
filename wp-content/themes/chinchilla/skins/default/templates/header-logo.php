<?php
/**
 * The template to display the logo or the site name and the slogan in the Header
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

$chinchilla_args = get_query_var( 'chinchilla_logo_args' );

// Site logo
$chinchilla_logo_type   = isset( $chinchilla_args['type'] ) ? $chinchilla_args['type'] : '';
$chinchilla_logo_image  = chinchilla_get_logo_image( $chinchilla_logo_type );
$chinchilla_logo_text   = chinchilla_is_on( chinchilla_get_theme_option( 'logo_text' ) ) ? get_bloginfo( 'name' ) : '';
$chinchilla_logo_slogan = get_bloginfo( 'description', 'display' );
if ( ! empty( $chinchilla_logo_image['logo'] ) || ! empty( $chinchilla_logo_text ) ) {
	?><a class="sc_layouts_logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php
		if ( ! empty( $chinchilla_logo_image['logo'] ) ) {
			if ( empty( $chinchilla_logo_type ) && function_exists( 'the_custom_logo' ) && is_numeric($chinchilla_logo_image['logo']) && (int) $chinchilla_logo_image['logo'] > 0 ) {
				the_custom_logo();
			} else {
				$chinchilla_attr = chinchilla_getimagesize( $chinchilla_logo_image['logo'] );
				echo '<img src="' . esc_url( $chinchilla_logo_image['logo'] ) . '"'
						. ( ! empty( $chinchilla_logo_image['logo_retina'] ) ? ' srcset="' . esc_url( $chinchilla_logo_image['logo_retina'] ) . ' 2x"' : '' )
						. ' alt="' . esc_attr( $chinchilla_logo_text ) . '"'
						. ( ! empty( $chinchilla_attr[3] ) ? ' ' . wp_kses_data( $chinchilla_attr[3] ) : '' )
						. '>';
			}
		} else {
			chinchilla_show_layout( chinchilla_prepare_macros( $chinchilla_logo_text ), '<span class="logo_text">', '</span>' );
			chinchilla_show_layout( chinchilla_prepare_macros( $chinchilla_logo_slogan ), '<span class="logo_slogan">', '</span>' );
		}
		?>
	</a>
	<?php
}
