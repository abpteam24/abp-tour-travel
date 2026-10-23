<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    if ( ! class_exists( 'ABPTT_Dates' ) ) {
        class ABPTT_Dates {
            public function __construct() {
                add_action( 'abptt_global_dates', array( $this, 'global_dates' ) );
                add_action( 'abptt_post_content', array( $this, 'post_content_dates' ) );
                add_action( 'wp_ajax_abptt_save_global_dates', array( $this, 'save_global_dates' ) );
            }
            public function global_dates(): void {
                ?>
                <div class="global_dates">
                    <?php $this->dates_config(); ?>
                </div>
                <?php
            }
            public function dates_config(): void {
                $date_infos   = ABPTT_Function::get_option( 'abptt_date_config' );
                $format_array = ABPTT_Layout::array_date_format();
                $date_format  = $date_infos['date_format'] ?? 'D d M , yy';
                $time_format  = $date_infos['time_format'] ?? ABPTT_Time_Format;
                ?>
                <div class="abp_form">
                    <div class="_fj_between">
                        <h4 class="abp_color_theme_gap_xs"><?php ABPTT_Static::svg( 'date_2' ); ?><?php esc_html_e( 'Global Dates Configuration', 'abp-tour-travel' ); ?></h4>
                        <?php ABPTT_Layout::button_global_save( 'global_dates', __( 'Save Global Date Configuration', 'abp-tour-travel' ) ); ?>
                    </div>
                    <?php ABPTT_Layout::info_text( 'abptt_dates' ); ?>
                    <div class="_divider_xxs"></div>
                    <div class="group_setting ">
                        <div class="setting_item">
                            <label class="_f_wrap_fj_between_fa_center">
                                <span><?php esc_html_e( 'Date Format', 'abp-tour-travel' ); ?></span>
                                <?php if ( sizeof( $format_array ) > 0 ) { ?>
                                    <select class="_form_control " name="date_format" required>
                                        <?php foreach ( $format_array as $key => $format ) { ?>
                                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $date_format, $key ) ?>><?php echo esc_html( $format ); ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } ?>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'date_format' ); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span><?php esc_html_e( 'Time Format', 'abp-tour-travel' ); ?></span>
                                <input type="text" class="_form_control" name="time_format" placeholder="<?php echo esc_attr( ABPTT_Time_Format ); ?>" value="<?php echo esc_attr( $time_format ); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'time_format' ); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span><?php esc_html_e( 'Buffer time in MIN (Optional)', 'abp-tour-travel' ); ?></span>
                                <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="sale_close_before" placeholder="Ex: 15" value="<?php echo esc_attr( $date_infos['sale_close_before'] ?? 0 ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'sale_close_before' ); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span><?php esc_html_e( 'Number of advance booking date', 'abp-tour-travel' ); ?></span>
                                <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="advance_date_number" placeholder="Ex: 28" value="<?php echo esc_attr( $date_infos['advance_date_number'] ?? 28 ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'advance_date_number' ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public static function repeat_patterns(): array {
                return array(
                    '1'  => __( 'Daily', 'abp-tour-travel' ),
                    '7'  => __( 'Weekly', 'abp-tour-travel' ),
                    '30' => __( 'Monthly', 'abp-tour-travel' ),
                );
            }

            public static function date_types(): array {
                return array(
                    'particular' => __( 'Particular Dates', 'abp-tour-travel' ),
                    'repeated'   => __( 'Repeated Dates', 'abp-tour-travel' ),
                );
            }

            public function post_content_dates( $post_infos ): void {
                $date_infos = $post_infos['abptt_dates'] ?? [];
                $time_infos = $post_infos['time_infos'] ?? [];
                $date_types = self::date_types();
                $date_type  = ( $date_infos['date_type'] ?? null ) ?: 'particular';
                if ( ! isset( $date_types[ $date_type ] ) ) {
                    $date_type = 'particular';
                }
                $particular_dates = $date_infos['particular_dates'] ?? [];
                $particular_dates = is_array( $particular_dates ) ? $particular_dates : array();
                $configured_count = 0;
                foreach ( $particular_dates as $particular_date ) {
                    if ( is_array( $particular_date ) && ! empty( $particular_date['checkin_date'] ) ) {
                        $configured_count ++;
                    }
                }
                ?>
                <div class="tab_item date_configuration" data-tabs="#abptt_dates">
                    <div class="setting_item _mar_b_xs">
                        <div class=" _fj_between">
                            <span class="abp_label"><?php esc_html_e( 'Date Type', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                            <div class="custom_radio _group_content">
                                <input type="hidden" class="_form_control" name="date_type" value="<?php echo esc_attr( $date_type ); ?>"/>
                                <?php foreach ( $date_types as $type_key => $type_label ) { ?>
                                    <div class="radio_item">
                                        <button type="button" class="_btn_light_info_xs <?php echo esc_attr( $date_type == $type_key ? 'abp_active' : '' ); ?>" data-close-target="#<?php echo esc_attr( $type_key ); ?>_dates" data-radio="<?php echo esc_attr( $type_key ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                            <span data-icon class="<?php echo esc_attr( $date_type == $type_key ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><?php echo esc_html( $type_label ); ?>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'date_type' ); ?>
                    </div>
                    <?php $this->particular_dates( $particular_dates, $date_type, $configured_count ); ?>
                    <?php $this->repeated_dates( $date_infos, $time_infos, $date_type ); ?>
                </div>
                <?php
            }

            public function particular_dates( array $particular_dates, string $date_type, int $configured_count ): void {
                ?>
                <div class="<?php echo esc_attr( $date_type == 'particular' ? 'abp_active' : '' ); ?>" data-close="#particular_dates">
                    <div class="group_setting">
                        <div class="setting_item full_width configuration_content">
                            <div class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label_gap_xs"><?php ABPTT_Static::svg( 'date_2' ); ?><?php esc_html_e( 'Particular Dates', 'abp-tour-travel' ); ?></span>
                                <?php /* translators: %d: number of configured particular dates. */ ?>
                                <span class="abptt_count_badge" data-entry-count data-singular="<?php echo esc_attr__( '%d Entry Configured', 'abp-tour-travel' ); ?>" data-plural="<?php echo esc_attr__( '%d Entries Configured', 'abp-tour-travel' ); ?>"><?php
                                        // translators: %d: number of configured particular dates.
                                        echo esc_html( sprintf( _n( '%d Entry Configured', '%d Entries Configured', $configured_count, 'abp-tour-travel' ), $configured_count ) );
                                    ?></span>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'specific_dates' ); ?>
                            <div class="_ov_auto_mar_t_xs">
                                <table class="abp">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Check In', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e( 'Check Out', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e( 'Registration End', 'abp-tour-travel' ); ?></th>
                                        <th class="_w_75"><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="insertable_area sortable_area">
                                    <?php
                                        foreach ( $particular_dates as $particular_date ) {
                                            if ( is_array( $particular_date ) && ! empty( $particular_date['checkin_date'] ) ) {
                                                $this->particular_date_item( $particular_date );
                                            }
                                        }
                                        if ( $configured_count === 0 ) {
                                            $this->particular_date_item();
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="abp_hidden">
                                <table class="abp">
                                    <tbody class="hidden_content">
                                    <?php $this->particular_date_item(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::button_add( __( 'Add New Particular Date', 'abp-tour-travel' ) ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function particular_date_item( array $particular_date = array() ): void {
                $checkin_date  = $particular_date['checkin_date'] ?? '';
                $checkin_time  = $particular_date['checkin_time'] ?? '';
                $checkout_date = $particular_date['checkout_date'] ?? '';
                $checkout_time = $particular_date['checkout_time'] ?? '';
                $reg_end_date  = $particular_date['reg_end_date'] ?? '';
                $reg_end_time  = $particular_date['reg_end_time'] ?? '';
                $groups        = array(
                    'checkin'  => array(
                        'title' => __( 'Check In', 'abp-tour-travel' ),
                        'date'  => $checkin_date,
                        'time'  => $checkin_time,
                        'name'  => 'particular_checkin',
                    ),
                    'checkout' => array(
                        'title' => __( 'Check Out', 'abp-tour-travel' ),
                        'date'  => $checkout_date,
                        'time'  => $checkout_time,
                        'name'  => 'particular_checkout',
                    ),
                    'reg_end'  => array(
                        'title' => __( 'Registration End', 'abp-tour-travel' ),
                        'date'  => $reg_end_date,
                        'time'  => $reg_end_time,
                        'name'  => 'particular_reg_end',
                    ),
                );
                ?>
                <tr class="delete_area ">
                    <?php foreach ( $groups as $group ) { ?>
                        <td>
                            <div class="_group_content">
                                <?php
                                    ABPTT_Layout::input_date( $group['name'] . '_date[]', $group['date'] );
                                    ABPTT_Layout::input_time( $group['name'] . '_time[]', $group['time'] );
                                ?>
                            </div>
                        </td>
                    <?php } ?>
                    <td><?php ABPTT_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }

            public function repeated_dates( array $date_infos, array $time_infos, string $date_type ): void {
                $repeat_after      = (string) ( $date_infos['repeat_after'] ?? '1' );
                $repeat_patterns   = self::repeat_patterns();
                $repeat_pattern    = isset( $repeat_patterns[ $repeat_after ] ) ? $repeat_after : 'custom';
                $repeat_end_date   = $date_infos['repeat_end_date'] ?? '';
                $repeat_start_date = $date_infos['repeat_start_date'] ?? '';
                $enable_time       = ( $date_infos['enable_time'] ?? null ) ?: 'on';
                $operation_times   = $time_infos['time'] ?? array();
                $day_slots         = $time_infos['day_time'] ?? array();
                $date_times        = $time_infos['date_times'] ?? array();
                $opt_time          = ! empty( $day_slots ) ? 'day_wise_time' : '';
                $opt_time          = ! empty( $date_times ) ? ( $opt_time ? $opt_time . ',' : '' ) . 'date_wise_time' : $opt_time;
                ?>
                <div class="<?php echo esc_attr( $date_type == 'repeated' ? 'abp_active' : '' ); ?>" data-close="#repeated_dates">
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <div class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e( 'Repeat Pattern', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                                <div class="custom_radio _group_content">
                                    <input type="hidden" name="repeat_pattern" value="<?php echo esc_attr( $repeat_pattern ); ?>"/>
                                    <?php foreach ( $repeat_patterns as $pattern_key => $pattern_label ) { ?>
                                        <div class="radio_item">
                                            <button type="button" class="_btn_light_info <?php echo esc_attr( $repeat_pattern == $pattern_key ? 'abp_active' : '' ); ?>" data-repeat-preset="<?php echo esc_attr( $pattern_key ); ?>" data-radio="<?php echo esc_attr( $pattern_key ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                                <span data-icon class="<?php echo esc_attr( $repeat_pattern == $pattern_key ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><?php echo esc_html( $pattern_label ); ?>
                                            </button>
                                        </div>
                                    <?php } ?>
                                    <div class="radio_item">
                                        <button type="button" class="_btn_light_info_xs <?php echo esc_attr( $repeat_pattern == 'custom' ? 'abp_active' : '' ); ?>" data-radio="custom" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                            <span data-icon class="<?php echo esc_attr( $repeat_pattern == 'custom' ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><?php esc_html_e( 'Custom', 'abp-tour-travel' ); ?>
                                            <input type="number" pattern="[0-9]*" min="1" step="1" class="_form_control validation_number" name="repeat_after_custom" data-repeat-custom-input value="<?php echo esc_attr( $repeat_after ); ?>" placeholder="1"/>
                                            <span class="abptt_pill_suffix"><?php esc_html_e( 'Days', 'abp-tour-travel' ); ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'repeat_after' ); ?>
                        </div>
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e( 'Start Date', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></span>
                                <?php ABPTT_Layout::input_date( 'repeat_start_date', $repeat_start_date, '', 'required' ); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'repeat_start_date' ); ?>
                        </div>
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label"><?php esc_html_e( 'End Date', 'abp-tour-travel' ); ?></span>
                                <?php ABPTT_Layout::input_date( 'repeat_end_date', $repeat_end_date ); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'repeat_end_date' ); ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <h4 class="abp_color_theme_gap_xxs">🚫<?php esc_html_e( 'Special On/Off Date (Optional)', 'abp-tour-travel' ); ?></h4>
                    <div class="_divider_xxs"></div>
                    <?php $this->special_on_off_dates( $date_infos ); ?>
                    <div class="_divider_xs"></div>
                    <h4 class="abp_color_theme_gap_xxs">⏰<?php esc_html_e( 'Time Configuration', 'abp-tour-travel' ); ?></h4>
                    <div class="_divider_xxs"></div>
                    <div class="group_setting">
                        <div class="setting_item full_width configuration_content">
                            <div class="_f_wrap_fj_between_fa_center">
                                <div class="_gap_xs">
                                    <?php ABPTT_Layout::switch_checkbox( 'enable_time', $enable_time ); ?>
                                    <span class="abp_label"><?php esc_html_e( 'Tour Time Slots', 'abp-tour-travel' ); ?></span>
                                </div>
                                <div class="<?php echo esc_attr( $enable_time === 'on' ? 'abp_active' : '' ); ?>" data-collapse="#enable_time">
                                    <div class="_group_content custom_checkbox">
                                        <input type="hidden" name="operation_time_optional" value="<?php echo esc_attr( $opt_time ); ?>"/>
                                        <div class="checkbox_item">
                                            <button type="button" class="_btn_light_info_xs <?php echo esc_attr( false !== strpos( $opt_time, 'day_wise_time' ) ? 'abp_active' : '' ); ?>" data-collapse-target="#day_wise_time" data-checked="day_wise_time" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                                <span data-icon class="_mar_r_xs far <?php echo esc_attr( false !== strpos( $opt_time, 'day_wise_time' ) ? 'fa-check-square' : 'fa-square' ); ?>"></span><?php esc_html_e( 'Day Wise Time', 'abp-tour-travel' ); ?>
                                            </button>
                                        </div>
                                        <div class="checkbox_item">
                                            <button type="button" class="_btn_light_info_xs <?php echo esc_attr( false !== strpos( $opt_time, 'date_wise_time' ) ? 'abp_active' : '' ); ?>" data-collapse-target="#date_wise_time" data-checked="date_wise_time" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                                <span data-icon class="_mar_r_xs far <?php echo esc_attr( false !== strpos( $opt_time, 'date_wise_time' ) ? 'fa-check-square' : 'fa-square' ); ?>"></span><?php esc_html_e( 'Date Wise Time', 'abp-tour-travel' ); ?>
                                            </button>
                                        </div>
                                        <?php ABPTT_Layout::button_add( __( 'Add Time Slot', 'abp-tour-travel' ) ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTT_Layout::info_text( 'enable_time' ); ?>
                            <div class="configuration_content <?php echo esc_attr( $enable_time === 'on' ? 'abp_active' : '' ); ?>" data-collapse="#enable_time">
                                <div class="_ov_auto_mar_t_xs">
                                    <table class="abp">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Slot Name', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                            <th><?php esc_html_e( 'Start Time', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                            <th><?php esc_html_e( 'Slot Stock', 'abp-tour-travel' ); ?></th>
                                            <th class="_w_75"><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="insertable_area sortable_area">
                                        <?php
                                            $slot_exit = 0;
                                            if ( ! empty( $operation_times ) ) {
                                                foreach ( $operation_times as $slot ) {
                                                    if ( is_array( $slot ) && ! empty( $slot['time'] ) ) {
                                                        $this->slot_item( 'slot', '', $slot );
                                                        $slot_exit ++;
                                                    }
                                                }
                                            }
                                            if ( $slot_exit === 0 ) {
                                                $this->slot_item( 'slot' );
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="abp_hidden">
                                    <table class="abp">
                                        <tbody class="hidden_content">
                                        <?php $this->slot_item( 'slot' ); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="<?php echo esc_attr( $enable_time === 'on' ? 'abp_active' : '' ); ?>" data-collapse="#enable_time">
                            <?php $this->day_wise_slots( $day_slots ); ?>
                            <?php $this->date_wise_slots( $date_times ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function day_wise_slots( array $day_slots = [] ): void {
                $days = ABPTT_Layout::week_day();
                ?>
                <div class="setting_item full_width <?php echo esc_attr( ! empty( $day_slots ) ? 'abp_active' : '' ); ?>" data-collapse="#day_wise_time">
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="abp_label"><?php esc_html_e( 'Day Wise Time Slots (Optional)', 'abp-tour-travel' ); ?></span>
                        <div class="custom_checkbox _group_content">
                            <?php foreach ( $days as $key => $day ) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr( array_key_exists( $key, $day_slots ) ? 'abp_active' : '' ); ?>" data-collapse-target="#abptt_day_<?php echo esc_attr( $key ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                        <span data-icon class="far <?php echo esc_attr( array_key_exists( $key, $day_slots ) ? 'far fa-check-square' : 'fa-square' ); ?>"></span><?php echo esc_html( $day ); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTT_Layout::info_text( 'day_wise_time' ); ?>
                    <?php foreach ( $days as $key => $day ) {
                        $operation_slots = $day_slots[ $key ] ?? array();
                        ?>
                        <div class="full_width configuration_content <?php echo esc_attr( array_key_exists( $key, $day_slots ) ? 'abp_active' : '' ); ?>" data-collapse="#abptt_day_<?php echo esc_attr( $key ); ?>">
                            <div class="_f_wrap_fj_between_fa_center">
                                <span class="abp_label"><?php echo esc_html( $day ); ?></span>
                                <?php ABPTT_Layout::button_add( __( 'Add Slot : ', 'abp-tour-travel' ) . $day ); ?>
                            </div>
                            <div class="_divider_xs"></div>
                            <div class="_ov_auto_mar_t_xs">
                                <table class="abp">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Slot Name', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e( 'Start Time', 'abp-tour-travel' ); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e( 'Slot Stock', 'abp-tour-travel' ); ?></th>
                                        <th class="_w_75"><?php esc_html_e( 'Action', 'abp-tour-travel' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="insertable_area sortable_area">
                                    <?php
                                        if ( ! empty( $operation_slots ) ) {
                                            foreach ( $operation_slots as $slot ) {
                                                if ( is_array( $slot ) && ! empty( $slot['time'] ) ) {
                                                    $this->slot_item( 'day_slot', $key, $slot );
                                                }
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="abp_hidden">
                                <table class="abp">
                                    <tbody class="hidden_content">
                                    <?php $this->slot_item( 'day_slot', $key ); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }

            public function date_wise_slots( array $date_times = [] ): void {
                ?>
                <div class="setting_item full_width configuration_content _mar_t_xs <?php echo esc_attr( ! empty( $date_times ) ? 'abp_active' : '' ); ?>" data-collapse="#date_wise_time">
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="abp_label"><?php esc_html_e( 'Date Wise Time (Optional)', 'abp-tour-travel' ); ?></span>
                        <?php ABPTT_Layout::button_add( __( 'Add New Date Wise Operation Time', 'abp-tour-travel' ) ); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTT_Layout::info_text( 'date_wise_time' ); ?>
                    <div class="insertable_area sortable_area">
                        <?php if ( ! empty( $date_times ) ) {
                            foreach ( $date_times as $key => $date_time ) {
                                $this->date_wise_slot_item( $date_time, $key );
                            }
                        } ?>
                    </div>
                    <div class="abp_hidden" data-hidden_id>
                        <div class="hidden_content">
                            <?php $this->date_wise_slot_item( [], uniqid( 'abp_' ) ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function slot_item( string $base, string $group = '', array $slot = array() ): void {
                $name   = $slot['name'] ?? '';
                $time   = $slot['time'] ?? '';
                $stock  = ( isset( $slot['stock'] ) && (int) $slot['stock'] > 0 ) ? (int) $slot['stock'] : '';
                $suffix = $group !== '' ? '[' . $group . ']' : '';
                ?>
                <tr class="delete_area ">
                    <td>
                        <label>
                            <input type="text" class="_form_control" name="<?php echo esc_attr( $base . '_name' . $suffix . '[]' ); ?>" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Morning Session', 'abp-tour-travel' ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="time" class="_form_control" name="<?php echo esc_attr( $base . '_time' . $suffix . '[]' ); ?>" value="<?php echo esc_attr( $time ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" min="0" step="1" class="_form_control validation_number" name="<?php echo esc_attr( $base . '_stock' . $suffix . '[]' ); ?>" value="<?php echo esc_attr( $stock ); ?>" placeholder="<?php esc_attr_e( 'Blank Will be default Stock', 'abp-tour-travel' ); ?>"/>
                        </label>
                    </td>
                    <td><?php ABPTT_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }

            public function date_wise_slot_item( array $date_time = [], string $key = '' ): void {
                $slots = $date_time['time'] ?? array();
                ?>
                <div class="configuration_content delete_area abptt_date_slot_card">
                    <input type="hidden" name="date_wise_time_id[]" class="hidden_id" value="<?php echo esc_attr( $key ); ?>">
                    <div class="_divider_xs"></div>
                    <div class="_fa_start_gap_xs">
                        <div class="_group_content">
                            <?php
                                ABPTT_Layout::button_sort();
                                ABPTT_Layout::input_date( 'date_wise_date[' . $key . '][]', ( $date_time['date'] ?? '' ) );
                                ABPTT_Layout::button_delete();
                            ?>
                        </div>
                        <?php ABPTT_Layout::button_add( __( 'Add Operation Time ', 'abp-tour-travel' ) ); ?>
                        <div class="insertable_area sortable_area _f_wrap_fa_gap_xs">
                            <?php if ( ! empty( $slots ) ) {
                                foreach ( $slots as $slot ) {
                                    if ( is_array( $slot ) && ! empty( $slot['time'] ) ) {
                                        $this->slot_item( 'date_slot', $key, $slot );
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="abp_hidden">
                        <div class="hidden_content">
                            <?php $this->slot_item( 'date_slot', $key ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }

            public function special_on_off_dates( $date_infos = [] ): void {
                $date_rule       = $date_infos['date_rule'] ?? '';
                $date_rule_array = $date_rule ? explode( ',', $date_rule ) : [];
                $weekend         = $date_infos['weekend'] ?? '';
                $weekend_array   = $weekend ? explode( ',', $weekend ) : [];
                $days            = ABPTT_Layout::week_day();
                $date_rules      = ABPTT_Layout::date_option_rules();
                ?>
                <div class="group_setting _mar_t_xs">
                    <div class="setting_item full_width">
                        <div class="_fj_between _mar_t_xs">
                            <span class="abp_label"><?php esc_html_e( 'Off/On Rules(optional)', 'abp-tour-travel' ); ?></span>
                            <div class="custom_checkbox _group_content">
                                <input type="hidden" name="date_rule" value="<?php echo esc_attr( $date_rule ); ?>"/>
                                <?php foreach ( $date_rules as $key => $rule ) { ?>
                                    <div class="checkbox_item _min_100">
                                        <button type="button" class="_btn_light_info_xs <?php echo esc_attr( in_array( $key, $date_rule_array, true ) ? 'abp_active' : '' ); ?>" data-collapse-target="#<?php echo esc_attr( $key ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                            <span data-icon class="far <?php echo esc_attr( in_array( $key, $date_rule_array, true ) ? 'far fa-check-square' : 'fa-square' ); ?>"></span><?php echo esc_html( $rule ); ?>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'date_rule' ); ?>
                    </div>
                    <div class="setting_item full_width <?php echo esc_attr( in_array( 'weekend', $date_rule_array, true ) ? 'abp_active' : '' ); ?> " data-collapse="#weekend">
                        <div class="_f_wrap_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e( 'Weekend(optional)', 'abp-tour-travel' ); ?></span>
                            <div class="custom_checkbox _group_content">
                                <input type="hidden" name="weekend" value="<?php echo esc_attr( $weekend ); ?>"/>
                                <?php foreach ( $days as $key => $day ) { ?>
                                    <div class="checkbox_item _min_100">
                                        <button type="button" class="_btn_light_info_xs <?php echo esc_attr( in_array( $key, $weekend_array ) ? 'abp_active' : '' ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                            <span data-icon class="<?php echo esc_attr( in_array( $key, $weekend_array ) ? 'far fa-check-square' : 'far fa-square' ); ?>"></span><?php echo esc_html( $day ); ?>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'weekend' ); ?>
                    </div>
                    <div class="setting_item configuration_content <?php echo esc_attr( in_array( 'specific_off_dates', $date_rule_array, true ) ? 'abp_active' : '' ); ?>" data-collapse="#specific_off_dates">
                        <div class="_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e( 'Specific Off Dates(optional)', 'abp-tour-travel' ); ?></span>
                            <?php ABPTT_Layout::button_add( __( 'Add Specific Off Date', 'abp-tour-travel' ) ); ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'specific_off_dates' ); ?>
                        <div class="insertable_area sortable_area _f_wrap_gap_xs_mar_t_xs">
                            <?php $specific_off_dates = $date_infos['specific_off_dates'] ?? [];
                                if ( sizeof( $specific_off_dates ) ) {
                                    foreach ( $specific_off_dates as $specific_date ) {
                                        if ( $specific_date ) {
                                            $this->date_item( 'specific_off_dates[]', $specific_date );
                                        }
                                    }
                                }
                            ?>
                        </div>
                        <div class="abp_hidden">
                            <div class="hidden_content">
                                <?php $this->date_item( 'specific_off_dates[]' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="setting_item configuration_content  <?php echo esc_attr( in_array( 'special_on_dates', $date_rule_array, true ) ? 'abp_active' : '' ); ?>" data-collapse="#special_on_dates">
                        <div class="_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e( 'Special On Dates (optional)', 'abp-tour-travel' ); ?></span>
                            <?php ABPTT_Layout::button_add( __( 'Add Special On Dates', 'abp-tour-travel' ) ); ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'special_on_dates' ); ?>
                        <div class="insertable_area sortable_area _f_wrap_gap_xs_mar_t_xs">
                            <?php $special_dates = $date_infos['special_on_dates'] ?? [];
                                if ( sizeof( $special_dates ) ) {
                                    foreach ( $special_dates as $specific_date ) {
                                        if ( ! empty( $specific_date ) ) {
                                            $specific_date = is_array( $specific_date ) ? ( $specific_date['date'] ?? '' ) : $specific_date;
                                            $this->date_item( 'special_on_dates[]', $specific_date );
                                        }
                                    }
                                }
                            ?>
                        </div>
                        <div class="abp_hidden">
                            <div class="hidden_content">
                                <?php $this->date_item( 'special_on_dates[]' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="setting_item configuration_content <?php echo esc_attr( in_array( 'off_date_range', $date_rule_array, true ) ? 'abp_active' : '' ); ?>" data-collapse="#off_date_range">
                        <div class="_fj_between_fa_center">
                            <span class="abp_label"><?php esc_html_e( 'Off Date Range(optional)', 'abp-tour-travel' ); ?></span>
                            <?php ABPTT_Layout::button_add( __( 'Add Off Date Range', 'abp-tour-travel' ) ); ?>
                        </div>
                        <div class="_divider_xs"></div>
                        <?php ABPTT_Layout::info_text( 'off_date_range' ); ?>
                        <div class="insertable_area sortable_area _f_wrap_gap_xs_mar_t_xs">
                            <?php $off_date_range = $date_infos['off_date_range'] ?? [];
                                if ( sizeof( $off_date_range ) ) {
                                    foreach ( $off_date_range as $specific_date ) {
                                        if ( is_array( $specific_date ) && sizeof( $specific_date ) > 0 && ! empty( $specific_date['from'] ) && ! empty( $specific_date['to'] ) ) {
                                            $this->off_day_range( $specific_date['from'], $specific_date['to'] );
                                        }
                                    }
                                }
                            ?>
                        </div>
                        <div class="abp_hidden">
                            <div class="hidden_content">
                                <?php $this->off_day_range(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            //=============================//
            public function off_day_range( $from_date = '', $to_date = '' ): void {
                ?>
                <div class="delete_area _group_content">
                    <?php
                        ABPTT_Layout::button_sort();
                        ABPTT_Layout::input_date( 'abptt_off_from[]', $from_date );
                        ABPTT_Layout::input_date( 'abptt_off_to[]', $to_date );
                        ABPTT_Layout::button_delete();
                    ?>
                </div>
                <?php
            }

            public function date_item( $name, $date = '' ): void {
                ?>
                <div class="delete_area _group_content">
                    <?php
                        ABPTT_Layout::button_sort();
                        ABPTT_Layout::input_date( $name, $date );
                        ABPTT_Layout::button_delete();
                    ?>
                </div>
                <?php
            }

            public function save_global_dates(): void {
                if ( ! check_ajax_referer( 'abptt_admin_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( [ 'msg' => __( 'Invalid security token or Insufficient permissions.', 'abp-tour-travel' ), 'type' => 'warn' ], 403 );
                }
                $post_val                           = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
                $date_config['date_format']         = $post_val( 'date_format' );
                $date_config['time_format']         = $post_val( 'time_format' );
                $date_config['advance_date_number'] = $post_val( 'advance_date_number', '28' );
                $date_config['sale_close_before']   = $post_val( 'sale_close_before' );
                update_option( 'abptt_date_config', $date_config );
                ob_start();
                $this->dates_config();
                $html = ob_get_clean();
                wp_send_json_success( [
                    'html' => $html,
                    'msg'  => __( 'Global Date Configuration Saved Successfully ..... !!', 'abp-tour-travel' ),
                    'type' => 'success'
                ] );
            }
        }
        new ABPTT_Dates();
    }
