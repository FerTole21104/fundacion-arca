<?php
/**
 * The template to display the page title and breadcrumbs
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

// Page (category, tag, archive, author) title

if ( chinchilla_need_page_title() ) {
	chinchilla_sc_layouts_showed( 'title', true );
	chinchilla_sc_layouts_showed( 'postmeta', true );
	?>
	<div class="top_panel_title sc_layouts_row sc_layouts_row_type_normal">
		<div class="content_wrap">
			<div class="sc_layouts_column sc_layouts_column_align_center">
				<div class="sc_layouts_item">
					<div class="sc_layouts_title sc_align_center">
						<?php
						// Post meta on the single post
						if ( is_single() ) {
							?>
							<div class="sc_layouts_title_meta">
							<?php
								chinchilla_show_post_meta(
									apply_filters(
										'chinchilla_filter_post_meta_args', array(
											'components' => join( ',', chinchilla_array_get_keys_by_value( chinchilla_get_theme_option( 'meta_parts' ) ) ),
											'counters'   => join( ',', chinchilla_array_get_keys_by_value( chinchilla_get_theme_option( 'counters' ) ) ),
											'seo'        => chinchilla_is_on( chinchilla_get_theme_option( 'seo_snippets' ) ),
										), 'header', 1
									)
								);
							?>
							</div>
							<?php
						}

						// Blog/Post title
						?>
						<div class="sc_layouts_title_title">
							<?php
							$chinchilla_blog_title           = chinchilla_get_blog_title();
							$chinchilla_blog_title_text      = '';
							$chinchilla_blog_title_class     = '';
							$chinchilla_blog_title_link      = '';
							$chinchilla_blog_title_link_text = '';
							if ( is_array( $chinchilla_blog_title ) ) {
								$chinchilla_blog_title_text      = $chinchilla_blog_title['text'];
								$chinchilla_blog_title_class     = ! empty( $chinchilla_blog_title['class'] ) ? ' ' . $chinchilla_blog_title['class'] : '';
								$chinchilla_blog_title_link      = ! empty( $chinchilla_blog_title['link'] ) ? $chinchilla_blog_title['link'] : '';
								$chinchilla_blog_title_link_text = ! empty( $chinchilla_blog_title['link_text'] ) ? $chinchilla_blog_title['link_text'] : '';
							} else {
								$chinchilla_blog_title_text = $chinchilla_blog_title;
							}
							?>
							<h1 itemprop="headline" class="sc_layouts_title_caption<?php echo esc_attr( $chinchilla_blog_title_class ); ?>">
								<?php
								$chinchilla_top_icon = chinchilla_get_term_image_small();
								if ( ! empty( $chinchilla_top_icon ) ) {
									$chinchilla_attr = chinchilla_getimagesize( $chinchilla_top_icon );
									?>
									<img src="<?php echo esc_url( $chinchilla_top_icon ); ?>" alt="<?php esc_attr_e( 'Site icon', 'chinchilla' ); ?>"
										<?php
										if ( ! empty( $chinchilla_attr[3] ) ) {
											chinchilla_show_layout( $chinchilla_attr[3] );
										}
										?>
									>
									<?php
								}
								echo wp_kses_data( $chinchilla_blog_title_text );
								?>
							</h1>
							<?php
							if ( ! empty( $chinchilla_blog_title_link ) && ! empty( $chinchilla_blog_title_link_text ) ) {
								?>
								<a href="<?php echo esc_url( $chinchilla_blog_title_link ); ?>" class="theme_button theme_button_small sc_layouts_title_link"><?php echo esc_html( $chinchilla_blog_title_link_text ); ?></a>
								<?php
							}

							// Category/Tag description
							if ( ! is_paged() && ( is_category() || is_tag() || is_tax() ) ) {
								the_archive_description( '<div class="sc_layouts_title_description">', '</div>' );
							}

							?>
						</div>
						<?php

						// Breadcrumbs
						ob_start();
						do_action( 'chinchilla_action_breadcrumbs' );
						$chinchilla_breadcrumbs = ob_get_contents();
						ob_end_clean();
						chinchilla_show_layout( $chinchilla_breadcrumbs, '<div class="sc_layouts_title_breadcrumbs">', '</div>' );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
