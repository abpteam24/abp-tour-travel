<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTT_Category' ) ) {
		class ABPTT_Category {
			public function __construct() {
				add_action( 'abptt_global_category', array( $this, 'global_category' ) );
				add_action( 'wp_ajax_abptt_add_tax_category', array( $this, 'add_tax_category' ) );
				add_action( 'wp_ajax_abptt_save_tax_category', array( $this, 'save_tax_category' ) );
				add_action( 'wp_ajax_abptt_delete_tax_category', array( $this, 'delete_tax_category' ) );
				add_action( 'abptt_category_update', array( $this, 'update_category' ) );
			}
			public function global_category(): void {
				//echo '<pre>';print_r(ABPTT_Function::get_taxonomy( 'abptt_category' ));echo '</pre>';
				if ( ABPTT_Function::on_off( 'category' ) ) {
					$label = ABPTT_Function::category_label(); ?>
                    <div class="_fj_between_mar_b_xs">
                        <h5 class="abp_gap_xxs"><?php ABPTT_Static::icon_svg( 'category_1' ); ?><?php echo esc_html( $label ); ?></h5>
						<?php ABPTT_Layout::button_global_popup( 'tax_category', __( 'Add New', 'abp-tour-travel' ) . ' ' . $label ); ?>
                    </div>
					<?php ABPTT_Layout::info_text( 'abptt_category' ); ?>
                    <div class="tax_category _ov_auto_mar_t_xs">
						<?php $this->category_list(); ?>
                    </div>
					<?php
				}
			}
			public function add_tax_category(): void {
				if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
				}
				$term_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
				ob_start();
				$name      = $slug = $des = '';
				$label     = ABPTT_Function::category_label();
				$btn_label = __( 'Save', 'abp-tour-travel' ) . ' ' . $label;
				$title     = __( 'Add new ', 'abp-tour-travel' ) . ' ' . $label;
				if ( ! empty( $term_id ) ) {
					$term = get_term( $term_id );
					if ( ! empty( $term ) ) {
						$name      = $term->name;
						$slug      = $term->slug;
						$des       = $term->description;
						$btn_label = __( 'Update', 'abp-tour-travel' ) . ' ' . $label . ' ' . $name;
						$title     = __( 'Edit ', 'abp-tour-travel' ) . ' ' . $label . ' ' . $name;
					}
				}
				?>
                <div class="abp_form">
                    <h5 class="abp_gap_xxs"><?php ABPTT_Static::icon_svg( 'category' ); ?><?php echo esc_html( $title ); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr( $term_id ); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Name', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-tour-travel' ); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
							<?php ABPTT_Layout::info_text( 'cat_name' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Slug (Optional)', 'abp-tour-travel' ); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-tour-travel' ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
							<?php ABPTT_Layout::info_text( 'cat_slug' ); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Description(Optional)', 'abp-tour-travel' ); ?></span>
                                <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Description', 'abp-tour-travel' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                            </label>
                            <div class="_divider_xs"></div>
							<?php ABPTT_Layout::info_text( 'cat_des' ); ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
					<?php ABPTT_Layout::button_global_save( 'tax_category', $btn_label ); ?>
                </div>
				<?php
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __( 'Form Loaded Successfully .....! ', 'abp-tour-travel' ) ] );
			}
			public function save_tax_category(): void {
				if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
				}
				$post_int      = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
				$post_val      = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
				$post_textarea = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : $default;
				$post_slug     = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_title( wp_unslash( $_POST[ $key ] ) ) : $default;
				$tax_id        = $post_int( 'id' );
				$name          = $post_val( 'name' );
				$slug          = $post_slug( 'slug' );
				$description   = $post_textarea( 'description' );
				$post_id       = $post_int( 'post_id' );
				$label         = ABPTT_Function::category_label();
				if ( empty( $name ) ) {
					ob_start();
					if ( empty( $post_id ) || $post_id <= 0 ) {
						$this->category_list();
					}
					$html = ob_get_clean();
					wp_send_json_error( [ 'html' => $html, 'type' => 'warn', 'msg' => $label . ' ' . __( 'Name cannot be blank!', 'abp-tour-travel' ) ], 400 );
				}
				if ( $tax_id > 0 ) {
					$result = wp_update_term( $tax_id, 'abptt_category', [
						'name'        => $name,
						'slug'        => $slug,
						'description' => $description,
					] );
				} else {
					$result = wp_insert_term( $name, 'abptt_category', [
						'slug'        => $slug,
						'description' => $description,
					] );
				}
				$this->update_category();
				ob_start();
				if ( empty( $post_id ) || $post_id <= 0 ) {
					$this->category_list();
				}
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'type' => 'warn', 'msg' => $result->get_error_message() ], 400 );
				}
				wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'js' => ABPTT_Function::option_js( $post_id, 'abptt_category' ), 'msg' => $label . ' ' . __( 'Saved Successfully !', 'abp-tour-travel' ), ] );
			}
			public function delete_tax_category(): void {
				if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
				}
				$tax_id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
				$label  = ABPTT_Function::category_label();
				if ( empty( $tax_id ) || ! is_numeric( $tax_id ) ) {
					ob_start();
					$this->category_list();
					$html = ob_get_clean();
					wp_send_json_error( [ 'html' => $html, 'msg' => $label . ' ' . __( 'id Invalid...!', 'abp-tour-travel' ), 'type' => 'warn' ] );
				}
				$result = wp_delete_term( $tax_id, 'abptt_category' );
				$this->update_category();
				ob_start();
				$this->category_list();
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn' ] );
				}
				wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => ABPTT_Function::category_label() . ' ' . __( 'Deleted Successfully !', 'abp-tour-travel' ) ] );
			}
			public function update_category(): void {
				$taxonomies = ABPTT_Function::get_taxonomy( 'abptt_category' );
				$tax        = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$tax[ $taxonomy->term_id ]['label']       = $taxonomy->name;
						$tax[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $tax );
				update_option( 'abptt_category', $tax );
			}
			public function category_list(): void {
				$all_categories = ABPTT_Function::get_option( 'abptt_category' );
				//echo '<pre>';print_r($all_categories);echo '</pre>';
				$count          = 1;
				if ( ! empty( $all_categories ) && is_array( $all_categories ) && sizeof( $all_categories ) > 0 ) { ?>
                    <table class="abp">
                        <thead>
                        <tr>
                            <th class="_w_75"><?php esc_html_e( 'SI', 'abp-tour-travel' ); ?></th>
                            <th><?php echo esc_html( ABPTT_Function::category_label() ); ?></th>
                            <th class="_w_75"><?php esc_html_e( 'ID', 'abp-tour-travel' ); ?></th>
                            <th class="_text_center"><?php esc_html_e( 'Description', 'abp-tour-travel' ); ?></th>
                            <th class="_w_250_text_center"><?php esc_html_e( 'Shortcode', 'abp-tour-travel' ); ?></th>
                            <th class="_w_100_text_center"><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $all_categories as $term_id => $category ) {
							$name = $category['label'] ?? ''; ?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="abp_fs_h5_color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <td><?php echo esc_html( $category['description'] ?? '' ); ?></td>
                                <th><code> [abptt-post cat_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th>
                                    <div class="_fj_center">
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_yellow_xxs" onclick="abptt_popup_open_global('tax_category','<?php echo esc_attr( $term_id ); ?>')" title="<?php echo esc_attr__( 'Edit : ', 'abp-tour-travel' ) . ' ' . esc_attr( $name ); ?>"><?php ABPTT_Static::icon_svg( 'edit' ); ?></button>
                                            <button type="button" class="_btn_light_danger_xxs" onclick="abptt_delete_global('tax_category','<?php echo esc_attr( $term_id ); ?>')" title="<?php echo esc_attr__( 'Trash : ', 'abp-tour-travel' ) . ' ' . esc_attr( $name ); ?>"><?php ABPTT_Static::icon_svg( 'close_1' ); ?></button>
                                        </div>
                                    </div>
                                </th>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPTT_Layout::layout_warning_info( 'no_category' );
				}
			}
		}
		new ABPTT_Category();
	}