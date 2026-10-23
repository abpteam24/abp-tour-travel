<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Why_Book' ) ) {
        class ABPTT_Why_Book {
            public function __construct() {
                add_action( 'abptt_global_why_book', array( $this, 'global_why_book' ) );
                add_action( 'wp_ajax_abptt_save_why_book_config', array( $this, 'save_why_book_config' ) );
            }

            public function global_why_book(): void {
                if ( ! ABPTT_Function::on_off( 'why_book_us' ) ) {
                    return;
                }
                ?>
                <div class="why_book_configuration">
                    <h4 class="abp_color_theme_gap_xs">👍<?php esc_html_e( 'Global Why Book With Us Configuration', 'abp-tour-travel' ); ?></h4>
                    <?php ABPTT_Layout::info_text( 'abptt_why_book' ); ?>
                    <div class="_divider_xxs"></div>
                    <div class="why_book_config">
                        <?php $this->why_book_config(); ?>
                    </div>
                </div>
                <?php
            }

            public function why_book_config(): void {
                $items = ABPTT_Function::why_book_items_raw();
                ?>
                <div class="abp_form">
                    <div class="configuration_content why_book_content">
                        <div class="insertable_area sortable_area">
                            <?php
                                if ( ! empty( $items ) && is_array( $items ) && sizeof( $items ) > 0 ) {
                                    foreach ( $items as $key => $item ) {
                                        $this->why_book_item( $item, $key );
                                    }
                                }
                            ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <div class="_fj_between">
                            <?php ABPTT_Layout::button_add( __( 'Add New Reason', 'abp-tour-travel' ) );
                                ABPTT_Layout::button_global_save( 'why_book_config', __( 'Save Why Book With Us Configuration', 'abp-tour-travel' ) ); ?>
                        </div>
                        <div class="abp_hidden">
                            <div class="hidden_content">
                                <?php $this->why_book_item(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function why_book_item( $item = [], $key = '' ): void {
                $title       = $item['title'] ?? '';
                $title_label = $title !== '' ? $title : __( 'NEW', 'abp-tour-travel' );
                $icon        = $item['icon'] ?? '';
                $description = $item['description'] ?? '';
                ?>
                <div class="delete_area why_book_item <?php echo esc_attr( empty( $item ) ? 'active' : '' ); ?>">
                    <div class="why_book_item_head">
                        <h6 class="abp edit_hook" data-paste="#why_book_title"><?php echo esc_html( $title_label ); ?></h6>
                        <?php ABPTT_Layout::button_delete_sort_edit(); ?>
                    </div>
                    <div class="edit_area why_book_item_body">
                        <div class="group_setting">
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <span class="abp_label"><?php esc_html_e( 'Icon', 'abp-tour-travel' ); ?></span>
                                    <?php do_action( 'abptt_add_icon', 'why_book_icon[]', $icon ); ?>
                                </div>
                            </div>
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="abp_label"><?php esc_html_e( 'Title', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                                    <input type="hidden" name="why_book_id[]" value="<?php echo esc_attr( $key ); ?>"/>
                                    <input type="text" class="_form_control" name="why_book_title[]" data-pass="#why_book_title" placeholder="<?php esc_attr_e( 'EX: Best Price', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
                                </label>
                            </div>
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="abp_label"><?php esc_html_e( 'Description', 'abp-tour-travel' ); ?></span>
                                    <textarea class="_form_control" name="why_book_description[]" rows="2" placeholder="<?php esc_attr_e( 'EX: No hidden charges and the lowest fares every single time.', 'abp-tour-travel' ); ?>"><?php echo esc_html( $description ); ?></textarea>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function save_why_book_config(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_array  = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
                $post_textea = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_textarea_field', wp_unslash( $_POST[ $key ] ) ) : [];
                $ids         = $post_array( 'why_book_id' );
                $icons       = $post_array( 'why_book_icon' );
                $titles      = $post_array( 'why_book_title' );
                $descs       = $post_textea( 'why_book_description' );
                $why_book    = [];
                if ( ! empty( $titles ) ) {
                    foreach ( $titles as $key => $item_title ) {
                        $item_title = trim( (string) $item_title );
                        $item_icon  = (string) ( $icons[ $key ] ?? '' );
                        $item_desc  = (string) ( $descs[ $key ] ?? '' );
                        if ( '' === $item_title ) {
                            continue;
                        }
                        if ( 'NEW' === $item_title && '' === trim( $item_icon ) && '' === trim( $item_desc ) ) {
                            continue;
                        }
                        $id              = isset( $ids[ $key ] ) && $ids[ $key ] !== '' ? $ids[ $key ] : uniqid();
                        $why_book[ $id ] = [
                            'icon'        => $item_icon,
                            'title'       => $item_title,
                            'description' => $item_desc,
                        ];
                    }
                }
                update_option( 'abptt_why_book', $why_book );
                ob_start();
                $this->why_book_config();
                $html = ob_get_clean();
                wp_send_json_success( [
                    'html' => $html,
                    'msg'  => __( 'Why Book With Us Configuration Saved Successfully..... !! ', 'abp-tour-travel' ),
                    'type' => 'success'
                ] );
            }
        }
        new ABPTT_Why_Book();
    }
