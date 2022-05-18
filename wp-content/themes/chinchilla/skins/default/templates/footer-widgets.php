<?php
/**
 * The template to display the widgets area in the footer
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.10
 */

// Footer sidebar
$chinchilla_footer_name    = chinchilla_get_theme_option( 'footer_widgets' );
$chinchilla_footer_present = ! chinchilla_is_off( $chinchilla_footer_name ) && is_active_sidebar( $chinchilla_footer_name );
if ( $chinchilla_footer_present ) {
	chinchilla_storage_set( 'current_sidebar', 'footer' );
	$chinchilla_footer_wide = chinchilla_get_theme_option( 'footer_wide' );
	ob_start();
	if ( is_active_sidebar( $chinchilla_footer_name ) ) {
		dynamic_sidebar( $chinchilla_footer_name );
	}
	$chinchilla_out = trim( ob_get_contents() );
	ob_end_clean();
	if ( ! empty( $chinchilla_out ) ) {
		$chinchilla_out          = preg_replace( "/<\\/aside>[\r\n\s]*<aside/", '</aside><aside', $chinchilla_out );
		$chinchilla_need_columns = true;   //or check: strpos($chinchilla_out, 'columns_wrap')===false;
		if ( $chinchilla_need_columns ) {
			$chinchilla_columns = max( 0, (int) chinchilla_get_theme_option( 'footer_columns' ) );			
			if ( 0 == $chinchilla_columns ) {
				$chinchilla_columns = min( 4, max( 1, chinchilla_tags_count( $chinchilla_out, 'aside' ) ) );
			}
			if ( $chinchilla_columns > 1 ) {
				$chinchilla_out = preg_replace( '/<aside([^>]*)class="widget/', '<aside$1class="column-1_' . esc_attr( $chinchilla_columns ) . ' widget', $chinchilla_out );
			} else {
				$chinchilla_need_columns = false;
			}
		}
		?>
		<div class="footer_widgets_wrap widget_area<?php echo ! empty( $chinchilla_footer_wide ) ? ' footer_fullwidth' : ''; ?> sc_layouts_row sc_layouts_row_type_normal">
			<?php do_action( 'chinchilla_action_before_sidebar_wrap', 'footer' ); ?>
			<div class="footer_widgets_inner widget_area_inner">
				<?php
				if ( ! $chinchilla_footer_wide ) {
					?>
					<div class="content_wrap">
					<?php
				}
				if ( $chinchilla_need_columns ) {
					?>
					<div class="columns_wrap">
					<?php
				}
				do_action( 'chinchilla_action_before_sidebar', 'footer' );
				chinchilla_show_layout( $chinchilla_out );
				do_action( 'chinchilla_action_after_sidebar', 'footer' );
				if ( $chinchilla_need_columns ) {
					?>
					</div><!-- /.columns_wrap -->
					<?php
				}
				if ( ! $chinchilla_footer_wide ) {
					?>
					</div><!-- /.content_wrap -->
					<?php
				}
				?>
			</div><!-- /.footer_widgets_inner -->
			<?php do_action( 'chinchilla_action_after_sidebar_wrap', 'footer' ); ?>
		</div><!-- /.footer_widgets_wrap -->
		<?php
	}
}
