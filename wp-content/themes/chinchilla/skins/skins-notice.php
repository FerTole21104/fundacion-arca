<?php
/**
 * The template to display Admin notices
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.64
 */

$chinchilla_skins_url  = get_admin_url( null, 'admin.php?page=trx_addons_theme_panel#trx_addons_theme_panel_section_skins' );
$chinchilla_skins_args = get_query_var( 'chinchilla_skins_notice_args' );
?>
<div class="chinchilla_admin_notice chinchilla_skins_notice notice notice-info is-dismissible" data-notice="skins">
	<?php
	// Theme image
	$chinchilla_theme_img = chinchilla_get_file_url( 'screenshot.jpg' );
	if ( '' != $chinchilla_theme_img ) {
		?>
		<div class="chinchilla_notice_image"><img src="<?php echo esc_url( $chinchilla_theme_img ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'chinchilla' ); ?>"></div>
		<?php
	}

	// Title
	?>
	<h3 class="chinchilla_notice_title">
		<?php esc_html_e( 'New skins available', 'chinchilla' ); ?>
	</h3>
	<?php

	// Description
	$chinchilla_total      = $chinchilla_skins_args['update'];	// Store value to the separate variable to avoid warnings from ThemeCheck plugin!
	$chinchilla_skins_msg  = $chinchilla_total > 0
							// Translators: Add new skins number
							? '<strong>' . sprintf( _n( '%d new version', '%d new versions', $chinchilla_total, 'chinchilla' ), $chinchilla_total ) . '</strong>'
							: '';
	$chinchilla_total      = $chinchilla_skins_args['free'];
	$chinchilla_skins_msg .= $chinchilla_total > 0
							? ( ! empty( $chinchilla_skins_msg ) ? ' ' . esc_html__( 'and', 'chinchilla' ) . ' ' : '' )
								// Translators: Add new skins number
								. '<strong>' . sprintf( _n( '%d free skin', '%d free skins', $chinchilla_total, 'chinchilla' ), $chinchilla_total ) . '</strong>'
							: '';
	$chinchilla_total      = $chinchilla_skins_args['pay'];
	$chinchilla_skins_msg .= $chinchilla_skins_args['pay'] > 0
							? ( ! empty( $chinchilla_skins_msg ) ? ' ' . esc_html__( 'and', 'chinchilla' ) . ' ' : '' )
								// Translators: Add new skins number
								. '<strong>' . sprintf( _n( '%d paid skin', '%d paid skins', $chinchilla_total, 'chinchilla' ), $chinchilla_total ) . '</strong>'
							: '';
	?>
	<div class="chinchilla_notice_text">
		<p>
			<?php
			// Translators: Add new skins info
			echo wp_kses_data( sprintf( __( "We are pleased to announce that %s are available for your theme", 'chinchilla' ), $chinchilla_skins_msg ) );
			?>
		</p>
	</div>
	<?php

	// Buttons
	?>
	<div class="chinchilla_notice_buttons">
		<?php
		// Link to the theme dashboard page
		?>
		<a href="<?php echo esc_url( $chinchilla_skins_url ); ?>" class="button button-primary"><i class="dashicons dashicons-update"></i> 
			<?php
			// Translators: Add theme name
			esc_html_e( 'Go to Skins manager', 'chinchilla' );
			?>
		</a>
	</div>
</div>
