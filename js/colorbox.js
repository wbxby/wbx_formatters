(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initColorboxYoutube = {
    attach: function (context, settings) {
      if (!$.isFunction($.colorbox) || typeof settings.colorboxYoutube === 'undefined') {
        return;
      }

      if (settings.colorboxYoutube.mobiledetect && window.matchMedia) {
        // Disable Colorbox for small screens.
        var mq = window.matchMedia('(max-device-width: ' + settings.colorboxYoutube.mobiledevicewidth + ')');
        if (mq.matches) {
          return;
        }
      }

      settings.colorboxYoutube.rel = function () {
        return $(this).data('colorbox-gallery')
      };

      $('.colorbox-youtube', context)
        .once('init-colorbox-youtube')
        .colorbox(settings.colorboxYoutube);
    }
  };

})(jQuery, Drupal);
