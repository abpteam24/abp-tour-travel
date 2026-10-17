<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptt_ticket_type_template', function ($post_infos, $form_data = []) {
        if (!empty($post_infos)) {
            $start_date = $form_data['tour_date'] ?? ( $form_data['start_date'] ?? '' );
            $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
            $display_ticket_type = ABPTT_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
            $ticket_infos = [];
            // echo '<pre>';        print_r($form_data);        echo '</pre>';
            $_ticket_infos = $post_infos['ticket_infos'] ?? [];
            if (is_array($_ticket_infos) && sizeof($_ticket_infos) > 0) {
                if ($display_ticket_type === 'off') {
                    $key = array_key_first($_ticket_infos);
                    $ticket_infos[$key] = $_ticket_infos[$key];
                } else {
                    $ticket_infos = $_ticket_infos;
                }
	            $sold_infos = ABPTT_Query::get_sold_ticket($form_data);
                foreach ($ticket_infos as $key => $ticket_info) {
                    $price = ABPTT_Function::get_price($post_infos, $key, $start_date);
                    $sold = $sold_infos[$key] ?? 0;
                    $qty = $ticket_info['qty'] ?? 0;
                    $reserve = $ticket_info['reserve'] ?? 0;
                    $available = $qty - $sold - $reserve;
                    $ticket_info['available'] = $available;
                    ?>
                    <div class="ticket_item _section_card_xs_w_full">
                        <div class="_fj_between">
                            <h5 class="abp_gap_xxs"><?php ABPTT_Layout::image_icon(ABPTT_Function::ticket_icon($key)); ?><?php echo esc_html(ABPTT_Function::ticket_name($key)); ?></h5>
                            <?php if (!empty($price)) { ?>
                                <div class="abp_tag price_value">
                                    <?php echo ($price > 0) ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-tour-travel'); ?>
                                    <sub class="_color_green_pale _fs_small"><?php esc_html_e('/Ticket', 'abp-tour-travel') ?></sub>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if ( ABPTT_Function::on_off( 'display_capacity' ) ) { ?>
                            <h6 class="abp"><?php echo esc_html__('Available : ', 'abp-tour-travel') . ' ' . esc_html($available . '/' . $qty); ?></h6>
                        <?php } ?>
                        <p class="abp"><?php echo esc_html($ticket_info['description'] ?? ''); ?></p>
                        <?php ABPTT_Layout::item_select($ticket_info, $key, $price);?>
                    </div>
                <?php }
            } else {
                ABPTT_Layout::layout_warning_info('no_ticket_config');
            }
        }
    }, 10, 3);