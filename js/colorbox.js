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

      // Check if there are images gallery in this field.
      var reloadNeeds = false;
      $('.colorbox-youtube', context)
        .once('init-colorbox-youtube')
        .each(function() {
          var attachTo = $(this).data('attach-to');
          if (typeof attachTo !== 'undefined') {
            var targetGallery = $(attachTo).find('.colorbox');
            if (targetGallery.length) {
              var galleryToken = targetGallery.data('colorbox-gallery');
              if (typeof galleryToken !== 'undefined' && galleryToken.length) {
                $(this).attr('data-colorbox-gallery', galleryToken);
                reloadNeeds = true;
              }
            }
          }
        });
      if (reloadNeeds) {
        settings.colorbox.iframe = true;
        settings.colorbox.height = '80%';
        settings.colorbox.width = '80%';
        $('.colorbox, .colorbox-youtube', context)
          .colorbox(settings.colorbox);
      } else {
        $('.colorbox-youtube', context)
          .colorbox(settings.colorboxYoutube);
      }
    }
  };

})(jQuery, Drupal);
