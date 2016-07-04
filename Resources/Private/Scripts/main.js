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

$('form input').on('invalid', function() {
   var el = $(this);
   var name = el.attr('name') || el.attr('id');

   if (Piwik && name) {
      Piwik.getTracker().trackEvent('Form', 'invalid', name);
   }
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

/**
 * Workaround until Weissheiten/Weissheiten.Neos.Bootstrap#7 is merged.
 * 
 * https://github.com/Weissheiten/Weissheiten.Neos.Bootstrap/pull/7
 */

$(".icon-next").each(function() {
   $(this).removeClass('icon-next').addClass('glyphicon glyphicon-chevron-right');
});

$(".icon-prev").each(function() {
   $(this).removeClass('icon-prev').addClass('glyphicon glyphicon-chevron-left');
});

var typeTimeout;

function typeNames(el, names, i, j) {
   if (i >= names.length) return;

   if (j >= names[i].length) {
      typeTimeout = setTimeout(function() {
         typeNames(el, names, i + 1, 0);
      }, 3000);

      return;
   }

   var n = el.text() || '';

   if (j === 0) n = '';

   el.css('line-height', el.height() + 'px');

   typeTimeout = setTimeout(function() {
      el.text(n + names[i][j]);

      if (el.prop('scrollHeight') > el.height()) {
         var fontSize = parseInt(el.css('font-size'));

         while (fontSize > 14 && el.prop('scrollHeight') > el.height()) {
            el.css('font-size', (--fontSize) + 'px');
         }
      }

      typeNames(el, names, i, j + 1);
   }, Math.random() * 800);
}

if ('transform' in $('body')[0].style) {
   (function() {
      var container = $('[alt$="animated"]').parent();
      container.addClass('your-name-container');

      if (container.length === 0) {
         return;
      }

      var yourName = $('<div>');
      yourName.addClass('your-name');
      yourName.appendTo(container);
      yourName.css('line-height', yourName.height() + 'px');
      yourName.click(function(ev) {
         ev.stopPropagation();

         clearTimeout(typeTimeout);
         
         if (Piwik) {
            Piwik.getTracker().trackEvent('Participate', 'change');
         }

         var name = prompt('Wie ist dein Name?');
         localStorage.setItem('name', name || '');

         if (name) {
            typeNames(yourName, [name], 0, 0);
         }

         return false;
      });

      var names = ['Ursula', 'Peter', 'Dein Name?'];

      if (localStorage && localStorage.getItem('name') && typeof localStorage.getItem('name') === 'string') {
         names = [localStorage.getItem('name')];
      }

      typeNames(yourName, names, 0, 0);
   }());
}

$('div[data-identifier]').each(function(){
   var el = $(this);
   
   el.html('<p><div class="spinner"><div class="loader"/></div> (Lade Daten)</p>');
   
   $.ajax({
      method: 'GET',
      data: {
         nodeId: el.attr('data-identifier'),
         ajax: true
      },
      success: function(response) {
         var html = $(response);
         el.after(html);

         initPreviews(html);
         fitGallery(html.find('.gallery'));

         el.remove();
      }
   });
});
