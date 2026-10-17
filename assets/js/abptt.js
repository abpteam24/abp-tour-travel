(function ($) {
    "use strict";
    let abptt_booking = abptt_parent.find('div.abptt_booking');
    $(document).on('click', 'div.abptt_area .pagination_item .select_post', function (e) {
        e.preventDefault();
        let post_id = parseInt($(this).attr('data-post_id'));
        let target = $(this).closest('div.abptt_area').find('#abptt_search_area .post_selection .dropdown_list li');
        target.each(function () {
            if (parseInt($(this).attr('data-value')) === post_id) {
                $(this).trigger('click');
                return true;
            }
        });
    });
    abptt_parent.on('abp_trigger', '.abp_search_form [name="post_id"]', function () {
        let parent = $(this).closest(".abp_search_form");
        let target_return = parent.find('.return_date');
        let target_journey = parent.find('.start_date');
        //load_bp(parent);
        let post_id = parent.find('[name="post_id"]').val();
        let formData = new FormData();
        formData.append('post_id', post_id);
        formData.append('action', 'abptt_load_date');
        formData.append('nonce', abptt_infos.nonce);
        $.ajax({
            type: 'POST', url: abptt_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptt_spinner(target_return);
                abptt_spinner(target_journey);
                abptt_toast_msg(abptt_infos.msg.date_loading);
            },
            success: function (response) {
                abptt_spinner_remove(target_journey);
                abptt_spinner_remove(target_return);
                if (response.data && response.data.hasOwnProperty('html_journey')) {
                    target_journey.html(response.data.html_journey).promise().done(function () {
                        if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                            abptt_init_dynamic_date_pickers(response.data.journey, response.data.picker_config);
                        } else {
                            abptt_load_datepicker(target_journey);
                        }
                    });
                    abptt_toast_msg(response.data.msg, response.data.type);
                } else {
                    abptt_toast_msg(response.data.msg, response.data.type);
                }
            }, error: function (xhr) {
                abptt_ajx_error(xhr, target_journey);
            }
        });
    });
    abptt_parent.on('submit', '#abptt_search_area form.abp_search_form', function (e) {
        e.preventDefault();
        let form_area = $(this).closest('#abptt_search_area');

        let post_id = parseInt(form_area.find('[name="post_id"]').val());
        if (post_id && post_id > 0) {
            if ($.trim(form_area.find('[name="start_date"]').val()).length === 0) {
                setTimeout(function () {
                    abptt_toast_msg(abptt_infos.msg.select_start_date);
                    form_area.find('#start_date').focus();
                }, 100);
                return;
            }
        }
        let target = abptt_parent.find('.abptt_booking');
        let formData = new FormData(this);
        formData.append('action', 'abptt_global_booking');
        formData.append('nonce', abptt_infos.nonce);
        $.ajax({
            type: 'POST', url: abptt_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptt_spinner(target);
                abptt_spinner(form_area);
                abptt_toast_msg(abptt_infos.msg.loading);
            },
            success: function (response) {
                abptt_spinner_remove(target);
                abptt_spinner_remove(form_area);
                abptt_toast_msg(response.data.msg, response.data.type);
                if (response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html).promise().done(function () {
                        abptt_init(target);
                    });
                } else {
                    window.location.reload();
                }
            }, error: function (xhr) {
                abptt_ajx_error(xhr, target);
            }
        });
    });
    // Refresh ticket/seat availability for the selected tour date and session.
    abptt_parent.on('change', '#abptt_search_area [name="start_date"], .post_top_filter [name="start_date"]', function () {
        let date = $(this).val();
        abptt_booking.find('form [name="tour_date"]').val(date);
        abptt_booking.find('form [name="session_time"]').val('');
        abptt_booking.find('form [name="session_time"]').first().trigger('change');
    });
    abptt_booking.on('change', "[name='session_time'] , [name='sp_id'] ", function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let target = $(this).closest(".booking_area");
        let parent = target.length ? target.closest("form") : $(this).closest('.abptt_booking').find('form').first();
        target = target.length ? target : parent.find('.booking_area');
        let target_html = target.find('.ticket_content');
        let formData = abptt_get_form_data(target_html);
        let booking_area = parent.closest('.abptt_booking');
        if ($(this).attr('name') === 'session_time') {
            parent.find('[name="session_time"]').val($(this).val());
        }
        formData.append('post_id', parent.find('[name="post_id"]').val());
        formData.append('sp_id', parent.find('[name="sp_id"]').val() || '');
        formData.append('tour_date', parent.find('[name="tour_date"]').val() || booking_area.find('[name="start_date"]').val());
        formData.append('session_time', parent.find('[name="session_time"]').val() || booking_area.find('[name="session_time"]').val());
        formData.append('action', 'abptt_load_booking_data');
        formData.append('nonce', abptt_infos.nonce);
        $.ajax({
            type: 'POST', url: abptt_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptt_spinner(target);
                abptt_toast_msg(abptt_infos.msg.loading);
            },
            success: function (response) {
                abptt_spinner_remove(target);
                //console.log(response);
                abptt_toast_msg(response.data.msg, response.data.type);
                if (response.data && Array.isArray(response.data.times)) {
                    let timeFields = booking_area.find('[name="session_time"]');
                    timeFields.each(function () {
                        let field = $(this);
                        if (field.is('select')) {
                            field.empty();
                            response.data.times.forEach(function (time) {
                                field.append($('<option>', {value: time.value, text: time.label}));
                            });
                            field.val(response.data.selected_time || '');
                        } else {
                            field.val(response.data.selected_time || '');
                        }
                    });
                }
                if (response.data && response.data.hasOwnProperty('html') && target_html.length > 0) {
                    target_html.html(response.data.html).promise().done(function () {
                        abptt_init(target_html);
                        all_management(target_html);
                    });
                }
            }, error: function (xhr) {
                abptt_ajx_error(xhr, target);
            }
        });
    });
    abptt_booking.on('abp_trigger', "[name='item_check[]']", function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let $this = $(this);
        let parent = $this.closest(".booking_area");
        let form = $this.closest("form");
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        let max_qty = parseInt(form.find('[name="max_qty"]').val());
        let data_id = $this.attr('data-id');
        let target = parent.find('[data-collapse="' + data_id + '"]');
        let item_parent = $this.closest('.ticket_item');
        if (!item_parent.hasClass('abp_active')) {
            let min_qty = parseInt(item_parent.find(`[name="item_qty[]"]`).attr('data-min'), 10) || 0;
            item_parent.find(`[name="item_qty[]"]`).val(min_qty);
        }
        let qty = get_quantity(parent, seat_type);
        if (max_qty > 0 && qty > max_qty) {
            item_parent.find('[data-checked]').trigger('abp_role_back');
            abptt_toast_msg(form.find('[name="max_qty"]').attr('data-msg'), 'warn');
        } else {
            if (target.length > 0) {
                target.slideToggle('fast');
                item_parent.toggleClass('abp_active');
            }
        }
        item_parent.find(`[name="item_qty[]"]`).trigger('change');
    });
    abptt_booking.on('change', '[name="item_qty[]"]', function (e) {
        e.preventDefault();
        let $this = $(this);
        let parent = $this.closest(".booking_area");
        let form = $this.closest("form");
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        let max_qty = parseInt(form.find('[name="max_qty"]').val());
        let qty = get_quantity(parent, seat_type);
        if (max_qty > 0 && qty > max_qty) {
            $this.val(parseInt($this.val()) - 1);
            abptt_toast_msg(form.find('[name="max_qty"]').attr('data-msg'), 'warn');
        }
        all_management($this);
    })
    abptt_booking.on('change', '.ex_price_calculate', function (e) {
        e.preventDefault();
        all_management($(this));
    });
    // Delegate to the stable plugin root so seats added by AJAX remain interactive.
    $(document).on('click', 'div.abptt_area .abptt_booking .sp_cell.available', function (e) {
        e.preventDefault();
        let current = $(this);
        current.toggleClass('selected').promise().done(function () {
            all_management(current);
        });
    });
    abptt_booking.on('click', '.book_continue', function (e) {
        e.preventDefault();
        let current = $(this);
        let form = current.closest("form");
        let parent = form.find('.booking_area');
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        if (get_quantity(parent, seat_type) > 0 ) {
            if (submit_validation(current) < 1) {
                form.find("[name='add-to-cart'], [name='add-admin-order']").first().trigger('click');
            }
        } else {
            abptt_alert(current);
        }
    });
    function all_management($this) {
        let form = $this.closest("form");
        let parent = form.find('.booking_area');
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        let total = 0;
        let qty = get_quantity(parent, seat_type);
        let total_qty = qty;
        let price = 0;
        let ex_price = 0;
        if (total_qty > 0) {
            if (qty > 0) {
                price = get_price(parent, seat_type);
                ex_price = get_additional_price(parent);
                form.find('.total_continue_area .price_up').slideDown('fast');
                parent.find('.additional_service_area').slideDown('fast');
                parent.find('.seat_selection').slideDown('fast');
            } else {
                form.find('.total_continue_area .price_up').slideUp('fast');
                form.find('.total_continue_area .ex_price_up').slideUp('fast')
                parent.find('.additional_service_area').slideUp('fast');
                parent.find('.seat_selection').slideUp('fast').find('.insert_item').html('');
            }
            form.find('.price_up .item_total').html(price > 0 ? abptt_wc_price_format(price) : abptt_infos.msg.free);
            total = price + ex_price ;
            form.find('.total_continue_area').slideDown('fast');
        } else {
            form.find('.additional_service_area').slideUp('fast');
            form.find('.total_continue_area').slideUp('fast');
            form.find('.seat_selection').slideUp('fast').find('.insert_item').html('');
        }
        attendee_management(parent, qty);
        total = total > 0 ? abptt_wc_price_format(total) : abptt_infos.msg.free;
        form.find('.abptt_total').html(total);
        //abptt_load_image();
    }
    function attendee_management(parent, qty) {
        let target = parent.find('.client_info_area');
        let item = target.find('.attendee_item');
        let count = item.length;
        if (count > 0) {
            let single_attendee = parent.closest("form").find('[name="same_attendee"]').val();
            if (single_attendee !== 'on') {
                if (qty > 0) {
                    let firstItem = item.first();
                    if (count < qty) {
                        let needed = qty - count;
                        for (let i = 0; i < needed; i++) {
                            let newItem = firstItem.clone();
                            newItem.find('input:not([type="checkbox"]):not([type="radio"]), textarea').val('');
                            newItem.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
                            newItem.find('select').prop('selectedIndex', 0);
                            target.append(newItem);
                            abptt_load_datepicker(target);
                        }
                    } else if (count > qty) {
                        item.slice(qty).remove();
                    }
                } else {
                    item.not(':first').remove();
                }
            }
        } else {
            item.not(':first').remove();
        }
    }
    function get_quantity(parent, seat_type) {
        let qty = 0;
        if (seat_type === 'sp') {
            qty = parent.find('.sp_cell.available.selected').length;
        } else {
            parent.find('.item_select').each(function () {
                let current = $(this);
                let active_property = $.trim(current.find(`[name="item_check[]"]`).val());
                let item_qty = parseInt($.trim(current.find(`[name="item_qty[]"]`).val()), 10) || 0;
                if (active_property && item_qty > 0) {
                    qty += item_qty;
                }
            });
        }
        return qty;
    }
    function get_price(parent, seat_type) {
        let total = 0;
        if (seat_type === 'sp') {
            let seat_names = [];
            let type_ids = [];
            let selection_target = parent.find('.seat_selection');
            let hidden_target = selection_target.find('.abp_hidden .delete_area');
            let target = selection_target.find('.insert_item');
            target.html('');
            parent.find('.seat_selection .insert_item').html('');
            parent.find('.sp_cell.available.selected').each(function () {
                let $this = $(this);
                let name = $.trim($this.attr('data-name'));
                let id = $.trim($this.attr('data-id'));
                if (name && id) {
                    let price = parseFloat($.trim($this.attr('data-price'))) || 0;
                    total += price;
                    seat_names.push(name);
                    type_ids.push(id);
                    let item_clone = hidden_target.clone();
                    item_clone.find('.seat_name').html(name);
                    item_clone.find('.seat_price').html(abptt_wc_price_format(price));
                    target.append(item_clone);
                }
            });
            selection_target.find('.sub_total').html(abptt_wc_price_format(total));
            parent.find(`[name="sp_selected_seat"]`).val(seat_names.join(','));
            parent.find(`[name="sp_selected_seat_id"]`).val(type_ids.join(','));
        } else {
            parent.find('.item_select').each(function () {
                let current = $(this);
                let target_qty = current.find(`[name="item_qty[]"]`);
                let active_property = $.trim(current.find(`[name="item_check[]"]`).val());
                let item_qty = parseInt($.trim(target_qty.val()), 10) || 0;
                if (active_property && item_qty > 0) {
                    let price = parseFloat($.trim(target_qty.attr('data-price'))) || 0;
                    if (price > 0) {
                        total += price * item_qty;
                    }
                }
            });
        }
        return total;
    }
    function get_additional_price(parent, r = false) {
        let form = parent.closest('form');
        let target = parent.find('.ex_price_calculate');
        let total = 0;
        if (target.length > 0) {
            let ex_qty = 0;
            target.each(function () {
                let qty = parseInt($(this).val());
                ex_qty += qty;
                let ex_price = $(this).attr('data-price');
                ex_price = ex_price && ex_price >= 0 ? ex_price : 0;
                total = total + parseFloat(ex_price) * qty;
            });
            let total_price = total > 0 ? abptt_wc_price_format(total) : abptt_infos.msg.free;
            if (ex_qty > 0) {
                form.find('.total_continue_area .ex_price_up').slideDown('fast').find('.additional_total').html(total_price);
            } else {
                form.find('.total_continue_area .ex_price_up').slideUp('fast').find('.additional_total').html(total_price);
            }
        }
        return total;
    }
    function submit_validation(current) {
        let exit = 0;
        current.closest('form').find("[required]").each(function () {
            let value = $(this).val();
            if (!value || value === ' ' || value === 'undefined' || value === '') {
                $(this).trigger('focus').addClass('abp_required');
                exit++;
            }
        });
        return exit;
    }
}(jQuery));