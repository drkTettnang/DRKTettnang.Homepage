if (!$('body').hasClass('neos-backend')) {
   $('article, .operation').each(function() {
      var self = $(this);
      var images = self.find('.images a').add(self.find('a[href$=".jpg"]:has(img)'));

      images.magnificPopup({
         tClose: 'Schließen (ESC)',
         tLoading: 'Lade Foto...',
         type: 'image',
         gallery: {
            enabled: true,
            tPrev: 'Vorheriges (Linke Pfeiltaste)',
            tNext: 'Nächstes (Rechte Pfeiltaste)',
            tCounter: '<span class="mfp-counter">%curr%. von %total% Fotos in diesem Beitrag</span>'
         },
         image: {
            tError: '<a href="%url%">Das Foto</a> konnte nicht geladen werden.'
         },
         callbacks: {
            imageLoadComplete: function() {
               var src = this.currItem.src;

               if (Piwik) {
                  Piwik.getTracker().trackLink(src, 'download');
               }
            }
         }
      });
   });
} else {
   $('a[href$=".jpg"]:has(img)').each(function() {
      $(this).attr('href', '#');
   });
}

function fitGallery(galleries) {
   galleries.each(function() {
      var self = $(this);
      var a = self.find('a');
      var n = a.length;
      var w = self.innerWidth();
      var small = false;
      var large;
      var r;

      for (i = 1; i <= n; i++) {
         r = i;

         if (Math.abs((w / i) - 100) < 20) {
            small = true;

            break;
         }
      }

      a.attr('data-width', (100 / r) - 1);
      a.css('width', ((100 / r) - 1) + '%');
      a.css('marginRight', '1%');

      var remaining = (small) ? a.slice(0, (n % r)) : a;

      remaining.css('width', ((100 / (n % r)) - 1) + '%').addClass('square');

      a.each(function() {
         var self = $(this);

         if (self.hasClass('square') && self.attr('data-bg-url-square')) {
            self.css('height', self.width());
            self.css('backgroundImage', 'url(' + self.attr('data-bg-url-square') + ')');
         } else if (self.width() > 240 && self.attr('data-bg-url-large')) {
            self.css('backgroundImage', 'url(' + self.attr('data-bg-url-large') + ')');
         } else if (self.width() > 120 && self.attr('data-bg-url-medium')) {
            self.css('backgroundImage', 'url(' + self.attr('data-bg-url-medium') + ')');
         }
      });

      // display only the following amount of rows by default
      var VISIBLE_ROWS = 3;

      if (Math.ceil(n / r) >= VISIBLE_ROWS) {
         // index of the last visible image
         var last = (n % r) + ((VISIBLE_ROWS - 1) * r) - 1;

         // check if there are more images
         if (last < n - 1) {
            // clear previous attached classes
            a.removeClass('lastImage hiddenImage');

            // need overlay to prevent gallery action
            var more = $('<div>');
            more.addClass('more');
            more.text('+' + (n - last - 1));
            more.click(function(ev) {
               ev.preventDefault();
               ev.stopPropagation();

               a.removeClass('lastImage hiddenImage');

               $(this).remove();
            });

            self.find('a:eq(' + last + ')').addClass('lastImage').empty().append(more);
            self.find('a:gt(' + last + ')').addClass('hiddenImage');
         }
      }
   });
}

var prevW = $(window).width();

fitGallery($('.gallery'));
$(window).resize(function() {
   if (prevW !== $(window).width()) {
      fitGallery($('.gallery'));

      prevW = $(window).width();
   }
});
