<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTT_Form')) {
        class ABPTT_Form {
            public function __construct() {
                add_action('abptt_global_client_form', array($this, 'global_client_form'));
                add_action('abptt_post_content', [$this, 'post_client_form']);
                add_filter('abptt_get_form_array', array($this, 'get_form_array'));
                add_action('wp_ajax_abptt_save_form_config', array($this, 'save_form_config'));
                add_action('wp_ajax_abptt_import_client_form_content', array($this, 'import_client_form_content'));
            }
            public function global_client_form(): void {
                ?>
                <div class="form_config">
                    <?php $this->form_config(); ?>
                </div>
                <?php
            }
            public function form_config(): void {
                if (ABPTT_Function::on_off('client_info')) {
                    $forms = ABPTT_Function::get_option('abptt_form', ABPTT_Static::form());
                    ?>
                    <div class="abp_form">
                        <h4 class="abp_color_theme_gap_xs">📋 <?php esc_html_e('Global Traveler Forms Configuration', 'abp-tour-travel'); ?></h4>
                        <?php ABPTT_Layout::info_text('abptt_form'); ?>
                        <div class="_divider_xxs"></div>
                        <?php $this->passenger_form_settings($forms, true); ?>
                    </div>
                    <?php
                }
            }
            public function post_client_form($post_infos): void {
                if (ABPTT_Function::on_off('client_info')) {
                    $client_forms = $post_infos['abptt_form'] ?? [];
                    $display = $post_infos['display_client_form'] ?? 'off';
                    $active_global_form = $post_infos['active_global_form'] ?? 'on';
                    $display_single_form = $post_infos['display_single_form'] ?? 'on';
                    $display_single_form = ABPTT_Function::on_off('same_attendee') ? $display_single_form : 'off';
                    ?>
                    <div class="tab_item abptt_client_form" data-tabs="#abptt_client_form">
                        <?php if (ABPTT_Function::on_off('custom_attendee')) { ?>
                            <div class="group_setting">
                                <div class="setting_item">
                                    <div class="_f_wrap_fj_between_fa_center">
                                        <label>
                                            <?php ABPTT_Layout::switch_checkbox('display_client_form', $display); ?>
                                            <span><?php esc_html_e('Traveler Forms ?', 'abp-tour-travel'); ?></span>
                                        </label>
                                    </div>
                                    <div class="_divider_xs"></div>
                                    <?php ABPTT_Layout::info_text('display_client_form'); ?>
                                </div>
                                <div data-collapse="#display_client_form" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                                    <div class="_fj_between">
                                        <label>
                                            <?php ABPTT_Layout::switch_checkbox('active_global_form', $active_global_form); ?>
                                            <span><?php esc_html_e('Use Global Traveler Forms ?', 'abp-tour-travel'); ?></span>
                                        </label>
                                        <div data-collapse="#active_global_form" class=" <?php echo esc_attr($active_global_form == 'on' ? '' : 'abp_active'); ?>">
                                            <button type="button" class="_btn_theme_xs" onclick="abptt_import_global('client_form_content')"><span class="fas fa-file-upload"></span><?php esc_html_e('Import Global Traveler Forms', 'abp-tour-travel'); ?></button>
                                        </div>
                                    </div>
                                    <div class="_divider_xs"></div>
                                    <?php ABPTT_Layout::info_text('active_global_form'); ?>
                                </div>
                                <?php if (ABPTT_Function::on_off('same_attendee')) { ?>
                                    <div data-collapse="#display_client_form" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                                        <label>
                                            <?php ABPTT_Layout::switch_checkbox('display_single_form', $display_single_form); ?>
                                            <span><?php esc_html_e('Same Guest?', 'abp-tour-travel'); ?></span>
                                        </label>
                                        <div class="_divider_xs"></div>
                                        <?php ABPTT_Layout::info_text('display_single_form'); ?>
                                    </div>
                                <?php } else { ?>
                                    <input type="hidden" name="display_single_form" value="<?php echo esc_attr($display_single_form); ?>">
                                <?php } ?>
                            </div>
                            <div class="<?php echo esc_attr($active_global_form == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_form">
                                <div class="client_form_content _mar_t_xs">
                                    <?php $this->passenger_form_settings($client_forms); ?>
                                </div>
                            </div>
                        <?php } else {
                            ABPTT_Layout::layout_warning_info_xs('attendee_off');
                        } ?>
                    </div>
                    <?php
                }
            }
            public function passenger_form_settings($passenger_forms, $global = false): void {
                ?>
                <div class="configuration_content">
                    <div class="_ov_auto">
                        <table class=" abp">
                            <thead>
                            <tr>
                                <th class="_text_table_center"><?php esc_html_e('Form Title', 'abp-tour-travel'); ?><sup class="_color_required">*</sup></th>
                                <th class="_text_table_center"><?php esc_html_e('Unique ID', 'abp-tour-travel'); ?><sup class="_color_required">*</sup></th>
                                <th class="_text_table_center"><?php esc_html_e('Form Type', 'abp-tour-travel'); ?><sup class="_color_required">*</sup></th>
                                <th class="_text_table_center">
                                    <?php esc_html_e('Value Option', 'abp-tour-travel'); ?><sup class="_color_required">*</sup>
                                    <?php ABPTT_Layout::info_text('client_form_option'); ?>
                                </th>
                                <th class="_text_table_center"><?php esc_html_e('Default Value', 'abp-tour-travel'); ?></th>
                                <th class="_w_100_text_table_center"><?php esc_html_e('Required', 'abp-tour-travel'); ?><sup class="_color_required">*</sup></th>
                                <th class="_w_75_text_table_center"><?php esc_html_e('Action', 'abp-tour-travel'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php
                                if ($passenger_forms && is_array($passenger_forms) && sizeof($passenger_forms) > 0) {
                                    foreach ($passenger_forms as $id => $form) {
                                        $this->form_item($form, $id);
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <?php ABPTT_Layout::button_add(__('Add New Form Item', 'abp-tour-travel'));
                            if ($global) {
                                ABPTT_Layout::button_global_save('form_config', __('Save Global Traveler Forms Configuration', 'abp-tour-travel'));
                            } ?>
                    </div>
                    <div class="abp_hidden">
                        <table class=" abp">
                            <tbody class="hidden_content">
                            <?php $this->form_item(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            public function form_item($form = [], $id = ''): void {
                $form = is_array($form) ? $form : [];
                $type = $form['type'] ?? 'text';
                $required = $form['required'] ?? 'off';
                $label = $form['label'] ?? '';
                $options = $form['option'] ?? '';
                $d_value = $form['d_value'] ?? '';
                $active_type = ($type == 'select' || $type == 'checkbox' || $type == 'radio') ? 'abp_active' : '';
                $active_value = $type != 'date' ? 'abp_active' : '';
                $date = $type == 'date' ? $d_value : '';
                $now = ABPTT_Function::date_format(current_time('Y-m-d'));
                $hidden_date = $date ? gmdate('Y-m-d', strtotime($date)) : '';
                $visible_date = $date ? ABPTT_Function::date_format($date, 'date') : '';
                $active_date = $type == 'date' ? 'abp_active' : '';
                ?>
                <tr class="delete_area data_single_collapse">
                    <td>
                        <label>
                            <input type="text" class="_form_control_min_150 validation_name" name="client_form_title[]" placeholder="<?php esc_attr_e('Name', 'abp-tour-travel'); ?>" value="<?php echo esc_attr($label); ?>"/>
                        </label>
                    </td>
                    <th class="_text_table_center">
                        <?php if ($id) { ?>
                            <input type="hidden" value="<?php echo esc_attr($id); ?>" name="client_form_id[]" /><?php echo esc_html($id); ?>
                        <?php } else { ?>
                            <label>
                                <input type="text" class="_form_control_min_150 validation_id" name="client_form_id[]" placeholder="<?php esc_attr_e('Unique ID', 'abp-tour-travel'); ?>" value="<?php echo esc_attr($id); ?>"/>
                            </label>
                        <?php } ?>
                    </th>
                    <td>
                        <label>
                            <select class="_form_control_min_150" name="client_form_type[]" data-collapse-target data-collapse-target-multi>
                                <option value="text" data-option-target-multi="#client_form_value" <?php echo esc_attr($type == 'text' ? 'selected' : ''); ?>><?php esc_html_e('Text', 'abp-tour-travel'); ?></option>
                                <option value="email" data-option-target-multi="#client_form_value" <?php echo esc_attr($type == 'email' ? 'selected' : ''); ?>><?php esc_html_e('E-Mail', 'abp-tour-travel'); ?></option>
                                <option value="number" data-option-target-multi="#client_form_value" <?php echo esc_attr($type == 'number' ? 'selected' : ''); ?>><?php esc_html_e('Number', 'abp-tour-travel'); ?></option>
                                <option value="select" data-option-target-multi="#client_form_type #client_form_value" <?php echo esc_attr($type == 'select' ? 'selected' : ''); ?>><?php esc_html_e('Select', 'abp-tour-travel'); ?></option>
                                <option value="checkbox" data-option-target-multi="#client_form_type #client_form_value" <?php echo esc_attr($type == 'checkbox' ? 'selected' : ''); ?>><?php esc_html_e('Checkbox', 'abp-tour-travel'); ?></option>
                                <option value="radio" data-option-target-multi="#client_form_type #client_form_value" <?php echo esc_attr($type == 'radio' ? 'selected' : ''); ?>><?php esc_html_e('Radio', 'abp-tour-travel'); ?></option>
                                <option value="textarea" data-option-target-multi="#client_form_value" <?php echo esc_attr($type == 'textarea' ? 'selected' : ''); ?>><?php esc_html_e('Textarea', 'abp-tour-travel'); ?></option>
                                <option value="date" data-option-target-multi="#client_form_type_date" <?php echo esc_attr($type == 'date' ? 'selected' : ''); ?>><?php esc_html_e('Date', 'abp-tour-travel'); ?></option>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label data-collapse="#client_form_type" class="<?php echo esc_attr($active_type); ?>">
                            <input type="text" class="_form_control_min_150 validation_name" name="client_form_option[]" placeholder="<?php esc_attr_e('Value Option', 'abp-tour-travel'); ?>" value="<?php echo esc_attr($options); ?>"/>
                        </label>
                    </td>
                    <td>
                        <div class="<?php echo esc_attr($active_value); ?>" data-collapse="#client_form_value">
                            <label>
                                <input type="text" class="_form_control_min_150 validation_name" name="client_form_value[]" placeholder="<?php esc_attr_e('Default Value', 'abp-tour-travel'); ?>" value="<?php echo esc_attr($d_value); ?>"/>
                            </label>
                        </div>
                        <div class="<?php echo esc_attr($active_date); ?>" data-collapse="#client_form_type_date">
                            <label>
                                <input type="hidden" name="client_form_value_date[]" value="<?php echo esc_attr($hidden_date); ?>"/>
                                <input type="text" readonly name="" class="_form_control_min_150 abp_datepicker" value="<?php echo esc_attr($visible_date); ?>" placeholder="<?php echo esc_attr($now); ?>"/>
                                <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-tour-travel'); ?>"></span>
                            </label>
                        </div>
                    </td>
                    <td>
                        <?php ABPTT_Layout::switch_checkbox('client_form_required[]', $required); ?>
                    </td>
                    <td><?php ABPTT_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
            //=============================//
            public function get_form_array(array $form_infos = []): array {
                $has_post_nonce = false;
                if (isset($_POST['abptt_post_nonce'])) {
                    $has_post_nonce = wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptt_post_nonce'])), 'abptt_post_nonce');
                }
                $has_ajax_nonce = check_ajax_referer('abptt_admin_ajax_nonce', 'nonce', false);
                if (($has_post_nonce || $has_ajax_nonce) && current_user_can('manage_options')) {
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $post_textarea_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_textarea_field', wp_unslash($_POST[$key])) : [];
                    $form_ids = $post_array('client_form_id');
                    $form_title = $post_array('client_form_title');
                    $types = $post_array('client_form_type');
                    $option = $post_array('client_form_option');
                    $d_value = $post_array('client_form_value');
                    $required = $post_array('client_form_required');
                    $date_value = $post_textarea_array('client_form_value_date');
                    if (!empty($form_ids)) {
                        foreach ($form_ids as $key => $form_id) {
                            $title = $form_title[$key] ?? '';
                            $type = $types[$key] ?? '';
                            if ($form_id && $title && $type) {
                                $value = ($type === 'date') ? ($date_value[$key] ?? '') : ($d_value[$key] ?? '');
                                $form_infos[$form_id] = [
                                    'label' => $title,
                                    'type' => $type,
                                    'option' => $option[$key] ?? '',
                                    'd_value' => $value,
                                    'required' => $required[$key] ?? 'off',
                                ];
                            }
                        }
                    }
                }
                return $form_infos;
            }
            public function save_form_config(): void {
                if (!check_ajax_referer('abptt_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-tour-travel'), 'type' => 'warn'], 403);
                }
                $form_infos = $this->get_form_array();
                update_option('abptt_form', $form_infos);
                wp_send_json_success(['msg' => __('Traveler Forms Configuration Saved Successfully..... !! ', 'abp-tour-travel'), 'type' => 'success']);
            }
            public function import_client_form_content(): void {
                if (!check_ajax_referer('abptt_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-tour-travel'), 'type' => 'warn'], 403);
                }
                $default_form = ABPTT_Static::form();
                $forms = ABPTT_Function::get_option('abptt_form', $default_form) ?? [];
                $forms = is_array($forms) ? $forms : [];
                ob_start();
                $this->passenger_form_settings($forms);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Global Traveler Forms Imported Successfully ..... !! ', 'abp-tour-travel'), 'type' => 'success']);
            }
        }
        new ABPTT_Form();
    }