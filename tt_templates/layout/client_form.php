<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptt_client_form_template', function ($post_infos) {
        $forms = ABPTT_Function::client_data($post_infos);
        if (!empty($forms) && is_array($forms)) { ?>
            <div class="client_info_area">
                <div class="item_box_1 attendee_item">
                    <h5 class=" abp_title">
                        <?php esc_html_e('Client Info : ', 'abp-tour-travel'); ?>&nbsp;<span class="_color_theme attendee_seat_name"></span>
                    </h5>
                    <?php
                        foreach ($forms as $id => $form) {
                            ABPTT_Layout::create_client_form($form, $id);
                        }
                    ?>
                </div>
            </div>
            <?php
        }
    }, 10, 2);