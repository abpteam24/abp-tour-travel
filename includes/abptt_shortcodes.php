<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Shortcodes' ) ) {
		class ABPTT_Shortcodes {
			public function __construct() {
				add_shortcode( 'abptt-booking', array( $this, 'booking' ) );
				add_shortcode( 'abptt-post', array( $this, 'post_list' ) );
				add_shortcode( 'abptt-gallery', array( $this, 'gallery' ) );
			}
			public function booking( $attribute ) {
				$params  = self::normalize_attributes( $attribute );
				$post_id = absint( $params['post_id'] ?? 0 );
				nocache_headers();
				ob_start();
				if ( ! empty( $post_id ) ) {
					do_action( 'abptt_load_details_template', $post_id );
				} else {
					$params['all_post']     = ABPTT_Query::get_post_id( $params );
					$params['global_order'] = 'yes';
					$style                  = sanitize_key( ( $params['style'] ?? 'grid' ) ?: 'grid' );
					$templates              = [ 'grid' => 'list/default.php', 'list' => 'list/default.php', 'missionary' => 'list/missionary.php', 'minimal' => 'list/minimal.php' ];
					$file                   = ABPTT_Function::template_path( $templates[ $style ] ?? $templates['grid'] );
					$hook_name              = ( $style == 'grid' || $style == 'list' ) ? 'default' : $style;
					?>
                    <div class="abptt_area">
                        <div class="abp_container">
                            <div class="global_form"><?php do_action( 'abptt_search_form', $params ); ?></div>
                            <div class="abptt_booking  _gap_fd_column">
                                <div class="abp_pagination _gap_fd_column">
									<?php
										do_action( 'abptt_post_filter', $params );
										if ( is_file( $file ) ) {
											include_once $file;
										} else {
											include_once ABPTT_Function::template_path( 'list/default.php' );
										}
										do_action( 'abptt_' . $hook_name . '_template', $params );
									?>
                                    <div class="not_found">
										<?php ABPTT_Layout::layout_warning_info( 'not_match' ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}
				return ob_get_clean();
			}
			public function post_list( $attribute ) {
				$params  = self::normalize_attributes( $attribute );
				$post_id = absint( $params['post_id'] ?? 0 );
				nocache_headers();
				ob_start();
				if ( ! empty( $post_id ) ) {
					do_action( 'abptt_load_details_template', $post_id );
				} else {
					$params['all_post'] = ABPTT_Query::get_post_id( $params );
					$style              = sanitize_key( ( $params['style'] ?? 'grid' ) ?: 'grid' );
					$templates          = [
						'grid'       => 'list/default.php',
						'list'       => 'list/default.php',
						'missionary' => 'list/missionary.php',
						'minimal'    => 'list/minimal.php',
					];
					$file               = ABPTT_Function::template_path( $templates[ $style ] ?? $templates['grid'] );
					$hook_name          = ( $style == 'grid' || $style == 'list' ) ? 'default' : $style;
					?>
                    <div class="abptt_area">
                        <div class="abp_container abptt_booking">
                            <div class="abp_pagination _gap_fd_column">
								<?php
									do_action( 'abptt_post_filter', $params );
									if ( is_file( $file ) ) {
										include_once $file;
									} else {
										include_once ABPTT_Function::template_path( 'list/default.php' );
									}
									do_action( 'abptt_' . $hook_name . '_template', $params ); ?>
                                <div class="not_found">
									<?php ABPTT_Layout::layout_warning_info( 'not_match' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}
				return ob_get_clean();
			}
			public function gallery( $attribute ) {
				$params           = self::normalize_attributes( $attribute );
				$post_id          = absint( $params['post_id'] ?? 0 );
				$normalize_images = static function ( $images ): string {
					if ( is_array( $images ) ) {
						$images = implode( ',', array_filter( array_map( 'absint', $images ) ) );
					}
					return is_string( $images ) ? $images : '';
				};
				ob_start();
				?>
                <div class="abptt_area">
                    <div class="abp_container global_slider abp_pagination">
						<?php
							if ( ! empty( $post_id ) ) {
								$img_infos = $normalize_images( ABPTT_Function::get_post_info( $post_id, 'abptt_slider', [] ) );
								do_action( 'abptt_slider', $img_infos, $params );
							} else {
								$post_ids  = ABPTT_Query::get_post_id( $params );
								$img_infos = '';
								if ( ! empty( $post_ids ) && sizeof( $post_ids ) > 0 ) {
									foreach ( $post_ids as $post_id ) {
										$info = $normalize_images( ABPTT_Function::get_post_info( $post_id, 'abptt_slider', [] ) );
										if ( ! empty( $info ) ) {
											$img_infos = $img_infos ? $img_infos . ',' . $info : $info;
										}
									}
									do_action( 'abptt_slider', $img_infos, $params );
								}
							}
						?>
                    </div>
                </div>
				<?php
				return ob_get_clean();
			}
			public static function default_attribute(): array {
				return array(
					"post_id"          => '',
					"cat_id"           => '',
					"loc_id"           => '',
					"organizer_id"     => '',
					"guide_id"       => '',
					"top_pick"         => '',
					"style"            => 'grid',
					"slider_style"     => 'gallery',
					"show"             => '',
					"column"           => 3,
					'sort'             => 'ASC',
					"pagination"       => "yes",
					"pagination-style" => "live",
					'form'             => 'inline',
					'filter'           => '',
				);
			}
			/**
			 * Map the shortcode `filter` tokens to the canonical taxonomy keys.
			 *
			 * @return array<string,string>
			 */
			public static function filter_aliases(): array {
				return array(
					'category'     => 'category',
					'categories'   => 'category',
					'cat'          => 'category',
					'cat_id'       => 'category',
					'location'     => 'location',
					'locations'    => 'location',
					'loc'          => 'location',
					'loc_id'       => 'location',
					'organizer'    => 'organizer',
					'organizers'   => 'organizer',
					'org'          => 'organizer',
					'organizer_id' => 'organizer',
					'guide'        => 'guide',
					'guides'       => 'guide',
					'guide_id'     => 'guide',
				);
			}
			/**
			 * Resolve the `filter` shortcode attribute into a list of taxonomy keys.
			 *
			 * Supported values:
			 *   "" / "all"                        -> every globally enabled taxonomy
			 *   "none"                            -> no taxonomy filter dropdowns
			 *   "category,location"               -> only the listed taxonomies
			 *   "-guide" / "all,-guide"           -> every taxonomy except the listed ones
			 *
			 * @param mixed $value Raw shortcode attribute.
			 * @return array<int,string>
			 */
			public static function normalize_filter_taxonomies( $value ): array {
				$all   = array( 'category', 'location', 'organizer', 'guide' );
				$value = is_array( $value ) ? implode( ',', $value ) : (string) $value;
				$value = strtolower( trim( $value ) );
				if ( '' === $value || 'all' === $value || '*' === $value ) {
					return $all;
				}
				if ( in_array( $value, array( 'none', 'no', 'off', 'false', '0' ), true ) ) {
					return array();
				}
				$aliases  = self::filter_aliases();
				$included = array();
				$excluded = array();
				foreach ( array_map( 'trim', explode( ',', $value ) ) as $token ) {
					if ( '' === $token ) {
						continue;
					}
					$exclude = false;
					if ( 0 === strpos( $token, '-' ) ) {
						$exclude = true;
						$token   = ltrim( $token, '-' );
					}
					$token = $aliases[ $token ] ?? '';
					if ( '' === $token ) {
						continue;
					}
					if ( $exclude ) {
						$excluded[] = $token;
					} else {
						$included[] = $token;
					}
				}
				$included = array_values( array_unique( $included ) );
				$excluded = array_values( array_unique( $excluded ) );
				if ( empty( $included ) && empty( $excluded ) ) {
					return $all;
				}
				if ( empty( $included ) ) {
					return array_values( array_diff( $all, $excluded ) );
				}
				$included = array_values( array_diff( $included, $excluded ) );
				return array_values( array_intersect( $all, $included ) );
			}
			private static function normalize_attributes( $attribute ): array {
				$params                     = shortcode_atts( self::default_attribute(), is_array( $attribute ) ? $attribute : array() );
				$params['post_id']          = absint( $params['post_id'] );
				$params['cat_id']           = absint( $params['cat_id'] );
				$params['loc_id']           = absint( $params['loc_id'] );
				$params['organizer_id']     = absint( $params['organizer_id'] );
				$params['guide_id']       = absint( $params['guide_id'] );
				$params['top_pick']         = in_array( strtolower( (string) $params['top_pick'] ), array( 'yes', 'true', 'on', '1' ), true ) ? 'yes' : '';
				$params['column']           = min( 10, max( 1, absint( $params['column'] ) ?: 3 ) );
				$params['show']             = min( 100, max( 0, absint( $params['show'] ) ) );
				$params['style']            = in_array( $params['style'], array( 'grid', 'list', 'missionary', 'minimal' ), true ) ? $params['style'] : 'grid';
				$params['slider_style']     = in_array( $params['slider_style'], array( 'gallery', 'slider' ), true ) ? $params['slider_style'] : 'gallery';
				$params['sort']             = strtoupper( (string) $params['sort'] ) === 'DESC' ? 'DESC' : 'ASC';
				$params['pagination']       = in_array( strtolower( (string) $params['pagination'] ), array( 'no', 'false', 'off', '0' ), true ) ? 'no' : 'yes';
				$params['pagination_style'] = in_array( $params['pagination-style'], array( 'live', 'number' ), true ) ? $params['pagination-style'] : 'live';
				$params['filter']           = is_string( $params['filter'] ) ? sanitize_text_field( $params['filter'] ) : '';
				$params['filter_taxonomies'] = self::normalize_filter_taxonomies( $params['filter'] );
				return $params;
			}
		}
		new ABPTT_Shortcodes();
	}