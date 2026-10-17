<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Feature' ) ) {
        class ABPTT_Feature {
            public function __construct() {
                add_action( 'abptt_global_feature', array( $this, 'global_feature' ) );
                add_action( 'abptt_post_content', array( $this, 'post_feature_config' ) );
                add_action( 'wp_ajax_abptt_add_option_feature', array( $this, 'add_option_feature' ) );
                add_action( 'wp_ajax_abptt_save_option_feature', array( $this, 'save_option_feature' ) );
                add_action( 'wp_ajax_abptt_delete_option_feature', array( $this, 'delete_option_feature' ) );
            }

            public function global_feature(): void {
                if ( ABPTT_Function::on_off( 'feature' ) ) {
                    $label = ABPTT_Function::feature_label(); ?>
                    <div class="_fj_between">
                        <h5 class="abp_gap_xs">🔗<?php echo esc_html( $label ); ?></h5>
                        <?php ABPTT_Layout::button_global_popup( 'option_feature', __( 'Add New', 'abp-tour-travel' ) . ' ' . $label ); ?>
                    </div>
                    <?php ABPTT_Layout::info_text( 'abptt_feature' ); ?>
                    <div class="option_feature _ov_auto_mar_t_xs">
                        <?php $this->feature_list(); ?>
                    </div>
                    <?php
                }
            }

            public function post_feature_config( $post_infos = [] ): void {
                if ( ! ABPTT_Function::on_off( 'feature' ) ) {
                    return;
                } ?>
                <div class="tab_item feature_configuration" data-tabs="#abptt_feature">
                    <div class="group_setting feature_box">
                        <?php
                            // 'post_feature' keeps its original meaning so tours saved before this tab
                            // existed still render their features as included.
                            $this->feature_section(
                                'post_feature',
                                'display_feature_included',
                                __( 'Included Feature', 'abp-tour-travel' ),
                                (string) ( $post_infos['post_feature'] ?? '' ),
                                (string) ( $post_infos['display_feature_included'] ?? 'on' )
                            );
                            $this->feature_section(
                                'post_feature_excluded',
                                'display_feature_excluded',
                                __( 'Excluded Feature', 'abp-tour-travel' ),
                                (string) ( $post_infos['post_feature_excluded'] ?? '' ),
                                (string) ( $post_infos['display_feature_excluded'] ?? 'on' )
                            );
                        ?>
                    </div>
                </div>
                <?php
            }

            public function feature_section( $name, $display_name, $title, $value, $display ): void {
                $display       = $display === 'off' ? 'off' : 'on';
                $switch_active = $display === 'on' ? 'abp_active' : '';
                ?>
                <div class="setting_item full_width <?php echo esc_attr( $name ); ?>">
                    <div class="_f_wrap_fj_between_fa_center">
                        <label>
                            <?php ABPTT_Layout::switch_checkbox( $display_name, $display ); ?>
                            <span><?php echo esc_html( $title ); ?></span>
                        </label>
                        <div data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $switch_active ); ?>">
                            <div class="_group_content">
                                <?php ABPTT_Layout::selection_area();
                                    ABPTT_Layout::button_global_popup( 'option_feature', __( 'Add New', 'abp-tour-travel' ) . ' ' . ABPTT_Function::feature_label() ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTT_Layout::info_text( $display_name ); ?>
                    <div data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $switch_active ); ?>">
                        <div class=" feature_selection_content">
                            <?php ABPTT_Layout::selected_area( $name, $value ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function add_option_feature(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                ob_start();
                $id        = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
                $options   = ABPTT_Function::get_option( 'abptt_feature' );
                $options   = is_array( $options ) ? $options : [];
                $feature   = $options[ $id ] ?? [];
                $label     = ABPTT_Function::feature_label();
                $btn_label = __( 'Save', 'abp-tour-travel' ) . ' ' . $label;
                $title     = __( 'Add new ', 'abp-tour-travel' ) . ' ' . $label;
                ?>
                <div class="abp_form">
                    <h5 class="abp"><span class="_mar_r_xs">🔗</span><?php echo esc_html( $title ); ?></h5>
                    <?php ABPTT_Layout::info_text( 'feature_icon' );
                        ABPTT_Layout::info_text( 'feature_name' );
                        ABPTT_Layout::info_text( 'feature_value' ); ?>
                    <div class="_divider_xxs"></div>
                    <div class="configuration_content">
                        <table class="abp ">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Icon', 'abp-tour-travel' ); ?></th>
                                <th><?php esc_html_e( 'Label', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                <th><?php esc_html_e( 'Value', 'abp-tour-travel' ); ?></th>
                                <th class="_w_10"><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php self::form_feature( $feature, $id ); ?>
                            </tbody>
                        </table>
                        <div class="_divider_xs"></div>
                        <div class="_fj_between">
                            <?php ABPTT_Layout::button_add( __( 'Add New Feature Item', 'abp-tour-travel' ) ); ?>
                            <?php ABPTT_Layout::button_global_save( 'option_feature', $btn_label ); ?>
                        </div>
                        <div class="abp_hidden">
                            <table class="abp">
                                <tbody class="hidden_content">
                                <?php self::form_feature(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success( [ 'html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __( 'Form Loaded Successfully .....! ', 'abp-tour-travel' ) ] );
            }

            public function save_option_feature(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_int       = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
                $post_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
                $old_features   = ABPTT_Function::get_option( 'abptt_feature' );
                $old_features   = is_array( $old_features ) ? $old_features : [];
                $feature_ids    = $post_array( 'feature_id' );
                $feature_names  = $post_array( 'feature_name' );
                $feature_values = $post_array( 'feature_value' );
                $feature_icon   = $post_array( 'feature_icon' );
                $post_id        = $post_int( 'post_id' );
                if ( ! empty( $feature_names ) ) {
                    foreach ( $feature_names as $key => $feature_name ) {
                        $feature_val = $feature_values[ $key ] ?? '';
                        if ( $feature_name !== '' ) {
                            $old_id = isset( $feature_ids[ $key ] ) ? (int) $feature_ids[ $key ] : '';
                            if ( ! empty( $old_id ) && isset( $old_features[ $old_id ] ) ) {
                                $id = $old_id;
                            } else {
                                $id = 1;
                                while ( isset( $old_features[ $id ] ) ) {
                                    $id ++;
                                }
                            }
                            $old_features[ $id ] = [
                                'label' => $feature_name,
                                'value' => $feature_val,
                                'icon'  => $feature_icon[ $key ] ?? '',
                            ];
                        }
                    }
                }
                update_option( 'abptt_feature', $old_features );
                $html = '';
                if ( empty( $post_id ) || $post_id <= 0 ) {
                    ob_start();
                    $this->feature_list();
                    $html = ob_get_clean();
                }
                wp_send_json_success( [
                    'html' => $html,
                    'msg'  => __( 'Feature Saved Successfully..........!!', 'abp-tour-travel' ),
                    'js'   => ABPTT_Function::option_js( $post_id, 'abptt_feature' ),
                ] );
            }

            public function delete_option_feature(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $id      = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
                $options = ABPTT_Function::get_option( 'abptt_feature' );
                $options = is_array( $options ) ? $options : [];
                if ( ! empty( $id ) && isset( $options[ $id ] ) ) {
                    unset( $options[ $id ] );
                    update_option( 'abptt_feature', $options );
                }
                ob_start();
                $this->feature_list();
                $html = ob_get_clean();
                wp_send_json_success( [
                    'html' => $html,
                    'msg'  => ABPTT_Function::feature_label() . ' ' . __( 'Deleted Successfully!', 'abp-tour-travel' ),
                    'type' => 'success'
                ] );
            }

            public function feature_list(): void {
                $features = ABPTT_Function::get_option( 'abptt_feature' );
                if ( sizeof( $features ) > 0 ) { ?>
                    <div class="_group_list">
                        <?php foreach ( $features as $key => $feature ) {
                            $label = $feature['label'] ?? '';
                            $value = $feature['value'] ?? '';
                            if ( ! empty( $label ) ) { ?>
                                <div class="_list_item">
                                    <h6 class="abp_gap_xs">
                                        <?php ABPTT_Layout::image_icon( $feature['icon'] ?? '' );
                                            echo esc_html( $label . ' ' . ( ! empty( $value ) ? '-' . $value : '' ) ); ?>
                                    </h6>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_xxs" onclick="abptt_popup_open_global('option_feature','<?php echo esc_attr( $key ); ?>')" title="<?php echo esc_attr__( 'Edit : ', 'abp-tour-travel' ) . ' ' . esc_attr( $label ); ?>"><?php ABPTT_Static::icon_svg( 'edit' ); ?></button>
                                        <button type="button" class="_btn_light_danger_xxs" onclick="abptt_delete_global('option_feature','<?php echo esc_attr( $key ); ?>')" title="<?php echo esc_attr__( 'Trash : ', 'abp-tour-travel' ) . ' ' . esc_attr( $label ); ?>"><?php ABPTT_Static::icon_svg( 'close_1' ); ?></button>
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                    </div>
                <?php } else {
                    ABPTT_Layout::layout_warning_info( 'no_feature' );
                }
            }

            public static function form_feature( $feature = [], $id = '' ): void {
                $label = $feature['label'] ?? '';
                $value = $feature['value'] ?? '';
                $icon  = $feature['icon'] ?? '';
                ?>
                <tr class="delete_area">
                    <th><?php do_action( 'abptt_add_icon', 'feature_icon[]', $icon ); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="feature_id[]" value="<?php echo esc_attr( $id ); ?>"/>
                            <input type="text" class="_form_control validation_name" name="feature_name[]" placeholder="<?php esc_attr_e( 'EX: Feature Title', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $label ); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="feature_value[]" placeholder="<?php esc_attr_e( 'EX: Feature Value', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                        </label>
                    </th>
                    <td><?php ABPTT_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
        }
        new ABPTT_Feature();
    }