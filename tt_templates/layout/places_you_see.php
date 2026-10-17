<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    add_action('abptt_places_you_see_template', function ($post_infos = [], $type = '') {
        if (!ABPTT_Function::on_off('places_you_see')) {
            return;
        }
        if (($post_infos['display_places_you_see'] ?? 'on') !== 'on') {
            return;
        }
        $places = ABPTT_Function::get_places_you_see( absint( $post_infos['post_id'] ?? 0 ) );
        $places = array_values(
            array_filter(
                $places,
                static function ($place) {
                    return is_array($place) && (absint($place['place_id'] ?? 0) > 0 || trim((string) ($place['name'] ?? '')) !== '');
                }
            )
        );
        if (empty($places)) {
            return;
        }
        ?>
        <div class="_section_card_xs_w_full places_you_see_area abptt_places_you_see_panel">
            <h4 class="abp">
                <span class="_mar_r_xxs">📍</span>
                <?php esc_html_e("Places You'll See", 'abp-tour-travel'); ?>
            </h4>
            <div class="_divider_xs"></div>
            <div class="places_you_see_list">
                <?php
                    foreach ($places as $index => $place) {
                        $place_id   = absint($place['place_id'] ?? 0);
                        $place_name = trim((string) ($place['name'] ?? ''));
                        if ($place_name === '') {
                            continue;
                        }
                        $place_des = trim((string) ($place['des'] ?? ''));
                        ?>
                        <div class="places_you_see_item">
                            <span class="places_you_see_number"><?php echo esc_html((string) ($index + 1)); ?></span>
                            <div class="places_you_see_content">
                                <h6 class="abp places_you_see_name"><?php echo esc_html($place_name); ?></h6>
                                <?php if ($place_des !== '') { ?>
                                    <div class="places_you_see_des"><?php echo wp_kses_post($place_des); ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                    }
                ?>
            </div>
        </div>
        <?php
    }, 10, 2);