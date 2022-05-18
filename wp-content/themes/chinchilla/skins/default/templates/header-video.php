<?php
/**
 * The template to display the background video in the header
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0.14
 */
$chinchilla_header_video = chinchilla_get_header_video();
$chinchilla_embed_video  = '';
if ( ! empty( $chinchilla_header_video ) && ! chinchilla_is_from_uploads( $chinchilla_header_video ) ) {
	if ( chinchilla_is_youtube_url( $chinchilla_header_video ) && preg_match( '/[=\/]([^=\/]*)$/', $chinchilla_header_video, $matches ) && ! empty( $matches[1] ) ) {
		?><div id="background_video" data-youtube-code="<?php echo esc_attr( $matches[1] ); ?>"></div>
		<?php
	} else {
		?>
		<div id="background_video"><?php chinchilla_show_layout( chinchilla_get_embed_video( $chinchilla_header_video ) ); ?></div>
		<?php
	}
}
