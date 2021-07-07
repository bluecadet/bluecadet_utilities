(function ($) {
  Drupal.behaviors.bcSimpleFormatter = {
    attach: function (context, settings) {
      $(".form-item .content-editable:not(.ec-bound)", context).each(function (i, el) {
        $attached_input = $(el).parents('.simple-formatter-field-container').find("input");
        $(el).on("blur keyup paste input", function () {
          $(this).parents('.simple-formatter-field-container').find("input").val(this.innerHTML);
        });
        $attached_input.on("keyup change", function () {
          $(this).parents('.simple-formatter-field-container').find(".content-editable")[0].innerHTML = $(this).val();
        }).trigger("change").on('invalid', function () {
          // Flip to real form item so browser validation works ok.
          $(this).parents('.simple-formatter-field-container').addClass('raw-mode');
        }); // Attach once.

        $("button.simple-editor-button", context).on('click', function (e) {
          e.preventDefault();

          if ($(this).hasClass('simple-editor-button--bold')) {
            document.execCommand('bold', false, null);
          }

          if ($(this).hasClass('simple-editor-button--italic')) {
            document.execCommand('italic', false, null);
          }

          if ($(this).hasClass('simple-editor-button--underline')) {
            document.execCommand('underline', false, null);
          }

          if ($(this).hasClass('simple-editor-button--remove_formatting')) {
            document.execCommand('removeFormat', false, null);
          }

          if ($(this).hasClass('simple-editor-button--toggle-source')) {
            $(this).parents('.simple-formatter-field-container').toggleClass('raw-mode');
          }
        });
        $(el).addClass('ec-bound');
      });
    }
  };
})(jQuery);

//# sourceMappingURL=simple-formatter.js.map
