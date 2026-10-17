window.abptt_parent = window.abptt_parent || jQuery('div.abptt_admin');
let abptt_feature = JSON.parse(abptt_admin_data.abptt_feature);
let abptt_category = JSON.parse(abptt_admin_data.abptt_category);
let abptt_organizer = JSON.parse(abptt_admin_data.abptt_organizer);
let abptt_guide = JSON.parse(abptt_admin_data.abptt_guide);
let abptt_location = JSON.parse(abptt_admin_data.abptt_location);
let abptt_related_info = JSON.parse(abptt_admin_data.related_info);
let abptt_sp_info = JSON.parse(abptt_admin_data.sp_data);
function abptt_admin_init(target) {
    abptt_sortable(target);
    abptt_color_picker_init(target);
    //abptt_wp_editor_init(target);
    abptt_ticket_type_selection(target);
}
function abptt_sortable(target = abptt_parent) {
    let $sortable = target.find('.sortable_area:not(.abp_hidden *)');
    if ($sortable.length === 0) {
        $sortable = target.closest('.sortable_area');
    }
    if ($sortable.length > 0) {
        $sortable.sortable({
            handle: '.sortable_handle',
            stop: function (event, ui) {
                ui.item.trigger('abp_trigger');
            }
        });
    }
}
function abptt_color_picker_init(target = abptt_parent) {
    let $pickers = target.find('.abp_color_picker:not(.abp_hidden *)');
    if ($pickers.length > 0) {
        $pickers.wpColorPicker({
            change: function (event, ui) {
                setTimeout(function () {
                    jQuery(event.target).trigger('abp_trigger');
                }, 50);
            },
            clear: function (event) {
                setTimeout(function () {
                    jQuery(event.target).trigger('abp_trigger');
                }, 50);
            }
        });
    }
}
function abptt_gmap_init(target = abptt_parent) {
    if (typeof window.google === 'undefined' || typeof window.google.maps === 'undefined') return;
    target.find('.abptt_gmap_wrap:not([data-gmap-ready])').each(function () {
        let wrap = jQuery(this);
        wrap.attr('data-gmap-ready', '1');
        let canvas = wrap.find('.abptt_gmap_canvas')[0];
        let searchInput = wrap.find('.abptt_gmap_search')[0];
        let $lat = wrap.find('.abptt_gmap_lat');
        let $lng = wrap.find('.abptt_gmap_lng');
        let $place = wrap.find('.abptt_gmap_place');
        let $address = wrap.find('.abptt_gmap_address');
        let $showName = wrap.find('.abptt_gmap_show_name');
        let lat = parseFloat($lat.val()) || 23.8103;
        let lng = parseFloat($lng.val()) || 90.4125;
        let hasCoords = $lat.val() !== '' && $lng.val() !== '';
        let map = new google.maps.Map(canvas, {
            center: { lat: lat, lng: lng },
            zoom: hasCoords ? 14 : 5,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false
        });
        let marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            draggable: true,
            visible: hasCoords
        });
        function setPlace(place) {
            if (!place || !place.geometry) return;
            let loc = place.geometry.location;
            marker.setPosition(loc);
            marker.setVisible(true);
            $lat.val(loc.lat());
            $lng.val(loc.lng());
            $place.val(place.name || '');
            $address.val(place.formatted_address || '');
            if ($showName.length) $showName.text(place.name || place.formatted_address || loc.lat() + ', ' + loc.lng());
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(loc);
                map.setZoom(16);
            }
        }
        google.maps.event.addListener(marker, 'dragend', function () {
            let pos = marker.getPosition();
            $lat.val(pos.lat());
            $lng.val(pos.lng());
            $place.val('');
            $address.val('');
            if ($showName.length) $showName.text(pos.lat() + ', ' + pos.lng());
        });
        if (searchInput) {
            let autocomplete = new google.maps.places.Autocomplete(searchInput, {
                fields: ['formatted_address', 'geometry', 'name', 'place_id']
            });
            autocomplete.addListener('place_changed', function () {
                let place = autocomplete.getPlace();
                setPlace(place);
            });
        }
    });
}
function abptt_wp_editor_init(target = abptt_parent) {
    let textArea = target.find('textarea.wp-editor-area');
    if (textArea.length > 0) {
        textArea.each(function () {
            let $currentTextarea = jQuery(this);
            let dynamicId = 'editor_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
            $currentTextarea.attr('id', dynamicId);
            let $wrap = $currentTextarea.closest('.wp-editor-wrap');
            if ($wrap.length > 0) {
                $wrap.replaceWith($currentTextarea);
            }
            $currentTextarea.show();
            if (typeof wp !== 'undefined' && wp.editor) {
                wp.editor.remove(dynamicId);
                wp.editor.initialize(dynamicId, {
                    tinymce: {
                        wpautop: true,
                        cleanup: false,
                        verify_html: false,
                        entity_encoding: 'raw',
                        forced_root_block: false,
                        valid_elements: '*[*]',
                        setup: function (editor) {
                            editor.on('change keyup', function () {
                                editor.save();
                            });
                        }
                    },
                    quicktags: true,
                    mediaButtons: true
                });
            }
        });
    }
}
function abptt_ticket_type_selection(target = abptt_parent) {
    const $selects = target.find('.ticket_configuration [name="ticket_name[]"]');
    if ($selects.length > 0) {
        const selectedValues = $selects.map(function () {
            return jQuery(this).val();
        }).get().filter(value => value !== "");
        $selects.each(function () {
            const $currentSelect = jQuery(this);
            const currentValue = $currentSelect.val();
            $currentSelect.html('<option value="" selected>' + abptt_admin_data.msg.select_ticket + '</option>');
            abptt_ticket_type.forEach(function (ticket) {
                if (!selectedValues.includes(ticket.id.toString()) || ticket.id.toString() === currentValue) {
                    const $option = jQuery('<option></option>').val(ticket.id).text(ticket.label);
                    if (ticket.id.toString() === currentValue) {
                        $option.prop('selected', true);
                    }
                    $currentSelect.append($option);
                }
            });
        });
    }
}
function abptt_emoji_check(str) {
    return !(/^fa[bsrld]\s/.test(str));
}
//========== Global Function =================//
window.abptt_popup_open_global = function (action, id = '') {
    if (action) {
        jQuery('body').addClass('_stop_scroll').find('[data-popup="#abptt_global_popup"]').addClass('in').promise().done(function () {
            let parent = abptt_parent.find('[data-popup="#abptt_global_popup"]').find('.popup_area').addClass(action);
            id = id !== '' ? id : '';
            let target = parent.find('.popup_body');
            let post_id = abptt_parent.find("[name='abptt_post_id']").val() || '';
            jQuery.ajax({
                type: 'POST', url: abptt_admin_data.ajax_url, data: {
                    "action": 'abptt_add_' + action, 'id': id, 'post_id': post_id, 'nonce': abptt_admin_data.nonce
                }, beforeSend: function () {
                    abptt_spinner(parent);
                    setTimeout(function () {
                        abptt_color_picker_init(parent);
                    }, 50);
                    abptt_toast_msg(abptt_admin_data.msg.loading);
                }, success: function (response) {
                    abptt_spinner_remove(parent);
                    if (response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptt_toast_msg(response.data.msg, response.data.type);
                            abptt_init(target);
                            abptt_wp_editor_init(target);
                            abptt_gmap_init(target);
                        });
                    }
                }, error: function (xhr) {
                    abptt_ajx_error(xhr, parent);
                }
            })
        });
    }
};
window.abptt_popup_close_global = function () {
    let deferred = jQuery.Deferred();
    let target = abptt_parent.find('[data-popup="#abptt_global_popup"]');
    if (target.length > 0) {
        target.removeClass('in').promise().done(function () {
            jQuery('body').removeClass('_stop_scroll');
            target.find('.popup_area').removeClass().addClass('popup_area').find('.popup_body').html('').promise().done(function () {
                deferred.resolve(true);
            });
        });
    } else {
        deferred.resolve(true);
    }
    return deferred.promise();
};
window.abptt_save_global = function (action, $_this) {
    if (action) {
        let $this = jQuery($_this);
        let parent = $this.closest('.abp_form');
        let formData = abptt_get_form_data(parent);
        let post_page = abptt_parent.find("[name='abptt_post_id']");
        if (post_page.length > 0) {
            formData.append('post_id', post_page.val());
        }
        formData.append('nonce', abptt_admin_data.nonce);
        formData.append('action', 'abptt_save_' + action);
        jQuery.when(abptt_popup_close_global()).done(function (isClosed) {
            if (isClosed) {
                let target = abptt_parent.find('.' + action);
                jQuery.ajax({
                    type: 'POST',
                    url: abptt_admin_data.ajax_url,
                    contentType: false,
                    processData: false,
                    data: formData,
                    beforeSend: function () {
                        abptt_spinner(target);
                        abptt_toast_msg(abptt_admin_data.msg.saving);
                    },
                    success: function (response) {
                        if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                            target.html(response.data.html).promise().done(function () {
                                abptt_init(target);
                                abptt_wp_editor_init(target);
                            });
                        }
                        if (response.data.hasOwnProperty('js')) {
                            if (action === 'ticket_type') {
                                abptt_ticket_type = response.data.js;
                                abptt_sp_init();
                                abptt_ticket_type_selection();
                            }
                            if (action === 'decor_item') {
                                abptt_decor_item = response.data.js;
                                abptt_sp_init();
                            }
                            if (action === 'option_feature') {
                                abptt_feature = response.data.js;
                                new ABPTT_Multi_Selection('div.abptt_admin .post_feature', abptt_feature);
                                new ABPTT_Multi_Selection('div.abptt_admin .post_feature_excluded', abptt_feature);
                            }
                            if (action === 'tax_category') {
                                abptt_category = response.data.js;
                                new ABPTT_Multi_Selection('div.abptt_admin .abptt_category', abptt_category);
                            }
                            if (action === 'tax_organizer') {
                                abptt_organizer = response.data.js;
                                new ABPTT_Multi_Selection('div.abptt_admin .abptt_organizer', abptt_organizer);
                            }
                            if (action === 'tax_guide') {
                                abptt_guide = response.data.js;
                                new ABPTT_Multi_Selection('div.abptt_admin .post_guide', abptt_guide);
                            }
if (action === 'tax_location') {
                                  abptt_location = response.data.js;
                                  new ABPTT_Multi_Selection('div.abptt_admin .abptt_location', abptt_location);
                                  abptt_init_place_rows();
                              }
                        }
                        abptt_spinner_remove(target);
                        abptt_toast_msg(response.data.msg, response.data.type);
                    },
                    error: function (xhr) {
                        abptt_ajx_error(xhr, target);
                    }
                });
            }
        });
    }
};
window.abptt_delete_global = function (action, id = '') {
    if (confirm(abptt_admin_data.msg.confirm_delete + ' \n\n' + abptt_admin_data.msg.confirm_ok + ' \n ' + abptt_admin_data.msg.confirm_cancel)) {
        if (action && id) {
            let target = abptt_parent.find('.' + action);
            jQuery.ajax({
                type: 'POST', url: abptt_admin_data.ajax_url, data: {
                    "action": 'abptt_delete_' + action, 'id': id, 'nonce': abptt_admin_data.nonce
                }, beforeSend: function () {
                    abptt_spinner(target);
                    abptt_toast_msg(abptt_admin_data.msg.deleting, 'error');
                }, success: function (response) {
                    if (response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptt_init(target);
                        });
                    }
                    if (response.data.hasOwnProperty('js')) {
                        if (action === 'ticket_type') {
                            abptt_ticket_type = response.data.js;
                            abptt_sp_init();
                        }
                        if (action === 'decor_item') {
                            abptt_decor_item = response.data.js;
                            abptt_sp_init();
                        }
                    }
                    abptt_toast_msg(response.data.msg, response.data.type);
                    abptt_spinner_remove(target);
                }, error: function (xhr) {
                    abptt_ajx_error(xhr, target);
                }
            })
        }
    }
};
window.abptt_post_action = function (action, id) {
    id = id !== '' ? parseInt(id) : '';
    if (action && !isNaN(id) && id !== '') {
        let parent = abptt_parent.find('.abptt_posts')
        jQuery.ajax({
            type: 'POST', url: abptt_admin_data.ajax_url, data: {
                "action": 'abptt_post_' + action, 'post_id': id, 'nonce': abptt_admin_data.nonce
            }, beforeSend: function () {
                abptt_spinner(parent);
                abptt_toast_msg((abptt_admin_data.msg[action] ? abptt_admin_data.msg[action] : abptt_admin_data.msg.loading), 'warn');
            }, success: function (response) {
                abptt_spinner_remove(parent);
                abptt_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }, error: function (xhr) {
                abptt_ajx_error(xhr, parent);
            }
        });
    }
};
window.abptt_import_global = function (action, btn) {
    if (action) {
        if (action === 'remove_dummy' && !window.confirm('This will remove ALL ABP Tour and Travel data: every tour, category, location, operator, guide and all plugin settings. This cannot be undone. Continue?')) {
            return;
        }
        let target = abptt_parent.find('.' + action);
        if (action === 'dummy' || action === 'remove_dummy') {
            target = btn ? jQuery(btn).closest('.dash_card, .abp_status') : abptt_parent.find('.dash_card, .abp_status').first();
        }
        jQuery.ajax({
            type: 'POST', url: abptt_admin_data.ajax_url, data: {
                "action": 'abptt_import_' + action, 'nonce': abptt_admin_data.nonce
            }, beforeSend: function () {
                abptt_spinner(target);
                abptt_toast_msg((abptt_admin_data.msg[action] ? abptt_admin_data.msg[action] : abptt_admin_data.msg.loading));
            }, success: function (response) {
                abptt_spinner_remove(target);
                abptt_toast_msg(response.data.msg, response.data.type);
                if (action === 'dummy' || action === 'remove_dummy') {
                    window.location.reload();
                } else {
                    if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptt_init(target);
                            setTimeout(function () {
                                abptt_color_picker_init(target);
                                abptt_wp_editor_init(target);
                            }, 50);
                        });
                    }
                }
            }, error: function (xhr) {
                abptt_ajx_error(xhr, target);
            }
        });
    }
};
window.abptt_create_page = function (page_type, btn) {
    if (page_type) {
        let parent = btn ? jQuery(btn).closest('.dash_card, .abp_status') : abptt_parent.find('.dash_card, .abp_status').first();
        jQuery.ajax({
            type: 'POST', url: abptt_admin_data.ajax_url, data: {
                "action": "abptt_create_page", 'nonce': abptt_admin_data.nonce, 'type': page_type
            }, beforeSend: function () {
                abptt_spinner(parent);
                abptt_toast_msg(abptt_admin_data.msg.create_post_page);
            }, success: function (response) {
                abptt_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }, error: function (xhr) {
                abptt_ajx_error(xhr, parent);
            }
        });
    }
};
window.abptt_wc_config = function (page_type, btn) {
    if (page_type) {
        let parent = btn ? jQuery(btn).closest('.dash_card, .abp_status') : abptt_parent.find('.dash_card, .abp_status').first();
        jQuery.ajax({
            type: 'POST', url: abptt_admin_data.ajax_url, data: {
                "action": "abptt_wc_config", 'nonce': abptt_admin_data.nonce, 'type': page_type
            }, beforeSend: function () {
                abptt_spinner(parent);
                abptt_toast_msg((abptt_admin_data.msg[page_type] ? abptt_admin_data.msg[page_type] : abptt_admin_data.msg.loading));
            }, success: function (response) {
                if (response.data && response.data.hasOwnProperty('msg')) {
                    abptt_toast_msg(response.data.msg, response.data.type);
                }
                window.location.reload();
            }, error: function (xhr) {
                abptt_ajx_error(xhr, parent);
            }
        });
    }
};
//==================image selection========================//
let abptt_media_uploader;
window.abptt_image_remove = function ($this) {
    $this = jQuery($this);
    let parent = $this.closest('.image_selection');
    if (parent && parent.length > 0) {
        parent.find('input').val('');
        parent.find('img').attr('src', '');
        parent.find('button').slideDown('fast');
        parent.find('.image_item').slideUp('fast');
        let id = parent.find('input').attr('data-target');
        let target = jQuery(id);
        if (target && target.length > 0) {
            jQuery(target).css('background-image', '');
        }
    }
};
window.abptt_image_selection = function ($this) {
    $this = jQuery($this);
    if (!abptt_media_uploader) {
        abptt_media_uploader = wp.media({
            multiple: false
        });
        abptt_media_uploader.on('select', function () {
            let attachment = abptt_media_uploader.state().get('selection').first().toJSON();
            let parent = abptt_media_uploader.current_target;
            if (parent && parent.length > 0) {
                parent.find('input').val(attachment.id);
                parent.find('img').attr('src', attachment.url);
                parent.find('button').slideUp('fast');
                parent.find('.image_item').slideDown('fast');
                let id = parent.find('input').attr('data-target');
                let target = jQuery(id);
                if (target && target.length > 0) {
                    jQuery(target).css('background-image', `url(${attachment.url})`);
                }
            }
        });
    }
    abptt_media_uploader.current_target = $this.closest('.image_selection');
    abptt_media_uploader.open();
};
//==========Post Pagination=================//

(function ($) {
    "use strict";
    $(document).on('click', 'div.abptt_area .post_list .pagination_area button[data-page]', function () {
        let $this = jQuery(this);
        if (!$this.hasClass('abp_active')) {
            let parent = $this.closest('.abptt_posts');
            let target = parent.find('.post_list');
            let filter_args = {};
            if (parent.find("[name='select_hidden_post_status']").length > 0) {
                filter_args['status'] = parent.find("[name='select_hidden_post_status']").val();
            }
            filter_args['page_number'] = parseInt($this.attr('data-page'));
            if (parent.find("[name='page_item']").length > 0) {
                filter_args['page_item'] = parseInt(parent.find("[name='page_item']").val());
            }
            if (target.length > 0) {
                jQuery.ajax({
                    type: 'POST', url: abptt_admin_data.ajax_url, data: {
                        "action": "abptt_reload_post_list", "filter_args": filter_args, 'nonce': abptt_admin_data.nonce
                    }, beforeSend: function () {
                        abptt_spinner(parent);
                        abptt_toast_msg(abptt_admin_data.msg.post_loading);
                    }, success: function (response) {
                        if (response.data && response.data.hasOwnProperty('html')) {
                            target.html(response.data.html);
                        }
                        abptt_spinner_remove(parent);
                        abptt_toast_msg(response.data.msg, response.data.type);
                    }, error: function (xhr) {
                        abptt_ajx_error(xhr, parent);
                    }
                });
            } else {
                parent.find('.post_tab').trigger('click');
            }
        }
    });
    //==========ticket config=================//
    abptt_parent.on('abp_trigger', '.ticket_configuration .add_new_hook', function () {
        abptt_ticket_type_selection();
    });
    abptt_parent.on('abp_trigger', '.ticket_configuration.configuration_content', function () {
        load_total($(this));
        load_price($(this));
    });
    abptt_parent.on('change', '.ticket_configuration [name="ticket_name[]"] ', function () {
        abptt_ticket_type_selection();
    });
    abptt_parent.on('change', '.abptt_ticket [name="seat_type"]', function () {
        load_ticket_type($(this));
    });
    abptt_parent.on('abp_trigger', '.abptt_ticket [name="display_ticket_type"]', function () {
        load_ticket_type($(this));
    });
    abptt_parent.on('change', '.sp_selection_area [name="sp_id[]"]', function () {
        let sp_id = $(this).val();
        let parent = $(this).closest('tr');
        let grand_parent = $(this).closest('.ticket_configuration');
        let $targetContainer = parent.find('.ticket_type_details');
        $targetContainer.empty();
        parent.find('.row_total').html('');
        if (!sp_id || !abptt_sp_info[sp_id]) {
            return;
        }
        let total_seat = 0;
        let ticketTypes = abptt_sp_info[sp_id];
        let html = '<div class="_gap_xs_f_wrap">';
        Object.keys(ticketTypes).forEach(function (key) {
            let item = ticketTypes[key];
            let label = item.label || '';
            let color = item.color || '#333';
            let icon = item.icon || '';
            let img = item.img || '';
            let icon_emoji = '';
            if (img && $.isNumeric(icon) && icon > 0) {
                icon_emoji = '<div class="abp_image"><img class="_img_control"  src="' + img + '" alt="#"></div>';
            } else {
                if (abptt_emoji_check(icon)) {
                    icon_emoji = '<span>' + icon + '</span>';
                } else {
                    icon_emoji = '<span class="' + icon + '"></span>';
                }
            }
            let seatCount = parseInt(item.seat, 10) || 0;
            total_seat = total_seat + seatCount;
            if (label !== '') {
                html += `<div class="abp_tag">`;
                html += `<span class="abp_gap_xxs" style="color: ${color}">`;
                if (icon_emoji !== '') {
                    html += ` ${icon_emoji}`;
                }
                html += ` ${label}`;
                html += `</h6>`;
                html += `<span class="_color_theme"> ( ${seatCount}</span> ) `;
                html += `</div>`;
            }
        });
        html += '</div>';
        parent.find('.row_total').html(total_seat);
        parent.find('.sp_id_change').attr('onclick', "abptt_popup_open_global('view_sp','" + sp_id + "')");
        $targetContainer.html(html);
        load_total(grand_parent);
        load_price(grand_parent);
    });
    function load_total(grand_parent) {
        let total_seat_count = 0;
        grand_parent.find('.row_total').each(function () {
            total_seat_count = total_seat_count + parseInt($(this).html(), 0);
        }).promise().done(function () {
            grand_parent.find('.total_ticket').html(total_seat_count);
        });
    }
    function load_ticket_type($this) {
        let parent = $this.closest('.abptt_ticket');
        let type = abptt_parent.find("[name='seat_type']").val();
        let display_ticket_type = abptt_parent.find("[name='display_ticket_type']").val();
        let target = parent.find('.ticket_configuration');
        let post_id = abptt_parent.find("[name='abptt_post_id']").val();
        $.ajax({
            type: 'POST', url: abptt_admin_data.ajax_url, data: {
                "action": 'abptt_type_switch', 'type': type, 'display_ticket_type': display_ticket_type, 'post_id': post_id, 'nonce': abptt_admin_data.nonce
            }, beforeSend: function () {
                abptt_spinner(parent);
                abptt_toast_msg(abptt_admin_data.msg.type_switch);
            }, success: function (response) {
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptt_spinner_remove(parent);
                abptt_toast_msg(response.data.msg, response.data.type);
            }, error: function (xhr) {
                abptt_spinner_remove(parent);
                if (xhr.response && xhr.response.data) {
                    abptt_toast_msg(xhr.response.data.msg, xhr.response.data.type);
                }
            }
        });
    }
    function load_price(parent) {
        let ticketTypesMap = {};
        let exit_ticket_types = {};
        let display_ticket_type = abptt_parent.find("[name='display_ticket_type']").val();
        if (display_ticket_type === 'on') {
            parent.find('.ticket_selection_area tr').each(function () {
                let ticket_val = $(this).find('[name="ticket_name[]"]').val();
                let price_val = $(this).find('[name="ticket_price[]"]').val();
                if (ticket_val) {
                    exit_ticket_types[ticket_val] = price_val || 0;
                }
            });
            parent.find('[name="sp_id[]"]').each(function () {
                let sp_id = $(this).val();
                if (sp_id && abptt_sp_info[sp_id]) {
                    let ticket_ids = Object.keys(abptt_sp_info[sp_id]);
                    ticket_ids.forEach(ticket_id => {
                        if (!ticketTypesMap[ticket_id]) {
                            let item = abptt_sp_info[sp_id][ticket_id];
                            ticketTypesMap[ticket_id] = {
                                id: ticket_id,
                                label: (item && item.label) ? item.label : ticket_id,
                                icon: (item && item.icon) ? item.icon + ' ' : ''
                            };
                        }
                    });
                }
            });
            let html = '';
            Object.values(ticketTypesMap).forEach(function (ticket) {
                let price = (exit_ticket_types[ticket.id] !== undefined) ? exit_ticket_types[ticket.id] : 0;
                html += `<tr><th>${ticket.label}<input type="hidden" name="ticket_name[]" value="${ticket.id}"/></th>
                                        <td><label><input data-required type="number" class="_form_control validation_price" name="ticket_price[]" placeholder="EX: 15" value="${price}"/></label></td></tr>`;
            });
            parent.find('.ticket_selection_area').html(html);
        }
    }
    //==========Orders list=================//
    abptt_parent.on('submit', 'div.abptt_orders form.abp_search_form', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.abptt_orders');
        let target = parent.find('.order_list');
        let formData = new FormData(this);
        if (parent.find('[data-page].abp_active').length > 0) {
            formData.append('page_number', parseInt(parent.find('[data-page].abp_active').attr('data-page')));
        }
        formData.append('page_item', parseInt(parent.find("[name='page_item']").val()));
        formData.append('status', parent.find('.order_status_menu [data-status].abp_active').attr('data-status'));
        formData.append('action', 'abptt_load_order_list');
        formData.append('nonce', abptt_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptt_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptt_spinner(parent);
                abptt_toast_msg(abptt_admin_data.msg.order_loading);
            },
            success: function (response) {
                abptt_spinner_remove(parent);
                if (response.data) {
                    if (response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptt_init(target);
                        });
                    }
                    abptt_toast_msg(response.data.msg, response.data.type);
                }
            }, error: function (xhr) {
                abptt_ajx_error(xhr, parent);
            }
        });
    });
    $(document).on('click', 'div.abptt_area div.abptt_orders .order_status_menu button[data-status]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            $this.closest('.order_status_menu').find('[data-status].abp_active').removeClass('abp_active').promise().done(function () {
                $this.addClass('abp_active').promise().done(function () {
                    $this.closest('.abptt_orders').find('form.abp_search_form').submit();
                });
            });
        }
    });
    $(document).on('click', 'div.abptt_area div.abptt_orders button.item_cancel', function () {
        let $this = $(this);
        let parent = $(this).closest('.abptt_orders');
        let item_id = $this.attr('data-item_id');
        if (confirm(abptt_admin_data.msg.confirm_delete + ' \n\n' + abptt_admin_data.msg.confirm_ok + ' \n ' + abptt_admin_data.msg.confirm_cancel)) {
            $.ajax({
                type: 'POST', url: abptt_admin_data.ajax_url, data: {
                    "action": "abptt_item_cancel", 'item_id': item_id, 'nonce': abptt_admin_data.nonce
                }, beforeSend: function () {
                    abptt_spinner(parent);
                    abptt_toast_msg(abptt_admin_data.msg.deleting, 'error');
                }, success: function (response) {
                    abptt_spinner_remove(parent);
                    if (response.data) {
                        abptt_toast_msg(response.data.msg, response.data.type);
                    }
                    $this.closest('.abptt_orders').find('form.abp_search_form').submit();
                }, error: function (xhr) {
                    abptt_ajx_error(xhr, parent);
                }
            });
        }
    });
    $(document).on('click', 'div.abptt_area div.abptt_orders .order_list .pagination_area button[data-page]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            let parent = $(this).closest('.order_list');
            parent.find('[data-page].abp_active').removeClass('abp_active').promise().done(function () {
                $this.addClass('abp_active').promise().done(function () {
                    $this.closest('.abptt_orders').find('form.abp_search_form').submit();
                });
            });
        }
    });
}(jQuery));
//==============Empty title check /image selection/add_new_delete============================//
(function ($) {
    'use strict';
    $(document).ready(function () {
        new ABPTT_Multi_Selection('div.abptt_admin .post_feature', abptt_feature);
        new ABPTT_Multi_Selection('div.abptt_admin .post_feature_excluded', abptt_feature);
        new ABPTT_Multi_Selection('div.abptt_admin .related_item', abptt_related_info);
        new ABPTT_Multi_Selection('div.abptt_admin .abptt_category', abptt_category);
        new ABPTT_Multi_Selection('div.abptt_admin .abptt_organizer', abptt_organizer);
        new ABPTT_Multi_Selection('div.abptt_admin .post_guide', abptt_guide);
        new ABPTT_Multi_Selection('div.abptt_admin .abptt_location', abptt_location);
        abptt_init_place_rows();
        //=========Color Picker==============//
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.wp-picker-container').length) {
                $('.wp-picker-container.wp-picker-active').find('.wp-color-result').trigger('click');
            }
        });
    });
    //========= Empty title check==============//
    $(document).on('click', '#publish, .editor-post-publish-button', function (e) {
        let hasPostIdInput = $('input[name="abptt_post_id"]').length > 0;
        if (hasPostIdInput) {
            let title = $('#title').val() || $('.editor-post-title__input').val();
            if (!title || title.trim().length === 0) {
                alert('Title empty! Please enter a title before updating.');
                e.preventDefault();
                return false;
            }
        }
    });
    //==================image selection========================//
    $(document).on('click', 'div.abptt_admin .add_image_multi', function () {
        let parent = $(this).closest('.multiple_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="multiple_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times _circle_icon_xs remove_image_multi"></span>';
            html += '<img class="_img_control" src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.find('.multiple_image').append(html);
            let value = parent.find('.multiple_image_ids').val();
            value = value ? value + ',' + attachment_id : attachment_id;
            parent.find('.multiple_image_ids').val(value);
        }
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on('click', 'div.abptt_admin .remove_image_multi', function () {
        let parent = $(this).closest('.multiple_image_area');
        let current_parent = $(this).closest('.multiple_image_item');
        let img_id = current_parent.data('image-id');
        current_parent.remove();
        let all_img_ids = parent.find('.multiple_image_ids').val();
        all_img_ids = all_img_ids.replace(',' + img_id, '')
        all_img_ids = all_img_ids.replace(img_id + ',', '')
        all_img_ids = all_img_ids.replace(img_id, '')
        parent.find('.multiple_image_ids').val(all_img_ids);
    });
    $(document).on('click', 'div.abptt_admin .icon_image_selection_area .icon_delete', function () {
        let parent = $(this).closest('.icon_image_selection_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('[data-add-icon]').removeAttr('class');
        parent.find('.icon_item').slideUp('fast');
        parent.find('.image_icon_select_area').slideDown('fast');
    });
    $(document).on('click', 'div.abptt_admin button.image_select', function () {
        let $this = $(this);
        let parent = $this.closest('.icon_image_selection_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            parent.find('input[type="hidden"]').val(attachment_id);
            parent.find('.icon_item').slideUp('fast');
            parent.find('img').attr('src', attachment_url);
            parent.find('.image_item').slideDown('fast');
            parent.find('.image_icon_select_area').slideUp('fast');
        }
        wp.media.editor.open($this);
        return false;
    });
    $(document).on('click', 'div.abptt_admin .icon_image_selection_area .image_delete', function () {
        let parent = $(this).closest('.icon_image_selection_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('img').attr('src', '');
        parent.find('.image_item').slideUp('fast');
        parent.find('.image_icon_select_area').slideDown('fast');
    });
    //=========add_new_delete ==============//
    $(document).on('click', 'div.abptt_area .delete_hook', function () {
        if (confirm(abptt_admin_data.msg.confirm_delete + ' \n\n' + abptt_admin_data.msg.confirm_ok + ' \n ' + abptt_admin_data.msg.confirm_cancel)) {
            let deleteArea = $(this).closest('.delete_area');
            let parent = $(this).closest('.configuration_content');
            deleteArea.slideUp(250, function () {
                $(this).remove();
                if (parent.find('.insertable_area .delete_area').length === 0) {
                    parent.find('.hide_on_load').slideUp(250);
                }
                abptt_toast_msg(abptt_admin_data.msg.delete_success);
                parent.trigger('abp_trigger');
            });
        }
    });
    $(document).on('click', 'div.abptt_area .add_new_hook', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let parent = $(this).closest('.configuration_content');
        let target_element = $(this).next('.abp_hidden');
        if (target_element.length === 0) {
            target_element = parent.children('.abp_hidden');
        }
        if (target_element.length === 0) {
            target_element = parent.find('.abp_hidden').first();
        }
        let item_html = target_element.find('.hidden_content').html();
        if (!item_html || item_html === "undefined" || item_html === " ") {
            target_element = parent.find('.abp_hidden').first();
            item_html = target_element.find('.hidden_content').html();
        }
        let $item = $(item_html);
        let unique_id = 'abp_' + Date.now();
        if (target_element.attr('data-hidden_id') !== undefined) {
            if (item_html && item_html !== "undefined" && item_html.trim() !== "") {
                let current_id = $item.find('.hidden_id').val();
                $item.find('.hidden_id').val(unique_id);
                $item.find('input, select, textarea').each(function () {
                    let current_name = $(this).attr('name');
                    if (current_name && current_id) {
                        let regex = new RegExp(current_id, 'g');
                        let new_name = current_name.replace(regex, unique_id);
                        $(this).attr('name', new_name);
                    }
                });
            }
        }
        let $insertable_area = parent.find('.insertable_area').first();
        $insertable_area.append($item);
        let target = $item.hasClass('delete_area') ? $item : $item.find('.delete_area');
        if (target.length === 0) {
            target = $item;
        }
        target.find('.edit_area').slideDown('fast');
        parent.find('.hide_on_load').slideDown('fast');
        abptt_init(target);
        setTimeout(function () {
            abptt_color_picker_init(target);
            abptt_wp_editor_init(target);
        }, 50);
        $(this).trigger('abp_trigger');
    });
    $(document).on('click', 'div.abptt_area .edit_hook', function () {
        $(this).closest('.delete_area').toggleClass('active').find('.edit_area').slideToggle('fast');
        //$(this).closest('.delete_area').find('.edit_area').slideToggle('fast');
    });
    $(document).on('keyup change', 'div.abptt_area [data-pass]', function () {
        let input_value = $(this).val();
        let input_id = $(this).attr('data-pass');
        $(this).closest('.delete_area').find("[data-paste='" + input_id + "']").each(function () {
            $(this).html(input_value);
        });
    });
})(jQuery);
//=================select icon=========================//
(function ($) {
    'use strict';
    let abptt_target_popup = $(document).find('div.abptt_admin .popup_icon');
    let abptt_category_list = abptt_target_popup.find('.dropdown_list');
    let abptt_search_field = abptt_target_popup.find('.abp_dropdown .abp_icon_search');
    let abptt_icon_title = abptt_target_popup.find('.item_icon_title');
    let abptt_icon_area = abptt_target_popup.find('.item_icon_area');
    let abptt_item_loader = abptt_target_popup.find('.item_loader');
    let search_result_icon = [];
    let total_icon = 0;
    let abptt_json_icon = [];
    $.getJSON(abptt_admin_data.icon_url, function (data) {
        abptt_json_icon = data;
        load_icon_category_list();
    }).fail(function () {
        abptt_icon_area.html('Nothing Found !');
    });
    $(document).on('click', 'div.abptt_admin .icon_image_selection_area button.icon_add', function () {
        load_icon_list();
    });
    $(document).on('abp_trigger', 'div.abptt_admin .abp_dropdown .abp_icon_search_hidden', function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptt_search_field.keyup(function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptt_search_field.change(function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptt_target_popup.find('.popup_close').click(function () {
        abptt_search_field.val('').trigger('change');
        abptt_target_popup.find('.icon_item').removeClass('abp_active');
    });
    abptt_target_popup.on('click', '.icon_item', function () {
        let parent = $('[data-active-popup]').closest('.icon_image_selection_area');
        let icon_class = $(this).data('icon-class');
        if (icon_class) {
            parent.find('input[type="hidden"]').val(icon_class);
            parent.find('.image_icon_select_area').slideUp('fast');
            parent.find('.image_item').slideUp('fast');
            parent.find('.icon_item').slideDown('fast');
            if (abptt_emoji_check(icon_class)) {
                parent.find('[data-add-icon]').removeAttr('class').html(icon_class);
            } else {
                parent.find('[data-add-icon]').removeAttr('class').addClass(icon_class).html('');
            }
            abptt_target_popup.find('.icon_item').removeClass('abp_active');
            abptt_target_popup.find('.popup_close').trigger('click');
        }
    });
    // ─── get search icon array / initial array───────────
    function get_icon_array() {
        let pool = [];
        let search_value = abptt_search_field.val().toLowerCase().trim();
        if (search_value) {
            $.each(abptt_json_icon, function (i, group) {
                if (group.category.toLowerCase().includes(search_value)) {
                    $.each(group.icons, function (iconKey, iconLabel) {
                        let match = iconLabel.match(/#(.*?)#/);
                        let finalLabel = match ? match[1] : iconLabel;
                        pool.push({key: iconKey, label: finalLabel});
                    });
                    return pool;
                } else {
                    if (i !== 0) {
                        $.each(group.icons, function (iconKey, iconLabel) {
                            if (iconLabel.toLowerCase().includes(search_value)) {
                                let match = iconLabel.match(/#(.*?)#/);
                                let finalLabel = match ? match[1] : iconLabel;
                                pool.push({key: iconKey, label: finalLabel});
                            }
                        });
                    }
                }
            });
        } else {
            let group = abptt_json_icon[0];
            if (!group) return [];
            $.each(group.icons, function (iconKey, iconLabel) {
                pool.push({key: iconKey, label: iconLabel});
            });
        }
        return pool;
    }
    // ─── load input category ───────────
    function load_icon_category_list() {
        let category_list = $('<ul>').addClass('abp');
        $.each(abptt_json_icon, function (i, group) {
            let current_count = Object.keys(group.icons).length;
            if (i !== 0) {
                total_icon += current_count;
            }
            let text = group.category;
            let category_li = $('<li>').attr('data-value', text).attr('data-text', text);
            $('<span>').addClass('_mar_r_xxs').text(group.emoji).appendTo(category_li);
            $('<span>').text(text).appendTo(category_li);
            $('<span>').text('( ' + current_count + ' )').appendTo(category_li);
            category_li.appendTo(category_list);
        });
        category_list.appendTo(abptt_category_list);
        abptt_spinner(abptt_item_loader);
    }
    function load_icon_list() {
        abptt_icon_area.empty();
        search_result_icon = get_icon_array();
        if (search_result_icon.length === 0) {
            abptt_icon_area.html('Nothing Found !');
            updateCount();
            return;
        }
        $.each(search_result_icon, function (i, item) {
            let $item = $('<div>').addClass('icon_item').attr('title', item.label).attr('data-icon-class', item.key);
            let $preview;
            if (abptt_emoji_check(item.key)) {
                $preview = $('<span>').text(item.key);
            } else {
                $preview = $('<span>').addClass(item.key);
            }
            $item.append($preview);
            $item.append($('<i>').text(item.label));
            $item.appendTo(abptt_icon_area);
        });
        updateCount();
    }
    function updateCount() {
        let search_value = abptt_search_field.val();
        search_value = search_value ? search_value : 'Selected Icon'
        abptt_icon_title.text(search_value + ' : ' + search_result_icon.length + ' / ' + total_icon + ' icons');
    }
})(jQuery);
//=========== Multi selection start=================//
class ABPTT_Multi_Selection {
    constructor(parentSelector, dataSource) {
        this.parent = document.querySelector(parentSelector);
        if (!this.parent) return;
        // One instance per container. Two live instances over the same DOM would each apply
        // every click, duplicating chips and rows, so hand the slot over to this one.
        const previous = this.parent.__abpttMultiSelection;
        if (previous) {
            previous.destroy();
        }
        this.parent.__abpttMultiSelection = this;
        this.dataSource = dataSource;
        this.hiddenInput = this.parent.querySelector('.selected_area input[type="hidden"]');
        this.selectedList = this.parent.querySelector('.selected_list');
        this.init();
    }
    init() {
        this.searchEl = this.parent.querySelector('.item_search');
        this.featureListEl = this.parent.querySelector('.selection_list');
        this.loadPreSelected();
        this.bindEvents();
        this.render();
    }
    bindEvents() {
        if (this.searchEl) {
            ['focusin', 'click'].forEach(eventType => {
                this.searchEl.addEventListener(eventType, (e) => {
                    e.stopPropagation();
                    this.featureListEl?.classList.add('active');
                });
            });
            this.searchEl.addEventListener('input', () => this.render());
        }
        // Kept as a stable reference so it can be removed again: this listener lives on
        // document and outlives the element it belongs to, so a re-rendered meta box would
        // otherwise leave a dead instance listening for the rest of the page's life.
        this._onDocClick = (e) => {
            if (!this.parent || !document.contains(this.parent)) {
                this.destroy();
                return;
            }
            if (!e.target.closest(this.parent.className.split(' ').map(c => '.' + c).join(''))) {
                this.featureListEl?.classList.remove('active');
            }
        };
        document.addEventListener('click', this._onDocClick);
    }
    destroy() {
        if (this._onDocClick) {
            document.removeEventListener('click', this._onDocClick);
            this._onDocClick = null;
        }
        if (this.parent && this.parent.__abpttMultiSelection === this) {
            delete this.parent.__abpttMultiSelection;
        }
        if (this.parent && this.parent.__abpttPlacePicker === this) {
            delete this.parent.__abpttPlacePicker;
        }
    }
    loadPreSelected() {
        if (!this.hiddenInput || !this.selectedList) return;
        let hiddenVal = this.hiddenInput.value;
        let preIds = hiddenVal ? hiddenVal.split(',').map(s => s.trim()).filter(Boolean) : [];
        if (preIds.length > 0) {
            this.selectedList.innerHTML = '';
            preIds.forEach(id => {
                let f = this.dataSource.find(x => String(x.id) === String(id));
                if (!f) return;
                this.appendSelectedItem(f);
            });
        }
    }
    getSelectedIds() {
        let ids = [];
        this.parent.querySelectorAll('.selected_item').forEach(el => {
            let id = el.getAttribute('data-id');
            if (id) ids.push(id);
        });
        return ids;
    }
    render() {
        if (!this.featureListEl) return;
        let q = this.searchEl ? this.searchEl.value.toLowerCase() : '';
        let selectedIds = this.getSelectedIds();
        let available = this.dataSource.filter(f => {
            return selectedIds.indexOf(String(f.id)) === -1 && f.label.toLowerCase().indexOf(q) !== -1;
        });
        if (available.length === 0) {
            this.featureListEl.innerHTML = `<div class="item_empty">${abptt_admin_data.msg.no_item}</div>`;
            return;
        }
        this.featureListEl.innerHTML = available.map(f => {
            let icon_text = abptt_emoji_check(f.icon) ? `<span>${f.icon}</span>` : `<span class="${f.icon}"></span>`;
            let label = f.value ? f.label + '-' + f.value : f.label;
            return `
                <div class="selection_item" data-id="${f.id}">
                    <div class="_gap_xxs">${icon_text}${label}</div>
                    <span class="fa-solid fa-plus fs-add"></span>
                </div>
            `;
        }).join('');
        this.featureListEl.querySelectorAll('.selection_item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectItem(item.getAttribute('data-id'));
            });
        });
    }
    selectItem(id) {
        let f = this.dataSource.find(x => String(x.id) === String(id));
        if (!f) return;
        let placeholder = this.selectedList.querySelector('.item_empty');
        if (placeholder) placeholder.remove();
        this.appendSelectedItem(f);
        this.updateHiddenField();
        this.render();
        setTimeout(() => {
            this.featureListEl?.classList.add('active');
        }, 0);
    }
    appendSelectedItem(f) {
        let div = document.createElement('div');
        div.className = 'selected_item';
        div.setAttribute('data-id', f.id);
        let icon_text = abptt_emoji_check(f.icon) ? `<span>${f.icon}</span>` : `<i class="${f.icon}"></i>`;
        let label = f.value ? f.label + '-' + f.value : f.label;
        div.innerHTML = `
            <div class="_gap_xs">${icon_text}${label}</div>
            <span class="item_remove">❌</span>
        `;
        div.querySelector('.item_remove').addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeItem(f.id);
        });
        this.selectedList.appendChild(div);
    }
    removeItem(id) {
        let item = this.selectedList.querySelector(`.selected_item[data-id="${id}"]`);
        if (item) item.remove();
        if (!this.selectedList.querySelector('.selected_item')) {
            this.selectedList.innerHTML = `<div class="item_empty">${abptt_admin_data.msg.no_item_selected}</div>`;
        }
        this.updateHiddenField();
        this.render();
    }
    updateHiddenField() {
        if (this.hiddenInput) {
            this.hiddenInput.value = this.getSelectedIds().join(',');
        }
    }
}
//===Places You'll See: keep the multi-selection chips and the per-place
//    description editors (each one is a real TinyMCE instance) in sync.===========//
function abptt_init_place_rows() {
    // Resolved explicitly: this function sits at file scope, where a bare `$` is not
    // guaranteed to be bound.
    const $ = window.jQuery;
    let parentEl = document.querySelector('div.abptt_admin .abptt_place');
    if (!parentEl) return;
    // The row list is a sibling of the picker inside .configuration_content, so it has to be
    // resolved from that node. abptt_parent.find() must not be used here: abptt_parent is the
    // div.abptt_admin element itself and jQuery never matches its own context node as an
    // ancestor, so 'div.abptt_admin .abptt_place' returned nothing and this function bailed
    // out before the picker was ever built.
    let config = parentEl.closest('.configuration_content');
    if (!config) return;
    let list = config.querySelector('.place_you_see_list');
    let template = config.querySelector('.hidden_content .place_you_see_item');
    if (!list || !template) return;
    let chipsOf = () => Array.prototype.slice.call(parentEl.querySelectorAll('.selected_list .selected_item'));
    let hasChip = (id) => !!parentEl.querySelector('.selected_list .selected_item[data-id="' + id + '"]');
    let labelFor = (id) => {
        let f = (abptt_location || []).find(x => String(x.id) === String(id));
        return f ? f.label : '';
    };
    let addRow = (id) => {
        let $row = $(template.cloneNode(true));
        $row.addClass('active');
        $row.attr('data-place-id', id);
        $row.find('[name="place_you_see_id[]"]').val(id);
        let name = labelFor(id);
        $row.find('.place_you_see_head h6').text(name);
        $row.find('input._form_control').val(name);
        list.appendChild($row[0]);
        abptt_init($row);
        $row.find('.edit_area').slideDown('fast');
        setTimeout(function () {
            abptt_wp_editor_init($row);
        }, 50);
    };
    let sync = () => {
        Array.prototype.slice.call(list.querySelectorAll('.place_you_see_item')).forEach(function (row) {
            let id = String(row.getAttribute('data-place-id') || '');
            if (id && !hasChip(id)) {
                row.remove();
            }
        });
        chipsOf().forEach(function (chip) {
            let id = String(chip.getAttribute('data-id') || '');
            if (id && !list.querySelector('.place_you_see_item[data-place-id="' + id + '"]')) {
                addRow(id);
            }
        });
    };
    let picker = parentEl.__abpttPlacePicker;
    if (!picker || !picker.parent) {
        picker = new ABPTT_Multi_Selection('div.abptt_admin .abptt_place', abptt_location);
        if (!picker.parent) return;
        parentEl.__abpttPlacePicker = picker;
        let origSelect = picker.selectItem.bind(picker);
        picker.selectItem = function (id) {
            origSelect(id);
            sync();
        };
        let origRemove = picker.removeItem.bind(picker);
        picker.removeItem = function (id) {
            origRemove(id);
            sync();
        };
        picker.sync = sync;
        $(document).off('click.abpttPlaceRows', 'div.abptt_admin .place_you_see_list .delete_hook')
            .on('click.abpttPlaceRows', 'div.abptt_admin .place_you_see_list .delete_hook', function () {
                let id = String($(this).closest('.place_you_see_item').attr('data-place-id') || '');
                if (!id) return;
                setTimeout(function () {
                    picker.removeItem(id);
                }, 320);
            });
    } else {
        // The global place popup replaces abptt_location with a fresh array. A reused picker would
        // otherwise keep searching and adding against the stale list.
        picker.dataSource = abptt_location;
        picker.render();
    }
    // Always reconcile: chips may have been added or removed since the last run.
    sync();
}//==========Dashboard Recent/Today Orders===========//
(function ($) {
    "use strict";
    $(document).on('click', 'div.abptt_admin .dash_order_tab[data-dtab]', function () {
        let $this = $(this);
        let $card = $this.closest('.dash_orders_card');
        if ($this.hasClass('abp_active')) {
            return;
        }
        $card.find('.dash_order_tab.abp_active').removeClass('abp_active');
        $this.addClass('abp_active');
        let key = $this.attr('data-dtab');
        $card.find('.dash_orders_pane.abp_active').removeClass('abp_active');
        let $panel = $card.find('.dash_orders_pane[data-dpane="' + key + '"]');
        $panel.addClass('abp_active');
    });
    $(document).on('click', 'div.abptt_admin button.dash_orders_loadmore', function () {
        let $this = $(this);
        let $pane = $this.closest('.dash_orders_pane');
        let step = parseInt($this.attr('data-step'));
        if (isNaN(step) || step < 1) {
            step = 10;
        }
        let $hidden = $pane.find('.dash_row_hidden');
        $hidden.slice(0, step).removeClass('dash_row_hidden');
        if ($pane.find('.dash_row_hidden').length === 0) {
            $this.remove();
        }
    });
    //========== Upcoming Journeys : click to open booking popup ==========//
    $(document).on('click', 'div.abptt_admin .dash_journey_row', function () {
        let $this = $(this);
        let post_id = $this.attr('data-post');
        let start_time = $this.attr('data-start');
        let direction = $this.attr('data-direction') || 'up';
        if (!post_id || !start_time) {
            return;
        }
        jQuery('body').addClass('_stop_scroll').find('[data-popup="#abptt_global_popup"]').addClass('in').promise().done(function () {
            let parent = abptt_parent.find('[data-popup="#abptt_global_popup"]').find('.popup_area').addClass('dash_journey_popup_area');
            let target = parent.find('.popup_body');
            jQuery.ajax({
                type: 'POST', url: abptt_admin_data.ajax_url, data: {
                    "action": 'abptt_journey_popup', 'post_id': post_id, 'start_time': start_time, 'direction': direction, 'nonce': abptt_admin_data.nonce
                }, beforeSend: function () {
                    abptt_spinner(parent);
                    abptt_toast_msg(abptt_admin_data.msg.loading);
                }, success: function (response) {
                    abptt_spinner_remove(parent);
                    if (response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptt_toast_msg(response.data.msg, response.data.type);
                            abptt_init(target);
                        });
                    }
                }, error: function (xhr) {
                    abptt_ajx_error(xhr, parent);
                }
            });
        });
    });
}(jQuery));