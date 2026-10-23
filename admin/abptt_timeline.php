<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Timeline' ) ) {
        class ABPTT_Timeline {
            public function __construct() {
                add_action( 'abptt_post_content', array( $this, 'post_timeline' ) );
                add_filter( 'abptt_meta_info_update', array( $this, 'save_timeline' ), 10, 2 );
            }

            public function post_timeline( $post_infos = [] ): void {
                if ( ABPTT_Function::on_off( 'timeline' ) ) {
                    $post_id  = absint( $post_infos['post_id'] ?? 0 );
                    $display  = $post_infos['display_timeline'] ?? 'on';
                    $timeline = get_post_meta( $post_id, 'abptt_timeline', true );
                    $timeline = is_array( $timeline ) ? $timeline : [];
                    ?>
                    <div class="tab_item" data-tabs="#abptt_timeline">
                        <h5 class="abp_color_theme_gap_xxs">⏱️<?php esc_html_e( 'Timeline Configuration', 'abp-tour-travel' ); ?></h5>
                        <div class="_divider_xxs"></div>
                        <div class="setting_item">
                            <label>
                                <?php ABPTT_Layout::switch_checkbox( 'display_timeline', $display ); ?>
                                <span><?php esc_html_e( 'Active Timeline ?', 'abp-tour-travel' ); ?></span>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTT_Layout::info_text( 'display_timeline' ); ?>
                        </div>
                        <div data-collapse="#display_timeline" class="configuration_content <?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">
                            <?php ABPTT_Layout::info_text( 'timeline_item' ); ?>
                            <div class="_divider_xs"></div>
                            <div class="insertable_area sortable_area">
                                <?php
                                    if ( ! empty( $timeline ) && sizeof( $timeline ) > 0 ) {
                                        foreach ( $timeline as $item ) {
                                            $this->timeline_item( $item );
                                        }
                                    }
                                ?>
                            </div>
                            <div class="_divider_xxs"></div>
                            <?php ABPTT_Layout::button_add( __( 'Add New Timeline Item', 'abp-tour-travel' ) ); ?>
                            <div class="abp_hidden">
                                <div class="hidden_content">
                                    <?php $this->timeline_item(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }

            public function timeline_item( $item = [] ): void {
                $time        = $item['time'] ?? '';
                $title       = $item['title'] ?? '';
                $title_label = $title !== '' ? $title : __( 'NEW', 'abp-tour-travel' );
                $description = $item['des'] ?? '';
                $description = $description ? html_entity_decode( $description ) : '';
                $editor_id   = uniqid( 'abptt_timeline_editor_' );
                ?>
                <div class="delete_area timeline_item <?php echo esc_attr( empty( $item ) ? 'active' : '' ); ?>">
                    <div class="timeline_head">
                        <h6 class="abp edit_hook" data-paste="#timeline_title"><?php echo esc_html( $title_label ); ?></h6>
                        <?php ABPTT_Layout::button_delete_sort_edit(); ?>
                    </div>
                    <div class="edit_area timeline_body">

                        <label class="_f_equal_f_wrap">
                            <span class="abp_label"><?php esc_html_e( 'Time', 'abp-tour-travel' ); ?></span>
                            <input type="text" class="_form_control" name="timeline_time[]" placeholder="<?php esc_attr_e( 'EX: 10:00 AM', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $time ); ?>"/>
                        </label>
                        <div class="_divider_xs"></div>
                        <label class="_f_equal_f_wrap">
                            <span class="abp_label"><?php esc_html_e( 'Title', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                            <input type="text" class="_form_control" name="timeline_title[]" data-pass="#timeline_title" placeholder="<?php esc_attr_e( 'EX: Registration Opens', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
                        </label>
                        <div class="_fd_column_mar_t_xs">
                            <span class="abp_label"><?php esc_html_e( 'Description', 'abp-tour-travel' ); ?></span>
                            <?php
                                wp_editor(
                                    $description,
                                    $editor_id,
                                    array(
                                        'textarea_name' => 'timeline_description[]',
                                        'textarea_rows' => 5,
                                        'media_buttons' => true,
                                        'teeny'         => false,
                                        'quicktags'     => true
                                    )
                                );
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
            public function save_timeline( array $meta_info, $post_id ): array {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                if ( get_post_type( $post_id ) == ABPTT_Function::get_cpt() && isset( $_POST['timeline_title'] ) && is_array( $_POST['timeline_title'] ) ) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $display = isset( $_POST['display_timeline'] ) ? sanitize_text_field( wp_unslash( $_POST['display_timeline'] ) ) : 'on';
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $times = ( isset( $_POST['timeline_time'] ) && is_array( $_POST['timeline_time'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['timeline_time'] ) ) : [];
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $titles = array_map( 'sanitize_text_field', wp_unslash( $_POST['timeline_title'] ) );
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in the caller save_settings() via abptt_post_nonce.
                    $descriptions = ( isset( $_POST['timeline_description'] ) && is_array( $_POST['timeline_description'] ) ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['timeline_description'] ) ) : [];
                    $timeline     = [];
                    if ( ! empty( $titles ) ) {
                        foreach ( $titles as $key => $title ) {
                            $title = trim( (string) $title );
                            if ( '' === $title ) {
                                continue;
                            }
                            $item_time = (string) ( $times[ $key ] ?? '' );
                            $item_des  = (string) ( $descriptions[ $key ] ?? '' );
                            // Drop the legacy placeholder row that older versions submitted from the hidden "Add New" template.
                            if ( 'NEW' === $title && '' === trim( $item_time ) && '' === trim( wp_strip_all_tags( $item_des ) ) ) {
                                continue;
                            }
                            $timeline[ $key ] = [
                                'time'  => $item_time,
                                'title' => $title,
                                'des'   => $item_des,
                            ];
                        }
                    }
                    $meta_info['display_timeline'] = $display;
                    $meta_info['abptt_timeline']   = array_values( $timeline );
                }

                return $meta_info;
            }
        }
        new ABPTT_Timeline();
    }