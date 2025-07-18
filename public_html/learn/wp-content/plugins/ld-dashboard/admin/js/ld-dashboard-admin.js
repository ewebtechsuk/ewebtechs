(function ($) {
  "use strict";

  $(document).ready(function () {
    const { __, _x, _n, sprintf } = wp.i18n;
    $("#wb_wss_seller_user_roles").selectize({
      placeholder: __("Select user role", "ld-dashboard"),
      plugins: ["remove_button"],
    });
    $(".ld-dashboard-color").wpColorPicker();

    /** Make user role selectize */
    $('.ld-select-user-roles').selectize({
      placeholder: __("Select user role", "ld-dashboard"),
      plugins: ["remove_button"],
    });

    function isElememntVisible($el) {
      var winTop = $(window).scrollTop();
      var winBottom = winTop + $(window).height();
      var elTop = $el.offset().top;
      var elBottom = elTop + $el.height();
      return ((elBottom <= winBottom) && (elTop >= winTop));
    }

    if (jQuery('.ld-dashboard-sticky-submit-btn').length) {
      $(window).scroll(function () {
        if (isElememntVisible(jQuery('.ld-dashboard-form-submit-btn'))) {
          jQuery('.ld-dashboard-sticky-submit-btn').hide();
        } else {
          jQuery('.ld-dashboard-sticky-submit-btn').show();
        }
      });
    }

    if (jQuery('.ld-dashboard-sharing-percentage-input').length) {
      jQuery('.ld-dashboard-sharing-percentage-input').on('keyup change', function () {
        let value = jQuery(this).val();
        if (value > 0) {
          let remaining = 100 - value;
          if (jQuery(this).hasClass('ld-dashboard-sharing-percentage-instructor')) {
            jQuery('.ld-dashboard-sharing-percentage-admin').val(remaining);
          }
          if (jQuery(this).hasClass('ld-dashboard-sharing-percentage-admin')) {
            jQuery('.ld-dashboard-sharing-percentage-instructor').val(remaining);
          }
        }
      });
    }

    if (jQuery('.ld-dashboard-form-field').length) {
      jQuery('.ld-dashboard-form-field').on('change', function () {
        let fieldKey = jQuery(this).data('key');
        let childClass = 'ld-dashboard-parent-' + fieldKey;
        let isChecked = jQuery(this).is(':checked');
        if (jQuery('.' + childClass).length) {
          jQuery('.' + childClass).each(function () {
            let childElementKey = jQuery(this).prev('.ld-dashboard-setting-switch').find('.ld-dashboard-form-field');
            if (isChecked) {
              jQuery(childElementKey).closest('.ld-single-grid').show();
            } else {
              jQuery(childElementKey).closest('.ld-single-grid').hide();
              jQuery(childElementKey).prop('checked', false);
            }
          });

        }

      });
      jQuery('.ld-dashboard-form-field').each(function () {
        let fieldKey = jQuery(this).data('key');
        let childClass = 'ld-dashboard-parent-' + fieldKey;
        let isChecked = jQuery(this).is(':checked');
        if (jQuery('.' + childClass).length) {
          jQuery('.' + childClass).each(function () {
            let childElementKey = jQuery(this).prev('.ld-dashboard-setting-switch').find('.ld-dashboard-form-field');
            if (isChecked) {
              jQuery(childElementKey).closest('.ld-single-grid').show();
            } else {
              jQuery(childElementKey).closest('.ld-single-grid').hide();
              jQuery(childElementKey).prop('checked', false);
            }
          });

        }

      });
    }

    if (jQuery('.ld-dashboard-design-settings-section').length) {
      if (jQuery('input[name="ld_dashboard_design_settings[preset]"]:checked').val() != '') {
        let val = jQuery('input[name="ld_dashboard_design_settings[preset]"]:checked').val();
        getCustomPresetFields(val);
      } else {
        getCustomPresetFields('default');
      }
      jQuery(document).on('focus', '.ld-dashboard-design-color-picker', function () {
        jQuery('input[name="ld_dashboard_design_settings[preset]"]').each(function () {
          if (jQuery(this).val() == 'custom') {
            jQuery(this).prop('checked', true);
          } else {
            jQuery(this).prop('checked', false);
          }
        });
        setCustomPresetDisplayColors();
      });
      jQuery(document).on('change', '.ld-dashboard-design-color-picker', function () {
        if (jQuery('input[name="ld_dashboard_design_settings[preset]"]:checked').val() == 'custom') {
          let preset = jQuery(this).attr('data-id');
          let color = jQuery(this).val();
          jQuery(this).next('.ld-dashboard-color-value').text(color);
          jQuery(".ld-dashboard-design-header.custom-preset-designs").find(`[data-id='${preset}']`).css('background-color', color);
        }
      });
      jQuery('input[name="ld_dashboard_design_settings[preset]"]').on('change', function () {
        let type = jQuery(this).val();
        getCustomPresetFields(type);
      });
      function setCustomPresetDisplayColors() {
        jQuery('.ld-dashboard-design-color-picker').each(function () {
          let preset = jQuery(this).attr('data-id');
          let color = jQuery(this).val();
          jQuery(".ld-dashboard-design-header.custom-preset-designs").find(`[data-id='${preset}']`).css('background-color', color);
        });
      }
      function getCustomPresetFields(type) {
        let params = {
          action: "ld_dashboard_get_custom_preset_fields",
          nonce: ld_dashboard_obj.field_ajax_nonce,
          type: type
        };
        jQuery.ajax({
          url: ld_dashboard_obj.ajax_url,
          type: "post",
          data: params,
          success: function (response) {
            jQuery('.custom-preset-container').html(response);
            $(".ld-dashboard-color").wpColorPicker();
            if ( jQuery('input[name="ld_dashboard_design_settings[preset]"]:checked').val() == 'custom' ) {
              setCustomPresetDisplayColors();
            }
          },
        });
      }
    }

    if (jQuery('.use-admin-account-checkbox').length) {
      if (jQuery('.use-admin-account-checkbox').is(':checked')) {
        getCoHostsInstructorsList();
      } else {
        jQuery('.wbcom-settings-section-wrap.ld-dashboard-instructors-listing').hide();
      }
      jQuery('.use-admin-account-checkbox').on('change', function () {
        if (jQuery(this).is(':checked')) {
          getCoHostsInstructorsList();
        } else {
          jQuery('.wbcom-settings-section-wrap.ld-dashboard-instructors-listing').hide();
          jQuery('.wbcom-settings-section-wrap.ld-dashboard-instructors-listing').find('.ld-grid-content').html('');
        }
      });
      function getCoHostsInstructorsList() {
        let params = {
          action: "ld_dashboard_get_instructors_listing",
          nonce: ld_dashboard_obj.field_ajax_nonce,
        };
        jQuery.ajax({
          url: ld_dashboard_obj.ajax_url,
          type: "post",
          data: params,
          success: function (response) {
            jQuery('.wbcom-settings-section-wrap.ld-dashboard-instructors-listing').find('.wbcom-settings-section-options').html(response);
            jQuery('.wbcom-settings-section-wrap.ld-dashboard-instructors-listing').show();
            jQuery('.ld-dashboard-co-host-instructors').selectize({
              placeholder: __("Select Instructors", "ld-dashboard"),
              plugins: ["remove_button"],
            });
          },
        });
      }
    }

    if (jQuery('.ld-dashboard-setting-accordian-wrapper').length) {
      jQuery('.ld-dashboard-setting-accordian-wrapper').find('.ld-dashboard-admin-general-title-section').on('click', function () {
        if (jQuery(this).closest('.ld-dashboard-setting-accordian-wrapper').hasClass('accordian-close')) {
          jQuery(this).closest('.ld-dashboard-setting-accordian-wrapper').removeClass('accordian-close');
        } else {
          jQuery(this).closest('.ld-dashboard-setting-accordian-wrapper').addClass('accordian-close');
        }
      });
    }

    // if ( jQuery('.ld-dashboard-menu-settings-role-section').length ) {
    //   jQuery('.ld-dashboard-menu-setting-role-filter').on( 'change', function(){
    //     let role = jQuery(this).val();
    //     jQuery('.ld-dashboard-menu-settings-role-section').hide();
    //     jQuery('.ld-dashboard-menu-settings-role-section').each(function(){
    //       if ( jQuery(this).hasClass('role-'+role) ) {
    //         jQuery(this).show();
    //       }
    //     });
    //   });
    //   jQuery(".ld-dashboard-menu-setting-role-filter").val("instructor").change();
    // }

    if (jQuery('.ld-dashboard-menu-tab-checkbox-hidden').length) {
      jQuery('.ld-dashboard-menu-tab-checkbox-hidden').each(function () {
        if (jQuery(this).val() == 1) {
          jQuery(this).prev('.ld-dashboard-menu-tab-checkbox').prop('checked', true);
        }
      });
      jQuery('.ld-dashboard-menu-tab-checkbox').on('change', function () {
        if (jQuery(this).is(':checked')) {
          jQuery(this).next('.ld-dashboard-menu-tab-checkbox-hidden').val(1);
        } else {
          jQuery(this).next('.ld-dashboard-menu-tab-checkbox-hidden').val(0);
        }
      });
    }

    if (jQuery('.ld-dashboard-meetings-actions-wrapper').length) {
      jQuery('.ld-delete-meeting-action').on('click', function (e) {
        e.preventDefault();
        let conf_delete = confirm(__('Are you sure?', 'ld-dashboard'));
        if (conf_delete) {
          let post_id = jQuery(this).data('post');
          let meeting = jQuery(this).data('meeting');
          let params = {
            action: "ld_dashboard_delete_meeting",
            post_id: post_id,
            meeting: meeting,
            nonce: ld_dashboard_obj.field_ajax_nonce,
          };
          jQuery.ajax({
            url: ld_dashboard_obj.ajax_url,
            type: "post",
            data: params,
            success: function (response) {
              location.reload();
            },
          });
        }
      });
    }

    if (jQuery('.ld-dashboard-popular-course-checkbox').length) {
      jQuery('.ld-dashboard-popular-course-checkbox').on('change', function () {
        jQuery('.ld-dashboard-popular-course-toggle').toggle();
      });
    }

    if (jQuery('body').hasClass('post-type-withdrawals')) {
      jQuery('.ld-dashboard-process-withrawal-request').on('click', function () {
        let id = jQuery(this).data('id');
        let type = 'reject';
        if (jQuery(this).hasClass('ld-dashboard-approve-withrawal-request')) {
          type = 'approve';
        }
        let cnf = confirm(__('Are you sure?', 'ld-dashboard'));
        if (!cnf) {
          return false;
        }
        let params = {
          action: "ld_dashboard_process_withrawal_request",
          type: type,
          post_id: id,
          nonce: ld_dashboard_obj.field_ajax_nonce,
        };
        jQuery.ajax({
          url: ld_dashboard_obj.ajax_url,
          type: "post",
          data: params,
          success: function (response) {
            location.reload();
          },
        });
      });
    }

    jQuery(".ld-dashboard-setting-switch-all").on("change", function () {
      if ($(this).prop("checked") == true) {
        jQuery(".form-table")
          .find(".ld-dashboard-switch")
          .each(function () {
            jQuery(this).prop("checked", true);
          });
      } else {
        jQuery(".form-table")
          .find(".ld-dashboard-switch")
          .each(function () {
            jQuery(this).prop("checked", false);
          });
      }
    });

    if ($("#ld-instructor-commission-update-tbl").length) {
      var table = $("#ld-instructor-commission-update-tbl").DataTable({
        deferRender: true,
        ordering: true,
      });
    }

    /*
      if ($("#ld-instructor-commission-report").length) {
        var comm_table = $("#ld-instructor-commission-report").DataTable({
          searching: false,
          lengthChange: false,
          deferRender: true,
          ordering: true,
          paging: false,
          bInfo: false,
        });
      }
    */

    /*================================================================================
    =            Ajax request to update individual instructor commission.            =
    ================================================================================*/

    $(document).on("click", ".ld-update-instructor-commision", function (e) {
      e.preventDefault();
      var update_text = $(this).html();
      var update_btn = $(this);
      $(this).html(__("Please wait ", "ld-dashboard") + '<i class="fa fa-spinner fa-spin"></i>');
      var input_id = $(this).data("id");
      var instructor_id = $(this).data("instructor-id");
      var instructor_commission = $("#" + input_id).val();

      if (instructor_id && instructor_commission) {
        var data = {
          action: "ld_ajax_update_instructor_commission",
          instructor_id: instructor_id,
          instructor_commission: instructor_commission,
          ajax_nonce: ld_dashboard_obj.ajax_nonce,
        };

        $.post(ld_dashboard_obj.ajax_url, data, function (response) {
          update_btn.html(update_text);
          $("#" + input_id).val(instructor_commission);
        });
      }
    });

    /*=====  End of Ajax request to update individual instructor commission.  ======*/

    /*============================================================================
    =            Instructor select event to generate commission data.            =
    ============================================================================*/


    $("#ld-instructor-dropdown").on("change", function () {
      if ($(this).val() != '') {
        $('#search-instructor').prop("disabled", false);
      } else {
        $('#search-instructor').prop("disabled", true);
      }
      /*
        var instructor_id = this.value;
        if (instructor_id && instructor_id != "select") {
          $("#ld-instructor-commission-report tbody").html(
            '<div class="load-commission-data"><i class="ld-load-result fa fa-refresh fa-spin"></i></div>'
          );
          $("#ld-instructor-commission-report tfoot").html("");
          var data = {
            action: "ld_ajax_generate_instructor_data",
            instructor_id: instructor_id,
            ajax_nonce: ld_dashboard_obj.ajax_nonce,
          };
          $.post(ld_dashboard_obj.ajax_url, data, function (response) {
            var fun_response = JSON.parse(response);
            $("#ld-instructor-commission-report tbody").html(
              fun_response.tr_html
            );
            $("#ld-instructor-commission-report tfoot").html(
              fun_response.tfoot_html
            );
          });
        }
      */
    });

    /*=====  End of Instructor select event to generate commission data.  ======*/

    /*====================================================
    =            Instructor pay unpaid amount            =
    ====================================================*/

    $(document).on("click", ".instructor-pay-amount", function (e) {
      e.preventDefault();
      $(".ld-instructor-dialog").addClass("visible");

      var instructor_id = $(this).attr("data-instructor-id");
      var unpaid_earning = $(this).attr("data-unpaid-amt");
      var paid_earning = $(this).attr("data-paid-amt");
      var total_earning = $(this).attr("data-total-earning");

      //dialog view html
      $(".ld-dialog-paid-earning").html(paid_earning);
      $(".ld-dialog-unpaid-earning").html(unpaid_earning);

      //set values in hidden input
      $("#ld-instructor-id").val(instructor_id);
      $("#ld-paid-earning").val(paid_earning);
      $("#ld-unpaid-earning").val(unpaid_earning);
      $("#ld-total-earning").val(total_earning);
    });

    /*=====  End of Instructor pay unpaid amount  ======*/

    $(document).on("click", ".ld-instructor-trigger-pay", function () {
      var clk_obj = $(this);
      var pay_txt = $(this).html();

      var instructor_id = $("#ld-instructor-id").val();
      var unpaid_earning = $("#ld-unpaid-earning").val();
      var paid_earning = $("#ld-paid-earning").val();
      var total_earning = $("#ld-total-earning").val();

      var paying_amount = $("#ld-pay-amount").val();

      if (paying_amount > unpaid_earning) {
        $(".ld-pay-error").addClass("visible");
      }

      if (
        paying_amount &&
        instructor_id &&
        (paying_amount < unpaid_earning || paying_amount == unpaid_earning)
      ) {
        $(".ld-pay-error").removeClass("visible");
        $(clk_obj).html(pay_txt + ' <i class="fa fa-spinner fa-spin"></i>');
        var data = {
          action: "ld_ajax_pay_instructor_amount",
          instructor_id: instructor_id,
          paid_earning: paid_earning,
          paying_amount: paying_amount,
          total_earning: total_earning,
          ajax_nonce: ld_dashboard_obj.ajax_nonce,
        };

        $.post(ld_dashboard_obj.ajax_url, data, function (response) {
          $(".ld-instructor-dialog").removeClass("visible");
          $("#ld-pay-amount").val("");
          $(clk_obj).html(pay_txt);
          window.location.reload();
        });
      }
    });

    $(document).on("click", ".ld-instructor-dialog-cancel", function () {
      $(".ld-instructor-dialog").removeClass("visible");
      //dialog view html
      $(".ld-dialog-paid-earning").html("");
      $(".ld-dialog-unpaid-earning").html("");

      //set values in hidden input
      $("#ld-instructor-id").val("");
      $("#ld-paid-earning").val("");
      $("#ld-unpaid-earning").val("");
      $("#ld-total-earning").val("");
      $("#ld-pay-amount").val("");
    });

    jQuery('.ld-dashboard-set-instructor-commission-btn').on('click', function () {
      let userId = jQuery(this).data('user');
      let commissionHtml = '<input type="number" class="ld-dashboard-commission-value" min="0" max="100" value="0"><button class="ld-dashboard-set-instructor-commission" data-user="' + userId + '">' + __('Set %', 'ld-dashboard') + '</button>';
      jQuery(this).replaceWith(commissionHtml);
    });
    jQuery(document).on('click', '.ld-dashboard-set-instructor-commission', function () {
      let userId = jQuery(this).data('user');
      let commission = jQuery(this).prev('input').val();
      if (commission != '') {
        let params = {
          action: "ld_dashboard_set_instructor_commission",
          commission: commission,
          user_id: userId,
          nonce: ld_dashboard_obj.field_ajax_nonce,
        };
        jQuery.ajax({
          url: ld_dashboard_obj.ajax_url,
          type: "post",
          data: params,
          success: function (response) {
            location.reload();
          },
        });
      } else {
        alert(__('Please enter a valid number', 'ld-dashboard'));
      }
    });

    if (jQuery('#ldd-add-new-instructor-form').length) {
      jQuery('.ldd-add-new-instructor-submit-btn').on('click', function (e) {
        e.preventDefault();
        jQuery('.ld-dashboard-add-instructor-message-container').html('');
        jQuery('#ldd-add-new-instructor-form').trigger('submit');
      });
      jQuery('#ldd-add-new-instructor-form').on('submit', function (e) {
        e.preventDefault();
        let fields = jQuery(this).serializeArray();
        let formdata = [];
        let hasError = false;
        fields.forEach((item) => {
          formdata[item.name] = item.value;
        });
        if (! /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(formdata['email'])) {
          displayRegisterMessageBox(__('Invalid email', 'ld-dashboard'), 'error');
          hasError = true;
        }
        if (formdata['pass'] != formdata['confirm_pass']) {
          displayRegisterMessageBox(__('Password did not match.', 'ld-dashboard'), 'error');
          hasError = true;
        }
        if (hasError) {
          return false;
        }
        let params = {
          action: "ld_dashboard_add_new_instructor_user",
          form_data: fields,
          nonce: ld_dashboard_obj.field_ajax_nonce,
        };
        jQuery.ajax({
          url: ld_dashboard_obj.ajax_url,
          type: "post",
          data: params,
          success: function (response) {
            let msg = '';
            if (response == '') {
              jQuery('#ldd-add-new-instructor-form').trigger("reset");
              msg = __('Instructor created successfully.', 'ld-dashboard');
              displayRegisterMessageBox(msg, 'success');
            } else {
              msg = response;
              displayRegisterMessageBox(msg, 'error');
            }
          },
        });
      });
    }

    function displayRegisterMessageBox(msg, status) {
      let classes = 'ld-dashboard-add-instructor-message';
      if (status == 'success') {
        classes += ' ld-dashboard-add-instructor-message-success';
      } else if (status == 'error') {
        classes += ' ld-dashboard-add-instructor-message-error';
      }
      jQuery('.ld-dashboard-add-instructor-message-container').append('<p class="' + classes + '">' + msg + '</p>');
      setTimeout(function () {
        jQuery('.ld-dashboard-add-instructor-message-container').html('');
        jQuery('.ld-dashboard-add-instructor-message').removeClass('ld-dashboard-add-instructor-message-success');
        jQuery('.ld-dashboard-add-instructor-message').removeClass('ld-dashboard-add-instructor-message-error');
      }, 5000);
    }

    if (jQuery('.wb-plugins_page_ld-dashboard-settings').length) {
      jQuery('.wb-plugins_page_ld-dashboard-settings').find('tr').on('mouseover', function () {
        jQuery(this).find('.ld-dashboard-action-container').show()
      });
      jQuery('.wb-plugins_page_ld-dashboard-settings').find('tr').on('mouseleave', function () {
        jQuery(this).find('.ld-dashboard-action-container').hide()
      });
      jQuery('.ld-dashboard-action-single').on('click', function () {
        let type = '';
        let that = jQuery(this);
        let userId = jQuery(this).parent().data('id');
        if (jQuery(this).hasClass('--approve')) {
          type = 'approve';
        }
        if (jQuery(this).hasClass('--reject')) {
          type = 'reject';
          let con = confirm(__('Are you sure?', 'ld-dashboard'))
          if (!con) {
            return;
          }
        }
        if (jQuery(this).hasClass('--remove')) {
          type = 'remove';
          let con = confirm(__('Are you sure want to remove this user as an Instructor?', 'ld-dashboard'))
          if (!con) {
            return;
          }
        }
        let params = {
          action: "ld_dashboard_set_instructor_role",
          user_id: userId,
          type: type,
          nonce: ld_dashboard_obj.field_ajax_nonce,
        };
        jQuery.ajax({
          url: ld_dashboard_obj.ajax_url,
          type: "post",
          data: params,
          success: function (response) {
            if (response != '') {
              location.reload();
            }
          },
        });
      })
    }

    if (jQuery("#ld_dashboard_field_restriction").length) {

      jQuery('.ld-dashboard-checkbox').on('click', function () {
        jQuery("#ld_dashboard_field_restriction").trigger("change");
      });

      jQuery(".ld-dashboard-fields-form-single").hide();
      displayGroupFieldsForm("course");
      jQuery("#ld_dashboard_field_restriction").on("change", function () {
        let group = jQuery(this).val();
        displayGroupFieldsForm(group);
      });
    }
    function displayGroupFieldsForm(group) {
      jQuery(".ld-dashboard-fields-form-single").each(function () {
        let currentGroup = jQuery(this).data("group");
        if (group == currentGroup) {
          let allChecked = true;
          jQuery(this).find('.ld-dashboard-checkbox').each(function () {
            if (!jQuery(this).is(':checked')) {
              allChecked = false;
              return false;
            }
          });
          if (allChecked) {
            jQuery(this).find('.ld-dashboard-setting-switch-all').prop('checked', true);
          } else {
            jQuery(this).find('.ld-dashboard-setting-switch-all').prop('checked', false);
          }

          jQuery(this).show();
          jQuery(this)
            .find(".ld-dashboard-checkbox")
            .each(function () {
              jQuery(this).addClass("ld-dashboard-switch");
            });
        } else {
          jQuery(this).hide();
          jQuery(this)
            .find(".ld-dashboard-checkbox")
            .each(function () {
              jQuery(this).removeClass("ld-dashboard-switch");
            });
        }
      });
    }
    // $( '.ld-dashboard-setting' ).each(function() {
    // 	var colorpicker_id = $(this).data('id');
    // 	if( $(this).prop("checked") == true ){
    //               $( '#' + colorpicker_id + '-bgcolor' ).show();
    //           } else {
    // 		$( '#' + colorpicker_id + '-bgcolor' ).hide();
    // 	}
    // });

    // $( '.ld-dashboard-setting' ).on( 'click', function () {
    // 	var colorpicker_id = $(this).data('id');
    // 	if( $(this).prop("checked") == true ){
    //               $( '#' + colorpicker_id + '-bgcolor' ).show(1000);
    //           } else {
    // 		$( '#' + colorpicker_id + '-bgcolor' ).hide(1000);
    // 	}
    // });

    $(document).on("click", ".ld-add-instructor-btn", function (e) {
      e.preventDefault();

      var $that = $(this);
      var post_id = $("#post_ID").val();

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          post_id: post_id,
          action: "ld_dashboard_load_instructors_modal",
        },
        beforeSend: function () {
          $that.addClass("ld-dashboard-updating-message");
        },
        success: function (data) {
          if (data.success) {
            $(".ld-instructors-modal-wrap .modal-container").html(
              data.data.output
            );
            $(".ld-instructors-modal-wrap").addClass("show");
            $("body").addClass("ld-modal-show");
          }
        },
        complete: function () {
          $that.removeClass("ld-dashboard-updating-message");
        },
      });
    });

    $(document).on("click", ".modal-close-btn", function (e) {
      e.preventDefault();
      $(".ld-modal-wrap").removeClass("show");
      $("body").removeClass("ld-modal-show");
    });

    /* Delay Function */

    var ld_dashboard_delay = (function () {
      var timer = 0;
      return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
      };
    })();

    var search_instructor;
    $(document).on("keyup", ".ld-instructors-modal-wrap .ld-instructor-modal-search-input", function (e) {
      e.preventDefault();

      var $that = $(this);
      var $modal = $(".ld-modal-wrap");

      var search_terms = $that.val();
      var post_id = $("#post_ID").val();
      search_instructor = $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          post_id: post_id,
          search_terms: search_terms,
          action: "ld_dashboard_load_instructors_modal",
        },
        beforeSend: function () {
          if (typeof search_instructor !== 'undefined') {
            search_instructor.abort();
          }
          $modal.addClass("loading");
        },
        success: function (data) {
          if (data.success) {
            $(".ld-instructors-modal-wrap .modal-container").html(
              data.data.output
            );
            $(".ld-instructors-modal-wrap").addClass("show");
            $("body").addClass("ld-modal-show");
          }
        },
        complete: function () {
          $modal.removeClass("loading");
        },
      });
    });

    $(document).on("click", ".add_instructor_to_course_btn", function (e) {
      e.preventDefault();

      var $that = $(this);
      var $modal = $(".ld-modal-wrap");
      var post_id = $("#post_ID").val();
      var data =
        $modal.find("input").serialize() +
        "&post_id=" +
        post_id +
        "&action=ld_dashboard_add_instructors_to_course";

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: data,
        beforeSend: function () {
          $that.addClass("ld-updating-message");
        },
        success: function (data) {
          if (data.success) {
            $(".ld-available-instructors").html(data.data.output);
            $(".ld-modal-wrap").removeClass("show");
            $("body").removeClass("ld-modal-show");
          }
        },
        complete: function () {
          $that.removeClass("ld-updating-message");
        },
      });
    });

    $(document).on(
      "click",
      ".ld-instructor-delete-btn,.ld-instructor-delete-btn .dashicons dashicons-no",
      function (e) {
        e.preventDefault();

        var $that = $(this);
        var post_id = $("#post_ID").val();
        var instructor_id = $that
          .closest(".added-instructor-item")
          .attr("data-instructor-id");

        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            post_id: post_id,
            instructor_id: instructor_id,
            action: "ld_dashboard_detach_instructor",
          },
          success: function (data) {
            if (data.success) {
              $that.closest(".added-instructor-item").remove();
            }
          },
        });
      }
    );

    $(".ld_dashboard_upload_image").on("click", function (e) {
      e.preventDefault();
      var image_id = $(this).data("slug");
      var image = wp
        .media({
          title: "Upload Image",
          // mutiple: true if you want to upload multiple files at once
          multiple: false,
        })
        .open()
        .on("select", function (e) {
          // This will return the selected image from the Media Uploader, the result is an object
          var uploaded_image = image.state().get("selection").first();
          // We convert uploaded_image to a JSON object to make accessing it easier
          // Output to the console uploaded_image
          var image_url = uploaded_image.toJSON().url;
          $("#" + image_id).val(image_url);

          $("." + image_id).attr("src", image_url);
          $(".ld-display-" + image_id).show();
        });
    });

    $(document).on("click", ".ld-dashboard-image-close", function (e) {
      e.preventDefault();
      var image_del_id = $(this).data("slug");
      $("#" + image_del_id).val("");
      $("." + image_del_id).attr("src", "");
      $(".ld-display-" + image_del_id).hide();
    });

    $(document).on("change", "#search_by_user_id", function () {
      $('form#search_email_logs').submit();
    });

    $('.ld-dashboard-date-picker').datepicker({ beforeShow: ld_dashboard_customRange, dateFormat: "yy-mm-dd", });

    function ld_dashboard_customRange(input) {
      if (input.id == 'ld-dashboard-end-date') {
        var minDate = new Date($('#ld-dashboard-start-date').val());
        minDate.setDate(minDate.getDate() + 1)

        return {
          minDate: minDate

        };
      }

      if (input.id == 'ld-dashboard-start-date') {
        var maxDate = new Date($('#ld-dashboard-end-date').val());
        maxDate.setDate(maxDate.getDate() - 1)

        return {
          maxDate: maxDate

        };
      }
      return {}
    }

    /**
     * LD Dashboard menu accordian
     */
    $('#wbcom-accordion').accordion(
      {
        collapsible: true
      }
    );
	
	$(".ld-dashboard-reset-time-tracking").on("click", function (e) {
		e.preventDefault();
		var course_id 	= $(this).data( 'course-id' );
		var user_id 	= $(this).data( 'user-id' );
		
		let params = {
          action: "ld_dashboard_reset_user_time_tracking",
          nonce: ld_dashboard_obj.field_ajax_nonce,
          course_id: course_id,
          user_id: user_id
        };
        jQuery.ajax({
			url: ld_dashboard_obj.ajax_url,
			type: "post",
			data: params,
			success: function (response) {
				$('li#ld-dashboard-course-' + course_id + ' span.ld-dashboard-time-show').html('00:00:00');
			},
        });
    });


  });

  $(function () {
    var acc_elmt = document.getElementsByClassName("wbcom-faq-accordion");
    var k;
    for (k = 0; k < acc_elmt.length; k++) {
      acc_elmt[k].onclick = function () {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
          panel.style.maxHeight = null;
        } else {
          panel.style.maxHeight = panel.scrollHeight + "px";
        }
      };
    }
  });

  //Learndash prerequisites select
  $(function () {
    if ($('body').hasClass('learndash-screen')) {
      $("#learndash-course-access-settings_course_prerequisite > option").each(function () {
        $(this).attr('selected', 'selected');
      });
    }
  });

  $(function () {
    $('.ld-dashboard-reset-button').on('click', (e) => {
      e.preventDefault();
      let setting = e.target.dataset.setting;
      $.post(ajaxurl, { action: 'reset_settings', setting: setting, security: ld_dashboard_obj.ajax_nonce }, (responce) => {
        if (responce.success) {
          Swal.fire({
            text: responce.data,
            icon: 'success'
          }).then((result) => {
            if (result.isConfirmed) {
              location.reload();
            }
          });
        } else {
          Swal.fire({
            text: responce.data,
            icon: 'error'
          });
        }

      });

    });
  });
  /*============================================================================
  =            Embed Meeting Button Toggle                                =
  ============================================================================*/
  $(document).on('click', '#ld-dashboard-embed-meeting-enable', (e) => {
    let embedButton = e.target;
    let shortCodeBox = $('#ld-dashboard-embed-meeting-box');
    let embedMeetingElements = $('.ld-dashboard-embed-meeting');
    if (embedButton.checked ) {
      shortCodeBox.css('display', 'block');
      embedMeetingElements.css('display', 'block');
    } else {
      shortCodeBox.css('display', 'none');
      embedMeetingElements.css('display', 'none');
    }
    
  });

  /*============================================================================
=            Copy the embed shortcode                              =
============================================================================*/
  $(document).on('click', '.ld-dashboard-copy-shortcode-button', (e) => {
    e.preventDefault();
    let shortcodeBox = e.target;
    let text = document.querySelector('.embed-meeting-shortcode-box').value;
    let tooltip = document.querySelector('.ld-dashboard-tooltip');
    
    if (navigator.clipboard && window.isSecureContext) {
      copyContent(text);
      tooltip.style.display = 'block';
      setTimeout( () => {
        tooltip.style.display = 'none';
      }, 5000);

    } else {
      // Use the 'out of viewport hidden text area' trick
      const textArea = document.createElement("textarea");
      textArea.value = text;
      // Move textarea out of the viewport so it's not visible
      textArea.style.position = "absolute";
      textArea.style.left = "-999999px";
      document.body.prepend(textArea);
      textArea.select();
      
      try {
        document.execCommand('copy');
        tooltip.style.display = 'block';
        setTimeout(() => {
          tooltip.style.display = 'none';
        }, 5000);
      } catch (error) {
        console.error(error);
      } finally {
        textArea.remove();
      }
    }
  });

  const copyContent = async (text) => {
    try {
      await navigator.clipboard.writeText(text);
      console.log('Content copied to clipboard');
    } catch (err) {
      console.error('Failed to copy: ', err);
    }
  }

  /*============================================================================
=            Toggle show/hide sdk                                          =
============================================================================*/
  $(document).on('click', '.ld-dashboard-password-toggle', (e) =>{
    let input = e.target.previousElementSibling;
    let toggler = e.target;
    
    if (input.type == 'password'){
      input.setAttribute('type', 'text');
      toggler.classList.add('dashicons-hidden');
      toggler.classList.remove('dashicons-visibility');
    } else {
      input.setAttribute('type', 'password');
      toggler.classList.add('dashicons-visibility');
      toggler.classList.remove('dashicons-hidden');
    }
    
  });
  
  
})(jQuery);


  
