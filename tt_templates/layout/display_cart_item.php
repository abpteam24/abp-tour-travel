<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptt_display_cart_item_template', function ($booking_infos = []) {
        $booking_info = $booking_infos['booking_infos'] ?? [];
        $post_id = $booking_infos['post_id'] ?? '';
        if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTT_Function::get_cpt()) {
            $return = '';
            foreach ($booking_info as $cart_item) {
                if (!empty($cart_item)) {
                    $ticket_infos = $cart_item['info'] ?? [];
                    $seat_type = $cart_item['seat_type'] ?? '';
                    if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
                        $tour_date = $cart_item['tour_date'] ?? '';
                        $session_time = $cart_item['session_time'] ?? '';
                        $additional_info = $cart_item['additional_info'] ?? [];
                        $attendee_infos = $cart_item['pass_info'] ?? [];
                        ?>
                        <div class="abptt_area">
                            <div class="_section_card_xs _fd_column_gap_xs">
                                <div class="_cart_details _w_full">
                                    <h6 class="abp _color_theme"><?php echo esc_html__('Booking Information ', 'abp-tour-travel') . ' ' . esc_html($return) . ' : '; ?></h6>
                                    <div class="_divider_xxs"></div>
                                    <ul class="abp cart_list">
                                        <li class="_gap_xxs">
                                            <span class="fas fa-calendar-check"></span>
                                            <span class="_fs_label"><?php esc_html_e('Tour Date : ', 'abp-tour-travel'); ?></span>&nbsp;<?php echo esc_html(ABPTT_Function::date_format($tour_date)); ?>
                                        </li>
                                        <li class="_gap_xxs">
                                            <span class="fas fa-calendar-check"></span>
                                            <span class="_fs_label"><?php esc_html_e('Session Time: ', 'abp-tour-travel'); ?></span>&nbsp;<?php echo esc_html(ABPTT_Function::date_format($tour_date . ' ' . $session_time)); ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="cart_ticket_info _w_full">
                                    <h6 class="abp _color_theme"><?php esc_html_e('Ticket Information : ', 'abp-tour-travel'); ?></h6>
                                    <div class="_divider_xxs"></div>
                                    <ul class="abp cart_list">
                                        <?php foreach ($ticket_infos as $ticket_info) {
                                            $price = $ticket_info['price'] ?? 0;
                                            $qty = $ticket_info['qty'] ?? 1;
                                            $price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-tour-travel');
                                            $price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-tour-travel');
                                            $name = $ticket_info['name'] ?? '';
                                            if ($seat_type == 'sp') {
                                                $name = $name . ' - ' . ABPTT_Function::sp_label($post_id, ($ticket_info['sp_id'] ?? $cart_item['sp_id'] ?? ''));
                                            } ?>
                                            <li class="_gap_xxs">
                                                <?php echo esc_html($name . __(' : ', 'abp-tour-travel')); ?>
                                                <?php echo wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($price); ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php if (ABPTT_Function::on_off('additional_info') && !empty($additional_info) && is_array($additional_info)) { ?>
                                    <div class="cart_additional _w_full">
                                        <h6 class="abp _color_theme"><?php esc_html_e('Additional Information : ', 'abp-tour-travel'); ?></h6>
                                        <div class="_divider_xxs"></div>
                                        <ul class="abp cart_list">
                                            <?php
                                                foreach ($additional_info as $additional) {
                                                    if (!is_array($additional) || empty($additional)) {
                                                        continue;
                                                    }
                                                    $icon_image = $additional['icon'] ?? '';
                                                    $name = $additional['name'] ?? '';
                                                    $qty = $additional['qty'] ?? 1;
                                                    $price = $additional['price'] ?? 0;
                                                    $price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-tour-travel');
                                                    $ex_price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-tour-travel');
                                                    ?>
                                                    <li class="_gap_xxs">
                                                        <?php ABPTT_Layout::image_icon($icon_image); ?>
                                                        <?php echo esc_html($name . __(' : ', 'abp-tour-travel')); ?>
                                                        <?php echo wp_kses_post($price_text) . ' X ' . esc_html($qty) . ' = ' . wp_kses_post($ex_price); ?>
                                                    </li>
                                                <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <?php if (ABPTT_Function::on_off('client_info') && !empty($attendee_infos) && is_array($attendee_infos)) { ?>
                                    <div class="cart_client_info _w_full">
                                        <h6 class="abp _color_theme"><?php esc_html_e('Client Information : ', 'abp-tour-travel'); ?></h6>
                                        <?php
                                            foreach ($attendee_infos as $attendee_info) {
                                                if (!empty($attendee_info)) { ?>
                                                    <div class="_divider_xxs"></div>
                                                    <ul class=" abp cart_list">
                                                        <?php foreach ($attendee_info as $attendee) {
                                                            $label = $attendee['label'] ?? '';
                                                            $value = $attendee['value'] ?? '';
                                                            if (!empty($label) && !empty($value)) { ?>
                                                                <li>
                                                                    <span class="abp_label"><?php echo esc_html($label . __(' : ', 'abp-tour-travel')); ?></span>
                                                                    <?php echo esc_html($value); ?>
                                                                </li>
                                                                <?php
                                                            }
                                                        } ?>
                                                    </ul>
                                                <?php }
                                            } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                        $return = __('( Return )', 'abp-tour-travel');
                    }
                }
            }
        }
    }, 10, 2);