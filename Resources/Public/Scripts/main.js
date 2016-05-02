var drk = drk || {};
drk["config"] = {  "facebookUrl": "https://facebook.com/drkTettnang",  "piwikUrl": "/piwik/"};
function formatDate(format, date) {
   var escaped = [];

   var weekdayShort = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
   var weekdayLong = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

   /** D	A textual representation of a day, three letters **/
   format = format.replace(/D/g, function() {
      return '{' + weekdayShort[date.getDay()] + '}';
   });

   /** l A full textual representation of the day of the week **/
   format = format.replace(/l/g, function() {
      return '{' + weekdayLong[date.getDay()] + '}';
   });

   format = format.replace(/\{(.+?)\}/g, function(match, $1) {
      escaped.push($1);

      return '{}';
   });

   /** d	Day of the month, 2 digits with leading zeros **/
   format = format.replace(/d/g, function() {
      d = date.getDate();
      return (d < 10) ? '0' + d : d;
   });

   /** j	Day of the month without leading zeros **/
   format = format.replace(/j/g, function() {
      return date.getDate();
   });

   /** m	Numeric representation of a month, with leading zeros **/
   format = format.replace(/m/g, function() {
      m = date.getMonth() + 1;
      return (d < 10) ? '0' + m : m;
   });

   /** n Numeric representation of a month, without leading zeros **/
   format = format.replace(/n/g, function() {
      return date.getMonth() + 1;
   });

   /** Y	A full numeric representation of a year, 4 digits **/
   format = format.replace(/Y/g, function() {
      return date.getFullYear();
   });

   /** y	A two digit representation of a year **/
   format = format.replace(/y/g, function() {
      return (date.getFullYear() + '').slice(2);
   });

   /** G	24-hour format of an hour without leading zeros **/
   format = format.replace(/G/g, function() {
      return date.getHours();
   });

   /** H	24-hour format of an hour with leading zero **/
   format = format.replace(/H/g, function() {
      H = date.getHours();
      return (H < 10) ? '0' + H : H;
   });

   /** i	Minutes with leading zeros **/
   format = format.replace(/i/g, function() {
      i = date.getMinutes();
      return (i < 10) ? '0' + i : i;
   });

   format = format.replace(/\{\}/g, function() {
      return escaped.shift();
   });

   return format;
};

function hashStr(str) {
   var hash = 0,
      i;

   if (str.length === 0) {
      return hash;
   }

   for (i = 0; i < str.length; i++) {
      hash = ((hash << 5) - hash) + str.charCodeAt(i);
      hash |= 0; // Convert to 32bit integer
   }

   return hash;
}

function buildFromToString(datum, von, bis) {
   var datestring = '';
   var lang = 'de-DE';

   if (!datum.match(/^(0?[1-9]|[12][0-9]|3[01])\.(0?[1-9]|1[012])\.\d{4}$/)) {
      return;
   }

   if (!von.match(/^(0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9])(:(0?[0-9]|[1-5][0-9]))?$/)) {
      return;
   }

   if (typeof bis === 'string' && bis.length > 0) {
      if (!bis.match(/^(0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9])(:(0?[0-9]|[1-5][0-9]))?$/)) {
         return;
      }
   } else {
      bis = null;
   }

   datum = datum.split('.');
   von = von.split(':');
   von = new Date(datum[2], datum[1] - 1, datum[0], von[0], von[1], von[2] || 0, 0);

   if (bis) {
      bis = bis.split(':');
      bis = new Date(datum[2], datum[1] - 1, datum[0], bis[0], bis[1], bis[2] || 0, 0);
   }

   datestring += formatDate('D., j.n.Y ', von);

   if (bis) {
      datestring += formatDate('{von} H:i ', von);
      datestring += formatDate('{bis} H:i {Uhr}', bis);
   } else {
      datestring += formatDate('{um} H:i {Uhr}', von);
   }

   return datestring;
}

function displayTrainingEvents(container, xml, options) {
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

function loadTrainingEvents() {
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
      Mon: (typeof dom.data('mon') === 'number' && dom.data('mon') >= 0) ? dom.data('mon') : '',
      Ort: (typeof dom.data('location') === 'string') ? dom.data('location') : ''
   };

   if (!options.url) {
      return;
   }

   dom.html('<p><div class="spinner"><div class="loader"/></div> (Lade Kurstermine)</p>');

   options.url += '?' + $.param(params);

   $.ajax({
      url: options.url,
      method: 'GET',
      dataType: 'xml',
      success: function(xml) {
         displayTrainingEvents(dom, xml, options);
      },
      error: function() {
         console.log('error', arguments);

         dom.html('<p>(Termine konnten nicht geladen werden)</p>');
      }
   });
}

function displayHiorgEvents(container, html, options) {
   options = options || {};

   var globalCategories = {};
   var i = 0;
   var table = $('<table>');

   html.find('tbody tr').each(function() {
      var eventRow = $(this);
      var tr = $('<tr>');
      var details = [];

      if (i >= options.limit && options.limit > 0) {
         return false;
      }

      var dayString = eventRow.find('td:eq(0)').text().trim().replace(/\s\s+/g, ' ');
      dayString = dayString.replace(/.*(\d{2}\.\d{1,2}\.\d{2,4}).*/, '$1');

      var timeString = eventRow.find('td:eq(1)').text().trim().replace(/\s\s+/g, ' ');
      timeStringSplit = timeString.split('-');
      var fromToString = buildFromToString(dayString, $.trim(timeStringSplit[0]), $.trim(timeStringSplit[1]));

      var locationString = eventRow.find('td:eq(2)').text().trim().replace(/\s\s+/g, ' ');
      var title = eventRow.find('td:eq(3)');
      var detailUrl = eventRow.find('td:eq(4) a').attr('href');

      var categories = title.find('.katlabel').map(function() {
         return $(this).text().trim().replace(/\s\s+/g, ' ');
      }).toArray();
      var categoryNumber = title.find('.katlabel').map(function() {
         return $(this).attr('class').replace(/(.*col([0-9]{1,3}))?.*/, '$2');
      }).toArray();
      var categoryClasses = $.map(categories, function(category) {
         return category.replace(/ /g, '-').toLowerCase();
      });
      var titleString = title.find('.katlabel').remove().end().text().trim().replace(/\s\s+/g, ' ');

      if (!locationString.match(options.locationRegex) || !titleString.match(options.titleRegex)) {
         return;
      }

      var categoryFilter = function(val) {
         return val.match(options.categoryRegex);
      };

      if ((categories.length > 0 && categories.filter(categoryFilter).length === 0) || (options.category !== '.*' && categories.length === 0)) {
         return;
      }

      details.push('<div class="title">' + titleString + '</div>');
      details.push('<div class="fromTo">' + fromToString + '</div>');
      details.push('<div class="location"><span class="fa fa-map-marker"></span> ' + locationString + '</div>');

      $('<td>').html(details.join('')).appendTo(tr);

      tr.addClass(categoryClasses.join(' '));

      $.each(categories, function(index, category) {
         var hash = hashStr(category);
         var hue = Math.abs(hash) % 360;
         var saturation = 90;
         var lightness = 55;

         var span = $('<span>');
         span.addClass(categoryClasses[index]);
         span.addClass('category');
         span.css({
            'background-color': 'hsl(' + hue + ', ' + saturation + '%, ' + lightness + '%)',
            'color': '#fff'
         });
         span.attr('title', category);

         if (categoryNumber && categoryNumber[index]) {
            span.addClass('cat' + categoryNumber[index]);
         }

         tr.find('.title').prepend(span);

         if (!globalCategories[categoryClasses[index]]) {
            globalCategories[categoryClasses[index]] = {
               'name': category,
               'number': categoryNumber[index],
               'cat': 'cat' + categoryNumber[index]
            };
         }
      });

      table.append(tr);
      i++;
   });

   container.empty();

   if (table.find('tr').length > 0) {
      container.append(table);

      if (Object && Object.keys && Object.keys(globalCategories).length > 1) {
         var keys = $('<div>');
         keys.addClass('keys');

         var p = $('<p>');
         keys.append(p);

         $.each(globalCategories, function(className, category) {
            var cat = $('<span>');

            cat.addClass(category.cat);
            cat.addClass(className);
            cat.addClass('category');
            cat.attr('title', category.name);
            //cat.text(category.name);

            var key = $('<span>');
            key.addClass('key');
            key.append(cat);
            key.append(document.createTextNode(category.name));

            p.append(key);
         });

         container.append(keys);
      }
   } else {
      container.append('<p>(Keine Termine gefunden)</p>');
   }
}

function loadHiorgEvents() {
   var dom = $(this);

   var options = {
      limit: dom.data('limit') || 0,
      title: dom.data('title') || '.*',
      location: dom.data('location') || '.*',
      category: dom.data('category') || '.*',
      ov: dom.data('ov') || '',
      url: dom.data('url')
   };

   options.locationRegex = new RegExp(options.location, 'i');
   options.titleRegex = new RegExp(options.title, 'i');
   options.categoryRegex = new RegExp(options.category, 'i');

   if (typeof options.ov !== 'string' || options.ov.length === 0 || typeof options.url !== 'string' || options.url.length === 0) {
      return;
   }

   options.url += '?' + $.param({
      ov: options.ov
   });

   dom.html('<p><div class="spinner"><div class="loader"/></div> (Lade Termine)</p>');

   var cache = '';

   try {
      cache = JSON.parse(localStorage.getItem(options.url)) || '';
   } catch (err) {}

   if (cache.time && (new Date().getTime() - cache.time) < 1000 * 60 * 60 && !$('body').hasClass('neos-backend')) {
      displayHiorgEvents(dom, $(cache.content), options);

      return;
   }

   $.ajax({
      url: options.url,
      method: 'GET',
      dataType: 'text',
      success: function(text) {
         text = text.replace(/<img[^>]*src=[^>]*>/gi, '');

         localStorage.setItem(options.url, JSON.stringify({
            time: new Date().getTime(),
            content: text
         }));

         displayHiorgEvents(dom, $(text), options);
      },
      error: function() {
         console.log('error', arguments);

         dom.html('<p>(Termine konnten nicht geladen werden)</p>');
      }
   });
}

$('.events').each(function() {
   switch ($(this).data('type')) {
      case 'hiorg':
         loadHiorgEvents.call(this);
         break;
      case 'training':
      default:
         loadTrainingEvents.call(this);
         break;
   }
});

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

         if ((n === 1 || (remaining.length === 1 && self.hasClass('square'))) && typeof Image === 'function' && self.attr('data-bg-url-max')) {
            self.css('backgroundImage', 'url(' + self.attr('data-bg-url-max') + ')');

            var image = new Image();
            image.onload = function() {
               var scale = image.width / image.height;
               var height = self.width() / scale;

               self.css('height', (height > self.width()) ? self.width() : height);
            };
            image.src = self.attr('data-bg-url-max');
         } else if (self.hasClass('square') && self.attr('data-bg-url-square')) {
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
         var last = (n % r === 0) ? (VISIBLE_ROWS * r) : ((n % r) + (VISIBLE_ROWS - 1) * r);
         last -= 1; // index starts at zero

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

$('.paypal').each(function() {
   var self = $(this);

   self.find('input[type="range"]').rangeslider({
      polyfill: false,
      onSlide: function(pos, val) {
         var slider = this;
         var fill = $(slider.$fill);

         //fill.css('opacity', (0.5 * pos / slider.maxHandlePos) + 0.5);

         if (val > 50) {
            var minp = 50,
               maxp = 100;
            var minv = Math.log(70),
               maxv = Math.log(1000);
            var scale = (maxv - minv) / (maxp - minp);

            val = Math.exp(minv + scale * (val - minp));
            val = Math.round(val / 10) * 10;
            val = Math.floor(val);
         }

         self.find('[name="amount"]').val(val);
      }
   });

   $('[name="amount"]').keyup(function() {
      var number = this.value.replace(/[^0-9]/g, '');

      if (this.value !== number) {
         this.value = number;
      }
   });
});

if (drk && drk.config && drk.config.piwikUrl) {
   var currentUser = window.location.pathname.replace(/.+@user-([a-z]+)\.html/i, '$1');
   currentUser = (currentUser !== window.location.pathname) ? currentUser : null;
   var _paq = _paq || [];
   if (typeof drkException === 'string' && drkException === 'notFoundExceptions') {
      _paq.push(['setDocumentTitle', '404/URL = ' + encodeURIComponent(document.location.pathname + document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);
   }
   _paq.push(['removeDownloadExtensions', "jpeg|jpg"]);
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

if (drk && drk.config && drk.config.facebookUrl) {
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
         'status': 'off',
         'perma_option': 'off',
         'dummy_img': '/_Resources/Static/Packages/DRKTettnang.Homepage/Vendor/socialshareprivacy/images/dummy_twitter.png'
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
