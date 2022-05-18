<?php
/**
 * Theme Options, Color Schemes and Fonts utilities
 *
 * @package CHINCHILLA
 * @since CHINCHILLA 1.0
 */

// -----------------------------------------------------------------
// -- Create and manage Theme Options
// -----------------------------------------------------------------

// Theme init priorities:
// 2 - create Theme Options
if ( ! function_exists( 'chinchilla_options_theme_setup2' ) ) {
	add_action( 'after_setup_theme', 'chinchilla_options_theme_setup2', 2 );
	function chinchilla_options_theme_setup2() {
		chinchilla_create_theme_options();
	}
}

// Theme init priorities:
// 3 - add/remove Theme Options elements
// Make some options titles translatable
if ( ! function_exists( 'chinchilla_options_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'chinchilla_options_theme_setup3', 3 );
	function chinchilla_options_theme_setup3() {
		$translates = array(
			'font-family'     => esc_html__( 'Font family', 'chinchilla' ),
			'font-size'       => esc_html__( 'Font size', 'chinchilla' ),
			'font-weight'     => esc_html__( 'Font weight', 'chinchilla' ),
			'font-style'      => esc_html__( 'Font style', 'chinchilla' ),
			'line-height'     => esc_html__( 'Line height', 'chinchilla' ),
			'text-decoration' => esc_html__( 'Text decoration', 'chinchilla' ),
			'text-transform'  => esc_html__( 'Text transform', 'chinchilla' ),
			'letter-spacing'  => esc_html__( 'Letter spacing', 'chinchilla' ),
			'margin-top'      => esc_html__( 'Top margin', 'chinchilla' ),
			'margin-bottom'   => esc_html__( 'Bottom margin', 'chinchilla' ),
		);
		global $CHINCHILLA_STORAGE;
		foreach ( $CHINCHILLA_STORAGE['options'] as $k => $v ) {
			foreach ( $translates as $tk => $tv ) {
				if ( strpos( $k, $tk ) !== false ) {
					$CHINCHILLA_STORAGE['options'][ $k ]['title'] = $tv;
					break;
				}
			}
		}
	}
}

// Step 1: Load default settings and previously saved mods
if ( ! function_exists( 'chinchilla_options_theme_setup5' ) ) {
	add_action( 'after_setup_theme', 'chinchilla_options_theme_setup5', 5 );
	function chinchilla_options_theme_setup5() {
		chinchilla_storage_set( 'options_reloaded', false );
		chinchilla_load_theme_options();
	}
}

// Step 2: Load current theme customization mods
if ( is_customize_preview() ) {
	if ( ! function_exists( 'chinchilla_load_custom_options' ) ) {
		add_action( 'wp_loaded', 'chinchilla_load_custom_options' );
		function chinchilla_load_custom_options() {
			if ( ! chinchilla_storage_get( 'options_reloaded' ) ) {
				chinchilla_storage_set( 'options_reloaded', true );
				chinchilla_load_theme_options();
			}
		}
	}
}

// Load current values for each customizable option
if ( ! function_exists( 'chinchilla_load_theme_options' ) ) {
	function chinchilla_load_theme_options() {
		$reset   = (int) get_theme_mod( 'reset_options', 0 );
		global $CHINCHILLA_STORAGE;
		foreach ( $CHINCHILLA_STORAGE['options'] as $k => $v ) {
			if ( isset( $v['std'] ) ) {
				$value = chinchilla_get_theme_option_std( $k, $v['std'] );
				if ( ! $reset ) {
					if ( isset( $_GET[ $k ] ) ) {
						$value = wp_kses_data( wp_unslash( $_GET[ $k ] ) );
					} else {
						$default_value = -987654321;
						$tmp           = get_theme_mod( $k, $default_value );
						if ( $tmp != $default_value ) {
							$value = $tmp;
						}
					}
				}
				$CHINCHILLA_STORAGE['options'][ $k ]['val'] = $value;
				if ( $reset ) {
					remove_theme_mod( $k );
				}
			}
		}
		if ( $reset ) {
			// Unset reset flag
			set_theme_mod( 'reset_options', 0 );
			// Regenerate CSS with default colors and fonts
			chinchilla_customizer_save_css();
		} else {
			do_action( 'chinchilla_action_load_options' );
		}
	}
}

// Override options with stored page/post meta
if ( ! function_exists( 'chinchilla_override_theme_options' ) ) {
	add_action( 'wp', 'chinchilla_override_theme_options', 1 );
	function chinchilla_override_theme_options( $query_vars = null, $page_id = 0 ) {
		if ( $page_id > 0 || is_page_template( 'blog.php' ) ) {
			chinchilla_storage_set( 'blog_archive', true );
			chinchilla_storage_set( 'blog_template', $page_id > 0 ? $page_id : get_the_ID() );
		}
		chinchilla_storage_set( 'blog_mode', $page_id > 0 ? 'blog' : chinchilla_detect_blog_mode() );
		if ( $page_id > 0 || chinchilla_is_singular() ) {
			chinchilla_storage_set( 'options_meta', get_post_meta( $page_id > 0 ? $page_id : get_the_ID(), 'chinchilla_options', true ) );
		}
		do_action( 'chinchilla_action_override_theme_options' );
	}
}

// Override options with stored page meta on 'Blog posts' pages
if ( ! function_exists( 'chinchilla_blog_override_theme_options' ) ) {
	add_action( 'chinchilla_action_override_theme_options', 'chinchilla_blog_override_theme_options' );
	function chinchilla_blog_override_theme_options() {
		global $wp_query;
		if ( is_home() && ! is_front_page() && ! empty( $wp_query->is_posts_page ) ) {
			$id = get_option( 'page_for_posts' );
			if ( $id > 0 ) {
				chinchilla_storage_set( 'options_meta', get_post_meta( $id, 'chinchilla_options', true ) );
			}
		}
	}
}


// Return 'std' value of the option, processed by special function (if specified)
if ( ! function_exists( 'chinchilla_get_theme_option_std' ) ) {
	function chinchilla_get_theme_option_std( $opt_name, $opt_std ) {
		if ( ! is_array( $opt_std ) && strpos( $opt_std, '$chinchilla_' ) !== false ) {
			$func = substr( $opt_std, 1 );
			if ( function_exists( $func ) ) {
				$opt_std = $func( $opt_name );
			}
		}
		return $opt_std;
	}
}


// Return customizable option value
if ( ! function_exists( 'chinchilla_get_theme_option' ) ) {
	function chinchilla_get_theme_option( $name, $defa = '', $strict_mode = false, $post_id = 0 ) {

		$rez            = $defa;
		$from_post_meta = false;

		if ( $post_id > 0 ) {
			if ( ! chinchilla_storage_isset( 'post_options_meta', $post_id ) ) {
				chinchilla_storage_set_array( 'post_options_meta', $post_id, get_post_meta( $post_id, 'chinchilla_options', true ) );
			}
			if ( chinchilla_storage_isset( 'post_options_meta', $post_id, $name ) ) {
				$tmp = chinchilla_storage_get_array( 'post_options_meta', $post_id, $name );
				if ( ! chinchilla_is_inherit( $tmp ) ) {
					$rez            = $tmp;
					$from_post_meta = true;
				}
			}
		}

		if ( ! $from_post_meta && chinchilla_storage_isset( 'options' ) ) {

			$blog_mode   = chinchilla_storage_get( 'blog_mode' );
			$mobile_mode = wp_is_mobile() ? 'mobile' : '';

			if ( ! chinchilla_storage_isset( 'options', $name )
				&& ( empty( $blog_mode ) || ! chinchilla_storage_isset( 'options', $name . '_' . $blog_mode ) )
				&& ( ! chinchilla_storage_isset( 'options_meta', $name ) || chinchilla_is_inherit( chinchilla_storage_get_array( 'options_meta', $name ) ) )
			) {

				$rez = '_not_exists_';
				$tmp = $rez;
				if ( function_exists( 'trx_addons_get_option' ) ) {
					$rez = trx_addons_get_option( $name, $tmp, false );
				}
				if ( $rez === $tmp ) {
					if ( $strict_mode && func_num_args() == 1 ) {
						$s = '';
						if ( function_exists( 'ddo' ) ) {
							$s = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
							array_shift($s);
							$s = ddo($s, 0, 3);
						}
						wp_die(
							// Translators: Add option's name to the message
							esc_html( sprintf( __( 'Undefined option "%s"', 'chinchilla' ), $name ) )
							. ( ! empty( $s )
									? ' ' . esc_html( __( 'called from:', 'chinchilla' ) ) . "<pre>" . wp_kses_data( $s ) . '</pre>'
									: ''
									)
						);
					} else {
						$rez = $defa;
					}
				}

			} else {

				// Single meta name: 'expand_content' -> 'expand_content_single'
				$single_meta_name = $name . ( chinchilla_is_single() && substr( $name, -7 ) != '_single' ? '_single' : '' );

				// Single option name: 'expand_content' -> 'expand_content_single'
				// If 'override_option_single' == 'post' - override option allowed only for post type 'post', otherwise - for all CPT
				$single_name = $name . ( ( chinchilla_get_theme_setting( 'override_option_single', 'post' ) == 'post'
											? chinchilla_is_singular( 'post' )
											: chinchilla_is_single()
											)
										&& substr( $name, -7 ) != '_single'
											? '_single'
											: ''
										);

				// Parent mode: 'team_single' -> 'team', 
				//              'post', 'home', 'category', 'tag', 'archive', 'author', 'search' -> 'blog'
				$blog_mode_parent = apply_filters( 
										'chinchilla_filter_blog_mode_parent',
										in_array( $blog_mode, array( 'post', 'home', 'category', 'tag', 'archive', 'author', 'search' ) )
											? 'blog'
											: str_replace( '_single', '', $blog_mode )
									);

				// Parent option name for posts: 'expand_content_single' -> 'expand_content_blog'
				$blog_name = 'post' == $blog_mode && substr( $name, -7 ) == '_single'
								? str_replace( '_single', '_blog', $name )
								: ( 'home' == $blog_mode && substr( $name, -5 ) != '_blog'
									? $name . '_blog'
									: ''
									);

				// Parent option name for CPT: 'expand_content_single_team' -> 'expand_content_team'
				$parent_name = strpos( $name, '_single') !== false ? str_replace( '_single', '', $name ) : '';

				// Get 'xxx_single' instead 'xxx_post'
				if ( 'post' == $blog_mode ) {
					$blog_mode = 'single';
				}

				// Override option from GET or POST for current blog mode
				// example: request 'expand_content_single_team'
				if ( ! empty( $blog_mode ) && isset( $_REQUEST[ $name . '_' . $blog_mode ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_REQUEST[ $name . '_' . $blog_mode ] ) );

					// Override option from GET or POST
					// example: request 'expand_content_single'
				} elseif ( isset( $_REQUEST[ $name ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_REQUEST[ $name ] ) );

				// Override option from COOKIE for current blog mode
				// example: request 'expand_content_single_team'
				}else if ( ! empty( $blog_mode ) && isset( $_COOKIE[ $name . '_' . $blog_mode ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_COOKIE[ $name . '_' . $blog_mode ] ) );

					// Override option from COOKIE
					// example: request 'expand_content_single'
				} elseif ( isset( $_COOKIE[ $name ] ) ) {
					$rez = wp_kses_data( wp_unslash( $_COOKIE[ $name ] ) );

					// Override option from current page settings (if exists) with mobile mode
					// example: meta 'expand_content_single_mobile'
				} elseif ( ! empty( $mobile_mode ) && chinchilla_storage_isset( 'options_meta', $name . '_' . $mobile_mode ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options_meta', $name . '_' . $mobile_mode ) ) ) {
					$rez = chinchilla_storage_get_array( 'options_meta', $name . '_' . $mobile_mode );

					// Override single option with mobile mode
					// example: option 'expand_content_single_mobile'
				} elseif ( ! empty( $mobile_mode ) && $single_name != $name && chinchilla_storage_isset( 'options', $single_name . '_' . $mobile_mode, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $single_name . '_' . $mobile_mode, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $single_name . '_' . $mobile_mode, 'val' );

					// Override option with mobile mode
					// example: option 'expand_content_mobile'
				} elseif ( ! empty( $mobile_mode ) && chinchilla_storage_isset( 'options', $name . '_' . $mobile_mode, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $name . '_' . $mobile_mode, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $name . '_' . $mobile_mode, 'val' );

					// Override option from current page settings (if exists)
					// example: meta 'expand_content'
				} elseif ( chinchilla_storage_isset( 'options_meta', $name ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options_meta', $name ) ) ) {
					$rez = chinchilla_storage_get_array( 'options_meta', $name );

					// Override option from current page settings (if exists)
					// example: meta 'expand_content_single'
				} elseif ( $single_meta_name != $name && chinchilla_storage_isset( 'options_meta', $single_meta_name ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options_meta', $single_meta_name ) ) ) {
					$rez = chinchilla_storage_get_array( 'options_meta', $single_meta_name );

					// Override option from current blog mode settings: 'front', 'search', 'page', 'post', 'blog', etc. (if exists)
					// if 'override_option_single' == 'all' - override allowed for any CPT
					// example: option 'expand_content_single_team'
				} elseif ( ! empty( $blog_mode ) && $single_name != $name && chinchilla_storage_isset( 'options', $single_name . '_' . $blog_mode, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $single_name . '_' . $blog_mode, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $single_name . '_' . $blog_mode, 'val' );

					// Override option from current blog mode settings: 'front', 'search', 'page', 'post', 'blog', etc. (if exists)
					// 'override_option_single' == 'post' - override allowed only for 'post', check 'xxx_single_CPT' manually
					// example: option 'expand_content_single_team'
				} elseif ( ! empty( $blog_mode ) && chinchilla_is_single() && $single_name == $name && ! in_array( $blog_mode, array( 'front', 'search', 'page', 'post', 'blog' ) ) && chinchilla_storage_isset( 'options', $name . '_single_' . $blog_mode, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $name . '_single_' . $blog_mode, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $name . '_single_' . $blog_mode, 'val' );

					// Override option from current blog mode settings: 'front', 'search', 'page', 'post', 'blog', etc. (if exists)
					// example: option 'expand_content_team'
				} elseif ( ! empty( $blog_mode ) && chinchilla_storage_isset( 'options', $name . '_' . $blog_mode, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $name . '_' . $blog_mode, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $name . '_' . $blog_mode, 'val' );

					// Override option from parent blog mode
					// example: option 'expand_content_team'
				} elseif ( ! empty( $blog_mode ) && ! empty( $parent_name ) && $parent_name != $name && chinchilla_storage_isset( 'options', $parent_name . '_' . $blog_mode, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $parent_name . '_' . $blog_mode, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $parent_name . '_' . $blog_mode, 'val' );

					// Override option for 'post' from 'blog' settings (if exists)
					// Also used for override 'xxx_single' on the 'xxx'
					// (instead 'sidebar_courses_single' return option for 'sidebar_courses')
					// example: option 'expand_content_single_team'
				} elseif ( ! empty( $blog_mode_parent ) && $blog_mode != $blog_mode_parent && $single_name != $name && chinchilla_storage_isset( 'options', $single_name . '_' . $blog_mode_parent, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $single_name . '_' . $blog_mode_parent, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $single_name . '_' . $blog_mode_parent, 'val' );

				} elseif ( ! empty( $blog_mode_parent ) && $blog_mode != $blog_mode_parent && chinchilla_storage_isset( 'options', $name . '_' . $blog_mode_parent, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $name . '_' . $blog_mode_parent, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $name . '_' . $blog_mode_parent, 'val' );

				} elseif ( ! empty( $blog_mode_parent ) && $blog_mode != $blog_mode_parent && $parent_name != $name && chinchilla_storage_isset( 'options', $parent_name . '_' . $blog_mode_parent, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $parent_name . '_' . $blog_mode_parent, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $parent_name . '_' . $blog_mode_parent, 'val' );

					// Get saved option value for single post
					// example: option 'expand_content_single'
				} elseif ( chinchilla_storage_isset( 'options', $single_name, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $single_name, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $single_name, 'val' );

					// Get saved option value
					// example: option 'expand_content'
				} elseif ( chinchilla_storage_isset( 'options', $name, 'val' ) && $single_name != $name && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $name, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $name, 'val' );

					// Override option for '_single' from '_blog' settings (if exists)
					// example: option 'expand_content_blog'
				} elseif ( ! empty( $blog_name ) && chinchilla_storage_isset( 'options', $blog_name, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $blog_name, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $blog_name, 'val' );

					// Override option for '_single' from parent settings (if exists)
					// example: option 'expand_content'
				} elseif ( ! empty( $parent_name ) && $parent_name != $name && chinchilla_storage_isset( 'options', $parent_name, 'val' ) && ! chinchilla_is_inherit( chinchilla_storage_get_array( 'options', $parent_name, 'val' ) ) ) {
					$rez = chinchilla_storage_get_array( 'options', $parent_name, 'val' );

					// Get saved option value if nobody override it
					// example: option 'expand_content'
				} elseif ( chinchilla_storage_isset( 'options', $name, 'val' ) ) {
					$rez = chinchilla_storage_get_array( 'options', $name, 'val' );

					// Get ThemeREX Addons option value
				} elseif ( function_exists( 'trx_addons_get_option' ) ) {
					$rez = trx_addons_get_option( $name, $defa, false );

				}
			}
		}

		return $rez;
	}
}


// Check if customizable option exists
if ( ! function_exists( 'chinchilla_check_theme_option' ) ) {
	function chinchilla_check_theme_option( $name ) {
		return chinchilla_storage_isset( 'options', $name );
	}
}


// Return customizable option value, stored in the posts meta
if ( ! function_exists( 'chinchilla_get_theme_option_from_meta' ) ) {
	function chinchilla_get_theme_option_from_meta( $name, $defa = '' ) {
		$rez = $defa;
		if ( chinchilla_storage_isset( 'options_meta' ) ) {
			if ( chinchilla_storage_isset( 'options_meta', $name ) ) {
				$rez = chinchilla_storage_get_array( 'options_meta', $name );
			} else {
				$rez = 'inherit';
			}
		}
		return $rez;
	}
}


// Get dependencies list from the Theme Options
if ( ! function_exists( 'chinchilla_get_theme_dependencies' ) ) {
	function chinchilla_get_theme_dependencies() {
		$depends = array();
		global $CHINCHILLA_STORAGE;
		foreach ( $CHINCHILLA_STORAGE['options'] as $k => $v ) {
			if ( isset( $v['dependency'] ) ) {
				$depends[ $k ] = $v['dependency'];
			}
		}
		return apply_filters( 'chinchilla_filter_get_theme_dependencies', $depends );
	}
}



//------------------------------------------------
// Save options
//------------------------------------------------
if ( ! function_exists( 'chinchilla_options_save' ) ) {
	add_action( 'after_setup_theme', 'chinchilla_options_save', 4 );
	function chinchilla_options_save() {

		if ( ! isset( $_REQUEST['page'] ) || 'theme_options' != $_REQUEST['page'] || '' == chinchilla_get_value_gp( 'chinchilla_nonce' ) ) {
			return;
		}

		// verify nonce
		if ( ! wp_verify_nonce( chinchilla_get_value_gp( 'chinchilla_nonce' ), admin_url() ) ) {
			chinchilla_add_admin_message( esc_html__( 'Bad security code! Options are not saved!', 'chinchilla' ), 'error', true );
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			chinchilla_add_admin_message( esc_html__( 'Manage options is denied for the current user! Options are not saved!', 'chinchilla' ), 'error', true );
			return;
		}

		// Save options
		chinchilla_options_update( null, 'chinchilla_options_field_' );

		// Return result
		chinchilla_add_admin_message( esc_html__( 'Options are saved', 'chinchilla' ) );
		wp_redirect( get_admin_url( null, 'admin.php?page=theme_options' ) );
		exit();
	}
}


// Update theme options from specified source
// (_POST or any other options storage)
if ( ! function_exists( 'chinchilla_options_update' ) ) {
	function chinchilla_options_update( $from = null, $from_prefix = '' ) {
		$external_storages = array();
		$values            = null === $from ? get_theme_mods() : $from;
		$options           = chinchilla_storage_get( 'options' );
		foreach ( $options as $k => $v ) {
			// Skip non-data options - sections, info, etc.
			if ( ! isset( $v['std'] ) ) {
				continue;
			}
			// Get new value
			$value = null;
			if ( null === $from ) {
				$from_name = "{$from_prefix}{$k}";
				if ( isset( $_POST[ $from_name ] ) ) {
					$value = chinchilla_get_value_gp( $from_name );
					if ( in_array( $v['type'], array( 'checkbox', 'switch' ) ) ) {
						$value = (int) $value;
					} else if ( is_numeric( $v['std'] ) ) {
						$value = strpos( strval( $value ), '.' ) === false ? (int) $value : (float) $value;
					} else if ( is_bool( $v['std'] ) ) {
						$value = (boolean) $value;
					}
					// Individual options processing
					if ( 'custom_logo' == $k ) {
						if ( ! empty( $value ) && 0 == (int) $value ) {
							$protocol = explode( '//', $value );
							$value = chinchilla_clear_thumb_size( $value );
							if ( strpos( $value, ':' ) === false && ! empty( $protocol[0] ) && substr( $protocol[0], -1 ) == ':' ) {
								$value = $protocol[0] . $value;
							}
							$value = chinchilla_attachment_url_to_postid( $value );
							if ( empty( $value ) ) {
								$value = null === $from ? get_theme_mod( $k ) : $values[$k];
							}
						}
					}
					// Save to the result array
					if ( ! empty( $v['type'] ) 
						&& ( 'hidden' != $v['type'] || 'reset_options' == $k )
						&& empty( $v['hidden'] )
						&& ( ! empty( $v['options_storage'] ) || chinchilla_get_theme_option_std( $k, $v['std'] ) !== $value )
					) {
						// If value is not hidden and not equal to 'std' - store it
						$values[ $k ] = $value;
					} elseif ( isset( $values[ $k ] ) ) {
						// Otherwise - remove this key from options
						unset( $values[ $k ] );
						$value = null;
					}
				}
			} else {
				$value = isset( $values[ $k ] )
								? $values[ $k ]
								: chinchilla_get_theme_option_std( $k, $v['std'] );
			}
			// External plugin's options
			if ( $value !== null && ! empty( $v['options_storage'] ) ) {
				if ( ! isset( $external_storages[ $v['options_storage'] ] ) ) {
					$external_storages[ $v['options_storage'] ] = array();
				}
				$external_storages[ $v['options_storage'] ][ $k ] = $value;
			}
		}

		// Update options in the external storages
		foreach ( $external_storages as $storage_name => $storage_values ) {
			$storage = get_option( $storage_name, false );
			if ( is_array( $storage ) ) {
				foreach ( $storage_values as $k => $v ) {
					if ( ! empty( $options[$k]['type'] )
						&& 'hidden' != $options[$k]['type']
						&& ( empty( $options[$k]['hidden'] ) || ! $options[$k]['hidden'] )
						&& chinchilla_get_theme_option_std( $k, $options[$k]['std'] ) != $v
					) {
						// If value is not hidden and not equal to 'std' - store it
						$storage[ $k ] = $v;
					} else {
						// Otherwise - remove this key from the external storage and from the theme options
						unset( $storage[ $k ] );
						unset( $values[ $k ] );
					}
				}
				update_option( $storage_name, apply_filters( 'chinchilla_filter_options_save', $storage, $storage_name ) );
			}
		}

		// Update Theme Mods (internal Theme Options)
		$stylesheet_slug = get_stylesheet();
		$values          = apply_filters( 'chinchilla_filter_options_save', $values, 'theme_mods' );
		update_option( "theme_mods_{$stylesheet_slug}", $values );

		do_action( 'chinchilla_action_just_save_options', $values );

		// Store new schemes colors
		if ( ! empty( $values['scheme_storage'] ) ) {
			$schemes = chinchilla_unserialize( $values['scheme_storage'] );
			if ( is_array( $schemes ) && count( $schemes ) > 0 ) {
				chinchilla_storage_set( 'schemes', $schemes );
			}
		}

		// Store new fonts parameters
		$fonts = chinchilla_get_theme_fonts();
		foreach ( $fonts as $tag => $v ) {
			foreach ( $v as $css_prop => $css_value ) {
				if ( in_array( $css_prop, array( 'title', 'description' ) ) ) {
					continue;
				}
				if ( isset( $values[ "{$tag}_{$css_prop}" ] ) ) {
					$fonts[ $tag ][ $css_prop ] = $values[ "{$tag}_{$css_prop}" ];
				}
			}
		}
		chinchilla_storage_set( 'theme_fonts', $fonts );

		// Update ThemeOptions save timestamp
		$stylesheet_time = time();
		update_option( "chinchilla_options_timestamp_{$stylesheet_slug}", $stylesheet_time );

		// Synchronize theme options between child and parent themes
		if ( chinchilla_get_theme_setting( 'duplicate_options' ) == 'both' ) {
			$theme_slug = get_template();
			if ( $theme_slug != $stylesheet_slug ) {
				chinchilla_customizer_duplicate_theme_options( $stylesheet_slug, $theme_slug, $stylesheet_time );
			}
		}

		// Apply action - moved to the delayed state (see below) to load all enabled modules and apply changes after
		// Attention! Don't remove comment the line below!
		// Not need here: do_action('chinchilla_action_save_options');
		update_option( 'chinchilla_action', 'chinchilla_action_save_options' );
	}
}


//-------------------------------------------------------
//-- Delayed action from previous session
//-- (after save options)
//-- to save new CSS, etc.
//-------------------------------------------------------
if ( ! function_exists( 'chinchilla_do_delayed_action' ) ) {
	add_action( 'after_setup_theme', 'chinchilla_do_delayed_action' );
	function chinchilla_do_delayed_action() {
		$action = get_option( 'chinchilla_action' );
		if ( '' != $action ) {
			do_action( $action );
			update_option( 'chinchilla_action', '' );
		}
	}
}



// -----------------------------------------------------------------
// -- Theme Settings utilities
// -----------------------------------------------------------------

// Return internal theme setting value
if ( ! function_exists( 'chinchilla_get_theme_setting' ) ) {
	function chinchilla_get_theme_setting( $name, $default = -999999 ) {
		if ( ! chinchilla_storage_isset( 'settings', $name ) ) {
			if ( $default != -999999 )
				return $default;
			else if ( defined( 'WP_CLI' ) )
				return false;
			else {
				$s = '';
				if ( function_exists( 'ddo' ) ) {
					$s = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
					array_shift($s);
					$s = ddo($s, 0, 3);
				}
				wp_die(
					// Translators: Add option's name to the message
					esc_html( sprintf( __( 'Undefined setting "%s"', 'chinchilla' ), $name ) )
					. ( ! empty( $s )
							? ' ' . esc_html( __( 'called from:', 'chinchilla' ) ) . "<pre>" . wp_kses_data( $s ) . '</pre>'
							: ''
							)
				);
			}
		} else {
			return chinchilla_storage_get_array( 'settings', $name );
		}
	}
}

// Set theme setting
if ( ! function_exists( 'chinchilla_set_theme_setting' ) ) {
	function chinchilla_set_theme_setting( $option_name, $value ) {
		if ( chinchilla_storage_isset( 'settings', $option_name ) ) {
			chinchilla_storage_set_array( 'settings', $option_name, $value );
		}
	}
}



// -----------------------------------------------------------------
// -- Color Schemes utilities
// -----------------------------------------------------------------

// Load saved values to the color schemes
if ( ! function_exists( 'chinchilla_load_schemes' ) ) {
	add_action( 'chinchilla_action_load_options', 'chinchilla_load_schemes' );
	function chinchilla_load_schemes() {
		$schemes = chinchilla_storage_get( 'schemes' );
		$storage = chinchilla_unserialize( chinchilla_get_theme_option( 'scheme_storage' ) );
		if ( is_array( $storage ) && count( $storage ) > 0 ) {
			chinchilla_storage_set( 'schemes', chinchilla_check_scheme_colors( $storage, $schemes ) );
		}
	}
}

// Compare schemes and return correct colors set
if ( ! function_exists( 'chinchilla_check_scheme_colors' ) ) {
	function chinchilla_check_scheme_colors( $storage, $schemes ) {
		// Remove old colors
		foreach ( $storage as $k => $v ) {
			if ( isset( $schemes[ $k ] ) ) {
				foreach ( $v['colors'] as $k1 => $v1 ) {
					if ( ! isset( $schemes[ $k ]['colors'][ $k1 ] ) ) {
						unset( $storage[ $k ]['colors'][ $k1 ] );
					}
				}
			}
		}
		// Add new colors
		foreach ( $schemes as $k => $v ) {
			foreach ( $v['colors'] as $k1 => $v1 ) {
				if ( ! isset( $storage[ $k ]['colors'][ $k1 ] ) ) {
					$storage[ $k ]['colors'][ $k1 ] = $v1;
				}
			}
		}
		return $storage;
	}
}

// Return specified color from current (or specified) color scheme
if ( ! function_exists( 'chinchilla_get_scheme_color' ) ) {
	function chinchilla_get_scheme_color( $color_name, $scheme = '' ) {
		if ( empty( $scheme ) ) {
			$scheme = chinchilla_get_theme_option( 'color_scheme' );
		}
		if ( empty( $scheme ) || chinchilla_storage_empty( 'schemes', $scheme ) ) {
			$scheme = 'default';
		}
		$colors = chinchilla_storage_get_array( 'schemes', $scheme, 'colors' );
		return $colors[ $color_name ];
	}
}

// Return colors from current color scheme
if ( ! function_exists( 'chinchilla_get_scheme_colors' ) ) {
	function chinchilla_get_scheme_colors( $scheme = '' ) {
		if ( empty( $scheme ) ) {
			$scheme = chinchilla_get_theme_option( 'color_scheme' );
		}
		if ( empty( $scheme ) || chinchilla_storage_empty( 'schemes', $scheme ) ) {
			$scheme = 'default';
		}
		return chinchilla_storage_get_array( 'schemes', $scheme, 'colors' );
	}
}

// Return colors from all schemes
if ( ! function_exists( 'chinchilla_get_scheme_storage' ) ) {
	function chinchilla_get_scheme_storage( $scheme = '' ) {
		return serialize( chinchilla_storage_get( 'schemes' ) );
	}
}

// Return theme fonts parameter's default value
if ( ! function_exists( 'chinchilla_get_scheme_color_option' ) ) {
	function chinchilla_get_scheme_color_option( $option_name ) {
		$parts = explode( '_', $option_name, 2 );
		return chinchilla_get_scheme_color( $parts[1] );
	}
}

// Return schemes list
if ( ! function_exists( 'chinchilla_get_list_schemes' ) ) {
	function chinchilla_get_list_schemes( $prepend_inherit = false ) {
		$list    = array();
		$schemes = chinchilla_storage_get( 'schemes' );
		if ( is_array( $schemes ) && count( $schemes ) > 0 ) {
			foreach ( $schemes as $slug => $scheme ) {
				$list[ $slug ] = $scheme['title'];
			}
		}
		return $prepend_inherit ? chinchilla_array_merge( array( 'inherit' => esc_html__( 'Inherit', 'chinchilla' ) ), $list ) : $list;
	}
}

// Return all schemes, sorted by usage in the parameters 'xxx_scheme' on the current page
if ( ! function_exists( 'chinchilla_get_sorted_schemes' ) ) {
	function chinchilla_get_sorted_schemes() {
		$params  = chinchilla_storage_get( 'schemes_sorted' );
		$schemes = chinchilla_storage_get( 'schemes' );
		$rez     = array();
		if ( is_array( $schemes ) ) {
			foreach ( $params as $p ) {
				if ( ! chinchilla_check_theme_option( $p ) ) {
					continue;
				}
				$s = chinchilla_get_theme_option( $p );
				if ( ! empty( $s ) && ! chinchilla_is_inherit( $s ) && isset( $schemes[ $s ] ) ) {
					$rez[ $s ] = $schemes[ $s ];
					unset( $schemes[ $s ] );
				}
			}
			if ( count( $schemes ) > 0 ) {
				$rez = array_merge( $rez, $schemes );
			}
		}
		return $rez;
	}
}

// Return color presets
if ( ! function_exists( 'chinchilla_get_color_presets' ) ) {
	function chinchilla_get_color_presets() {
		return apply_filters( 'chinchilla_filter_color_presets', chinchilla_storage_get( 'color_presets' ) );
	}
}

// Return color presets list
if ( ! function_exists( 'chinchilla_get_list_color_presets' ) ) {
	function chinchilla_get_list_color_presets( $prepend_inherit = false ) {
		$list    = array();
		$presets = chinchilla_get_color_presets();
		if ( is_array( $presets ) && count( $presets ) > 0 ) {
			foreach ( $presets as $slug => $preset ) {
				$list[ $slug ] = array(
									'title' => $preset['title'],
									'icon'  => sprintf( 'images/theme-options/color-preset/%s.png', chinchilla_esc( $slug ) ),
									);
			}
		}
		return $prepend_inherit
					? chinchilla_array_merge(
							array( 
								'inherit' => array(
												'title' => esc_html__( 'Inherit', 'chinchilla' ),
												'icon'  => 'images/theme-options/inherit.png',
												),
							),
							$list
						)
					: $list;
	}
}


// -----------------------------------------------------------------
// -- Theme Fonts utilities
// -----------------------------------------------------------------

// Load saved values into fonts list
if ( ! function_exists( 'chinchilla_load_fonts' ) ) {
	add_action( 'chinchilla_action_load_options', 'chinchilla_load_fonts' );
	function chinchilla_load_fonts() {
		// Fonts to load when theme starts
		$load_fonts = array();
		for ( $i = 1; $i <= chinchilla_get_theme_setting( 'max_load_fonts' ); $i++ ) {
			$name = chinchilla_get_theme_option( "load_fonts-{$i}-name" );
			if ( '' != $name ) {
				$load_fonts[] = array(
					'name'   => $name,
					'family' => chinchilla_get_theme_option( "load_fonts-{$i}-family" ),
					'styles' => chinchilla_get_theme_option( "load_fonts-{$i}-styles" ),
					'link'   => chinchilla_get_theme_option( "load_fonts-{$i}-link" ),
				);
			}
		}
		chinchilla_storage_set( 'load_fonts', $load_fonts );
		chinchilla_storage_set( 'load_fonts_subset', chinchilla_get_theme_option( 'load_fonts_subset' ) );

		// Font parameters of the main theme's elements
		$fonts = chinchilla_get_theme_fonts();
		foreach ( $fonts as $tag => $v ) {
			foreach ( $v as $css_prop => $css_value ) {
				if ( in_array( $css_prop, array( 'title', 'description' ) ) ) {
					continue;
				}
				$fonts[ $tag ][ $css_prop ] = chinchilla_get_theme_option( "{$tag}_{$css_prop}" );
			}
		}
		chinchilla_storage_set( 'theme_fonts', $fonts );
	}
}

// Return slug of the loaded font
if ( ! function_exists( 'chinchilla_get_load_fonts_slug' ) ) {
	function chinchilla_get_load_fonts_slug( $name ) {
		return str_replace( ' ', '-', $name );
	}
}

// Return font-family string from the font parameters
if ( ! function_exists( 'chinchilla_get_load_fonts_family_string' ) ) {
	function chinchilla_get_load_fonts_family_string( $font ) {
		$parts = array( $font['name'] );
		if ( ! empty( $font['family'] ) ) {
			$parts = array_merge( $parts, explode( ',', $font['family'] ) );
		}
		foreach( $parts as $k => $v ) {
			$v = trim( $v );
			$parts[ $k ] = strpos( $v, '"' ) === false && strpos( $v, ' ' ) !== false
								? '"' . trim( $v ) . '"'
								: $v;
		}
		return join( ',', $parts );
	}
}

// Return load fonts parameter's default value
if ( ! function_exists( 'chinchilla_get_load_fonts_option' ) ) {
	function chinchilla_get_load_fonts_option( $option_name ) {
		$rez        = '';
		$parts      = explode( '-', $option_name );
		$load_fonts = chinchilla_storage_get( 'load_fonts' );
		if ( 'load_fonts' == $parts[0] && count( $load_fonts ) > $parts[1] - 1 && isset( $load_fonts[ $parts[1] - 1 ][ $parts[2] ] ) ) {
			$rez = $load_fonts[ $parts[1] - 1 ][ $parts[2] ];
		}
		return $rez;
	}
}

// Return load fonts subset's default value
if ( ! function_exists( 'chinchilla_get_load_fonts_subset' ) ) {
	function chinchilla_get_load_fonts_subset( $option_name ) {
		return chinchilla_storage_get( 'load_fonts_subset' );
	}
}

// Return load fonts list
if ( ! function_exists( 'chinchilla_get_list_load_fonts' ) ) {
	function chinchilla_get_list_load_fonts( $prepend_inherit = false ) {
		$list       = array();
		$load_fonts = chinchilla_storage_get( 'load_fonts' );
		if ( is_array( $load_fonts ) && count( $load_fonts ) > 0 ) {
			foreach ( $load_fonts as $font ) {
				$list[ chinchilla_get_load_fonts_family_string( $font ) ] = $font['name'];
			}
		}
		return $prepend_inherit ? chinchilla_array_merge( array( 'inherit' => esc_html__( 'Inherit', 'chinchilla' ) ), $list ) : $list;
	}
}

// Return font settings of the theme specific elements
if ( ! function_exists( 'chinchilla_get_theme_fonts' ) ) {
	function chinchilla_get_theme_fonts() {
		return chinchilla_storage_get( 'theme_fonts' );
	}
}

// Return theme fonts parameter's default value
if ( ! function_exists( 'chinchilla_get_theme_fonts_option' ) ) {
	function chinchilla_get_theme_fonts_option( $option_name ) {
		$rez         = '';
		$parts       = explode( '_', $option_name );
		$theme_fonts = chinchilla_storage_get( 'theme_fonts' );
		if ( ! empty( $theme_fonts[ $parts[0] ][ $parts[1] ] ) ) {
			$rez = $theme_fonts[ $parts[0] ][ $parts[1] ];
		}
		return $rez;
	}
}

// Update loaded fonts list in the each tag's parameter (p, h1..h6,...) after the 'load_fonts' options are loaded
if ( ! function_exists( 'chinchilla_update_list_load_fonts' ) ) {
	add_action( 'chinchilla_action_load_options', 'chinchilla_update_list_load_fonts', 11 );
	function chinchilla_update_list_load_fonts() {
		$theme_fonts = chinchilla_get_theme_fonts();
		$load_fonts  = chinchilla_get_list_load_fonts( true );
		foreach ( $theme_fonts as $tag => $v ) {
			chinchilla_storage_set_array2( 'options', $tag . '_font-family', 'options', $load_fonts );
		}
	}
}

// Return font presets
if ( ! function_exists( 'chinchilla_get_font_presets' ) ) {
	function chinchilla_get_font_presets() {
		return apply_filters( 'chinchilla_filter_font_presets', chinchilla_storage_get( 'font_presets' ) );
	}
}

// Return font presets list
if ( ! function_exists( 'chinchilla_get_list_font_presets' ) ) {
	function chinchilla_get_list_font_presets( $prepend_inherit = false ) {
		$list    = array();
		$presets = chinchilla_get_font_presets();
		if ( is_array( $presets ) && count( $presets ) > 0 ) {
			foreach ( $presets as $slug => $preset ) {
				$list[ $slug ] = array(
									'title' => $preset['title'],
									'icon'  => sprintf( 'images/theme-options/font-preset/%s.png', chinchilla_esc( $slug ) ),
									);
			}
		}
		return $prepend_inherit
					? chinchilla_array_merge(
							array( 
								'inherit' => array(
												'title' => esc_html__( 'Inherit', 'chinchilla' ),
												'icon'  => 'images/theme-options/inherit.png',
												),
							),
							$list
						)
					: $list;
	}
}



// -----------------------------------------------------------------
// -- Other options utilities
// -----------------------------------------------------------------

// Return all vars from Theme Options with option 'customizer'
if ( ! function_exists( 'chinchilla_get_theme_vars' ) ) {
	function chinchilla_get_theme_vars() {
		$vars = chinchilla_theme_defaults();
		global $CHINCHILLA_STORAGE;
		foreach ( $CHINCHILLA_STORAGE['options'] as $k => $v ) {
			if ( ! empty( $v['customizer'] ) ) {
				$vars[ $v['customizer'] ] = chinchilla_theme_defaults( $v['customizer'], chinchilla_get_theme_option( $k ) );
			}
		}
		return $vars;
	}
}

// Return current theme-specific border radius for form's fields and buttons
if ( ! function_exists( 'chinchilla_get_border_radius' ) ) {
	function chinchilla_get_border_radius() {
		$rad = str_replace( ' ', '', chinchilla_get_theme_option( 'border_radius' ) );
		if ( empty( $rad ) ) {
			$rad = 0;
		}
		return chinchilla_prepare_css_value( $rad );
	}
}




// -----------------------------------------------------------------
// -- Theme Options page
// -----------------------------------------------------------------

if ( ! function_exists( 'chinchilla_options_init_page_builder' ) ) {
	add_action( 'after_setup_theme', 'chinchilla_options_init_page_builder' );
	function chinchilla_options_init_page_builder() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', 'chinchilla_options_add_scripts' );
		}
	}
}

// Load required styles and scripts for admin mode
if ( ! function_exists( 'chinchilla_options_add_scripts' ) ) {
	//Handler of the add_action("admin_enqueue_scripts", 'chinchilla_options_add_scripts');
	function chinchilla_options_add_scripts() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ! empty( $screen->id ) && false !== strpos($screen->id, '_page_theme_options') ) {
			wp_enqueue_style( 'chinchilla-fontello', chinchilla_get_file_url( 'css/font-icons/css/fontello.css' ), array(), null );
			wp_enqueue_style( 'wp-color-picker', false, array(), null );
			wp_enqueue_script( 'wp-color-picker', false, array( 'jquery' ), null, true );
			wp_enqueue_script( 'jquery-ui-tabs', false, array( 'jquery', 'jquery-ui-core' ), null, true );
			wp_enqueue_script( 'jquery-ui-accordion', false, array( 'jquery', 'jquery-ui-core' ), null, true );
			wp_enqueue_script( 'jquery-ui-sortable', false, array('jquery', 'jquery-ui-core'), null, true);
			wp_enqueue_script( 'chinchilla-options', chinchilla_get_file_url( 'theme-options/theme-options.js' ), array( 'jquery' ), null, true );
			wp_enqueue_style(  'spectrum', chinchilla_get_file_url( 'js/colorpicker/spectrum/spectrum.css' ), array(), null );
			wp_enqueue_script( 'spectrum', chinchilla_get_file_url( 'js/colorpicker/spectrum/spectrum.js' ), array( 'jquery' ), null, true );
			wp_localize_script( 'chinchilla-options', 'chinchilla_dependencies', chinchilla_get_theme_dependencies() );
			wp_localize_script( 'chinchilla-options', 'chinchilla_color_schemes', chinchilla_storage_get( 'schemes' ) );
			wp_localize_script( 'chinchilla-options', 'chinchilla_simple_schemes', chinchilla_storage_get( 'schemes_simple' ) );
			wp_localize_script( 'chinchilla-options', 'chinchilla_sorted_schemes', chinchilla_storage_get( 'schemes_sorted' ) );
			wp_localize_script( 'chinchilla-options', 'chinchilla_color_presets', chinchilla_get_color_presets() );
			wp_localize_script( 'chinchilla-options', 'chinchilla_theme_fonts', chinchilla_storage_get( 'theme_fonts' ) );
			wp_localize_script( 'chinchilla-options', 'chinchilla_font_presets', chinchilla_get_font_presets() );
			wp_localize_script( 'chinchilla-options', 'chinchilla_theme_vars', chinchilla_get_theme_vars() );
			wp_localize_script(
				'chinchilla-options', 'chinchilla_options_vars', apply_filters(
					'chinchilla_filter_options_vars', array(
						'max_load_fonts'            => chinchilla_get_theme_setting( 'max_load_fonts' ),
						'save_only_changed_options' => chinchilla_get_theme_setting( 'save_only_changed_options' ),
					)
				)
			);
		}
	}
}

// Add Theme Options item in the Appearance menu
if ( ! function_exists( 'chinchilla_options_add_theme_panel_page' ) ) {
	add_action( 'trx_addons_filter_add_theme_panel_pages', 'chinchilla_options_add_theme_panel_page' );
	function chinchilla_options_add_theme_panel_page($list) {
		$list[] = array(
			esc_html__( 'Theme Options', 'chinchilla' ),
			esc_html__( 'Theme Options', 'chinchilla' ),
			'manage_options',
			'theme_options',
			'chinchilla_options_page_builder',
			'dashicons-admin-generic'
		);
		return $list;
	}
}


// Build options page
if ( ! function_exists( 'chinchilla_options_page_builder' ) ) {
	function chinchilla_options_page_builder() {
		?>
		<span class="wp-header-end" style="display:none"></span>
		<div class="chinchilla_options">
			<div class="chinchilla_options_header">
				<h2 class="chinchilla_options_title"><?php esc_html_e( 'Theme Options', 'chinchilla' ); ?></h2>
				<div class="chinchilla_options_buttons">
					<a href="#" class="chinchilla_options_button_submit chinchilla_options_button chinchilla_options_button_accent" tabindex="0"><?php esc_html_e( 'Save Options', 'chinchilla' ); ?></a>
					<a href="#" class="chinchilla_options_button_export chinchilla_options_button" tabindex="0"><?php esc_html_e( 'Export Options', 'chinchilla' ); ?></a>
					<a href="#" class="chinchilla_options_button_import chinchilla_options_button" tabindex="0"><?php esc_html_e( 'Import Options', 'chinchilla' ); ?></a>
					<a href="#" class="chinchilla_options_button_reset chinchilla_options_button" tabindex="0"><?php esc_html_e( 'Reset Options', 'chinchilla' ); ?></a>
				</div>
			</div>
			<?php chinchilla_show_admin_messages(); ?>
			<form id="chinchilla_options_form" action="" method="post" enctype="multipart/form-data">
				<input type="hidden" name="chinchilla_nonce" value="<?php echo esc_attr( wp_create_nonce( admin_url() ) ); ?>" />
				<?php chinchilla_options_show_fields(); ?>
			</form>
		</div>
		<?php
	}
}


// Display all option's fields
if ( ! function_exists( 'chinchilla_options_show_fields' ) ) {
	function chinchilla_options_show_fields( $options = false ) {
		$options_total = 1;	// nonce field
		if ( empty( $options ) ) {
			$options = chinchilla_storage_get( 'options' );
		}
		$tabs_titles      = array();
		$tabs_content     = array();
		$last_panel_super = '';
		$last_panel       = '';
		$last_section     = '';
		$last_batch       = '';
		$allow_subtabs    = chinchilla_get_theme_setting( 'options_tabs_position' ) == 'vertical' && chinchilla_get_theme_setting( 'allow_subtabs' );
		foreach ( $options as $k => $v ) {
			if ( 'panel' == $v['type'] || ( 'section' == $v['type'] && ( empty( $last_panel ) || $allow_subtabs ) ) ) {
				// New tab
				if ( ! isset( $tabs_titles[ $k ] ) ) {
					$tabs_titles[ $k ]  = $v;
					$tabs_content[ $k ] = '';
				}
				if ( ! empty( $last_batch ) ) {
					$tabs_content[ $last_section ] .= '</div></div>';
					$last_batch                     = '';
				}
				$last_section = $k;
				if ( 'panel' == $v['type'] || $allow_subtabs ) {
					$last_panel = $k;
					if ( 'section' == $v['type'] && ! empty( $last_panel_super ) ) {
						$tabs_titles[ $last_panel_super ][ 'super' ] = true;
						$tabs_titles[ $k ][ 'sub' ] = true;
					}
				}
				if ( 'panel' == $v['type'] ) {
					$last_panel_super = $k;
				}
			} elseif ( 'batch' == $v['type'] || ( 'section' == $v['type'] && ! empty( $last_panel ) ) ) {
				// New batch
				if ( empty( $last_batch ) ) {
					$tabs_content[ $last_section ] = ( ! isset( $tabs_content[ $last_section ] ) ? '' : $tabs_content[ $last_section ] )
													. '<div class="chinchilla_accordion chinchilla_options_batch">';
				} else {
					$tabs_content[ $last_section ] .= '</div>';
				}
				$tabs_content[ $last_section ] .= '<h4 class="chinchilla_accordion_title chinchilla_options_batch_title">' . esc_html( $v['title'] ) . '</h4>'
												. '<div class="chinchilla_accordion_content chinchilla_options_batch_content">';
				$last_batch                     = $k;
			} elseif ( in_array( $v['type'], array( 'batch_end', 'section_end', 'panel_end' ) ) ) {
				// End panel, section or batch
				if ( ! empty( $last_batch ) && ( 'section_end' != $v['type'] || empty( $last_panel ) ) ) {
					$tabs_content[ $last_section ] .= '</div></div>';
					$last_batch                     = '';
				}
				if ( 'panel_end' == $v['type'] ) {
					$last_panel = '';
					$last_panel_super = '';
				}
			} else if ( 'group' == $v['type'] ) {
				// Fields set (group)
				if ( count( $v['fields'] ) > 0 ) {
					$tabs_content[ $last_section ] = ( ! isset( $tabs_content[ $last_section] ) ? '' : $tabs_content[ $last_section ] ) 
													. chinchilla_options_show_group( $k, $v );
				}
			} else {
				// Field's layout
				$options_total++;
				$tabs_content[ $last_section ] = ( ! isset( $tabs_content[ $last_section ] ) ? '' : $tabs_content[ $last_section ] )
												. chinchilla_options_show_field( $k, $v );
			}
		}
		if ( ! empty( $last_batch ) ) {
			$tabs_content[ $last_section ] .= '</div></div>';
		}

		if ( count( $tabs_content ) > 0 ) {
			// Remove empty sections
			foreach ( $tabs_content as $k => $v ) {
				if ( empty( $v ) && empty( $tabs_titles[ $k ]['super'] ) ) {
					unset( $tabs_titles[ $k ] );
					unset( $tabs_content[ $k ] );
				}
			}
			// Display alert if options count greater then PHP setting 'max_input_vars'
			if ( ! chinchilla_get_theme_setting( 'save_only_changed_options' ) ) {
				$options_max = function_exists( 'ini_get' ) ? ini_get( 'max_input_vars' ) : 0;
				if ( $options_max > 0 && $options_total > $options_max ) {
					?>
					<div class="chinchilla_admin_messages">
						<div class="chinchilla_admin_message_item error">
							<p><?php
								// Translators: Add total options and max input vars to the message
								echo wp_kses_data( sprintf( __( "<strong>Attention! The number of theme options ( %1\$d )</strong> on this page <strong>exceeds the maximum number of variables ( %2\$d )</strong> specified in your server's PHP configuration!", 'chinchilla' ), $options_total, $options_max ) )
									. '<br>'
									. wp_kses_data( __( "When you save the options, you will lose some of the settings (they will take their default values).", 'chinchilla' ) );
							?></p>
						</div>
					</div>
					<?php
				}
			}
			?>
			<div id="chinchilla_options_tabs" class="chinchilla_tabs chinchilla_tabs_<?php echo esc_attr( chinchilla_get_theme_setting( 'options_tabs_position' ) ); ?> <?php echo count( $tabs_titles ) > 1 ? 'with_tabs' : 'no_tabs'; ?>">
				<?php
				if ( count( $tabs_titles ) > 1 ) {
					?>
					<ul>
						<?php
						$cnt = 0;
						foreach ( $tabs_titles as $k => $v ) {
							$cnt++;
							echo '<li class="chinchilla_tabs_title chinchilla_tabs_title_' . esc_attr( $v['type'] )
									. ( ! empty( $v['super'] ) ? ' chinchilla_tabs_title_super' : '' )
									. ( ! empty( $v['sub'] ) ? ' chinchilla_tabs_title_sub' : '' )
								. '"><a href="#chinchilla_options_section_' . esc_attr( ! empty( $v['super'] ) ? $cnt + 1 : $cnt ) . '">'
										. ( !empty( $v['icon'] ) ? '<i class="' . esc_attr( $v['icon'] ) . '"></i>' : '' )
										. '<span class="chinchilla_tabs_caption">' . esc_html( $v['title'] ) . '</span>'
									. '</a>'
								. '</li>';
						}
						?>
					</ul>
					<?php
				}
				$cnt = 0;
				foreach ( $tabs_content as $k => $v ) {
					$cnt++;
					if ( ! empty( $v['super'] ) ) {
						continue;
					}
					?>
					<div id="chinchilla_options_section_<?php echo esc_attr( $cnt ); ?>" class="chinchilla_tabs_section chinchilla_options_section">
						<?php chinchilla_show_layout( $v ); ?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}
}


// Display option's group
if ( ! function_exists( 'chinchilla_options_show_group' ) ) {
	function chinchilla_options_show_group( $k, $v, $post_type = '' ) {
		$inherit_allow = ! empty( $post_type );
		$inherit_state = ! empty( $post_type ) && isset( $v['val'] ) && chinchilla_is_inherit( $v['val'] );
		$output = '<div class="chinchilla_options_group'
						. ( $inherit_allow ? ' chinchilla_options_inherit_' . ( $inherit_state ? 'on' : 'off' ) : '' )
						. ( ! empty( $v['pro_only'] ) ? ' chinchilla_options_pro_only' : '' )
						.'"'
						. ( isset( $v['dependency'] ) ? ' data-param="' . esc_attr( $k ) . '" data-type="group"' : '' )
					. '>'
						. '<h4 class="chinchilla_options_group_title'
							. ( ! empty( $v['title_class'] ) ? ' ' . esc_attr( $v['title_class'] ) : '' )
						. '">'
							. esc_html( $v['title'] )
							. chinchilla_add_inherit_lock( $k, $v, $inherit_allow )
						. '</h4>'
						. ( ! empty( $v['override']['desc'] ) || ! empty( $v['desc'] )
							? ( '<div class="chinchilla_options_group_description">'
								. ( ! empty( $v['override']['desc'] ) 	// param 'desc' already processed with wp_kses()!
									? $v['override']['desc']
									: ( ! empty( $v['desc'] ) ? $v['desc'] : '' )
									)
								. '</div>'
								)
							: ''
							)
						. '<div class="chinchilla_options_group_fields">';
		if ( ! isset( $v['val'] ) || ! is_array( $v['val'] ) || count( $v['val'] ) == 0 ) {
			$v['val'] = isset( $v['std'] ) ? $v['std'] : array( array() );
		}
		foreach ( $v['val'] as $idx => $values ) {
			$output .= '<div class="chinchilla_options_fields_set' 
							. ( ! empty( $v['clone'] ) ? ' chinchilla_options_clone' : '' )
						. '">'
							. ( ! empty( $v['clone'] )
									? '<span class="chinchilla_options_clone_control chinchilla_options_clone_control_move" data-tooltip-text="' . esc_attr__('Drag to reorder', 'chinchilla') . '">'
											. '<span class="icon-menu"></span>'
										. '</span>'
									: ''
								);
			foreach ( $v['fields'] as $k1 => $v1 ) {
				$v1['val'] = isset( $values[ $k1 ] ) ? $values[ $k1 ] : $v1['std'];
				$output   .= chinchilla_options_show_field( $k1, $v1, '', "{$k}[{$idx}]" );
			}
			$output .= ! empty( $v['clone'] )
						? '<span class="chinchilla_options_clone_control chinchilla_options_clone_control_add" tabindex="0" data-tooltip-text="' . esc_attr__('Clone items', 'chinchilla') . '">'
								. '<span class="icon-docs"></span>'
							. '</span>'
							. '<span class="chinchilla_options_clone_control chinchilla_options_clone_control_delete" tabindex="0" data-tooltip-text="' . esc_attr__('Delete items', 'chinchilla') . '">'
								. '<span class="icon-clear-button"></span>'
							. '</span>'
						: '';
			$output .= '</div>';
		}
		if ( ! empty( $v['clone'] ) ) {
			$output .= '<div class="chinchilla_options_clone_buttons">'
							. '<a class="chinchilla_button chinchilla_button_accent chinchilla_options_clone_button_add" tabindex="0">'
								. esc_html__('+ Add New Item', 'chinchilla')
							. '</a>'
						. '</div>';
		}
		$output .= chinchilla_add_inherit_cover( $k, $v, $inherit_allow, $inherit_state )
					. '</div>'
				.'</div>';
		return $output;
	}
}


// Display single option's field
if ( ! function_exists( 'chinchilla_options_show_field' ) ) {
	function chinchilla_options_show_field( $name, $field, $post_type = '', $group = '' ) {

		$inherit_allow = ! empty( $post_type );
		$inherit_state = ! empty( $post_type ) && isset( $field['val'] ) && chinchilla_is_inherit( $field['val'] );

		$field_data_present = 'info' != $field['type'] || ! empty( $field['override']['desc'] ) || ! empty( $field['desc'] );

		if ( ( 'hidden' == $field['type'] && $inherit_allow )         // Hidden field in the post meta (not in the root Theme Options)
			|| ( ! empty( $field['hidden'] ) && ! $inherit_allow )    // Field only for post meta in the root Theme Options
		) {
			return '';
		}

		// Prepare 'name' for the group fields
		if ( ! empty( $group ) ) {
			$name = "{$group}[{$name}]";
		}
		$id = str_replace( array( '[', ']' ), array('_', ''), $name );

		if ( 'hidden' == $field['type'] ) {
			$output = isset( $field['val'] )
							? '<input type="hidden" name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
							: '';

		} else {
			$output = ( ! empty( $field['class'] ) && strpos( $field['class'], 'chinchilla_new_row' ) !== false
						? '<div class="chinchilla_new_row_before"></div>'
						: '' )
						. '<div class="chinchilla_options_item chinchilla_options_item_' . esc_attr( $field['type'] )
									. ( $inherit_allow ? ' chinchilla_options_inherit_' . ( $inherit_state ? 'on' : 'off' ) : '' )
									. ( ! empty( $field['pro_only'] ) ? ' chinchilla_options_pro_only' : '' )
									. ( ! empty( $field['class'] ) ? ' ' . esc_attr( $field['class'] ) : '' )
									. '">'
							. '<h4 class="chinchilla_options_item_title'
							. ( ! empty( $field['override'] )
								? ' chinchilla_options_item_title_override " title="' . esc_attr__('This option can be overridden in the following sections (Blog, Plugins settings, etc.) or in the settings of individual pages', 'chinchilla') . '"'
								: '"'
								)
							. '>'
								. esc_html( $field['title'] )
								. ( ! empty( $field['override'] )
										? ' <span class="chinchilla_options_asterisk"></span>'
										: '' )
								. chinchilla_add_inherit_lock( $id, $field, $inherit_allow )
							. '</h4>'
							. ( $field_data_present
								? '<div class="chinchilla_options_item_data">'
									. '<div class="chinchilla_options_item_field"'
										. ' data-param="' . esc_attr( $name ). '"'
										. ' data-type="' . esc_attr( $field['type'] ). '"'
										. ( ! empty( $field['linked'] ) ? ' data-linked="' . esc_attr( $field['linked'] ) . '"' : '' )
									. '>'
								: '' );

			if ( 'checkbox' == $field['type'] ) {
				// Type 'checkbox'
				$output .= '<label class="chinchilla_options_item_label">'
							// Hack to always send checkbox value even it not checked
							. '<input type="hidden" name="chinchilla_options_field_' . esc_attr( $name ) . '"'
									. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
									. ' data-std="' . esc_attr( $field['std'] ) . '"'
									. ' />'
							. '<input type="checkbox" name="chinchilla_options_field_' . esc_attr( $name ) . '_chk" value="1"'
									. ( 1 == $field['val'] ? ' checked="checked"' : '' )
									. ' />'
							. '<span class="chinchilla_options_item_caption">'
								. esc_html( $field['title'] )
							. '</span>'
						. '</label>';

			} else if ( 'switch' == $field['type'] ) {
				// Type 'switch'
				$output .= '<label class="chinchilla_options_item_label">'
							// Hack to always send checkbox value even it not checked
							. '<input type="hidden" name="chinchilla_options_field_' . esc_attr( $name ) . '"'
									. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
									. ' data-std="' . esc_attr( $field['std'] ) . '"'
									. ' />'
							. '<input type="checkbox" name="chinchilla_options_field_' . esc_attr( $name ) . '_chk" value="1"'
									. ( 1 == $field['val'] ? ' checked="checked"' : '' )
									. ' />'
							. '<span class="chinchilla_options_item_holder" tabindex="0">'
								. '<span class="chinchilla_options_item_holder_back"></span>'
								. '<span class="chinchilla_options_item_holder_handle"></span>'
							. '</span>'
							. ( ! empty( $field['title'] )
								? '<span class="chinchilla_options_item_caption">' . esc_html( $field['title'] ) . '</span>'
								: ''
								)
						. '</label>';

			} elseif ( in_array( $field['type'], array( 'radio' ) ) ) {
				// Type 'radio' (2+ choises)
				$field['options'] = apply_filters( 'chinchilla_filter_options_get_list_choises', $field['options'], $name );
				$first            = true;
				foreach ( $field['options'] as $k => $v ) {
					$output .= '<label class="chinchilla_options_item_label">'
								. '<input type="radio" name="chinchilla_options_field_' . esc_attr( $name ) . '"'
										. ' value="' . esc_attr( $k ) . '"'
										. ' data-std="' . esc_attr( $field['std'] ) . '"'
										. ( ( '#' . $field['val'] ) == ( '#' . $k ) || ( $first && ! isset( $field['options'][ $field['val'] ] ) ) ? ' checked="checked"' : '' )
										. ' />'
								. '<span class="chinchilla_options_item_holder" tabindex="0"></span>'
								. '<span class="chinchilla_options_item_caption">'
									. esc_html( $v )
								. '</span>'
							. '</label>';
					$first   = false;
				}

			} elseif ( in_array( $field['type'], array( 'text', 'time', 'date' ) ) ) {
				// Type 'text' or 'time' or 'date'
				$output .= '<input type="text" name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />';

			} elseif ( 'textarea' == $field['type'] ) {
				// Type 'textarea'
				$output .= '<textarea name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
							. '>'
								. esc_html( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] )
							. '</textarea>';

			} elseif ( 'text_editor' == $field['type'] ) {
				// Type 'text_editor'
				$output .= '<input type="hidden" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_textarea( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
							. chinchilla_show_custom_field(
								'chinchilla_options_field_' . esc_attr( $id ) . '_tinymce',
								$field,
								chinchilla_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} elseif ( 'select' == $field['type'] ) {
				// Type 'select'
				$field['options'] = apply_filters( 'chinchilla_filter_options_get_list_choises', $field['options'], $name );
				$output          .= '<select size="1" name="chinchilla_options_field_' . esc_attr( $name ) . '"'
									. ' data-std="' . esc_attr( $field['std'] ) . '"'
									. '>';
				foreach ( $field['options'] as $k => $v ) {
					$output .= '<option value="' . esc_attr( $k ) . '"' . ( ( '#' . $field['val'] ) == ( '#' . $k ) ? ' selected="selected"' : '' ) . '>' . esc_html( $v ) . '</option>';
				}
				$output .= '</select>';

			} elseif ( in_array( $field['type'], array( 'image', 'media', 'video', 'audio' ) ) ) {
				// Type 'image', 'media', 'video' or 'audio'
				if ( (int) $field['val'] > 0 ) {
					$image        = wp_get_attachment_image_src( $field['val'], 'full' );
					$field['val'] = empty( $image[0] ) ? '' : $image[0];
				}
				$output .= '<input type="hidden" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
						. chinchilla_show_custom_field(
							'chinchilla_options_field_' . esc_attr( $id ) . '_button',
							array(
								'type'            => 'mediamanager',
								'multiple'        => ! empty( $field['multiple'] ),
								'data_type'       => $field['type'],
								'linked_field_id' => 'chinchilla_options_field_' . esc_attr( $id ),
							),
							chinchilla_is_inherit( $field['val'] ) ? '' : $field['val']
						);

			} elseif ( 'color' == $field['type'] ) {
				// Type 'color'
				$output .= '<input type="text" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' class="chinchilla_color_selector"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />';

			} elseif ( 'icon' == $field['type'] ) {
				// Type 'icon'
				$output .= '<input type="hidden" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
							. chinchilla_show_custom_field(
								'chinchilla_options_field_' . esc_attr( $id ) . '_button',
								array(
									'type'   => 'icons',
									'style'  => ! empty( $field['style'] ) ? $field['style'] : 'icons',
									'button' => true,
									'icons'  => true,
								),
								chinchilla_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} elseif ( 'choice' == $field['type'] ) {
				// Type 'choice'
				$field['options'] = apply_filters( 'chinchilla_filter_options_get_list_choises', $field['options'], $name );
				$output .= '<input type="hidden" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
							. chinchilla_show_custom_field(
								'chinchilla_options_field_' . esc_attr( $id ) . '_list',
								array(
									'type'    => 'choice',
									'options' => $field['options']
								),
								$field['val']
							);

			} elseif ( 'checklist' == $field['type'] ) {
				// Type 'checklist'
				$field['options'] = apply_filters( 'chinchilla_filter_options_get_list_choises', $field['options'], $name );
				$output .= '<input type="hidden" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
							. chinchilla_show_custom_field(
								'chinchilla_options_field_' . esc_attr( $id ) . '_list',
								$field,
								chinchilla_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} elseif ( 'scheme_editor' == $field['type'] ) {
				// Type 'scheme_editor'
				$storage = chinchilla_check_scheme_colors( chinchilla_unserialize( $field['val'] ), chinchilla_storage_get( 'schemes' ) );
				$field['val'] = serialize( $storage );
				$output .= '<input type="hidden" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ' />'
							. chinchilla_show_custom_field(
								'chinchilla_options_field_' . esc_attr( $id ) . '_scheme',
								$field,
								$storage
							);

			} elseif ( 'presets' == $field['type'] ) {
				// Type 'presets'
				$presets_type = chinchilla_get_edited_post_type();
				if ( empty( $preset_type ) ) {
					$preset_type = '#';
				}
				$presets = get_option( 'chinchilla_options_presets' );
				if ( empty( $presets ) || ! is_array( $presets ) ) {
					$presets = array();
				}
				if ( empty( $presets[ $presets_type ] ) || ! is_array( $presets[ $presets_type ] ) ) {
					$presets[ $presets_type ] = array();
				}
				$output .= '<select class="chinchilla_options_presets_list" size="1" name="chinchilla_options_field_' . esc_attr( $name ) . '" data-type="' . esc_attr( $presets_type ) . '">';
				$output .= '<option value="">' . chinchilla_get_not_selected_text( esc_html__( 'Select preset', 'chinchilla' ) ) . '</option>';
				foreach ( $presets[ $presets_type ] as $k => $v ) {
					$output .= '<option value="' . esc_attr( $v ) . '">' . esc_html( $k ) . '</option>';
				}
				$output .= '</select>';
				$output .= '<a href="#"'
								. ' class="button chinchilla_options_presets_apply icon-check-2"'
								. ' title="' .  esc_attr__( 'Apply the selected preset', 'chinchilla' ) . '"'
							. '></a>';
				$output .= '<a href="#"'
								. ' class="button chinchilla_options_presets_add icon-plus-2"'
								. ' title="' .  esc_attr__( 'Create a new preset', 'chinchilla' ) . '"'
							. '></a>';
				$output .= '<a href="#"'
								. ' class="button chinchilla_options_presets_delete icon-clear-button"'
								. ' title="' .  esc_attr__( 'Delete the selected preset', 'chinchilla' ) . '"'
							. '></a>';

			} elseif ( in_array( $field['type'], array( 'slider', 'range' ) ) ) {
				// Type 'slider' || 'range'
				$field['show_value'] = ! isset( $field['show_value'] ) || $field['show_value'];
				$output             .= '<input type="' . ( ! $field['show_value'] ? 'hidden' : 'text' ) . '" id="chinchilla_options_field_' . esc_attr( $id ) . '"'
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( chinchilla_is_inherit( $field['val'] ) ? '' : $field['val'] ) . '"'
								. ' data-std="' . esc_attr( $field['std'] ) . '"'
								. ( $field['show_value'] ? ' class="chinchilla_range_slider_value"' : '' )
								. ' data-type="' . esc_attr( $field['type'] ) . '"'
								. ' />'
							. ( $field['show_value'] && ! empty( $field['units'] ) ? '<span class="chinchilla_range_slider_units">' . esc_html( $field['units'] ) . '</span>' : '' )
							. chinchilla_show_custom_field(
								'chinchilla_options_field_' . esc_attr( $id ) . '_slider',
								$field,
								chinchilla_is_inherit( $field['val'] ) ? '' : $field['val']
							);

			} else if ( 'button' == $field['type'] ) {
				// Type 'button' - call specified js function
				$output .= '<input type="button"'
								. ( ! empty($field['class_field'] ) ? ' class="' . esc_attr( $field['class_field'] ) . '"' : '')
								. ' name="chinchilla_options_field_' . esc_attr( $name ) . '"'
								. ' value="' . esc_attr( ! empty( $field['caption'] ) ? $field['caption'] : $field['title']) . '"'
								. ' data-action="' . esc_attr(!empty($field['action']) ? $field['action'] : $field['std']) . '"'
								. ( ! empty( $field['callback'] ) ? ' data-callback="'.esc_attr( $field['callback'] ) . '"' : '')
								. '>';

			} else {
				// Unknown type - apply filters
				$output .= apply_filters( 'chinchilla_filter_get_custom_field', '', $name, $field, $inherit_allow, $inherit_state );

			}

			$output .= chinchilla_add_inherit_cover( $name, $field, $inherit_allow, $inherit_state )
						. ( $field_data_present ? '</div>' : '' )
						. ( ! empty( $field['override']['desc'] ) || ! empty( $field['desc'] )
							? '<div class="chinchilla_options_item_description">'
								. ( ! empty( $field['override']['desc'] )   // param 'desc' already processed with wp_kses()!
										? $field['override']['desc']
										: $field['desc'] )
								. '</div>'
							: '' )
					. ( $field_data_present ? '</div>' : '' )
				. '</div>';
		}
		return $output;
	}
}


// Add 'Inherit' lock to the field
if ( ! function_exists( 'chinchilla_add_inherit_lock' ) ) {
	function chinchilla_add_inherit_lock( $id, $field, $inherit_allow ) {
		return $inherit_allow
					? '<span class="chinchilla_options_inherit_lock' . ( ! empty( $field['pro_only'] ) ? ' chinchilla_options_pro_only_lock' : '' ) . '"'
							. ' id="chinchilla_options_inherit_' . esc_attr( $id ) . '"'
							. ( empty( $field['pro_only'] ) ? ' tabindex="0"' : '' )
						. '>'
						. '</span>'
					: '';
	}
}


// Add 'Inherit' cover to the field
if ( ! function_exists( 'chinchilla_add_inherit_cover' ) ) {
	function chinchilla_add_inherit_cover( $id, $field, $inherit_allow=false, $inherit_state=false ) {
		return $inherit_allow
					? '<div class="chinchilla_options_inherit_cover'
						. ( ! empty( $field['pro_only'] )
								? ' chinchilla_options_pro_only_cover'
								: ( ! $inherit_state ? ' chinchilla_hidden' : '' ) 
								)
						. '">'
							. ( ! empty( $field['pro_only'] )
								? ( '<a href="' . esc_url( chinchilla_storage_get( 'theme_download_url' ) ) . '" target="_blank" class="chinchilla_options_inherit_label chinchilla_options_pro_only_label">'
										. esc_html__( 'Activate Pro version', 'chinchilla' )
									. '</a>' )
								: ( '<span class="chinchilla_options_inherit_label">'
										. esc_html__( 'Inherit', 'chinchilla' )
									. '</span>' )
								)
							. '<input type="hidden" name="chinchilla_options_inherit_' . esc_attr( $id ) . '"'
								. ' value="' . esc_attr( $inherit_state ? 'inherit' : '' ) . '"'
								. ' />'
						. '</div>'
					: ( 'info' != $field['type'] && ! empty( $field['pro_only'] )
						? '<div class="chinchilla_options_inherit_cover chinchilla_options_pro_only_cover">'
								. '<a href="' . esc_url( chinchilla_storage_get( 'theme_download_url' ) ) . '" target="_blank" class="chinchilla_options_inherit_label chinchilla_options_pro_only_label">'
									. esc_html__( 'Activate Pro version', 'chinchilla' )
								. '</a>'
							. '</div>'
						: '' );
	}
}


// Show theme specific fields
if ( ! function_exists( 'chinchilla_show_custom_field' ) ) {
	function chinchilla_show_custom_field( $id, $field, $value ) {
		$output = '';

		switch ( $field['type'] ) {

			case 'mediamanager':
				wp_enqueue_media();
				$title   = empty( $field['data_type'] ) || 'image' == $field['data_type']
								? ( ! empty( $field['multiple'] ) ? esc_html__( 'Add Images', 'chinchilla' ) : esc_html__( 'Choose Image', 'chinchilla' ) )
								: ( ! empty( $field['multiple'] ) ? esc_html__( 'Add Media', 'chinchilla' ) : esc_html__( 'Choose Media', 'chinchilla' ) );
				$images  = explode( '|', $value );
				$output .= '<span class="chinchilla_media_selector_preview'
								. ' chinchilla_media_selector_preview_' . ( ! empty( $field['multiple'] ) ? 'multiple' : 'single' )
								. ( is_array( $images ) && count( $images ) > 0 ? ' chinchilla_media_selector_preview_with_image' : '' )
							. '">';
				if ( is_array( $images ) ) {
					foreach ( $images as $img ) {
						$output .= $img && ! chinchilla_is_inherit( $img )
								? '<span class="chinchilla_media_selector_preview_image" tabindex="0">'
										. ( in_array( chinchilla_get_file_ext( $img ), array( 'gif', 'jpg', 'jpeg', 'png' ) )
												? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr__( 'Selected image', 'chinchilla' ) . '">'
												: '<a href="' . esc_attr( $img ) . '">' . esc_html( basename( $img ) ) . '</a>'
											)
									. '</span>'
								: '';
					}
				}
				$output .= '</span>';
				$output .= '<input type="button"'
								. ' id="' . esc_attr( $id ) . '"'
								. ' class="button mediamanager chinchilla_media_selector"'
								. '	data-param="' . esc_attr( $id ) . '"'
								. '	data-choose="' . esc_attr( $title ) . '"'
								. ' data-update="' . esc_attr( $title ) . '"'
								. '	data-multiple="' . esc_attr( ! empty( $field['multiple'] ) ? '1' : '0' ) . '"'
								. '	data-type="' . esc_attr( ! empty( $field['data_type'] ) ? $field['data_type'] : 'image' ) . '"'
								. '	data-linked-field="' . esc_attr( $field['linked_field_id'] ) . '"'
								. ' value="' .  esc_attr( $title ) . '"'
							. '>';
				break;

			case 'icons':
				$icons_type = ! empty( $field['style'] )
								? $field['style']
								: chinchilla_get_theme_setting( 'icons_type' );
				if ( empty( $field['return'] ) ) {
					$field['return'] = 'full';
				}
				$chinchilla_icons = chinchilla_get_list_icons( $icons_type );
				if ( is_array( $chinchilla_icons ) ) {
					if ( ! empty( $field['button'] ) ) {
						$output .= '<span id="' . esc_attr( $id ) . '"'
										. ' tabindex="0"'
										. ' class="chinchilla_list_icons_selector'
												. ( 'icons' == $icons_type && ! empty( $value ) ? ' ' . esc_attr( $value ) : '' )
												. '"'
										. ' title="' . esc_attr__( 'Select icon', 'chinchilla' ) . '"'
										. ' data-style="' . esc_attr( $icons_type ) . '"'
										. ( in_array( $icons_type, array( 'images', 'svg' ) ) && ! empty( $value )
											? ' style="background-image: url(' . esc_url( 'slug' == $field['return'] ? $chinchilla_icons[ $value ] : $value ) . ');"'
											: ''
											)
									. '></span>';
					}
					if ( ! empty( $field['icons'] ) ) {
						$output .= '<div class="chinchilla_list_icons">'
										. '<input type="text" class="chinchilla_list_icons_search" placeholder="' . esc_attr__( 'Search for an icon', 'chinchilla' ) . '">'
										. '<div class="chinchilla_list_icons_wrap">'
											. '<div class="chinchilla_list_icons_inner">';
						foreach ( $chinchilla_icons as $slug => $icon ) {
							$output .= '<span tabindex="0" class="' . esc_attr( 'icons' == $icons_type ? $icon : $slug )
									. ( ( 'full' == $field['return'] ? $icon : $slug ) == $value ? ' chinchilla_list_active' : '' )
									. '"'
									. ' title="' . esc_attr( $slug ) . '"'
									. ' data-icon="' . esc_attr( 'full' == $field['return'] ? $icon : $slug ) . '"'
									. ( ! empty( $icon ) && in_array( $icons_type, array( 'images', 'svg' ) ) ? ' style="background-image: url(' . esc_url( $icon ) . ');"' : '' )
									. '></span>';
						}
						$output .= '</div></div></div>';
					}
				}
				break;

			case 'choice':
				if ( is_array( $field['options'] ) ) {
					$output .= '<div class="chinchilla_list_choice">';
					foreach ( $field['options'] as $slug => $data ) {
						$output .= ( ! empty( $data['new_row'] )
										? '<span class="chinchilla_list_choice_rows_separator"></span>'
										: ''
										) 
								. '<span tabindex="0" class="chinchilla_list_choice_item'
									. ( $slug == $value && strlen( $slug ) == strlen( $value ) ? ' chinchilla_list_active' : '' )
									. '"'
									. ' data-choice="' . esc_attr( $slug ) . '"'
									. ( ! empty( $data[ 'description' ] ) ? ' title="' . esc_attr( $data[ 'description' ] ) . '"' : '' )
								. '>'
									. '<span class="chinchilla_list_choice_item_icon">'
										. '<img src="' . esc_url( chinchilla_get_file_url( $data['icon'] ) ) . '" alt="' . esc_attr( $data['title'] ) . '">'
									. '</span>'
									. '<span class="chinchilla_list_choice_item_title">'
										. esc_html( $data['title'] )
									. '</span>'
								. '</span>';
					}
					$output .= '</div>';
				}
				break;

			case 'checklist':
				if ( ! empty( $field['sortable'] ) ) {
					wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery', 'jquery-ui-core' ), null, true );
				}
				$output .= '<div class="chinchilla_checklist chinchilla_checklist_' . esc_attr( $field['dir'] )
							. ( ! empty( $field['sortable'] ) ? ' chinchilla_sortable' : '' )
							. '">';
				if ( ! is_array( $value ) ) {
					if ( ! empty( $value ) && ! chinchilla_is_inherit( $value ) ) {
						parse_str( str_replace( '|', '&', $value ), $value );
					} else {
						$value = array();
					}
				}
				// Sortable
				if ( ! empty( $field['sortable'] ) ) {
					// Sort options by values order
					if ( is_array( $value ) ) {
						$field['options'] = chinchilla_array_merge( $value, $field['options'] );
					}
					if ( ! empty( $field['group'] ) ) {
						$field['group'] = false;
					}
				}
				$last_group = '';
				foreach ( $field['options'] as $k => $v ) {
					if ( ! empty( $field['group'] ) ) {
						if ( preg_match( '/\\(([^\\)]*)\\)/', $v, $matches ) ) {
							$cur_group = $matches[1];
							$v = trim( str_replace( '(' . $cur_group . ')', '', $v ) );
							if ( $cur_group != $last_group ) {
								$last_group = $cur_group;
								$output .= '<p class="trx_addons_options_item_choises_group">' . esc_html( $last_group ) . '</p>';
							}
						}
					}
					$output .= '<label class="chinchilla_checklist_item_label' . ( ! empty( $field['sortable'] ) ? ' chinchilla_sortable_item' : '' ) . '"'
									. ( 'horizontal' == $field['dir'] && substr( $v, 0, 4 ) != 'http' && strlen( $v ) >= 20 ? ' title="' . esc_attr( $v ) . '"' : '' )
								. '>'
								. '<input type="checkbox" value="1" data-name="' . $k . '"'
									. ( isset( $value[ $k ] ) && 1 == (int) $value[ $k ] ? ' checked="checked"' : '' )
									. ' />'
								. ( substr( $v, 0, 4 ) == 'http' ? '<img src="' . esc_url( $v ) . '">' : esc_html( $v ) )
							. '</label>';
				}
				$output .= '</div>';
				break;

			case 'slider':
			case 'range':
				wp_enqueue_script( 'jquery-ui-slider', false, array( 'jquery', 'jquery-ui-core' ), null, true );
				$is_range   = 'range' == $field['type'];
				$field_min  = ! empty( $field['min'] ) ? $field['min'] : 0;
				$field_max  = ! empty( $field['max'] ) ? $field['max'] : 100;
				$field_step = ! empty( $field['step'] ) ? $field['step'] : 1;
				$field_val  = ! empty( $value )
								? ( $value . ( $is_range && strpos( $value, ',' ) === false ? ',' . $field_max : '' ) )
								: ( $is_range ? $field_min . ',' . $field_max : $field_min );
				$output    .= '<div id="' . esc_attr( $id ) . '"'
								. ' class="chinchilla_range_slider"'
								. ' data-range="' . esc_attr( $is_range ? 'true' : 'min' ) . '"'
								. ' data-min="' . esc_attr( $field_min ) . '"'
								. ' data-max="' . esc_attr( $field_max ) . '"'
								. ' data-step="' . esc_attr( $field_step ) . '"'
								. '>'
								. '<span class="chinchilla_range_slider_label chinchilla_range_slider_label_min">'
									. esc_html( $field_min )
								. '</span>'
								. '<span class="chinchilla_range_slider_label chinchilla_range_slider_label_avg">'
									. ( ( $field_max + $field_min ) / 2 == intval( ( $field_max + $field_min ) / 2 ) || $field_step !== intval( $field_step )
										? esc_html( round( ( $field_max + $field_min ) / 2, $field_step == (int)$field_step ? 0 : 2 ) )
										: ''
										)
								. '</span>'
								. '<span class="chinchilla_range_slider_label chinchilla_range_slider_label_max">'
									. esc_html( $field_max )
								. '</span>';
				$output    .= '<div class="chinchilla_range_slider_scale">';
				for ( $i = 0; $i <= 11; $i++ ) {
					$output    .= '<span></span>';
				}
				$output    .= '</div>';
				$values     = explode( ',', $field_val );
				for ( $i = 0; $i < count( $values ); $i++ ) {
					$output .= '<span class="chinchilla_range_slider_label chinchilla_range_slider_label_cur">'
									. esc_html( $values[ $i ] )
								. '</span>';
				}
				$output .= '</div>';
				break;

			case 'text_editor':
				if ( function_exists( 'wp_enqueue_editor' ) ) {
					wp_enqueue_editor();
				}
				ob_start();
				wp_editor(
					$value, $id, array(
						'default_editor' => 'tmce',
						'wpautop'        => isset( $field['wpautop'] ) ? $field['wpautop'] : false,
						'teeny'          => isset( $field['teeny'] ) ? $field['teeny'] : false,
						'textarea_rows'  => isset( $field['rows'] ) && $field['rows'] > 1 ? $field['rows'] : 10,
						'editor_height'  => 16 * ( isset( $field['rows'] ) && $field['rows'] > 1 ? (int) $field['rows'] : 10 ),
						'tinymce'        => array(
							'resize'             => false,
							'wp_autoresize_on'   => false,
							'add_unload_trigger' => false,
						),
					)
				);
				$editor_html = ob_get_contents();
				ob_end_clean();
				$output .= '<div class="chinchilla_text_editor" data-editor-html="' . esc_attr( $editor_html ) . '">' . $editor_html . '</div>';
				break;

			case 'scheme_editor':
				if ( ! is_array( $value ) ) {
					break;
				}
				if ( empty( $field['colorpicker'] ) ) {
					$field['colorpicker'] = 'internal';
				}
				$output .= '<div class="chinchilla_scheme_editor">';
				// Select scheme
				$output .= '<div class="chinchilla_scheme_editor_scheme">'
								. '<select class="chinchilla_scheme_editor_selector">';
				foreach ( $value as $scheme => $v ) {
					$output .= '<option value="' . esc_attr( $scheme ) . '">' . esc_html( $v['title'] ) . '</option>';
				}
				$output .= '</select>';
				// Scheme controls
				$output .= '<span class="chinchilla_scheme_editor_controls">'
								. '<span class="chinchilla_scheme_editor_control chinchilla_scheme_editor_control_reset" title="' . esc_attr__( 'Reload scheme', 'chinchilla' ) . '"></span>'
								. '<span class="chinchilla_scheme_editor_control chinchilla_scheme_editor_control_copy" title="' . esc_attr__( 'Duplicate scheme', 'chinchilla' ) . '"></span>'
								. '<span class="chinchilla_scheme_editor_control chinchilla_scheme_editor_control_delete" title="' . esc_attr__( 'Delete scheme', 'chinchilla' ) . '"></span>'
							. '</span>'
						. '</div>';
				// Select type
				$output .= '<div class="chinchilla_scheme_editor_type">'
								. '<div class="chinchilla_scheme_editor_row">'
									. '<span class="chinchilla_scheme_editor_row_cell">'
										. esc_html__( 'Editor type', 'chinchilla' )
									. '</span>'
									. '<span class="chinchilla_scheme_editor_row_cell chinchilla_scheme_editor_row_cell_span">'
										. '<label>'
											. '<input name="chinchilla_scheme_editor_type" type="radio" value="simple" checked="checked"> '
											. '<span class="chinchilla_options_item_holder" tabindex="0"></span>'
											. '<span class="chinchilla_options_item_caption">'
												. esc_html__( 'Simple', 'chinchilla' )
											. '</span>'
										. '</label>'
										. '<label>'
											. '<input name="chinchilla_scheme_editor_type" type="radio" value="advanced"> '
											. '<span class="chinchilla_options_item_holder" tabindex="0"></span>'
											. '<span class="chinchilla_options_item_caption">'
												. esc_html__( 'Advanced', 'chinchilla' )
											. '</span>'
										. '</label>'
									. '</span>'
								. '</div>'
							. '</div>';
				// Colors
				$used    = array();
				$groups  = chinchilla_storage_get( 'scheme_color_groups' );
				$colors  = chinchilla_storage_get( 'scheme_color_names' );
				$output .= '<div class="chinchilla_scheme_editor_colors">';
				$first   = true;
				foreach ( $value as $scheme => $v ) {
					if ( $first ) {
						$output .= '<div class="chinchilla_scheme_editor_header">'
										. '<span class="chinchilla_scheme_editor_header_cell chinchilla_scheme_editor_row_cell_caption"></span>';
						// Display column titles
						foreach ( $groups as $group_name => $group_data ) {
							$output .= '<span class="chinchilla_scheme_editor_header_cell chinchilla_scheme_editor_row_cell_color" title="' . esc_attr( $group_data['description'] ) . '">'
										. esc_html( $group_data['title'] )
										. '</span>';
						}
						$output .= '</div>';
						// Each row - it's a group of colors: text_light - alter_light - extra_light - ...
						foreach ( $colors as $color_name => $color_data ) {
							$output .= '<div class="chinchilla_scheme_editor_row">'
										. '<span class="chinchilla_scheme_editor_row_cell chinchilla_scheme_editor_row_cell_caption" title="' . esc_attr( $color_data['description'] ) . '">'
										. esc_html( $color_data['title'] )
										. '</span>';
							foreach ( $groups as $group_name => $group_data ) {
								$slug    = 'main' == $group_name
											? $color_name
											: str_replace( 'text_', '', "{$group_name}_{$color_name}" );
								$used[]  = $slug;
								$output .= '<span class="chinchilla_scheme_editor_row_cell chinchilla_scheme_editor_row_cell_color"'
											. ' title="' . esc_attr( sprintf( '%1$s: %2$s', $group_data['description'], $color_data['description'] ) ) . '"'
											. '>'
												. ( isset( $v['colors'][ $slug ] )
													? "<input type=\"text\" name=\"{$slug}\" class=\""
														. ( 'tiny' == $field['colorpicker']
															? 'tinyColorPicker'
															: ( 'spectrum' == $field['colorpicker']
																? 'spectrumColorPicker'
																: 'iColorPicker'
																)
															) 
														. '" value="' . esc_attr( $v['colors'][ $slug ] ) . '">'
													: ''
													)
											. '</span>';
							}
							$output .= '</div>';
						}
					}
					// Additional color ( defined by theme / skin developer ) - only in the main group
					foreach ( $v['colors'] as $slug => $color ) {
						if ( in_array( $slug, $used ) ) {
							continue;
						}
						$title   = ! empty( $colors[ $slug ][ 'title' ] )
										? $colors[ $slug ][ 'title' ]
										: ucfirst( join( ' ', explode( '_', $slug ) ) );
						$output .= '<div class="chinchilla_scheme_editor_row">'
									. '<span class="chinchilla_scheme_editor_row_cell chinchilla_scheme_editor_row_cell_caption"'
										. ( ! empty( $colors[ $slug ][ 'description' ] )
											? ' title="' . esc_attr( $colors[ $slug ][ 'description' ] ) . '"'
											: '' )
									. '>'
										. esc_html( $title )
									. '</span>';
						foreach ( $groups as $group_name => $group_data ) {
							$fld = 'main' == $group_name
											? $slug
											: "{$group_name}_{$slug}";
							$used[]  = $fld;
							$output .= '<span class="chinchilla_scheme_editor_row_cell chinchilla_scheme_editor_row_cell_color">'
											. ( isset( $v['colors'][ $fld ] )
												? '<input type="text" name="' . esc_attr( $fld ) . '" class="'
													. ( 'tiny' == $field['colorpicker']
														? 'tinyColorPicker'
														: ( 'spectrum' == $field['colorpicker']
															? 'spectrumColorPicker'
															: 'iColorPicker'
															)
														) 
													. '" value="' . esc_attr( $v['colors'][ $fld ] ) . '">'
												: ''
												)
										. '</span>';
						}
						$output .= '</div>';
					}
					$first = false;
					// If all schemes contain similar colors - break
					break;
				}
				$output .= '</div>'
						. '</div>';
				break;
		}
		return apply_filters( 'chinchilla_filter_show_custom_field', $output, $id, $field, $value );
	}
}


// Refresh data in the linked field
// according the main field value
if ( ! function_exists( 'chinchilla_refresh_linked_data' ) ) {
	function chinchilla_refresh_linked_data( $value, $linked_name ) {
		if ( 'parent_cat' == $linked_name ) {
			$tax   = chinchilla_get_post_type_taxonomy( $value );
			$terms = ! empty( $tax ) ? chinchilla_get_list_terms( false, $tax ) : array();
			$terms = chinchilla_array_merge( array( 0 => chinchilla_get_not_selected_text( esc_html__( 'Select category', 'chinchilla' ) ) ), $terms );
			chinchilla_storage_set_array2( 'options', $linked_name, 'options', $terms );
		}
	}
}


// AJAX: Refresh data in the linked fields
if ( ! function_exists( 'chinchilla_callback_get_linked_data' ) ) {
	add_action( 'wp_ajax_chinchilla_get_linked_data', 'chinchilla_callback_get_linked_data' );
	function chinchilla_callback_get_linked_data() {
		chinchilla_verify_nonce();
		$response  = array( 'error' => '' );
		if ( ! empty( $_REQUEST['chg_name'] ) ) {
			$chg_name  = wp_kses_data( wp_unslash( $_REQUEST['chg_name'] ) );
			$chg_value = wp_kses_data( wp_unslash( $_REQUEST['chg_value'] ) );
			if ( 'post_type' == $chg_name ) {
				$tax              = chinchilla_get_post_type_taxonomy( $chg_value );
				$terms            = ! empty( $tax ) ? chinchilla_get_list_terms( false, $tax ) : array();
				$response['list'] = chinchilla_array_merge( array( 0 => chinchilla_get_not_selected_text( esc_html__( 'Select category', 'chinchilla' ) ) ), $terms );
			}
		}
		chinchilla_ajax_response( $response );
	}
}
