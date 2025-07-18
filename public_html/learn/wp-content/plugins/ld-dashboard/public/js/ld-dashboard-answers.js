(function ($) {
  "use strict";

  /**
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  jQuery(document).ready(function ($) {
    const { __, _x, _n, sprintf } = wp.i18n;
    if (jQuery(".acf-field-61c030e4a9f63").length) {
      function question_get_form_html(type, index) {
        // return false;
        // html content for single choice
        if (type == "single") {
          let correctIndex = index;
          var singleContent = "";
          singleContent +=
            "<div class='appendsingleContent ld-dashboard-question-answer-box' data-item_key='" +
            index +
            "'>";
          singleContent +=
            "<div class='correct-singleContent-answer-input'>";
          singleContent +=
            "<div class='correct-singleContent'>";
          singleContent +=
            "<div class='correct-singleContent--options'>" + __( 'Options', 'ld-dashboard' ) + "</div><label><input type='radio' name='sfwd-question_single_answer_cld[" +
            correctIndex +
            "][correct]' class='correct ldd-single-choice-radio' value='on'/>" + __( 'Correct', 'ld-dashboard' ) + "</label>";
          singleContent +=
            "<label><input type='checkbox' name='sfwd-question_single_answer_cld[" +
            index +
            "][allow_html]' class='allow_html' value='on'/>" + __( 'Allow HTML', 'ld-dashboard' ) + "<label>";
          singleContent += "</div>";
          singleContent += "<div class='answer-input'>";
          singleContent += "<label name =''>" + __( 'Answer', 'ld-dashboard' ) + "</label>";
          singleContent +=
            "<textarea name='sfwd-question_single_answer_cld[" +
            index +
            "][answer]' value=''></textarea>";
          singleContent += "</div>";
          singleContent += "</div>";
          singleContent +=
            "<div class='correct-singleContent-bottom'><button class='delete-ques-ans'>" + __( 'Delete Answer', 'ld-dashboard' ) + "</button>";
          singleContent +=
            "<button class='add-media-ques-ans' data-index='" +
            index +
            "'>" + __( 'Add Media', 'ld-dashboard' ) + "</button>";
          singleContent +=
            "<span class='move-ques-ans'>" + __( 'Move', 'ld-dashboard' ) + "</span>";
          singleContent += "</div></div>";
          return singleContent;
        }
        if (type == "multiple") {
          // html content for multiple choice
          var multipleContent = "";
          multipleContent +=
            "<div class='appendsingleContent ld-dashboard-question-answer-box' data-item_key='" +
            index +
            "'>";
          multipleContent +=
            "<div class='correct-singleContent-answer-input'>";
          multipleContent +=
            "<div class='correct-singleContent'>";
          multipleContent +=
            "<div class='correct-singleContent--options'>" + __( 'Options', 'ld-dashboard' ) + "</div><label><input type='checkbox' name='sfwd-question_single_answer_cld[" +
            index +
            "][correct]' value='on'/>" + __( 'Correct', 'ld-dashboard' ) + "</label>";
          multipleContent +=
            "<label><input type='checkbox' name='sfwd-question_single_answer_cld[" +
            index +
            "][allow_html]' class='allow_html' value='on'/>" + __( 'Allow HTML', 'ld-dashboard' ) + "</label>";
          multipleContent += "</div>";
          multipleContent += "<div class='answer-input'>";
          multipleContent += "<label name =''>" + __( 'Answer', 'ld-dashboard' ) + "</label>";
          multipleContent +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[" +
            index +
            "][answer]' value=''></textarea>";
          multipleContent += "</div>";
          multipleContent += "</div>";
          multipleContent +=
            "<div class='correct-singleContent-bottom'><button class='delete-ques-ans' style='margin:0 5px'>" + __( 'Delete Answer', 'ld-dashboard' ) + "</button>";
          multipleContent +=
            "<button class='add-media-ques-ans' style='margin:0 5px'>" + __( 'Add Media', 'ld-dashboard' ) + "</button>";
          multipleContent +=
            "<span class='move-ques-ans' style='margin:0 5px'>" + __( 'Move', 'ld-dashboard' ) + "</span>";
          multipleContent += "</div></div>";
          return multipleContent;
        }

        if (type == "free_answer") {
          // html content for free choice
          var freeContent =
            "<div class='appendsingleContent' data-item_key='" +
            index +
            "'>";
          freeContent +=
            "<span><small>" + __( 'Correct answers (one per line) (answers will be converted to lower case)', 'ld-dashboard' ) + "</small></span>";
          freeContent +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[0][answer]'></textarea></div>";
          return freeContent;
        }

        if (type == "sort_answer") {
          // html content for sorting choice
          var sortingContent = "";
          sortingContent +=
            "<div class='appendsingleContent ld-dashboard-question-answer-box' data-item_key='" +
            index +
            "'>";
          sortingContent +=
            "<div class='correct-singleContent-answer-input'>";
          sortingContent +=
            "<div class='correct-singleContent'>";
          sortingContent +=
            "<div class='correct-singleContent--options'>" + __( 'Options', 'ld-dashboard' ) + "</div><label><input type='checkbox' name='sfwd-question_single_answer_cld[" +
            index +
            "][allow_html]' class='allow_html' value='on'/>" + __( 'Allow HTML', 'ld-dashboard' ) + "</label>";
          sortingContent += "</div>";
          sortingContent += "<div class='answer-input'>";
          sortingContent += "<label name =''>" + __( 'Answer', 'ld-dashboard' ) + "</label>";
          sortingContent +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[" +
            index +
            "][answer]'></textarea>";
          sortingContent += "</div>";
          sortingContent += "</div>";
          sortingContent +=
            "<div class='correct-singleContent-bottom'><button class='delete-ques-ans'>" + __( 'Delete Answer', 'ld-dashboard' ) + "</button>";
          sortingContent +=
            "<button class='add-media-ques-ans'>" + __( 'Add Media', 'ld-dashboard' ) + "</button>";
          sortingContent +=
            "<span class='move-ques-ans'>" + __( 'Move', 'ld-dashboard' ) + "</span>";
          sortingContent += "</div></div>";
          return sortingContent;
        }

        if (type == "matrix_sort_answer") {
          // html content for matrix content choice
          var matrixContent = "";
          matrixContent +=
            "<div class='appendsingleContent ld-dashboard-question-answer-box ld_dashboard_matrix_sort_answer' data-item_key='" +
            index +
            "'>";
          matrixContent +=
            "<div class='correct-singleContent-answer-input'>";
          matrixContent +=
            "<div class='correct-singleContent'>";
          matrixContent += "<div class='correct-singleContent--options'>" + __( "Criterion", "ld-dashboard" ) + "</div>";
          matrixContent +=
            "<label><input type='checkbox' name='sfwd-question_single_answer_cld[" +
            index +
            "][allow_html]' class='allow_html' value='on' />" + __( 'Allow HTML', 'ld-dashboard' ) + "</label>";
          matrixContent +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[" +
            index +
            "][answer]'></textarea>";
          matrixContent += "</div>";
          matrixContent += "<div class='answer-input'>";
          matrixContent += "<label>" + __( 'Sort elements', 'ld-dashboard' ) + "</label>";
          matrixContent +=
            "<label><input type='checkbox' name='sfwd-question_single_answer_cld[" +
            index +
            "][sort_string_html]' class='allow_html' value='on'/>" + __( 'Allow HTML', 'ld-dashboard' ) + "</label>";
          matrixContent +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[" +
            index +
            "][sort_string]'></textarea>";
          
          matrixContent += "</div>";
          matrixContent += "</div>";
          matrixContent +=
            "<div class='correct-singleContent-bottom'><button class='delete-ques-ans'>" + __( 'Delete Answer', 'ld-dashboard' ) + "</button>";
          matrixContent +=
            "<button class='add-media-ques-ans'>" + __( 'Add Media', 'ld-dashboard' ) + "</button>";
          matrixContent +=
            "<span class='move-ques-ans'>" + __( 'Move', 'ld-dashboard' ) + "</span>";
          matrixContent += "</div></div>";
          return matrixContent;
        }

        if (type == "cloze_answer") {
          // html content for fill in the blank choice
          var fillintheblank =
            '<div class="cloze_answer"><p class="description">' + __( 'Enclose the searched words with { } e.g. "I {play} soccer". Capital and small letters will be ignored.', 'ld-dashboard' ) + '</p><p class="description">' + __( 'You can specify multiple options for a search word. Enclose the word with [ ] e.g.', 'ld-dashboard' ) + '<span style="font-style: normal; letter-spacing: 2px;">' + __( '"I {[play][love][hate]} soccer"</span>. In this case answers play, love OR hate are correct.</p><p class="description" style="margin-top: 10px;">If mode "Different points for every answer" is activated, you can assign points with |POINTS. Otherwise 1 point will be awarded for every answer.</p><p class="description">e.g. "I {play} soccer, with a {ball|3}" - "play" gives 1 point and "ball" 3 points.', 'ld-dashboard' ) + '</p></div>';
          fillintheblank +=
            "<div class='appendsingleContent' data-item_key='" +
            index +
            "'>";
          fillintheblank +=
            "<div class='correct-singleContent-answer-input'>";
          fillintheblank += "<div class='answer-input'>";
          fillintheblank +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[0][answer]' value=''></textarea><br>";
          fillintheblank += "</div>";
          fillintheblank += "</div>";
          fillintheblank += "</div>";
          return fillintheblank;
        }

        if (type == "assessment_answer") {
          // html content for assessment choice
          var assessment =
            '<div class="assessment_answer"><p class="description">' + __( 'Here you can create an assessment ','ld-dashboard' ) + ld_dashboard_js_labels.question + __( '.</p><p class="description">Enclose a assesment with {}. The individual assessments are marked with [].<br>The number of options in the maximum score.</p><p>	Examples:<br>* less true { [1] [2] [3] [4] [5] } more true</p><p>* less true { [a] [b] [c] } more true</p><p></p></div>', 'ld-dashboard' );
          assessment +=
            "<div class='appendsingleContent' data-item_key='" +
            index +
            "'>";
          assessment +=
            "<div class='correct-singleContent-answer-input'>";
          assessment += "<div class='answer-input'>";
          assessment +=
            "<textarea type='text' name='sfwd-question_single_answer_cld[0][answer]' value=''/></textarea><br>";
          assessment += "</div>";
          assessment += "</div>";
          assessment += "</div>";
          return assessment;
        }

        if (type == "essay") {
          // html content for easy_open_ans choice
          var easy_open_ans = "";
          easy_open_ans +=
            "<div class='container appendsingleContent ld-dashboard-question-answer-box' data-item_key='" +
            index +
            "'>";
          easy_open_ans +=
            "<div class='correct-singleContent-answer-input'>";
          easy_open_ans += "<div class='answer-input'>";
          easy_open_ans +=
            "<label>" + __( 'How should the user submit their answer?', 'ld-dashboard' ) + "</label>";
          easy_open_ans +=
            "<select name='sfwd-question_single_answer_cld[0][gradedType]'><option value=''>" + __( 'Select', 'ld-dashboard' ) + "</option><option value='text'>" + __( 'Text Box', 'ld-dashboard') + "</option><option value='upload'>" + __( 'Upload', 'ld-dashboard' ) + "</option></select>";
          easy_open_ans +=
            "<label>" + sprintf( __( "This is a %s that can be graded and potentially prevent a user from progressing to the next step of the course.", "ld-dashboard" ), ld_dashboard_js_labels.question ) + "</label>";
          easy_open_ans +=
            "<label>" + __( "The user can only progress if the essay is marked as 'Graded' and if the user has enough points to move on.", "ld-dashboard" ) + "</label>";
          easy_open_ans +=
          "<label>" + sprintf( __( "How should the answer to this %s be marked and graded upon quiz submission?", "ld-dashboard" ), ld_dashboard_js_labels.question ) + "</label>";
          easy_open_ans +=
            "<select name='sfwd-question_single_answer_cld[0][gradingProgression]'><option value=''>" + __( 'Select', 'ld-dashboard' ) + "</option><option value='not-graded-none'>" + __( 'Not Graded, No Points Awarded', 'ld-dashboard' ) + "</option><option value='not-graded-full'>" + __( 'Not Graded, Full Points Awarded', 'ld-dashboard' ) + "</option><option value='graded-full'>" + __( 'Graded, Full Points Awarded', 'ld-dashboard' ) + "</option></select>";
          easy_open_ans += "</div>";
          easy_open_ans += "</div>";
          easy_open_ans += "</div>";
          return easy_open_ans;
        }
      }

      function initSortableAnswers() {
        jQuery("#FirstDiv").sortable({
          scrollSpeed: 1,
          axis: "y",
          cursor: "move",
          handle: ".move-ques-ans",
          scrollSensitivity: 1,
          update: function (event, ui) {
            setAnswerOrder();
          },
        });
      }

      function setAnswerOrder() {
        let newOrder = '[';
        jQuery("#FirstDiv").find(".appendsingleContent").each(function (index, child) {
          let key = jQuery(this).attr('data-item_key');
          newOrder += key;
          if ( index < ( jQuery('.appendsingleContent').length - 1 ) ) {
            newOrder += ',';
          }
        });
        newOrder += ']';
        jQuery( '.ld-dashboard-answer-order').val(newOrder);
      }

      if (jQuery("#FirstDiv .appendsingleContent").length > 1) {
        initSortableAnswers();
      }
      if (jQuery(".move-ques-ans").length) {
        jQuery(".move-ques-ans").css("cursor", "move");
      }

      jQuery("#FirstDiv").on("click", ".move-ques-ans", function (e) {
        e.preventDefault();
      });
      // Check selected answer option value
      jQuery('input[name="acf[field_61c030e4a9f63]"]').click(function () {
        var val = jQuery(
          'input[name="acf[field_61c030e4a9f63]"]:checked'
        ).val();
        jQuery("#FirstDiv").html("");
        questionAnswersingleContent(val);
        jQuery(".add-new-user-btn").css({ display: "block" });
        if (val == "free_answer") {
          jQuery(".add-new-user-btn").css({ display: "none" });
        }
        if (val == "cloze_answer") {
          jQuery(".add-new-user-btn").css({ display: "none" });
        }
        if (val == "essay") {
          jQuery(".add-new-user-btn").css({ display: "none" });
        }
        if (val == "assessment_answer") {
          jQuery(".add-new-user-btn").css({ display: "none" });
        }
        jQuery("button.add-new-user-btn").attr("data-type", val);
      });

      if ( jQuery('#custom_ld_answer_field').length ) {
        let answerType = jQuery('#custom_ld_answer_field').data('type');
        let hideArr = ['free_answer', 'assessment_answer', 'essay', 'cloze_answer'];
        if ( hideArr.includes( answerType ) ) {
          jQuery(".add-new-user-btn").css({ display: "none" });
        }
      }
      

      if (jQuery("#custom_ld_answer_field").length) {
        let answerCount = jQuery("#custom_ld_answer_field").attr(
          "data-answers"
        );
        jQuery('#custom_ld_answer_field').prev('.acf-input-wrap').remove();
        let answerType = jQuery("#custom_ld_answer_field").attr("data-type");
        if (answerCount == "0" || answerCount == undefined) {
          answerType =
            answerType != "" && answerType != undefined ? answerType : "single";
          questionAnswersingleContent(answerType);
        }
      }

      if( jQuery('.ldd-single-choice-radio').length ) {
        jQuery('#FirstDiv').on('click', '.ldd-single-choice-radio', function(){
          jQuery('.ldd-single-choice-radio').each(function( el, index ){
            jQuery(this).prop('checked', false);
            jQuery(this).removeAttr("checked")
          });
          jQuery(this).attr('checked', 'checked');
          jQuery(this).prop('checked', true);
        });
      }

      // load html content according to the selected answer option value
      function questionAnswersingleContent(type) {
        let element_count = '0';
        jQuery("#FirstDiv").find(".appendsingleContent").each(function (index, child) {
          let key = jQuery(this).attr('data-item_key');
          if ( parseInt( key ) > parseInt( element_count ) ) {
            element_count = key;
          }
        });
        let htmlContent = question_get_form_html(type, ( parseInt( element_count ) + 1 ));
        jQuery("#FirstDiv").append(htmlContent);
      }

      // add html content on click the add new user button according to the selected answer option value
      jQuery(".add-new-user-btn").click(function (e) {
        e.preventDefault();
        let type = jQuery(this).attr("data-type");
        questionAnswersingleContent(type);
        if (jQuery("#FirstDiv .appendsingleContent").length > 1) {
          if (jQuery(".move-ques-ans").length) {
            jQuery(".move-ques-ans").css("cursor", "move");
          }
          initSortableAnswers();
        }
        setAnswerOrder();
      });

      // delete html content on click the belete button
      jQuery("#FirstDiv").on("click", ".delete-ques-ans", function (e) {
        e.preventDefault();
        jQuery(this).closest(".appendsingleContent").remove();
        if (jQuery("#FirstDiv .appendsingleContent").length < 2) {
          if (jQuery(".move-ques-ans").length) {
            jQuery(".move-ques-ans").css("cursor", "auto");
          }
          //$("#FirstDiv").sortable("disable");
        }
        setAnswerOrder();
      });

      // Create a new media frame
      var frame = wp.media({
        title: __( "Select or Upload Media Of Your Chosen Persuasion", "ld-dashboard" ),
        button: {
          text: __( "Use this media", "ld-dashboard" ),
        },
        multiple: false,
      });

      jQuery("#FirstDiv").on("click", ".add-media-ques-ans", function (event) {
        event.preventDefault();
        $(".allow_html").prop("checked", true);
        jQuery(this)
          .parent()
          .parent()
          .find(".correct-singleContent-answer-input")
          .find(".answer-input")
          .find("textarea")
          .addClass("ansUrl");
        if (frame) {
          frame.open();
          return;
        }
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        let oldVal = jQuery(".answer-input > .ansUrl").val();
        oldVal +=
          '<img src="' + attachment.url + '" alt="" style="max-width:100%;"/>';
        jQuery(".answer-input > .ansUrl").val(oldVal);
        jQuery(".answer-input > .ansUrl").removeClass("ansUrl");
        frame.close();
      });
    }
  });
})(jQuery);
