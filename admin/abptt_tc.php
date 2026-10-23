<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_TC' ) ) {
        class ABPTT_TC {
            public function __construct() {
                add_action( 'abptt_global_tc', array( $this, 'global_tc' ) );
                add_action( 'abptt_post_content', array( $this, 'post_tc' ) );
                add_action( 'wp_ajax_abptt_save_tc_config', array( $this, 'save_tc_config' ) );
                add_action( 'wp_ajax_abptt_import_tc_content', array( $this, 'import_tc_content' ) );
            }

            public function global_tc(): void {
                if ( ! ABPTT_Function::on_off( 'tc' ) ) {
                    return;
                }
                $tcs = ABPTT_Function::get_option( 'abptt_tc', '' );
                ?>
                <div class="abp_form">
                    <div class="_fj_between">
                        <h4 class="abp_color_theme_gap_xs">🤝<?php esc_html_e( 'Global Term & Conditions Configuration', 'abp-tour-travel' ); ?></h4>
                        <?php ABPTT_Layout::button_global_save( 'tc_config', __( 'Save Term & Conditions Configuration', 'abp-tour-travel' ) ); ?>
                    </div>
                    <?php ABPTT_Layout::info_text( 'abptt_tc' ); ?>
                    <div class="_divider_xxs"></div>
                    <div class="tc_config">
                        <?php $this->tc( $tcs ); ?>
                    </div>
                </div>
                <?php
            }

            public function tc( $tcs = '' ): void {
                $description = $tcs ? wp_kses_post( $tcs ) : '';
                $editor_id   = 'abptt_editor_tc_' . wp_rand( 0, 999 );
                ?>
                <div class="edit_area">
                    <?php
                        wp_editor(
                            $description,
                            $editor_id,
                            array(
                                'textarea_name' => 'tc_content',
                                'textarea_rows' => 12,
                                'media_buttons' => true,
                                'teeny'         => false,
                                'quicktags'     => true
                            )
                        );
                    ?>
                </div>
                <?php
            }

            public function post_tc( $post_infos = [] ): void {
                if ( ABPTT_Function::on_off( 'tc' ) ) {
                    $post_id          = absint( $post_infos['post_id'] ?? 0 );
                    $abptt_tc         = get_post_meta( $post_id, 'abptt_tc', true );
                    $display          = $post_infos['display_tc'] ?? 'on';
                    $active_global_tc = $post_infos['active_global_tc'] ?? 'on';
                    ?>
                    <div class="tab_item tc_configuration" data-tabs="#abptt_tc">
                        <h6 class="abp_color_theme_gap_xs">🤝<?php esc_html_e( 'Term & Conditions', 'abp-tour-travel' ); ?></h6>
                        <div class="_divider_xxs"></div>
                        <div class="group_setting">
                            <div class="setting_item">
                                <label>
                                    <?php ABPTT_Layout::switch_checkbox( 'display_tc', $display ); ?>
                                    <span><?php esc_html_e( 'Active Term & Conditions ?', 'abp-tour-travel' ); ?></span>
                                </label>
                                <div class="_divider_xs"></div>
                                <?php ABPTT_Layout::info_text( 'display_tc' ); ?>
                            </div>
                            <div data-collapse="#display_tc" class="setting_item <?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">
                                <div class="_fj_between">
                                    <label>
                                        <?php ABPTT_Layout::switch_checkbox( 'active_global_tc', $active_global_tc ); ?>
                                        <span><?php esc_html_e( 'Use Global Term & Conditions ?', 'abp-tour-travel' ); ?></span>
                                    </label>
                                    <div data-collapse="#active_global_tc" class=" <?php echo esc_attr( $active_global_tc == 'on' ? '' : 'abp_active' ); ?>">
                                        <button type="button" class="_btn_theme_xs" onclick="abptt_import_global('tc_content')"><span class="fas fa-file-upload"></span><?php esc_html_e( 'Import Global Term & Conditions', 'abp-tour-travel' ); ?></button>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTT_Layout::info_text( 'active_global_tc' ); ?>
                            </div>
                            <div data-collapse="#display_tc" class="<?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">
                                <div class="setting_item full_width <?php echo esc_attr( $active_global_tc == 'on' ? '' : 'abp_active' ); ?>" data-collapse="#active_global_tc">
                                    <div class="tc_content">
                                        <?php $this->tc( $abptt_tc ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }

            public function save_tc_config(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_html = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : $default;
                update_option( 'abptt_tc', $post_html( 'tc_content' ) );
                ob_start();
                $tcs = ABPTT_Function::get_option( 'abptt_tc', '' );
                $this->tc( $tcs );
                $html = ob_get_clean();
                wp_send_json_success( [
                    'html' => $html,
                    'msg'  => __( 'Term & Conditions  Saved Successfully..... !!', 'abp-tour-travel' ),
                    'type' => 'success'
                ] );
            }

            public function import_tc_content(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $tcs = ABPTT_Function::get_option( 'abptt_tc', '' );
                ob_start();
                $this->tc( $tcs );
                $html_content = ob_get_clean();
                wp_send_json_success( [ 'html' => $html_content, 'msg' => __( 'Global Term & Conditions  Imported Successfully ..... !! ', 'abp-tour-travel' ), 'type' => 'success' ] );
            }
        }
        new ABPTT_TC();
    }
