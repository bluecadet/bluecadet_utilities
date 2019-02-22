(function ($) {
  Drupal.behaviors.nhmUtilityTextEditor = {
    attach: function (context, settings) {
      initEditor();
    }
  };

  function initEditor() {
    $('.text-format-editor').each(function () {
      var $field = $(this);

      var $defaultValue = $field.val();
      // OLD version used 'data-drupal-selector', but that doesn't add the AJAX random id
      // var editorName = $field.attr('data-drupal-selector');
      var editorName = $field.attr('id');
      if (editorName) {
        var $editor = CKEDITOR.instances[editorName];

        if ($editor && !$field.hasClass('text-editor-process')) {
          $field.addClass('text-editor-process');
          $editor.setData($defaultValue);
          $editor.on('change', function () {

            var editorData = this.getData();

            $field.val(editorData).attr('data-editor-value-original', editorData);
          });

          $editor.on('destroy', function (event) {
            $field.removeClass('text-editor-process');
          });
        }
      }
    });
  }

  $(document).ajaxSuccess(function (event, xhr, settings) {
    var url = settings.url;

    if (url.indexOf('editor/filter_xss') >= 0) {
      initEditor();
    }
  });

})(jQuery);