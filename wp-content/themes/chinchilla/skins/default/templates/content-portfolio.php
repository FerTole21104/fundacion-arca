<?php
/**
 * The Portfolio template to display the content
 *
 * Used for index/archive/search.
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

$chinchilla_template_args = get_query_var( 'chinchilla_template_args' );
if ( is_array( $chinchilla_template_args ) ) {
	$chinchilla_columns    = empty( $chinchilla_template_args['columns'] ) ? 2 : max( 1, $chinchilla_template_args['columns'] );
	$chinchilla_blog_style = array( $chinchilla_template_args['type'], $chinchilla_columns );
    $chinchilla_columns_class = chinchilla_get_column_class( 1, $chinchilla_columns, ! empty( $chinchilla_template_args['columns_tablet']) ? $chinchilla_template_args['columns_tablet'] : '', ! empty($chinchilla_template_args['columns_mobile']) ? $chinchilla_template_args['columns_mobile'] : '' );
} else {
	$chinchilla_blog_style = explode( '_', chinchilla_get_theme_option( 'blog_style' ) );
	$chinchilla_columns    = empty( $chinchilla_blog_style[1] ) ? 2 : max( 1, $chinchilla_blog_style[1] );
    $chinchilla_columns_class = chinchilla_get_column_class( 1, $chinchilla_columns );
}

$chinchilla_post_format = get_post_format();
$chinchilla_post_format = empty( $chinchilla_post_format ) ? 'standard' : str_replace( 'post-format-', '', $chinchilla_post_format );

?><div class="
<?php
if ( ! empty( $chinchilla_template_args['slider'] ) ) {
	echo ' slider-slide swiper-slide';
} else {
	echo ( chinchilla_is_blog_style_use_masonry( $chinchilla_blog_style[0] ) ? 'masonry_item masonry_item-1_' . esc_attr( $chinchilla_columns ) : esc_attr( $chinchilla_columns_class ));
}
?>
"><article id="post-<?php the_ID(); ?>" 
	<?php
	post_class(
		'post_item post_item_container post_format_' . esc_attr( $chinchilla_post_format )
		. ' post_layout_portfolio'
		. ' post_layout_portfolio_' . esc_attr( $chinchilla_columns )
		. ( 'portfolio' != $chinchilla_blog_style[0] ? ' ' . esc_attr( $chinchilla_blog_style[0] )  . '_' . esc_attr( $chinchilla_columns ) : '' )
	);
	chinchilla_add_blog_animation( $chinchilla_template_args );
	?>
>
<?php

	// Sticky label
	if ( is_sticky() && ! is_paged() ) {
		?>
		<span class="post_label label_sticky"></span>
		<?php
	}

	$chinchilla_hover   = ! empty( $chinchilla_template_args['hover'] ) && ! chinchilla_is_inherit( $chinchilla_template_args['hover'] )
								? $chinchilla_template_args['hover']
								: chinchilla_get_theme_option( 'image_hover' );

	if ( 'dots' == $chinchilla_hover ) {
		$chinchilla_post_link = empty( $chinchilla_template_args['no_links'] )
								? ( ! empty( $chinchilla_template_args['link'] )
									? $chinchilla_template_args['link']
									: get_permalink()
									)
								: '';
		$chinchilla_target    = ! empty( $chinchilla_post_link ) && false === strpos( $chinchilla_post_link, home_url() )
								? ' target="_blank" rel="nofollow"'
								: '';
	}
	
	// Meta parts
	$chinchilla_components = ! empty( $chinchilla_template_args['meta_parts'] )
							? ( is_array( $chinchilla_template_args['meta_parts'] )
								? $chinchilla_template_args['meta_parts']
								: explode( ',', $chinchilla_template_args['meta_parts'] )
								)
							: chinchilla_array_get_keys_by_value( chinchilla_get_theme_option( 'meta_parts' ) );

	// Featured image
	chinchilla_show_post_featured( apply_filters( 'chinchilla_filter_args_featured',
        array(
			'hover'         => $chinchilla_hover,
			'no_links'      => ! empty( $chinchilla_template_args['no_links'] ),
			'thumb_size'    => ! empty( $chinchilla_template_args['thumb_size'] )
								? $chinchilla_template_args['thumb_size']
								: chinchilla_get_thumb_size(
									chinchilla_is_blog_style_use_masonry( $chinchilla_blog_style[0] )
										? (	strpos( chinchilla_get_theme_option( 'body_style' ), 'full' ) !== false || $chinchilla_columns < 3
											? 'masonry-big'
											: 'masonry'
											)
										: (	strpos( chinchilla_get_theme_option( 'body_style' ), 'full' ) !== false || $chinchilla_columns < 3
											? 'square'
											: 'square'
											)
								),
			'thumb_bg' => chinchilla_is_blog_style_use_masonry( $chinchilla_blog_style[0] ) ? false : true,
			'show_no_image' => true,
			'meta_parts'    => $chinchilla_components,
			'class'         => 'dots' == $chinchilla_hover ? 'hover_with_info' : '',
			'post_info'     => 'dots' == $chinchilla_hover
										? '<div class="post_info"><h5 class="post_title">'
											. ( ! empty( $chinchilla_post_link )
												? '<a href="' . esc_url( $chinchilla_post_link ) . '"' . ( ! empty( $target ) ? $target : '' ) . '>'
												: ''
												)
												. esc_html( get_the_title() ) 
											. ( ! empty( $chinchilla_post_link )
												? '</a>'
												: ''
												)
											. '</h5></div>'
										: '',
            'thumb_ratio'   => 'info' == $chinchilla_hover ?  '100:102' : '',
        ),
        'content-portfolio',
        $chinchilla_template_args
    ) );
	?>
</article></div><?php
// Need opening PHP-tag above, because <article> is a inline-block element (used as column)!