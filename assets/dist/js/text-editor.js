!function(){const t={NODE_ENV:"production"};try{if(process)return process.env=Object.assign({},process.env),void Object.assign(process.env,t)}catch(t){}globalThis.process={env:t}}(),function(t){function e(){t(".text-format-editor").each((function(){var e=t(this),s=e.val(),o=e.attr("id");if(o){var a=CKEDITOR.instances[o];a&&!e.hasClass("text-editor-process")&&(e.addClass("text-editor-process"),a.setData(s),a.on("change",(function(){var t=this.getData();e.val(t).attr("data-editor-value-original",t)})),a.on("destroy",(function(t){e.removeClass("text-editor-process")})))}}))}Drupal.behaviors.bcUtilityTextEditor={attach:function(t,s){e()}},t(document).ajaxSuccess((function(t,s,o){o.url.indexOf("editor/filter_xss")>=0&&e()}))}(jQuery);