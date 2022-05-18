<?php
/**
 * The template 'Style 1' to displaying related posts
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

$chinchilla_link        = get_permalink();
$chinchilla_post_format = get_post_format();
$chinchilla_post_format = empty( $chinchilla_post_format ) ? 'standard' : str_replace( 'post-format-', '', $chinchilla_post_format );
?><div id="post-<?php the_ID(); ?>" <?php post_class( 'related_item post_format_' . esc_attr( $chinchilla_post_format ) ); ?> data-post-id="<?php the_ID(); ?>">
	<?php
	chinchilla_show_post_featured(
		array(
			'thumb_size'    => apply_filters( 'chinchilla_filter_related_thumb_size', chinchilla_get_thumb_size( (int) chinchilla_get_theme_option( 'related_posts' ) == 1 ? 'huge' : 'big' ) ),
			'post_info'     => '<div class="post_header entry-header">'
									. '<div class="post_categories">' . wp_kses( chinchilla_get_post_categories( '' ), 'chinchilla_kses_content' ) . '</div>'
									. '<h6 class="post_title entry-title"><a href="' . esc_url( $chinchilla_link ) . '">'
										. wp_kses_data( '' == get_the_title() ? esc_html__( '- No title -', 'chinchilla' ) : get_the_title() )
									. '</a></h6>'
									. ( in_array( get_post_type(), array( 'post', 'attachment' ) )
											? '<div class="post_meta"><a href="' . esc_url( $chinchilla_link ) . '" class="post_meta_item post_date">' . wp_kses_data( chinchilla_get_date() ) . '</a></div>'
											: '' )
								. '</div>',
		)
	);
	?>
</div>
