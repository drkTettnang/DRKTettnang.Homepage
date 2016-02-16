var drk = drk || {};
drk["config"] = {  "facebookUrl": "https://facebook.com/drkTettnang",  "piwikUrl": "/piwik/"};
function buildFromToString(datum, von, bis) {
   var datestring = '';
   var lang = 'de-DE';
   
   if(!datum.match(/^(0?[1-9]|[12][0-9]|3[01])\.(0?[1-9]|1[012])\.\d{4}$/)) {
      return;
   }
   
   if(!von.match(/^(0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9]):(0?[0-9]|[1-5][0-9])$/)) {
      return;
   }
   
   if(!bis.match(/^(0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9]):(0?[0-9]|[1-5][0-9])$/)) {
      return;
   }
   
   datum = datum.split('.');
   von = von.split(':');
   bis = bis.split(':');
   
   von = new Date(datum[2], datum[1]-1, datum[0], von[0], von[1], von[2], 0);
   bis = new Date(datum[2], datum[1]-1, datum[0], bis[0], bis[1], bis[2], 0);
   
   if (typeof von.toLocaleString === 'function') {
      datestring += von.toLocaleString(lang, {
         weekday: 'short', 
         year: 'numeric', 
         month: 'numeric', 
         day: 'numeric'
      });
      datestring += ' von ' + von.toLocaleString(lang, {
         hour: 'numeric',
         minute: 'numeric'
      });
      datestring += ' bis ' + bis.toLocaleString(lang, {
         hour: 'numeric',
         minute: 'numeric'
      });
      datestring += ' Uhr';
   } else {
      datestring += von.toLocaleDateString(lang);
      datestring += ' von ' + von.toLocaleTimeString(lang);
      datestring += ' bis ' + bis.toLocaleTimeString(lang);
      datestring += ' Uhr';
   }
   
   return datestring;
}

function displayEvents(container, xml, options) {
   options = options || {};
   
   var table = $('<table>');
   
   $(xml).find('lehrgang').each(function(index) {
      if (typeof options.maxEvents === 'number' && options.maxEvents > 0 && index >= options.maxEvents) {
         return false;
      }
      
      var lehrgang = $(this);
      var tr = $('<tr>');
      
      var lehrgangstyp = lehrgang.find('lehrgangstyp bezeichnung').text();
      var ausbildungsort = lehrgang.find('ausbildungsort');
      var ort = ausbildungsort.find('plz').text() + ' ' + ausbildungsort.find('ort').text();
      var adresse = ausbildungsort.find('adresse').text();
      var gebuehr = lehrgang.find('tn_gebuehr').text();
      var plaetze = lehrgang.find('freie_plaetze').text();

      plaetze = parseInt(plaetze);
      plaetze = (!isNaN(plaetze) && plaetze > 0) ? plaetze : 0;
      
      if (options.bezeichnung) {
         $('<td>').addClass('bezeichnung').text(lehrgangstyp).appendTo(tr);
      }
      
      var details = [];
      
      var termine = lehrgang.find('termine termin');
      termine.each(function() {
         var termin = $(this);
         var datum = termin.find('datum').text();
         var von = termin.find('uhrzeit_von').text();
         var bis = termin.find('uhrzeit_bis').text();
         
         details.push('<span class="fromTo">' + buildFromToString(datum, von, bis) + '</span>');
      });
      
      details.push('<span class="ort">' + ort + ', ' + adresse + '</span>');
      
      if (plaetze === 0) {
         details.push('<span class="plaetze">Leider sind alle Plätze vergeben.</small>');
      } else {
         details.push('<span class="preis">Preis: <em>' + gebuehr + '€</em>,</span> <span class="plaetze">Freie Plätze: <em>' + plaetze + '</em></span>');
      }
      
      $('<td>').html(details.join('<br />')).appendTo(tr);
      
      var url = lehrgang.find('url_anmeldung').text();
      if (url) {
         var link = $('<a>');
         link.text('Anmelden');
         link.attr('href', url);
         link.attr('target', '_blank');
         $('<td>').append(link).appendTo(tr);
      } else {
         $('<td>').appendTo(tr);
      }
      
      tr.attr('data-plaetze', plaetze);
      
      tr.appendTo(table);
   });
   
   container.empty();

   if (table.find('tr').length > 0) {
      container.append(table);
   } else {
      container.append('<p>(Keine Kurstermine gefunden)</p>');
   }
}

$('.events').each(function() {
   var dom = $(this);
   
   var options = {
      bezeichnung: dom.data('bezeichnung') || false,
      maxEvents: dom.data('maxevents') || 0,
      url: dom.data('url')
   };

   var params = {
      Lic: dom.data('lic'),
      XML: 'J',
      LgTyp: dom.data('lgtyp') || '0',
      Mon: (typeof dom.data('mon') === 'number' && dom.data('mon') >= 0)? dom.data('mon') : '',
      Ort: (typeof dom.data('location') === 'string') ? dom.data('location') : ''
   };
   
   if (!options.url) {
      return;
   }
   
   dom.html('<p>(Lade Kurstermine)</p>');
   
   options.url += '?' + $.param(params);

   $.ajax({
      url: options.url,
      method: 'GET',
      dataType: 'xml',
      success: function(xml) {
         displayEvents(dom, xml, options);
      },
      error: function() {
         console.log('error', arguments);
         
         dom.html('<p>(Termine konnten nicht geladen werden)</p>');
      }
   });
});


if (drk && drk.config && drk.config.piwikUrl) {
   var currentUser = window.location.pathname.replace(/.+@user-([a-z]+)\.html/i, '$1');
   currentUser = (currentUser !== window.location.pathname) ? currentUser : null;
   var _paq = _paq || [];
   if (typeof drkException === 'string' && drkException === 'notFoundExceptions') {
      _paq.push(['setDocumentTitle',  '404/URL = ' +  encodeURIComponent(document.location.pathname+document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);
   }
   _paq.push(['trackPageView']);
   _paq.push(['enableLinkTracking']);
   (function() {
      var u = drk.config.piwikUrl;
      if (!u.match(/^(http|\/\/)/)) {
         if (u.match(/^\//)) {
            u = '/' + u;
         }
         u = '//' + window.location.host + u;
      }
      if (currentUser) {
         _paq.push(['setUserId', currentUser]);
      }
      _paq.push(['setTrackerUrl', u + 'piwik.php']);
      _paq.push(['setSiteId', 1]);
      var d = document,
         g = d.createElement('script'),
         s = d.getElementsByTagName('script')[0];
      g.type = 'text/javascript';
      g.async = true;
      g.defer = true;
      g.src = u + 'piwik.js';
      s.parentNode.insertBefore(g, s);
   })();
}

var socialUri;

if(drk && drk.config && drk.config.facebookUrl) {
   socialUri = function() {
      return drk.config.facebookUrl;
   };
}

$('#socialmedia').socialSharePrivacy({
   'css_path': '',
   'lang_path': '/_Resources/Static/Packages/DRKTettnang.Homepage/Vendor/socialshareprivacy/lang/',
   'language': 'de',
   'uri': socialUri,
   services: {
      facebook: {
         'action': 'like',
         'perma_option': 'off',
         'dummy_img': '/_Resources/Static/Packages/DRKTettnang.Homepage/Vendor/socialshareprivacy/images/dummy_facebook_like.png'
      },
      twitter: {
         'status': 'off'
      },
      gplus: {
         'perma_option': 'off',
         'dummy_img': '/_Resources/Static/Packages/DRKTettnang.Homepage/Vendor/socialshareprivacy/images/dummy_gplus.png'
      },
   }
});

/** Google Plus */
window.___gcfg = {
   lang: "de"
};

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
      var self = $(this);
      var images = self.find('.images a').add(self.find('a:has(img)'));

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
