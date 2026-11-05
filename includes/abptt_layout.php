<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Layout' ) ) {
		class ABPTT_Layout {
			public function __construct() {
				add_action( 'abptt_load_date_picker', [ $this, 'load_date_picker' ], 10, 2 );
				//==============================//
				add_action( 'abptt_add_icon', array( $this, 'load_icon' ), 10, 2 );
				add_action( 'abptt_add_image_multiple', array( $this, 'add_image_multi' ), 10, 2 );
				add_action( 'abptt_add_image_icon', array( $this, 'selection_icon_image' ), 10, 3 );
				add_action( 'abptt_image_selection', array( $this, 'image_selection' ), 10, 3 );
			}
			public function load_date_picker( $selector, $dates ): void {
				if ( empty( $dates ) || ! is_array( $dates ) ) {
					return;
				}
				$picker_data   = self::create_datepicker_array( $dates );
				$json_selector = wp_json_encode( sanitize_text_field( $selector ) );
				$json_data     = wp_json_encode( $picker_data );
				$inline_js     = "window.abptt_picker_data = window.abptt_picker_data || {}; window.abptt_picker_data[{$json_selector}] = {$json_data};";
				wp_add_inline_script( 'jquery-ui-datepicker', $inline_js );
			}
			public static function create_datepicker_array( $dates ): array {
				$start_date  = current( $dates );
				$start_year  = (int) gmdate( 'Y', strtotime( $start_date ) );
				$start_month = (int) ( gmdate( 'n', strtotime( $start_date ) ) - 1 );
				$start_day   = (int) gmdate( 'j', strtotime( $start_date ) );
				$end_date    = end( $dates );
				$end_year    = (int) gmdate( 'Y', strtotime( $end_date ) );
				$end_month   = (int) ( gmdate( 'n', strtotime( $end_date ) ) - 1 );
				$end_day     = (int) gmdate( 'j', strtotime( $end_date ) );
				$all_dates   = [];
				foreach ( $dates as $date ) {
					$all_dates[] = gmdate( 'j-n-Y', strtotime( $date ) );
				}
				return [
					'minYear'     => $start_year,
					'minMonth'    => $start_month,
					'minDay'      => $start_day,
					'maxYear'     => $end_year,
					'maxMonth'    => $end_month,
					'maxDay'      => $end_day,
					'activeDates' => $all_dates,
					'txtAvail'    => esc_js( __( 'Available', 'abp-tour-travel' ) ),
					'txtUnavail'  => esc_js( __( 'Unavailable', 'abp-tour-travel' ) )
				];
			}
			//==============================//
			public static function load_admin_globally(): void {
				?>
                <div class="abp_popup " data-popup="#abptt_global_popup">
                    <div class="popup_area">
                        <span class="close_icon" onclick="abptt_popup_close_global()"><i class="fas fa-times"></i></span>
                        <div class="popup_body"></div>
                    </div>
                </div>
                <div class="popup_icon abp_popup" data-popup="#abptt_popup_icon">
                    <div class="popup_area">
                        <div class="popup_head _all_center">
                            <div class="abp_dropdown _max_400">
                                <label class="abp_all_center">
                                    <input type="hidden" class="abp_icon_search_hidden" name="abp_icon_search" value=""/>
                                    <input type="text" class="_form_control_text_center validation_name abptt_allow abp_icon_search" name="" placeholder="<?php esc_attr_e( 'Search  icon', 'abp-tour-travel' ); ?>" value=""/>
                                </label>
                                <div class="dropdown_list"></div>
                            </div>
                            <span class="popup_close"><i class="fas fa-times"></i></span>
                        </div>
                        <div class="popup_body">
                            <h4 class="abp_text_center item_icon_title"></h4>
                            <div class="item_icon_area"></div>
                        </div>
                    </div>
                </div>
				<?php
			}
			//==============================//
			public static function button_add( $button_text, $class = '', $button_class = '' ): void {
				$class        = $class ?: 'add_new_hook';
				$button_class = $button_class ?: '_btn_light_active_xs_br_no';
				$button_text  = $button_text ?: __( 'Add New', 'abp-tour-travel' );
				?>
                <button class="<?php echo esc_attr( $button_class . ' ' . $class ); ?>" type="button">
					<?php ABPTT_Static::svg( 'plus' );
						echo esc_html( $button_text ); ?>
                </button>
				<?php
			}
            public static function button_save($text = '', $class = ''): void {
                $class = $class ?: '_btn_green_pale_xs_br_no';
                $text = $text ?: __('save', 'abp-tour-travel');
                ?>
                <button class="<?php echo esc_attr($class); ?>" type="submit">
                    <?php ABPTT_Static::svg('save');
                        echo esc_html($text); ?>
                </button>
                <?php
            }
            public static function button_global_save( $action, $text = '', $class = '' ): void {
                if ( ! empty( $action ) ) {
                    $class = $class ?: '_btn_green_pale_xs_br_no';
                    $text  = $text ?: __( 'save', 'abp-tour-travel' );
                    ?>
                    <button class="<?php echo esc_attr( $class ); ?>" type="button" onclick="abptt_save_global('<?php echo esc_attr( $action ); ?>',this)">
                        <?php ABPTT_Static::svg( 'save' );
                            echo esc_html( $text ); ?>
                    </button>
                    <?php
                }
            }
			public static function button_delete_sort_edit(): void {
				?>
                <div class="_all_center">
                    <div class="_group_content">
						<?php
							self::button_edit();
							self::button_sort();
							self::button_delete();
						?>
                    </div>
                </div>
				<?php
			}
			public static function button_delete_sort(): void {
				?>
                <div class="_all_center">
                    <div class="_group_content">
						<?php
							self::button_sort();
							self::button_delete();
						?>
                    </div>
                </div>
				<?php
			}
			public static function button_edit( $class_edit = 'edit_hook' ): void {
				?>
                <button class="_btn_light_navy_blue_xs <?php echo esc_attr( $class_edit ); ?>" type="button" title="<?php esc_attr_e( 'Edit This Item', 'abp-tour-travel' ); ?>">
					<?php ABPTT_Static::svg( 'edit' ); ?>
                </button>
				<?php
			}
			public static function button_delete( $class = 'delete_hook' ): void {
				?>
                <button class="_btn_light_danger_xxs <?php echo esc_attr( $class ); ?>" type="button" title="<?php esc_attr_e( 'Delete This Item', 'abp-tour-travel' ); ?>"><?php ABPTT_Static::svg( 'close_1' ); ?></button>
				<?php
			}
			public static function button_sort(): void {
				?>
                <div class="_btn_light_info_xxs sortable_handle" type="button" title="<?php esc_attr_e( 'Move This Item', 'abp-tour-travel' ); ?>">
					<?php ABPTT_Static::svg( 'drag' ); ?>
                </div>
				<?php
			}

			public static function button_global_popup( $action, $text = '', $class = '' ): void {
				if ( ! empty( $action ) ) {
					$class = $class ?: '_btn_light_active_xs';
					$text  = $text ?: __( 'Add New', 'abp-tour-travel' );
					?>
                    <button type="button" class="<?php echo esc_attr( $class ) ?>" onclick="abptt_popup_open_global('<?php echo esc_attr( $action ); ?>')">
						<?php ABPTT_Static::svg( 'plus' );
							echo esc_html( $text ); ?>
                    </button>
					<?php
				}
			}
			//=============================//
			public static function info_text( $key = '', $data = '' ): void {
				$data = empty( $data ) ? ABPTT_Static::array_info( $key ) : $data;
				if ( $data ) {
					?>
                    <div class="info_text load_more">
                        ℹ️ &nbsp;<?php echo wp_kses_post( $data ); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e( '.... Less ', 'abp-tour-travel' ); ?>" data-more="<?php esc_html_e( '.... More', 'abp-tour-travel' ); ?>"><?php esc_html_e( '.... More', 'abp-tour-travel' ); ?></span>
                    </div>
					<?php
				}
			}
			public static function load_more( $data = '' ): void {
				if ( $data ) {
					?>
                    <div class="load_more">
						<?php echo wp_kses_post( $data ); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e( '.... Less ', 'abp-tour-travel' ); ?>" data-more="<?php esc_html_e( '.... More', 'abp-tour-travel' ); ?>"><?php esc_html_e( '.... More', 'abp-tour-travel' ); ?></span>
                    </div>
					<?php
				}
			}
			public static function layout_warning_info( $key ): void {
				$data = ABPTT_Static::array_info( $key );
				if ( $data ) {
					echo '<div class="_section_bg_warning_mar_zero"><h4 class="abp_text_center_color_white">' . esc_html( $data ) . '</h4></div>';
				}
			}
			public static function layout_warning_info_xs( $key, $data = '' ): void {
				$data = empty( $data ) ? ABPTT_Static::array_info( $key ) : $data;
				if ( $data ) {
					echo '<div class="abp_text_center_color_white_bg_warning_padding_xxs_fs_label">' . esc_html( $data ) . '</div>';
				}
			}
			public static function layout_info_xs( $key, $data = '' ): void {
				$data = empty( $data ) ? ABPTT_Static::array_info( $key ) : $data;
				if ( $data ) {
					echo '<div class="abp_bg_info_padding_xxs _all_center _color_5">' . esc_html( $data ) . '</div>';
				}
			}
			public static function on() {
				ob_start();
				?>
                <strong class="abp_color_theme"> <?php esc_html_e( 'ON', 'abp-tour-travel' ); ?></strong>
				<?php
				return ob_get_clean();
			}
			public static function off() {
				ob_start();
				?>
                <strong class="abp_color_theme"> <?php esc_html_e( 'OFF', 'abp-tour-travel' ); ?></strong>
				<?php
				return ob_get_clean();
			}
			//==============Input field===============//
			public static function quantity_input( $input_info = [] ): void {
				$name        = $input_info['name'] ?? '';
				$price       = floatval( $input_info['price'] ?? 0 );
				$min_qty     = absint( $input_info['min_qty'] ?? 1 );
				$max_qty     = absint( $input_info['max_qty'] ?? 1 );
				$class       = $input_info['class'] ?? '';
				$collapse_id = $input_info['collapse_id'] ?? '';
				if ( $name && $max_qty >= $min_qty ) {
					if ( ! empty( $collapse_id ) ) {
						?> <div data-collapse="<?php echo esc_attr( $collapse_id ); ?>"><?php
					}
					?>
                    <div class="_group_content qty_input">
                        <div class="qty_decrease _ag_content"><?php ABPTT_Static::svg( 'minus_1' ); ?></div>
                        <label>
                            <input type="text" class="_form_control  validation_number <?php echo esc_attr( $class ); ?>"
                                   name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $min_qty ); ?>"
                                   data-price="<?php echo esc_attr( $price ); ?>" data-min="<?php echo esc_attr( $min_qty ); ?>" data-max="<?php echo esc_attr( $max_qty ); ?>"
                            />
                        </label>
                        <div class="qty_increase _ag_content"><?php ABPTT_Static::svg( 'plus' ); ?></div>
                    </div>
					<?php
					if ( ! empty( $collapse_id ) ) {
						?></div><?php
					}
				}
			}
			public static function switch_checkbox( $name, $value = '' ): void {
				$value = in_array( $value, [ 'on', 'off', '' ], true ) ? $value : '';
				?>
                <div class="<?php echo esc_attr( $value === 'on' ? 'abp_active' : '' ); ?>" data-switch data-collapse-target="#<?php echo esc_attr( $name ); ?>">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                </div>
				<?php
			}
			public static function input_title( $label = '', $required = '' ): void {
				if ( $label ) { ?>
                    <span class="_mar_b_xxs">
							<?php echo esc_html( $label ); ?>
						<?php if ( $required ) { ?>
                            <sup class="_color_required">*</sup>
						<?php } ?>
						</span>
					<?php
				}
			}
			public static function input_date( $name, $date = '', $label = '', $required = '' ): void {
				$now          = ABPTT_Function::date_format( current_time( 'Y-m-d' ) );
				$hidden_date  = $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
				$visible_date = $date ? ABPTT_Function::date_format( $date, 'date' ) : '';
				?>
                <label class="_input_item">
					<?php self::input_title( $label, $required ); ?>
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $hidden_date ); ?>" <?php echo esc_attr( $required ); ?>/>
                    <input type="text" name="" class="_form_control abp_datepicker" value="<?php echo esc_attr( $visible_date ); ?>" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                    <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                </label>
				<?php
			}
			public static function input_time( $name, $time = '', $label = '', $required = '' ): void {
				?>
                <label class="_input_item">
					<?php self::input_title( $label, $required ); ?>
                    <input type="time" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $time ); ?>" <?php echo esc_attr( $required ); ?>/>
                    <span class="fas fa-times time_close_icon" title="<?php esc_attr_e( 'Clear Time', 'abp-tour-travel' ); ?>"></span>
                </label>
				<?php
			}
			public static function textarea( $name, $value = '', $label = '', $required = '' ): void {
				?>
                <label class="abptt_textarea _input_item">
					<?php self::input_title( $label, $required ); ?>
                    <textarea name="<?php echo esc_attr( $name ); ?>" rows="3" class="_form_control" placeholder="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>"  <?php echo esc_attr( $required ); ?>><?php echo esc_textarea( $value ); ?></textarea>
                </label>
				<?php
			}
			public static function select( $name, $value = '', $label = '', $required = '', $options = [] ): void {
				if ( is_array( $options ) && sizeof( $options ) > 0 ) {
					?>
                    <label class="_input_item">
						<?php self::input_title( $label, $required ); ?>
                        <select name="<?php echo esc_attr( $name ); ?>" class="_form_control" title="<?php echo esc_attr( $label ); ?>" <?php echo esc_attr( $required ); ?>>
                            <option value="" disabled selected><?php echo esc_html__( 'Please Select', 'abp-tour-travel' ) . ' ' . esc_html( $label ); ?></option>
							<?php foreach ( $options as $option ) { ?>
                                <option value="<?php echo esc_attr( $option ); ?>" <?php echo esc_attr( $option == $value ? 'selected' : '' ); ?>><?php echo esc_html( $option ); ?></option>
							<?php } ?>
                        </select>
                    </label>
					<?php
				}
			}
			public static function checkbox( $name, $value = '', $label = '', $required = '', $options = [] ): void {
				if ( is_array( $options ) && sizeof( $options ) > 0 ) {
					?>
                    <div class=" _input_item">
                        <span class="_fs_label"> <?php self::input_title( $label, $required ); ?></span>
                        <div class="custom_checkbox">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
							<?php foreach ( $options as $option ) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr( $option == $value ? 'abp_active' : '' ); ?>" data-checked="<?php echo esc_attr( $option ); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( $option == $value ? 'far fa-check-square' : 'far fa-square' ); ?>"></span><?php echo esc_html( $option ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}
			}
			public static function radio( $name, $value = '', $label = '', $required = '', $options = [] ): void {
				if ( is_array( $options ) && sizeof( $options ) > 0 ) {
					?>
                    <div class=" _input_item">
                        <span class="_fs_label"> <?php self::input_title( $label, $required ); ?></span>
                        <div class="custom_radio">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
							<?php foreach ( $options as $option ) { ?>
                                <div class="radio_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr( $option == $value ? 'abp_active' : '' ); ?>" data-radio="<?php echo esc_attr( $option ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( $option == $value ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><?php echo esc_html( $option ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}
			}
			//=============Add  Image / Icon================//
			public static function image( $post_id = '', $image_id = '', $url = '', $class = '' ): void {
				$image_url = ( $post_id > 0 || $image_id ) ? ABPTT_Function::get_image_url( $post_id, $image_id ) : $url;
				$post_url  = $post_id > 0 ? get_the_permalink( $post_id ) : '';
				$image_url = $image_url ?: ABPTT_BLANK_IMG_URL;
				if ( $image_url ) {
					?>
                    <div class="abp_image <?php echo esc_attr( $class ); ?>" data-image-href="<?php echo esc_url( $image_url ); ?>" <?php if ( ! empty( $post_url ) ) { ?> data-href="<?php echo esc_url( $post_url ); ?>" <?php } ?> >
                        <img class="_img_control" src="#" alt="<?php echo esc_attr( max( $post_id, $image_id ) ); ?>">
                    </div>
					<?php
				}
			}
			public static function image_icon( $icon_image, $class = '' ): void {
				if ( ! empty( $icon_image ) ) {
					$icon = $image = $emoji = '';
					if ( is_numeric( $icon_image ) ) {
						$image = $icon_image;
					} elseif ( preg_match( '/\s/', $icon_image ) ) {
						$icon = $icon_image;
					} else {
						$emoji = $icon_image;
					}
					if ( $image ) {
						ABPTT_Layout::image( '', $image );
					} else { ?>
                        <span class="<?php echo esc_attr( $icon . ' ' . $class ); ?>"><?php echo esc_html( $emoji ); ?></span>
					<?php }
				}
			}
			public function load_icon( $name, $value = '' ): void {
				$button_active_class = $value ? '_d_none' : '';
				$icon                = $emoji = '';
				if ( preg_match( '/\s/', $value ) ) {
					$icon = $value;
				} else {
					$emoji = $value;
				}
				$icon_class = ( $icon || $emoji ) ? '' : '_d_none';
				?>
                <div class="icon_image_selection_area">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                    <div class="icon_item  <?php echo esc_attr( $icon_class ); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr( $icon ); ?>" data-add-icon><?php echo esc_html( $emoji ); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e( 'Remove Icon', 'abp-tour-travel' ); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr( $button_active_class ); ?>">
                        <button class="_btn_info_xs icon_add" type="button" data-target-popup="#abptt_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                    </div>
                </div>
				<?php
			}
			public function image_selection( $name, $image_id = '', $target = '' ): void {
				?>
                <div class="image_selection">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $image_id ); ?>" data-target="<?php echo esc_attr( $target ); ?>"/>
                    <div class="image_item <?php echo esc_attr( empty( $image_id ) ? '_d_none' : '' ); ?>" data-image-id="<?php echo esc_attr( $image_id ); ?>'">
                        <span class="image_remove" onclick="abptt_image_remove(this)">❌</span>
                        <img class="_img_control" src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'medium' ) ); ?>" alt="<?php echo esc_attr( $image_id ); ?>"/>
                    </div>
                    <button type="button" class="_btn_light_active_xs <?php echo esc_attr( $image_id ? '_d_none' : '' ); ?>" onclick="abptt_image_selection(this)">
                        <span class="fas fa-image"></span><?php esc_html_e( 'Select Image', 'abp-tour-travel' ); ?>
                    </button>
                </div>
				<?php
			}
			public function add_image_multi( $name, $images ): void {
				$images = is_array( $images ) ? ABPTT_Function::array_to_string( $images ) : $images;
				?>
                <div class="multiple_image_area">
                    <input type="hidden" class="multiple_image_ids" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $images ); ?>"/>
                    <div class="multiple_image">
						<?php
							$all_images = explode( ',', $images );
							if ( $images && sizeof( $all_images ) > 0 ) {
								foreach ( $all_images as $image ) {
									$img_url = ABPTT_Function::get_image_url( '', $image, 'medium' ) ?: ABPTT_BLANK_IMG_URL;
									?>
                                    <div class="multiple_image_item" data-image-id="<?php echo esc_attr( $image ); ?>">
                                        <span class="fas fa-times _circle_icon_xs remove_image_multi"></span>
                                        <img class="_img_control" src="<?php echo esc_attr( $img_url ); ?>" alt="<?php echo esc_attr( $image ); ?>"/>
                                    </div>
									<?php
								}
							}
						?>
                    </div>
					<?php ABPTT_Layout::button_add( __( 'Add  Image', 'abp-tour-travel' ), 'add_image_multi _mar_t_xs' ); ?>
                </div>
				<?php
			}
			public function selection_icon_image( $name, $value = '' ): void {
				$icon = $image = $emoji = '';
				if ( is_numeric( $value ) ) {
					$image = $value;
				} elseif ( preg_match( '/\s/', $value ) ) {
					$icon = $value;
				} else {
					$emoji = $value;
				}
				$icon_class          = ( $icon || $emoji ) ? '' : '_d_none';
				$image_class         = $image ? '' : '_d_none';
				$button_active_class = ( $icon || $image || $emoji ) ? '_d_none' : '';
				?>
                <div class="icon_image_selection_area _fd_column">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                    <div class="icon_item <?php echo esc_attr( $icon_class ); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr( $icon ); ?>" data-add-icon><?php echo esc_html( $emoji ); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e( 'Remove Icon', 'abp-tour-travel' ); ?>"></span>
                    </div>
                    <div class="image_item <?php echo esc_attr( $image_class ); ?>">
                        <img class="_img_control" src="<?php echo esc_url( ABPTT_Function::get_image_url( '', $image, 'medium' ) ); ?>" alt="image">
                        <span class="fas fa-times icon_close image_delete" title="<?php esc_html_e( 'Remove Image', 'abp-tour-travel' ); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr( $button_active_class ); ?>">
                        <div class="_group_content_f_equal_w_full">
                            <button class="_btn_light_info_xs image_select" type="button"><span class="fas fa-image _fs_h6"></span></button>
                            <button class="_btn_light_info_xs icon_add" type="button" data-target-popup="#abptt_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                        </div>
                    </div>
                </div>
				<?php
			}
			//=============static array================//
			public static function ticket_type( $key = '' ) {
				$types = [
					'sp'     => __( 'Seat Plan', 'abp-tour-travel' ),
					'ticket' => __( 'Ticket', 'abp-tour-travel' ),
				];
				return ! empty( $key ) ? ( $types[ $key ] ?? '' ) : $types;
			}
			public static function status_text( $status ): string {
				if ( ! is_string( $status ) && ! is_int( $status ) ) {
					return '';
				}
				$status_array = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : [];
				return is_array( $status_array ) ? ( $status_array[ $status ] ?? '' ) : '';
			}
			public static function week_day(): array {
				return [
					'monday'    => __( 'Monday', 'abp-tour-travel' ),
					'tuesday'   => __( 'Tuesday', 'abp-tour-travel' ),
					'wednesday' => __( 'Wednesday', 'abp-tour-travel' ),
					'thursday'  => __( 'Thursday', 'abp-tour-travel' ),
					'friday'    => __( 'Friday', 'abp-tour-travel' ),
					'saturday'  => __( 'Saturday', 'abp-tour-travel' ),
					'sunday'    => __( 'Sunday', 'abp-tour-travel' ),
				];
			}
			public static function date_option_rules(): array {
				$rules = [
					'weekend'            => __( 'Weekend', 'abp-tour-travel' ),
					'specific_off_dates' => __( 'Specific Off Dates', 'abp-tour-travel' ),
					'special_on_dates'   => __( 'Special On Dates', 'abp-tour-travel' ),
					'off_date_range'     => __( 'Off Dates Range', 'abp-tour-travel' ),
				];
				return apply_filters( 'abptt_filter_date_rule', $rules );
			}
			public static function array_date_format(): array {
				$current_date = current_time( 'Y-m-d' );
				return [
					'yy-mm-dd'   => $current_date,
					'yy/mm/dd'   => date_i18n( 'Y/m/d', strtotime( $current_date ) ),
					'yy-dd-mm'   => date_i18n( 'Y-d-m', strtotime( $current_date ) ),
					'yy/dd/mm'   => date_i18n( 'Y/d/m', strtotime( $current_date ) ),
					'dd-mm-yy'   => date_i18n( 'd-m-Y', strtotime( $current_date ) ),
					'dd/mm/yy'   => date_i18n( 'd/m/Y', strtotime( $current_date ) ),
					'mm-dd-yy'   => date_i18n( 'm-d-Y', strtotime( $current_date ) ),
					'mm/dd/yy'   => date_i18n( 'm/d/Y', strtotime( $current_date ) ),
					'd M , yy'   => date_i18n( 'j M , Y', strtotime( $current_date ) ),
					'D d M , yy' => date_i18n( 'D j M , Y', strtotime( $current_date ) ),
					'M d , yy'   => date_i18n( 'M  j, Y', strtotime( $current_date ) ),
					'D M d , yy' => date_i18n( 'D M  j, Y', strtotime( $current_date ) ),
				];
			}
			//=============================//
			public static function selection_area(): void {
				?>
                <div class="selection_area">
                    <label>
                        <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e( 'Search ....', 'abp-tour-travel' ); ?>"/>
                    </label>
                    <div class="selection_list"></div>
                </div>
				<?php
			}
			public static function selected_area( $name, $value = '' ): void {
				?>
                <div class="selected_area">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                    <div class="selected_list"></div>
                </div>
				<?php
			}
			//=============================//
			public static function title( $post_infos = [] ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! empty( $post_id ) && $post_id > 0 ) {
					$display_sku = $post_infos['display_sku'] ?? ABPTT_Function::get_post_info( $post_id, 'display_sku', 'off' );
					$post_sku    = $post_infos['post_sku'] ?? ABPTT_Function::get_post_info( $post_id, 'post_sku' );
					if ( ABPTT_Function::on_off( 'post_icon' ) ) {
						ABPTT_Layout::image_icon( ( $post_infos['post_icon'] ?? ABPTT_Function::get_post_info( $post_id, 'post_icon' ) ) );
					}
					echo esc_html( get_the_title( $post_id ) ); ?>
					<?php if ( ! empty( $post_sku ) && $display_sku == 'on' && ABPTT_Function::on_off( 'sku' ) ) { ?>
                        <small class="abp_color_gray">&nbsp;(<?php echo esc_html( $post_sku ); ?>)</small>
					<?php }
				}
			}
			public static function sub_title( $post_infos = [], $class = 'sub_title' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ABPTT_Function::on_off( 'sub_title' ) && $post_id > 0 ) {
					$value = $post_infos['sub_title'] ?? ABPTT_Function::get_post_info( $post_id, 'sub_title' );
					if ( ! empty( $value ) ) { ?>
                        <p class="abp <?php echo esc_attr( $class ); ?>">
							<?php echo esc_html( $value ); ?>
                        </p>
						<?php
					}
				}
			}
			public static function capacity( $post_infos = [], $class = 'publish' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ABPTT_Function::on_off( 'display_capacity' ) && $post_id > 0 ) {
					$display  = $post_infos['display_capacity'] ?? ABPTT_Function::get_post_info( $post_id, 'display_capacity', 'on' );
					$capacity = $post_infos['capacity'] ?? ABPTT_Function::get_total_qty( $post_id, $post_infos );
					if ( ! empty( $capacity ) && $display === 'on' ) { ?>
                        <div class="abp_tag <?php echo esc_attr( $class ); ?>">
							<?php ABPTT_Static::svg( 'user_group_2' );
								echo esc_html( $capacity . ' ' . __( 'Guests   ', 'abp-tour-travel' ) ); ?>
                        </div>
						<?php
					}
				}
			}
			public static function tour_duration( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'duration' ) || $post_id <= 0 ) {
					return;
				}
				$display = $post_infos['abptt_display_duration'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_duration', 'on' );
				$value   = $post_infos['abptt_duration'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_duration' );
				if ( $display === 'off' || empty( $value ) ) {
					return;
				}
				$type  = $post_infos['abptt_duration_type'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_duration_type', 'day' );
				if ( 'hour' === $type ) {
					$label = __( 'Hours', 'abp-tour-travel' );
				} elseif ( 'min' === $type ) {
					$label = __( 'Minutes', 'abp-tour-travel' );
				} else {
					$label = _n( 'Day', 'Days', $value, 'abp-tour-travel' );
				}
				?>
                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Tour Duration', 'abp-tour-travel' ); ?>">
                    <i class="fas fa-clock" aria-hidden="true"></i>
					<?php echo esc_html( $value . ' ' . $label ); ?>
                </div>
				<?php
			}
			public static function tour_night( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'stay_night' ) || $post_id <= 0 ) {
					return;
				}
				$display = $post_infos['abptt_display_duration_night'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_duration_night', 'off' );
				$value   = $post_infos['abptt_duration_night'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_duration_night' );
				if ( $display === 'off' || empty( $value ) ) {
					return;
				}
				?>
                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Stay Night', 'abp-tour-travel' ); ?>">
                    <i class="fas fa-moon" aria-hidden="true"></i>
					<?php // translators: %s: number of nights.
					echo esc_html( sprintf( _n( '%s Night', '%s Nights', $value, 'abp-tour-travel' ), $value ) ); ?>
                </div>
				<?php
			}
			public static function max_people( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'max_people' ) || $post_id <= 0 ) {
					return;
				}
				$display = $post_infos['abptt_display_max_people'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_max_people', 'on' );
				$value   = $post_infos['abptt_max_people'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_max_people' );
				if ( $display === 'off' || empty( $value ) ) {
					return;
				}
				?>
                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Max Guests', 'abp-tour-travel' ); ?>">
                    <i class="fas fa-user-friends" aria-hidden="true"></i>
					<?php // translators: %s: maximum number of guests.
					echo esc_html( sprintf( __( 'Max %s Guests', 'abp-tour-travel' ), $value ) ); ?>
                </div>
				<?php
			}
			public static function min_age( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'min_age' ) || $post_id <= 0 ) {
					return;
				}
				$display = $post_infos['abptt_display_min_age'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_min_age', 'on' );
				$value   = $post_infos['abptt_min_age'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_min_age' );
				if ( $display === 'off' || empty( $value ) ) {
					return;
				}
				?>
                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Age Range', 'abp-tour-travel' ); ?>">
                    <i class="fas fa-user-clock" aria-hidden="true"></i>
					<?php // translators: %s: minimum age.
					echo esc_html( sprintf( __( 'Age %s', 'abp-tour-travel' ), $value ) ); ?>
                </div>
				<?php
			}
			public static function start_place( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'start_place' ) || $post_id <= 0 ) {
					return;
				}
				$display = $post_infos['abptt_display_start_place'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_start_place', 'on' );
				$value   = $post_infos['abptt_start_place'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_start_place' );
				if ( $display === 'off' || empty( $value ) ) {
					return;
				}
				?>
                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Start Point', 'abp-tour-travel' ); ?>">
                    <i class="fas fa-flag-checkered" aria-hidden="true"></i>
					<?php echo esc_html( $value ); ?>
                </div>
				<?php
			}
			public static function rating( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'rating' ) || $post_id <= 0 ) {
					return;
				}
				$display = $post_infos['abptt_display_rating'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_display_rating', 'on' );
				if ( $display === 'off' ) {
					return;
				}
				$rating_data = ABPTT_Function::get_rating( $post_id );
				if ( empty( $rating_data ) ) {
					return;
				}
				$rating  = (float) $rating_data['rating'];
				$count   = (int) $rating_data['count'];
				$rounded = round( $rating * 2 ) / 2;
				?>
                <div class="abp_tag abp_rating_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Rating & Reviews', 'abp-tour-travel' ); ?>">
                    <span class="abp_rating_stars">
						<?php for ( $i = 1; $i <= 5; $i ++ ) :
							if ( $rounded >= $i ) { ?>
                                <i class="fas fa-star" aria-hidden="true"></i>
							<?php } elseif ( $rounded >= $i - 0.5 ) { ?>
                                <i class="fas fa-star-half-alt" aria-hidden="true"></i>
							<?php } else { ?>
                                <i class="far fa-star" aria-hidden="true"></i>
							<?php } ?>
						<?php endfor; ?>
                    </span>
                    <span class="abp_rating_value"><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></span>
                    <span class="abp_rating_count"><?php
						// translators: %s: number of reviews.
						echo esc_html( sprintf( _n( '(%s Review)', '(%s Reviews)', $count, 'abp-tour-travel' ), number_format_i18n( $count ) ) );
					?></span>
                </div>
				<?php
			}
			public static function top_pick( $post_infos = [], $class = 'top_pick' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ! ABPTT_Function::on_off( 'top_pick' ) || $post_id <= 0 ) {
					return;
				}
				$value = $post_infos['abptt_top_pick'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_top_pick', 'off' );
				if ( $value !== 'on' ) {
					return;
				}
				?>
                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php esc_attr_e( 'Top Pick', 'abp-tour-travel' ); ?>">
                    <i class="fas fa-thumbs-up" aria-hidden="true"></i>
					<?php esc_html_e( 'Top Pick', 'abp-tour-travel' ); ?>
                </div>
				<?php
			}
			public static function category( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ABPTT_Function::on_off( 'category' ) && $post_id > 0 ) {
					$display = $post_infos['display_category'] ?? ABPTT_Function::get_post_info( $post_id, 'display_category', 'on' );
					$value   = $post_infos['abptt_category'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_category' );
					if ( ! empty( $value ) && $display === 'on' ) {
						$values = explode( ',', $value );
						if ( ! empty( $values ) ) {
							foreach ( $values as $value ) {
								$value = ABPTT_Function::category_value( $value ); ?>
                                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( ABPTT_Function::category_label() . ' : ' . $value ); ?>">
									<?php ABPTT_Static::svg( 'category_1' );
										echo esc_html( $value ); ?>
                                </div>
								<?php
							}
						}
					}
				}
			}
			public static function organizer( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ABPTT_Function::on_off( 'organizer' ) && $post_id > 0 ) {
					$display = $post_infos['display_organizer'] ?? ABPTT_Function::get_post_info( $post_id, 'display_organizer', 'off' );
					$value   = $post_infos['abptt_organizer'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_organizer' );
					if ( ! empty( $value ) && $display === 'on' ) {
						$values = explode( ',', $value );
						if ( ! empty( $values ) ) {
							foreach ( $values as $value ) {
								$value = ABPTT_Function::organizer_value( $value );
								?>
                                <div class="abp_tag <?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( ABPTT_Function::organizer_label() . ' : ' . $value ); ?>">
									<?php ABPTT_Static::svg( 'organizer_2' );
										echo esc_html( $value ); ?>
                                </div>
								<?php
							}
						}
								}
							}
						}
						public static function location( $post_infos = [], $class = '' ): void {
							$post_id = absint( $post_infos['post_id'] ?? 0 );
							if ( ! ABPTT_Function::on_off( 'location' ) || $post_id <= 0 ) {
								return;
							}
							$display = $post_infos['display_location'] ?? ABPTT_Function::get_post_info( $post_id, 'display_location', 'on' );
							$value   = $post_infos['abptt_location'] ?? ABPTT_Function::get_post_info( $post_id, 'abptt_location' );
							if ( $display === 'on' && ! empty( $value ) ) {
								$values = array_filter( array_map( 'trim', explode( ',', $value ) ) );
								foreach ( $values as $value ) {
									$value = ABPTT_Function::location_value( $value );
									echo '<div class="abp_tag ' . esc_attr( $class ) . '" title="' . esc_attr( ABPTT_Function::location_label() . ' : ' . $value ) . '">';
									ABPTT_Static::svg( 'location_1' );
									echo esc_html( $value ) . '</div>';
								}
							}
						}
			public static function description( $post_infos = [], $class = '' ): void {
				$post_id = absint( $post_infos['post_id'] ?? 0 );
				if ( ABPTT_Function::on_off( 'post_des' ) && $post_id > 0 ) {
					$value = $post_infos['post_description'] ?? ABPTT_Function::get_post_info( $post_id, 'post_description' );
					if ( ! empty( $value ) ) { ?>
                        <div class="<?php echo esc_attr( $class ); ?>">
							<?php self::load_more( $value ); ?>
                        </div>
						<?php
					}
				}
			}
			public static function date_select_limit(): int {
				return max( 1, (int) apply_filters( 'abptt_date_select_limit', 7 ) );
			}
			public static function use_date_select( int $count ): bool {
				return $count > 1 && $count <= self::date_select_limit();
			}
			public static function use_date_picker( int $count ): bool {
				return $count > self::date_select_limit();
			}
			public static function start_date( $all_dates, $date = '', $all_times = [], $time_infos = [], $post_id = 0, $start_time = '' ): void {
				$all_dates = is_array( $all_dates ) ? array_values( $all_dates ) : [];
				$all_times = is_array( $all_times ) ? array_values( array_filter( array_map( 'strval', $all_times ), 'strlen' ) ) : [];
				$time_infos = is_array( $time_infos ) ? $time_infos : [];
				$post_id    = absint( $post_id );
				$start_time = is_scalar( $start_time ) ? (string) $start_time : '';
				if ( sizeof( $all_dates ) > 0 ) {
					$now          = ABPTT_Function::date_format( current_time( 'Y-m-d' ) );
					$date         = $date ?: current( $all_dates );
					$hidden_date  = ! empty( $date ) ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
					$visible_date = ! empty( $date ) ? ABPTT_Function::date_format( $date, 'date' ) : '';
					$date_count   = sizeof( $all_dates );
					if ( $date_count > 1 && ! self::use_date_select( $date_count ) ) {
						?>
                        <label>
                            <span><?php ABPTT_Static::svg( 'date_1' ); ?><?php esc_html_e( 'Select Tour Date', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                            <input type="hidden" name="start_date" value="<?php echo esc_attr( $hidden_date ); ?>" required/>
                            <input id="start_date" type="text" value="<?php echo esc_attr( $visible_date ); ?>" class="_form_control" placeholder="<?php echo esc_attr( $now ); ?>" data-alert="<?php esc_attr_e( 'Please Select Tour Date', 'abp-tour-travel' ); ?>" readonly required/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                        </label>
						<?php
						do_action( 'abptt_load_date_picker', '#start_date', $all_dates );
					} elseif ( $date_count > 1 ) {
						?>
                        <label>
                            <span><?php ABPTT_Static::svg( 'date_1' ); ?><?php esc_html_e( 'Select Tour Date', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                            <select id="abptt_start_date_select" name="start_date" class="_form_control" data-alert="<?php esc_attr_e( 'Please Select Tour Date', 'abp-tour-travel' ); ?>" required>
								<?php foreach ( $all_dates as $available_date ) {
									$date_value = gmdate( 'Y-m-d', strtotime( $available_date ) );
									$date_times = [];
									if ( $post_id > 0 && ! empty( $time_infos ) ) {
										$date_times = ABPTT_Function::available_times( $post_id, $time_infos, $date_value );
									}
									if ( empty( $date_times ) ) {
										?>
                                        <option value="<?php echo esc_attr( $date_value ); ?>" <?php selected( $date_value, $hidden_date ); ?>><?php echo esc_html( ABPTT_Function::date_format( $date_value, 'date' ) ); ?></option>
										<?php
									} else {
										foreach ( $date_times as $date_time ) {
											$option_value = $date_value . '|' . $date_time;
											$is_selected  = $date_value === $hidden_date && ( '' === $start_time || $date_time === $start_time );
											?>
                                            <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $is_selected, true ); ?>><?php echo esc_html( ABPTT_Function::date_format( $date_value . ' ' . $date_time, 'full' ) ); ?></option>
											<?php
										}
									}
								} ?>
                            </select>
                        </label>
						<?php
					} else {
						$single_time  = 1 === sizeof( $all_times ) ? (string) $all_times[0] : '';
						$display_date = $visible_date;
						if ( $single_time !== '' ) {
							$base_date    = gmdate( 'Y-m-d', strtotime( $date ) );
							$display_date = ABPTT_Function::date_format( $base_date . ' ' . $single_time, 'full' );
						}
						?>
                        <label>
                            <span><?php ABPTT_Static::svg( 'date_1' ); ?><?php esc_html_e( 'Tour Date', 'abp-tour-travel' ); ?></span>
                            <input type="hidden" name="start_date" value="<?php echo esc_attr( $hidden_date ); ?>" required/>
                            <?php if ( $single_time !== '' ) { ?>
                                <input type="hidden" name="session_time" value="<?php echo esc_attr( $single_time ); ?>"/>
                            <?php } ?>
                            <span class="_color_theme_fs_h5"><?php echo esc_html( $display_date ); ?></span>
                        </label>
						<?php
					}
				} else {
					ABPTT_Layout::layout_warning_info_xs( 'not_date' );
				}
			}
			public static function start_time( $form_data=[]): void {
				$all_times  = $form_data['all_times'] ?? [];
				$all_times  = is_array( $all_times ) ? array_values( array_filter( array_map( 'strval', $all_times ), 'strlen' ) ) : [];
				$start_date = $form_data['start_date'] ?? '';
				$start_time = $form_data['start_time'] ?? '';
				$date_count = is_array( $form_data['all_dates'] ?? null ) ? count( $form_data['all_dates'] ) : 0;
				if ( $date_count > 1 && self::use_date_select( $date_count ) ) {
					return;
				}
				if ( empty( $all_times ) ) {
					return;
				}
				$selected_time = in_array( $start_time, $all_times, true ) ? $start_time : (string) $all_times[0];
				$base_date     = ! empty( $start_date ) ? gmdate( 'Y-m-d', strtotime( $start_date ) ) : current_time( 'Y-m-d' );
				if ( 1 === sizeof( $all_times ) && 1 === $date_count ) {
					return;
				}
				?>
                    <label class="_text_nowrap abptt_top_time">
                        <span>⏰ <?php esc_html_e( 'Time :', 'abp-tour-travel' ); ?></span>
						<?php if ( sizeof( $all_times ) > 1 ) { ?>
                            <select class="_form_control" name="session_time">
								<?php foreach ( $all_times as $time ) { ?>
                                    <option value="<?php echo esc_attr( $time ); ?>" <?php selected( $time, $selected_time ) ?>><?php echo esc_html( ABPTT_Function::date_format( $base_date . ' ' . $time ) ); ?></option>
								<?php } ?>
                            </select>
						<?php } else {
							$time_label = $base_date . ' ' . $selected_time;
							?>
                            <input type="hidden" name="session_time" value="<?php echo esc_attr( $selected_time ); ?>"/>
                            <span class="_color_theme_fs_h5"><?php echo esc_html( ABPTT_Function::date_format( $time_label, 'time' ) ); ?></span>
						<?php } ?>
                    </label>
				<?php
			}

			public static function item_feature( $features = '' ): void {
				if ( ! ABPTT_Function::on_off( 'feature' ) ) {
					return;
				}
				// The per-tour templates pass $post_infos so both lists and their switches are
				// honoured. A plain string stays supported for callers that only have the legacy
				// 'post_feature' value, which keeps older themes and addons working.
				$post_infos    = is_array( $features ) ? $features : [];
				$included      = is_array( $features ) ? ( $features['post_feature'] ?? '' ) : $features;
				$excluded      = (string) ( $post_infos['post_feature_excluded'] ?? '' );
				$show_included = (string) ( $post_infos['display_feature_included'] ?? 'on' ) !== 'off';
				$show_excluded = (string) ( $post_infos['display_feature_excluded'] ?? 'on' ) !== 'off';
				$included      = $show_included ? (string) $included : '';
				$excluded      = $show_excluded ? $excluded : '';

				if ( $included === '' && $excluded === '' ) {
					return;
				}
				$abptt_feature = defined( 'ABPTT_Feature' ) ? ABPTT_Feature : [];
				if ( ! is_array( $abptt_feature ) ) {
					return;
				}

				self::feature_area( $included, $abptt_feature, 'included_feature_area' );
				self::feature_area( $excluded, $abptt_feature, 'excluded_feature_area' );
			}

			private static function feature_area( $features, $abptt_feature, $class ): void {
				if ( $features === '' ) {
					return;
				}
				$feature_ids = array_filter( array_map( 'trim', explode( ',', (string) $features ) ) );
				if ( empty( $feature_ids ) ) {
					return;
				}
				ob_start();
				foreach ( $feature_ids as $fec_id ) {
					$feature = $abptt_feature[ $fec_id ] ?? null;
					if ( ! is_array( $feature ) ) {
						continue;
					}
					$label = $feature['label'] ?? '';
					$value = $feature['value'] ?? '';
					$icon  = $feature['icon'] ?? '';
					if ( ! $label && ! $value ) {
						continue;
					}
					echo '<span class="abp_tag" title="' . esc_attr( $label ) . '">';
					ABPTT_Layout::image_icon( $icon );
					echo esc_html( implode( ' - ', array_filter( [ $label, $value ] ) ) );
					echo '</span>';
				}
				$tags = ob_get_clean();
				if ( $tags === '' ) {
					return;
				}
				?>
				<div class="item_spec load_more <?php echo esc_attr( $class ); ?>">
					<div class="_f_wrap_gap_xxs">
						<?php echo $tags; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped HTML assembled by the layout builder. ?>
					</div>
					<span class="load_more_action" data-less="<?php esc_html_e( '....Less ', 'abp-tour-travel' ); ?>" data-more="<?php esc_html_e( '.... More', 'abp-tour-travel' ); ?>"><?php esc_html_e( '.... More', 'abp-tour-travel' ); ?></span>
				</div>
				<?php
			}
			public static function item_select( $ticket_info, $key, $price = 0 ): void {
				if ( ! is_array( $ticket_info ) || empty( $key ) ) {
					return;
				}
				$total_qty     = intval( $ticket_info['qty'] ?? 0 );
				$max_qty       = $ticket_info['max_qty'] ?? '';
				$available_qty = $ticket_info['available'] ?? $total_qty;
				$max_qty       = ( $max_qty !== '' && intval( $max_qty ) <= $available_qty ) ? intval( $max_qty ) : $available_qty;
				$min_qty       = intval( $ticket_info['min_qty'] ?? 1 );
				//echo '<pre>';print_r($min_qty);echo '</pre>';
				if ( $max_qty >= $min_qty ) {
					$collapse_id = '#ticket_' . $key;
					?>
                    <div class="_divider_xxs"></div>
                    <div class="item_select">
                        <div class="custom_checkbox">
                            <input type="hidden" name="item_check[]" value="" data-id="<?php echo esc_attr( $collapse_id ); ?>"/>
                            <div class="checkbox_item" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                <h3 class="abp"><span data-icon class="far fa-square"></span></h3>
								<?php echo esc_html__( 'Select ', 'abp-tour-travel' ) . ' ' . esc_html( ABPTT_Function::ticket_name( $key ) ); ?>
                            </div>
                        </div>
						<?php
							if ( $max_qty > $min_qty ) {
								$input_info = [
									'name'        => 'item_qty[]',
									'price'       => $price,
									'available'   => $available_qty,
									'min_qty'     => $min_qty,
									'max_qty'     => $max_qty,
									'collapse_id' => $collapse_id,
								];
								ABPTT_Layout::quantity_input( $input_info );
							} else { ?>
                                <input type="hidden" name="item_qty[]" value="<?php echo esc_attr( $min_qty ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-min="<?php echo esc_attr( $min_qty ); ?>"/>
							<?php } ?>
                    </div>
				<?php } else { ?>
                    <span class="trash abp_tag"><?php esc_html_e( 'Sold Out !', 'abp-tour-travel' ); ?></span>
					<?php
				}
			}
			public static function create_client_form( $form, $name ): void {
				if ( ! is_array( $form ) ) {
					return;
				}
				$name     = is_string( $name ) ? $name . '[]' : '';
				$type     = $form['type'] ?? '';
				$required = ( ( $form['required'] ?? '' ) === 'on' ) ? 'required' : '';
				$label    = $form['label'] ?? '';
				$d_value  = $form['d_value'] ?? '';
				if ( $type === 'text' || $type === 'number' || $type === 'email' ) {
					if ( 'text' === $type ) {
						$validation_class = 'validation_name';
					} elseif ( 'number' === $type ) {
						$validation_class = 'validation_number';
					} else {
						$validation_class = '';
					}
					?>
                    <label class="_input_item">
						<?php ABPTT_Layout::input_title( $label, $required ); ?>
                        <input type="<?php echo esc_attr( $type ); ?>"
                               name="<?php echo esc_attr( $name ); ?>"
                               value="<?php echo esc_attr( $d_value ); ?>"
                               class="_form_control <?php echo esc_attr( $validation_class ); ?>"
                               placeholder="<?php echo esc_attr( $label ); ?>"
                               title="<?php echo esc_attr( $label ); ?>"
							<?php echo esc_attr( $required ); ?> />
                    </label>
					<?php
					return;
				}
				if ( $type === 'date' ) {
					ABPTT_Layout::input_date( $name, $d_value, $label, $required );
					return;
				}
				if ( $type === 'textarea' ) {
					ABPTT_Layout::textarea( $name, $d_value, $label, $required );
					return;
				}
				// Options bound input layouts (Select, Checkbox, Radio)
				if ( $type === 'select' || $type === 'checkbox' || $type === 'radio' ) {
					$options_str = $form['option'] ?? '';
					$options     = ( $options_str !== '' ) ? explode( ',', $options_str ) : [];
					if ( 'select' === $type ) {
						ABPTT_Layout::select( $name, $d_value, $label, $required, $options );
					} elseif ( 'checkbox' === $type ) {
						ABPTT_Layout::checkbox( $name, $d_value, $label, $required, $options );
					} elseif ( 'radio' === $type ) {
						ABPTT_Layout::radio( $name, $d_value, $label, $required, $options );
					}
				}
			}
			public static function sp( $id = '', $sp_info = [], $post_infos = [], $form_data = [] ): void {
				if ( empty( $sp_info ) ) {
					if ( ! empty( $id ) ) {
						$row = ABPTT_Query::get_sp( $id );
						if ( ! empty( $row ) ) {
							$sp_info = current( $row );
						}
					}
				}
				if ( ! empty( $sp_info ) ) {
					$others      = json_decode( $sp_info['others'] ?? '', true ) ?: [];
					$cell_width  = $others['width'] ?? 50;
					$cell_height = $others['height'] ?? 50;
					$gap         = $others['gap'] ?? 0;
					$bg_image    = $others['bg_image'] ?? '';
					$img_url     = ! empty( $bg_image ) && $bg_image > 0 ? ABPTT_Function::get_image_url( '', $bg_image ) : '';
					$bg_color    = $others['bg_color'] ?? '#fff';
					$radius      = $others['radius'] ?? 0;
					$layout      = json_decode( $sp_info['layout_data'] ?? '', true ) ?: [];
					//echo '<pre>';                print_r($ticket_types);                echo '</pre>';
					$cols         = intval( $others['column'] ?? 10 );
					$meta_info    = json_decode( $sp_info['seat_info'] ?? '', true ) ?: [];
					$hidden_cells = [];
					foreach ( $layout as $index => $cell ) {
						$c_span = intval( $cell['width_ratio'] ?? 1 );
						$r_span = intval( $cell['height_ratio'] ?? 1 );
						if ( $c_span > 1 || $r_span > 1 ) {
							for ( $r = 0; $r < $r_span; $r ++ ) {
								for ( $c = 0; $c < $c_span; $c ++ ) {
									if ( $r === 0 && $c === 0 ) {
										continue;
									}
									$target_idx                  = $index + ( $r * $cols ) + $c;
									$hidden_cells[ $target_idx ] = true;
								}
							}
						}
					}
					$post_id       = $form_data['post_id'] ?? '';
					$tour_date    = $form_data['tour_date'] ?? ($form_data['start_date'] ?? '');
					$session_time  = $form_data['session_time'] ?? ($form_data['start_time'] ?? '');
					$price_info    = [];
					$sold_seat     = [];
					$reserved_seat = [];
					$sale          = false;
					if ( ! empty( $tour_date ) && ! empty( $session_time ) && ! empty( $post_id ) && $post_id > 0 ) {
						foreach ( $meta_info as $tic_id => $ticket_num ) {
							$price_info[ $tic_id ] = ABPTT_Function::get_price( $post_infos, $tic_id, $session_time );
						}
						$form_data['sp_id'] = $id;
						$sold_seat          = ABPTT_Query::get_sold_seat( $form_data );
						$sale               = true;
					}
					//echo '<pre>';                    print_r($form_data);                    echo '</pre>';
					// echo '<pre>';                    print_r($sold_seat);                    echo '</pre>';
					?>
                    <div class="sp_canvas _section_15_xs_mar_auto" style="grid-template-columns: repeat(<?php echo esc_attr( $cols ); ?>, 1fr); background-image: url('<?php echo esc_url( $img_url ); ?>'); background-color: <?php echo esc_attr( $bg_color ); ?>;gap: <?php echo esc_attr( $gap ); ?>px;">
						<?php foreach ( $layout as $index => $cell ) {
							if ( isset( $hidden_cells[ $index ] ) ) {
								continue;
							}
							$type_id         = $cell['id'] ?? '';
							$name            = $cell['name'] ?? '';
							$c_span          = intval( $cell['width_ratio'] ?? 1 );
							$r_span          = intval( $cell['height_ratio'] ?? 1 );
							$rotate          = intval( $cell['rotate'] ?? 0 );
							$fs              = $cell['fs'] ?? 12;
							$is_seat         = ( $cell['type'] === 'seat' );
							$seat_type_class = $sale && $is_seat ? 'available' : '';
							$title           = $sale && $is_seat ? __( 'Available !', 'abp-tour-travel' ) : '';
							$seat_type_class = $sale && in_array( $name, $sold_seat ) ? 'sold' : $seat_type_class;
							$title           = $sale && in_array( $name, $sold_seat ) ? __( 'Sold !', 'abp-tour-travel' ) : $title;
							$class           = $is_seat ? "sp_cell " . $seat_type_class : "sp_decor";
							$tag_attr        = $sale && $is_seat && $seat_type_class == 'available' ? 'data-name="' . $name . '" data-id="' . $type_id . '" data-price="' . ( $price_info[ $type_id ] ?? 0 ) . '"' : '';
							if ( ! empty( $title ) ) {
								$tag_attr = $tag_attr . '  title="' . $title . '"';
							}
							$color      = $is_seat ? ABPTT_Function::ticket_color( $type_id ) : ABPTT_Function::decor_color( $type_id );
							$icon_image = $is_seat ? ABPTT_Function::ticket_icon( $type_id ) : ABPTT_Function::decor_icon( $type_id );
							$width      = $cell_width * $c_span;
							$height     = $cell_height * $r_span;
							if ( $gap > 0 ) {
								$width  = $c_span > 1 ? $width + ( $c_span - 1 ) * $gap : $width;
								$height = $r_span > 1 ? $height + ( $r_span - 1 ) * $gap : $height;
							}
							$style = "color: {$color}; grid-column: span {$c_span}; grid-row: span {$r_span}; width:{$width}px;height:{$height}px; border:1px solid  {$color};font-size:{$fs}px;border-radius:{$radius}px;";
							$image = '';
							if ( ! empty( $icon_image ) ) {
								if ( is_numeric( $icon_image ) ) {
									$image = ABPTT_Function::get_image_url( '', $icon_image );
								}
							}
							?>
                            <div class="<?php echo esc_attr( $class ); ?>" style="<?php echo esc_attr( $style ); ?>" <?php echo wp_kses_post( $tag_attr ); ?>>
                                <div class="cell_content <?php echo esc_attr( $rotate ? "rotate-{$rotate}" : "" ); ?>" style="background-image: url('<?php echo esc_url( $image ); ?>');">
									<?php ABPTT_Layout::image_icon( $icon_image ); ?>
                                    <span class="cell_label"><?php echo esc_html( $name ); ?></span>
                                </div>
                            </div>
						<?php } ?>
                    </div>
					<?php
				} else {
					ABPTT_Layout::layout_warning_info( 'no_sp_config' );
				}
			}
			//=============================//
			public static function ticket_info( $ticket_infos, $post_id, $seat_type = '', $sp_id = 0 ): void {
				if ( ! empty( $ticket_infos ) && is_array( $ticket_infos ) ) { ?>
                    <ul class=" abp">
						<?php foreach ( $ticket_infos as $tic_id => $ticket_info ) {
							if ( ! empty( $ticket_info ) && sizeof( $ticket_info ) > 0 ) {
								$current_seat_type = $ticket_info['seat_type'] ?? $seat_type;
								$qty       = $ticket_info['qty'] ?? 1;
								$price     = $ticket_info['price'] ?? 0;
								$total     = $price * $qty;
								$name      = $ticket_info['name'] ?? '';
								if ( $current_seat_type === 'sp' ) {
									$name = $name . ' - ' . ABPTT_Function::sp_label( $post_id, ( $ticket_info['sp_id'] ?? $sp_id ) );
								}
								?>
                                <li>
                                    <strong><?php echo esc_html( $name ); ?></strong>
									<?php echo esc_html( ' X ' . $qty . ' = ' ) . ' ' . ( ! empty( $price ) && $total > 0 ? wp_kses_post( wc_price( $total ) ) : esc_html__( 'FREE', 'abp-tour-travel' ) ); ?>
                                </li>
								<?php
							}
						} ?>
                    </ul>
				<?php }
			}
			public static function additional_info( $additional_infos ): void {
				if ( ! empty( $additional_infos ) && is_array( $additional_infos ) ) { ?>
                    <ul class=" abp">
						<?php foreach ( $additional_infos as $ex_info ) {
							if ( ! empty( $ex_info ) && sizeof( $ex_info ) > 0 ) {
								$name       = $ex_info['name'] ?? '';
								$qty        = $ex_info['qty'] ?? 1;
								$price      = $ex_info['price'] ?? 0;
								$total      = $price * $qty;
								$returnable = $ex_info['returnable'] ?? 'no';
								if ( ! empty( $name ) ) { ?>
                                    <li>
                                        <strong><?php echo esc_html( $name ); ?></strong>
										<?php echo esc_html( ' X ' . $qty . ' = ' ) . ' ' . ( ! empty( $price ) && $total > 0 ? wp_kses_post( wc_price( $total ) ) : esc_html__( 'FREE', 'abp-tour-travel' ) ); ?>
										<?php
											if ( $returnable == 'yes' ) {
												?> <span class="_color_required"> - <?php esc_html_e( 'Returnable', 'abp-tour-travel' ); ?></span><?php
											} ?>
                                    </li>
									<?php
								}
							}
						} ?>
                    </ul>
				<?php }
			}
			public static function client_info( $passenger_infos ): void {
				if ( ! empty( $passenger_infos ) && is_array( $passenger_infos ) ) { ?>
                    <ul class=" abp">
						<?php foreach ( $passenger_infos as $pas_form ) {
							if ( ! empty( $pas_form ) && sizeof( $pas_form ) > 0 ) {
								foreach ( $pas_form as $info ) {
									$label = $info['label'] ?? '';
									$value = $info['value'] ?? '';
									if ( ! empty( $label ) && ! empty( $value ) ) { ?>
                                        <li>
                                            <strong><?php echo esc_html( $label ); ?></strong> : <?php echo esc_html( $value ); ?>
                                        </li>
										<?php
									}
								}
							}
						} ?>
                    </ul>
				<?php }
			}
			public static function billing_info( $booking_list ): void {
				if ( ! empty( $booking_list ) ) {
					$billing_name    = $booking_list['billing_name'] ?? '';
					$billing_email   = $booking_list['billing_email'] ?? '';
					$billing_phone   = $booking_list['billing_phone'] ?? '';
					$billing_address = $booking_list['billing_address'] ?? '';
					?>
                    <ul class=" abp">
						<?php if ( ! empty( $billing_name ) ) { ?>
                            <li><strong><?php esc_html_e( 'Name :', 'abp-tour-travel' ); ?></strong>&nbsp;<?php echo esc_html( $billing_name ); ?></li>
						<?php } ?>
						<?php if ( ! empty( $billing_email ) ) { ?>
                            <li><strong><?php esc_html_e( 'E-Mail :', 'abp-tour-travel' ); ?></strong>&nbsp;<?php echo esc_html( $billing_email ); ?></li>
						<?php } ?>
						<?php if ( ! empty( $billing_phone ) ) { ?>
                            <li><strong><?php esc_html_e( 'Phone :', 'abp-tour-travel' ); ?></strong>&nbsp;<?php echo esc_html( $billing_phone ); ?></li>
						<?php } ?>
						<?php if ( ! empty( $billing_address ) ) { ?>
                            <li><strong><?php esc_html_e( 'Address :', 'abp-tour-travel' ); ?></strong>&nbsp;<?php echo esc_html( $billing_address ); ?></li>
						<?php } ?>
                    </ul>
					<?php
				}
			}
			//=============================//
			public static function filter_post_list(): void {
				$label      = ABPTT_Function::label();
				$brand_icon = ABPTT_Function::icon();
				// echo '<pre>';print_r($configuration);echo '</pre>';
				?>
                <div class="_input_item abp_dropdown post_selection">
                    <label>
                        <span class="_gap_xxs"><?php ABPTT_Layout::image_icon( $brand_icon ); ?><?php echo esc_html( $label ); ?></span>
                        <input type="hidden" name="post_id" value=""/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php echo esc_attr( $label ); ?>" value=""/>
                    </label>
					<?php if ( sizeof( ABPTT_ids ) > 0 ) { ?>
                        <div class="dropdown_list">
                            <ul class="abp ">
								<?php foreach ( ABPTT_ids as $all_post_id ) {
									$sku      = ABPTT_Function::get_post_info( $all_post_id, 'post_sku' );
									$category = ABPTT_Function::get_post_info( $all_post_id, 'abptt_category' );
									$category = ! empty( $category ) ? get_term( $category )->name : '';
									$title    = get_the_title( $all_post_id );
									?>
                                    <li class="_gap_xxs" data-value="<?php echo esc_attr( $all_post_id ); ?>" data-text="<?php echo esc_attr( $title ); ?>">
										<?php if ( ABPTT_Function::on_off( 'post_icon' ) ) {
											ABPTT_Layout::image_icon( ABPTT_Function::get_post_info( $all_post_id, 'post_icon' ) );
										} ?>
                                        <span class="_fs_label"><?php echo esc_html( $title ); ?></span>
										<?php if ( ! empty( $category ) && ABPTT_Function::on_off( 'category' ) ) { ?>
                                            <sub class="abp_color_gray"> - <?php echo esc_html( $category ); ?></sub>
										<?php } ?>
										<?php if ( ! empty( $sku ) && ABPTT_Function::on_off( 'sku' ) ) { ?>
                                            <sub class="abp_color_info"> - <?php echo esc_html( $sku ); ?></sub>
										<?php } ?>
                                    </li>
								<?php } ?>
                            </ul>
                        </div>
					<?php } ?>
                </div>
				<?php
			}
			public static function filter_booking_date(): void {
				$now         = ABPTT_Function::date_format( current_time( 'Y-m-d' ) );
				?>
                <div class="_input_item">
                    <label>
                        <span class="_gap_xs"><?php ABPTT_Static::svg( 'date_1' ); ?><?php esc_html_e( 'Tour Date', 'abp-tour-travel' ) ?></span>
                        <input type="hidden" name="tour_date" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                    </label>
                </div>
				<?php
			}
			public static function filter_booking_date_between(): void {
				$now         = ABPTT_Function::date_format( current_time( 'Y-m-d' ) );
				?>
                <div class="_g_input_input_item_fd_column">
                    <label><span class="_gap_xs"><?php ABPTT_Static::svg( 'date_2' ); ?><?php esc_html_e( 'Tour Date Between', 'abp-tour-travel' ) ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="tour_date_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="tour_date_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                        </label>
                    </div>
                </div>
				<?php
			}
			public static function filter_order_date(): void {
				$now         = ABPTT_Function::date_format( current_time( 'Y-m-d' ) );
				?>
                <div class="_input_item">
                    <label>
                        <span class="_gap_xs">🗓️ <?php esc_html_e( 'Order Date', 'abp-tour-travel' ) ?></span>
                        <input type="hidden" name="order_date" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                    </label>
                </div>
				<?php
			}
			public static function filter_order_date_between(): void {
				$now         = ABPTT_Function::date_format( current_time( 'Y-m-d' ) );
				?>
                <div class="_g_input_input_item_fd_column" data-collapse="#view_more_filter_option">
                    <label class="_mar_b_xxs"><span class="_gap_xs">⏰ <?php esc_html_e( 'Order Date Between', 'abp-tour-travel' ); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="order_date_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="order_date_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-tour-travel' ); ?>"></span>
                        </label>
                    </div>
                </div>
				<?php
			}
			public static function filter_user_id(): void {
				$all_users = get_users( array(
					'fields' => array( 'ID', 'display_name' ),
				) );
				?>
                <div class="_input_item abp_dropdown " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">👨‍💼  <?php esc_html_e( 'User Name', 'abp-tour-travel' ); ?></span>
                        <input type="hidden" name="user_id" value=""/>
                        <input type="text" class="_form_control_w_full" placeholder="<?php esc_attr_e( 'User Name', 'abp-tour-travel' ); ?>" value=""/>
                    </label>
					<?php if ( ! empty( $all_users ) ) { ?>
                        <div class="dropdown_list">
                            <ul class="abp ">
								<?php foreach ( $all_users as $user ) { ?>
                                    <li data-value="<?php echo esc_attr( $user->ID ); ?>" data-text="<?php echo esc_attr( $user->display_name ); ?>">
                                        <span class="_fs_label"><?php echo esc_html( $user->display_name ); ?></span>
                                    </li>
								<?php } ?>
                            </ul>
                        </div>
					<?php } ?>
                </div>
				<?php
			}
			public static function filter_order_id(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">📦 <?php esc_html_e( 'Order ID', 'abp-tour-travel' ); ?></span>
                        <input type="number" class="_form_control_w_full validation_number" name="order_id" placeholder="<?php esc_attr_e( 'Order ID', 'abp-tour-travel' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}
			public static function filter_bill_name(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">👤 <?php esc_html_e( 'Billing Name', 'abp-tour-travel' ); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_name" placeholder="<?php esc_attr_e( 'Billing Name', 'abp-tour-travel' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}
			public static function filter_bill_email(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">✉️ <?php esc_html_e( 'Billing Email', 'abp-tour-travel' ); ?></span>
                        <input type="email" class="_form_control_w_full " name="billing_email" placeholder="<?php esc_attr_e( 'Billing Email', 'abp-tour-travel' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}
			public static function filter_bill_phone(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label>
                        <span class="_gap_xs">☎️ <?php esc_html_e( 'Billing phone', 'abp-tour-travel' ); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_phone" placeholder="<?php esc_attr_e( 'Billing phone', 'abp-tour-travel' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}
		}
		new ABPTT_Layout();
	}