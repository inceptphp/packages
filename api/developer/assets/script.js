jQuery(function($) {
  $(window).on('bootstrap-validator-init', function (e, target) {
    target = $(target);

    if(!target.hasClass('app-form')) {
      return;
    }

    target.on('submit', function(e) {
      if ($('.has-error', target).length) {
        return false;
      }

      //ajax up

      const method = target.attr('method') || 'post';
      const action = target.attr('action') || window.location.href;

      const trigger = $('<span>')
        .data('method', method)
        .data('href', action)
        .data('form', target);

      $(window).trigger('panel-mobile-rest-reload-click', trigger);

      return false;
    });
  });
});
