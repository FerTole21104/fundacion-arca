<?php
/**
 * The template to display the widgets area in the header
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

// Header sidebar
$chinchilla_header_name    = chinchilla_get_theme_option( 'header_widgets' );
$chinchilla_header_present = ! chinchilla_is_off( $chinchilla_header_name ) && is_active_sidebar( $chinchilla_header_name );
if ( $chinchilla_header_present ) {
	chinchilla_storage_set( 'current_sidebar', 'header' );
	$chinchilla_header_wide = chinchilla_get_theme_option( 'header_wide' );
	ob_start();
	if ( is_active_sidebar( $chinchilla_header_name ) ) {
		dynamic_sidebar( $chinchilla_header_name );
	}
	$chinchilla_widgets_output = ob_get_contents();
	ob_end_clean();
	if ( ! empty( $chinchilla_widgets_output ) ) {
		$chinchilla_widgets_output = preg_replace( "/<\/aside>[\r\n\s]*<aside/", '</aside><aside', $chinchilla_widgets_output );
		$chinchilla_need_columns   = strpos( $chinchilla_widgets_output, 'columns_wrap' ) === false;
		if ( $chinchilla_need_columns ) {
			$chinchilla_columns = max( 0, (int) chinchilla_get_theme_option( 'header_columns' ) );
			if ( 0 == $chinchilla_columns ) {
				$chinchilla_columns = min( 6, max( 1, chinchilla_tags_count( $chinchilla_widgets_output, 'aside' ) ) );
			}
			if ( $chinchilla_columns > 1 ) {
				$chinchilla_widgets_output = preg_replace( '/<aside([^>]*)class="widget/', '<aside$1class="column-1_' . esc_attr( $chinchilla_columns ) . ' widget', $chinchilla_widgets_output );
			} else {
				$chinchilla_need_columns = false;
			}
		}
		?>
		<div class="header_widgets_wrap widget_area<?php echo ! empty( $chinchilla_header_wide ) ? ' header_fullwidth' : ' header_boxed'; ?>">
			<?php do_action( 'chinchilla_action_before_sidebar_wrap', 'header' ); ?>
			<div class="header_widgets_inner widget_area_inner">
				<?php
				if ( ! $chinchilla_header_wide ) {
					?>
					<div class="content_wrap">
					<?php
				}
				if ( $chinchilla_need_columns ) {
					?>
					<div class="columns_wrap">
					<?php
				}
				do_action( 'chinchilla_action_before_sidebar', 'header' );
				chinchilla_show_layout( $chinchilla_widgets_output );
				do_action( 'chinchilla_action_after_sidebar', 'header' );
				if ( $chinchilla_need_columns ) {
					?>
					</div>	<!-- /.columns_wrap -->
					<?php
				}
				if ( ! $chinchilla_header_wide ) {
					?>
					</div>	<!-- /.content_wrap -->
					<?php
				}
				?>
			</div>	<!-- /.header_widgets_inner -->
			<?php do_action( 'chinchilla_action_after_sidebar_wrap', 'header' ); ?>
		</div>	<!-- /.header_widgets_wrap -->
		<?php
	}
}
