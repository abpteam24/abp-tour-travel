<?php
    if ( ! defined( 'ABSPATH' ) ) {
        die;
    } // Cannot access pages directly.
    if ( ! class_exists( 'ABPTT_Ticket' ) ) {
        class ABPTT_Ticket {
            public function __construct() {
                add_action( 'abptt_post_content', [ $this, 'ticket_configuration' ] );
                add_action( 'wp_ajax_abptt_type_switch', [ $this, 'type_switch' ] );
            }

            public function ticket_configuration( $post_infos = [] ): void {
                $seat_type           = $post_infos['seat_type'] ?? 'ticket';
                $seat_type           = ABPTT_Function::on_off( 'sp' ) ? $seat_type : 'ticket';
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTT_Function::on_off( 'ticket_type' ) ? $display_ticket_type : 'off';
                $ticket_info_key     = ABPTT_Function::on_off( 'sp' ) ? 'seat_type' : 'ticket_type';
                $multi_info_key      = ABPTT_Function::on_off( 'ticket_type' ) ? 'display_ticket_type' : 'single_ticket_type';
                ?>
                <div class="tab_item abptt_ticket" data-tabs="#abptt_ticket">
                    <div class="group_setting">
                        <div class="setting_item">
                            <label class="_fj_between">
                                <span class="abp_label"><?php esc_html_e( 'Ticket Type', 'abp-tour-travel' ); ?></span>
                                <?php if ( ABPTT_Function::on_off( 'sp' ) ) { ?>
                                    <select class="_form_control " name="seat_type" required>
                                        <option disabled selected> <?php esc_html_e( 'Please Select', 'abp-tour-travel' ); ?></option>
                                        <option value="sp" <?php echo esc_attr( $seat_type == 'sp' ? 'selected' : '' ); ?>><?php echo esc_html( ABPTT_Layout::ticket_type( 'sp' ) ); ?></option>
                                        <option value="ticket" <?php echo esc_attr( $seat_type == 'ticket' ? 'selected' : '' ); ?>><?php echo esc_html( ABPTT_Layout::ticket_type( 'ticket' ) ); ?></option>
                                    </select>
                                <?php } else { ?>
                                    <input type="hidden" name="seat_type" value="<?php echo esc_attr( $seat_type ); ?>">
                                    <span class="abp_label"><?php esc_html_e( 'Ticket', 'abp-tour-travel' ); ?></span>
                                <?php } ?>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTT_Layout::info_text( $ticket_info_key ); ?>
                        </div>
                        <div class="setting_item">
                            <?php if ( ABPTT_Function::on_off( 'ticket_type' ) ) { ?>
                                <label>
                                    <?php ABPTT_Layout::switch_checkbox( 'display_ticket_type', $display_ticket_type ); ?>
                                    <?php esc_html_e( 'Multiple Ticket Type?', 'abp-tour-travel' ); ?>
                                </label>
                            <?php } else { ?>
                                <label class="_fj_between">
                                    <input type="hidden" name="display_ticket_type" value="<?php echo esc_attr( $display_ticket_type ); ?>">
                                    <span><?php esc_html_e( 'Ticket Type', 'abp-tour-travel' ); ?></span>
                                    <span><?php esc_html_e( 'Single', 'abp-tour-travel' ); ?></span>
                                </label>
                            <?php } ?>
                            <div class="_divider_xxs"></div>
                            <?php ABPTT_Layout::info_text( $multi_info_key ); ?>
                        </div>
                        <?php if ( ABPTT_Function::on_off( 'min_max' ) ) { ?>
                            <div class="setting_item">
                                <label class="_fj_between _f_wrap">
                                    <span class="abp_label"><?php esc_html_e( 'Min Qty/Order', 'abp-tour-travel' ); ?></span>
                                    <input class="_form_control validation_number" name="min_qty" value="<?php echo esc_attr( $post_infos['min_qty'] ?? 0 ); ?>" placeholder="<?php esc_attr_e( 'Ex: 1', 'abp-tour-travel' ); ?>"/>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'min_qty' ); ?>
                            </div>
                            <div class="setting_item">
                                <label class="_fj_between _f_wrap">
                                    <span class="abp_label"><?php esc_html_e( 'Max Qty/Order', 'abp-tour-travel' ); ?></span>
                                    <input class="_form_control validation_number" name="max_qty" value="<?php echo esc_attr( $post_infos['max_qty'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Ex: 5', 'abp-tour-travel' ); ?>"/>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTT_Layout::info_text( 'max_qty' ); ?>
                            </div>
                        <?php } ?>
                        <div class="ticket_configuration configuration_content setting_item full_width">
                            <?php
                                if ( $seat_type == 'sp' ) {
                                    $this->seat_type( $post_infos );
                                } else {
                                    $this->ticket_type( $post_infos );
                                } ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function type_switch(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_val                          = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
                $post_int                          = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
                $post_id                           = $post_int( 'post_id' );
                $seat_type                         = $post_val( 'type' );
                $post_infos                        = ABPTT_Function::get_all_meta( $post_id );
                $post_infos['display_ticket_type'] = $post_val( 'display_ticket_type' );
                ob_start();
                if ( $seat_type == 'sp' ) {
                    $this->seat_type( $post_infos );
                } else {
                    $this->ticket_type( $post_infos );
                }
                $html = ob_get_clean();
                wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Ticket Type Switched Successfully...!', 'abp-tour-travel' ), 'type' => 'success' ] );
            }

            public function seat_type( $post_infos ): void {
                $sp_infos            = $post_infos['sp_infos'] ?? [];
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTT_Function::on_off( 'ticket_type' ) ? $display_ticket_type : 'off';
                $sp_data             = ABPTT_Query::get_sp();
                $post_id             = $post_infos['post_id'] ?? '';
                $ticket_infos        = $post_infos['ticket_infos'] ?? [];
                ?>
                <div class="_fj_between">
                    <h5 class="abp"><?php esc_html_e( 'Seat Plan Configuration', 'abp-tour-travel' ); ?></h5>
                    <button type="button" class="_btn_light_active_xs">
                        <?php esc_html_e( 'Total Seat :', 'abp-tour-travel' ); ?>
                        <span class="abp_color_theme total_ticket"><?php echo esc_html( ABPTT_Function::get_total_qty( $post_id, $post_infos ) ); ?></span>
                    </button>
                    <div class="_group_content">
                        <?php ABPTT_Layout::button_add( __( 'Add New Seat Label', 'abp-tour-travel' ) ); ?>
                    </div>
                </div>
                <div class="_divider_xs"></div>
                <div class="_ov_auto">
                </div>
                <div class="_ov_auto _gap">
                    <table class="abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Seat Plan Name', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Frontend Name', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                            <?php if ( ABPTT_Function::on_off( 'ticket_type' ) && $display_ticket_type == 'on' ) { ?>
                                <th><?php esc_html_e( 'Type Wise Quantity', 'abp-tour-travel' ); ?></th>
                            <?php } ?>
                            <th><?php esc_html_e( 'Total Quantity', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area sp_selection_area">
                        <?php
                            if ( ! empty( $sp_infos ) ) {
                                foreach ( $sp_infos as $ticket_info ) {
                                    $this->sp_item( $sp_data, $display_ticket_type, $ticket_info );
                                }
                            } else {
                                $this->sp_item( $sp_data, $display_ticket_type );
                            }
                        ?>
                        </tbody>
                    </table>
                    <div class="_max_300">
                        <table class="abp">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Name', 'abp-tour-travel' ); ?></th>
                                <th><?php esc_html_e( 'Price', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                            </tr>
                            </thead>
                            <tbody class="ticket_selection_area">
                            <?php
                                if ( ! empty( $ticket_infos ) && $display_ticket_type == 'on' ) {
                                    foreach ( $ticket_infos as $key => $ticket_info ) {
                                        $this->sp_ticket_item( $ticket_info, $key );
                                    }
                                } else {
                                    $this->sp_ticket_item( [], 'ticket' );
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="abp_hidden">
                    <table class="abp">
                        <tbody class="hidden_content">
                        <?php $this->sp_item( $sp_data, $display_ticket_type ); ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }

            public function ticket_type( $post_infos ): void {
                $ticket_infos        = $post_infos['ticket_infos'] ?? [];
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTT_Function::on_off( 'ticket_type' ) ? $display_ticket_type : 'off';
                ?>
                <div class="_fj_between">
                    <h5 class="abp"><?php esc_html_e( 'Ticket Configuration', 'abp-tour-travel' ); ?></h5>
                    <div class="_group_content">
                        <?php if ( ABPTT_Function::on_off( 'ticket_type' ) && $display_ticket_type == 'on' ) {
                            ABPTT_Layout::button_add( __( 'Add New item', 'abp-tour-travel' ) );
                        }
                            ABPTT_Layout::button_global_popup( 'ticket_type', __( 'Add New Ticket Type', 'abp-tour-travel' ) ); ?>
                    </div>
                </div>
                <div class="_divider_xxs"></div>
                <div class="_ov_auto">
                    <table class="abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e( 'Quantity', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e( 'Price', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e( 'Reserve Quantity', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Min qty/Order', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Max qty/Order', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'abp-tour-travel' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area">
                        <?php
                            if ( ! empty( $ticket_infos ) ) {
                                if ( $display_ticket_type === 'off' ) {
                                    $key = array_key_first( $ticket_infos );
                                    $this->ticket_item( $ticket_infos[ $key ], $key );
                                } else {
                                    foreach ( $ticket_infos as $key => $ticket_info ) {
                                        $this->ticket_item( $ticket_info, $key );
                                    }
                                }
                            } else {
                                $this->ticket_item();
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php if ( $display_ticket_type === 'on' ) { ?>
                    <div class="abp_hidden">
                        <table class="abp">
                            <tbody class="hidden_content">
                            <?php $this->ticket_item(); ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                }
            }

            public function ticket_item( $field = array(), $id = '' ): void {
                ?>
                <tr class="delete_area">
                    <td>
                        <label>
                            <select name="ticket_name[]" class='_form_control' data-required>
                                <option value="" selected><?php esc_html_e( 'Select Ticket Name.', 'abp-tour-travel' ); ?></option>
                                <?php if ( ! empty( ABPTT_Ticket ) ) {
                                    foreach ( ABPTT_Ticket as $key => $ticket ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key == $id ? 'selected' : '' ); ?>><?php echo esc_html( $ticket['label'] ?? '' ); ?></option>
                                        <?php
                                    }
                                } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input data-required type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_qty[]" placeholder="<?php esc_attr_e( 'EX: 15', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['qty'] ?? 10 ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input data-required type="number" class="_form_control validation_price" name="ticket_price[]" placeholder="<?php esc_attr_e( 'EX: 15', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['price'] ?? 0 ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="reserve_qty[]" placeholder="<?php esc_attr_e( 'EX: 15', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['reserve'] ?? 0 ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_min_qty[]" placeholder="<?php esc_attr_e( 'EX: 1', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['min_qty'] ?? 1 ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_max_qty[]" placeholder="<?php esc_attr_e( 'EX: 15', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['max_qty'] ?? '' ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <textarea class="_form_control" name="ticket_description[]" placeholder="<?php esc_attr_e( 'EX: Description', 'abp-tour-travel' ); ?>"><?php echo esc_html( $field['description'] ?? '' ); ?></textarea>
                        </label>
                    </td>
                    <td><?php ABPTT_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }

            public function sp_ticket_item( $field = array(), $id = '' ): void {
                ?>
                <tr>
                    <th><?php echo esc_html( ABPTT_Function::ticket_name( $id ) ); ?>
                        <input type="hidden" name="ticket_name[]" value="<?php echo esc_attr( $id ); ?>"/>
                    </th>
                    <td>
                        <label>
                            <input data-required type="number" class="_form_control validation_price" name="ticket_price[]" placeholder="<?php esc_attr_e( 'EX: 15', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['price'] ?? 0 ); ?>"/>
                        </label>
                    </td>
                </tr>
                <?php
            }

            public function sp_item( $sp_data, $display_ticket_type, $field = array() ): void {
                $field      = $field ?: [];
                $id         = $field['id'] ?? '';
                $total_seat = 0;
                $meta_info  = [];
                ?>
                <tr class="delete_area">
                    <td>
                        <label>
                            <select name="sp_id[]" class='_form_control' data-required>
                                <option value="" selected><?php esc_html_e( 'Select Seat plan', 'abp-tour-travel' ); ?></option>
                                <?php if ( ! empty( $sp_data ) ) {
                                    foreach ( $sp_data as $sp_info ) {
                                        $key = $sp_info['id'] ?? '';
                                        if ( ! empty( $key ) ) {
                                            if ( $key == $id ) {
                                                $meta_info  = json_decode( $sp_info['seat_info'] ?? '', true ) ?: [];
                                                $total_seat = $sp_info['total_seats'] ?? 0;
                                            }
                                            ?>
                                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key == $id ? 'selected' : '' ); ?>><?php echo esc_html( $sp_info['name'] ?? '' ); ?></option>
                                            <?php
                                        }
                                    }
                                } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="text" class="_form_control validation_name" name="sp_name[]" placeholder="<?php esc_attr_e( 'EX: Lower Deck', 'abp-tour-travel' ); ?>" value="<?php echo esc_attr( $field['name'] ?? '' ); ?>"/>
                        </label>
                    </td>
                    <?php if ( ABPTT_Function::on_off( 'ticket_type' ) && $display_ticket_type == 'on' ) { ?>
                        <th class="ticket_type_details">
                            <?php ABPTT_Seat_Plan::sp_seat_list( ABPTT_Ticket, $meta_info ); ?>
                        </th>
                    <?php } ?>
                    <th class="row_total"><?php echo esc_html( $total_seat ); ?> </th>
                    <td>
                        <div class="_all_center">
                            <div class="_group_content">
                                <button type="button" class="_btn_light_theme_xxs sp_id_change" onclick="abptt_popup_open_global('view_sp','<?php echo esc_attr( $id ); ?>')" title="<?php esc_attr_e( 'View : Seat Plan', 'abp-tour-travel' ); ?>"><?php ABPTT_Static::icon_svg( 'view_1' ); ?></button>
                                <?php
                                    ABPTT_Layout::button_sort();
                                    ABPTT_Layout::button_delete();
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
        new ABPTT_Ticket();
    }