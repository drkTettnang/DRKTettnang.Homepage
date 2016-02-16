$('#main-sidebar>*').width($('#main-sidebar').width() + 'px');

$(window).resize(function() {
   $('#main-sidebar>*').width($('#main-sidebar').width() + 'px');
});

if ($('#main-content').height() > $('#main-sidebar').height()) {
   $('#main-sidebar > .inner').affix({
      offset: {
         top: $('#main-sidebar > .inner').offset().top,
         bottom: $('footer').outerHeight(true) + 30
      }
   });
}

$('[data-toggle="tooltip"]').not('input').tooltip();

$('input[data-toggle="tooltip"]').tooltip({
   placement: "bottom",
   trigger: "focus"
});

if (!$('body').hasClass('neos-backend')) {
   $('a[href$=".jpg"]:has(img)').not('.images a').magnificPopup({
      type: 'image'
   });

   $('article').each(function() {
      var images = $(this).find('.images a');
      images.add($(this).find('a:has(img)'));

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
         }
      });
   });
} else {
   $('a[href$=".jpg"]:has(img)').each(function() {
      $(this).attr('href', '#');
   });
}

$('.gallery').each(function() {
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

   a.slice(-1 * (n % r)).css('width', ((100 / (n % r)) - 1) + '%');

   a.each(function(){
      var self = $(this);
      
      if(self.width() > 240 && self.attr('data-bg-url-large')) {
         self.css('backgroundImage', 'url('+ self.attr('data-bg-url-large') +')');
      } else if(self.width() > 120 && self.attr('data-bg-url-medium')) {
         self.css('backgroundImage', 'url('+ self.attr('data-bg-url-medium') +')');
      }
   });
});

$('[data-edon][data-niamod]').each(function() {
   var href = 'mailto:';
   href += $(this).attr('data-edon');
   href += '@';
   href += $(this).attr('data-niamod');

   $(this).attr('href', href);
});

function formToObject(form) {
   form = form || $('form').first();

   var obj = {};

   form.find('.form-group').each(function() {
      var group = $(this);
      var label = group.find('.control-label') || group.find('label');

      if (!label) {
         return;
      }

      label = label.first().text();

      var inputs = {};

      group.find('input, textarea').each(function() {
         var input = $(this);
         var name = input.attr('name');
         var value = input.val();

         if (name && value) {
            inputs[name] = value;
         }
      });

      obj[label] = inputs;
   });

   return obj;
}

$('.cache input').each(function() {
   var input = $(this);
   var key = window.location.pathname + '#' + input.attr('name');
   var saved = localStorage.getItem(key);

   if (saved) {
      input.val(saved);
   }
});

$('.cache input').change(function() {
   var input = $(this);
   var key = window.location.pathname + '#' + input.attr('name');

   localStorage.setItem(key, input.val());
});

$('form').on('reset', function() {
   var form = $(this);

   setTimeout(function() {
      form.find('input').change();
   }, 500);
});

$('.printOnClick').click(function() {
   window.print();
});

if ($('button.reset').length && $('button[type="reset"]').length) {

   $('button.reset').show();
   $('button[type="reset"]').hide();

   $('button.reset').click(function() {
      $('body').click(function() {
         $('button.reset').show();
         $('button[type="reset"]').hide();
      });

      $(this).button('reset');

      setTimeout(function() {
         $('button.reset').hide();
         $('button[type="reset"]').show();
      }, 200);

      return false;
   });

   $('button[type="reset"]').removeClass('btn-default').addClass('btn-danger').each(function() {
      $(this).text($(this).attr('data-value'));
   });
}

$('.time').clockpicker({
   donetext: 'Fertig',
   minutestep: 5,
   autoclose: true
});

$('.date input').datepicker({
   format: "dd.mm.yyyy",
   weekStart: 1,
   startDate: "today",
   language: "de",
   calendarWeeks: true,
   autoclose: true,
   todayHighlight: true
});
