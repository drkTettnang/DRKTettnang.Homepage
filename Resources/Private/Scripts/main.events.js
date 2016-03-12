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

   if (typeof von.toLocaleString === 'function') {
      datestring += von.toLocaleString(lang, {
         weekday: 'short',
         year: 'numeric',
         month: 'numeric',
         day: 'numeric'
      });

      if (bis) {
         datestring += ' von ' + von.toLocaleString(lang, {
            hour: 'numeric',
            minute: 'numeric'
         });
         datestring += ' bis ' + bis.toLocaleString(lang, {
            hour: 'numeric',
            minute: 'numeric'
         });
      } else {
         datestring += ' um ' + von.toLocaleString(lang, {
            hour: 'numeric',
            minute: 'numeric'
         });
      }

      datestring += ' Uhr';
   } else {
      datestring += von.getDate() + '.' + (von.getMonth() + 1) + '.' + von.getFullYear();

      if (bis) {
         datestring += ' von ' + von.getHours() + ':' + von.getMinutes();
         datestring += ' bis ' + bis.getHours() + ':' + bis.getMinutes();
      } else {
         datestring += ' um ' + von.getHours() + ':' + von.getMinutes();
      }

      datestring += ' Uhr';
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
      var categoryClasses = $.map(categories, function(category) {
         return category.replace(/ /g, '-').toLowerCase();
      });
      var titleString = title.find('.katlabel').remove().end().text().trim().replace(/\s\s+/g, ' ');

      if (!locationString.match(options.locationRegex) || !titleString.match(options.titleRegex)) {
         return;
      }

      details.push('<div class="title">' + titleString + '</div>');
      details.push('<div class="fromTo">' + fromToString + '</div>');
      details.push('<div class="location">' + locationString + '</div>');

      $('<td>').html(details.join('')).appendTo(tr);

      tr.addClass(categoryClasses.join(' '));

      /*$.each(categories, function(index, category){
         var hash = hashStr(category);
         var hue = Math.abs(hash) % 360;
         var saturation = 90;
         var lightness = 65;

         var span = $('<span>');
         span.addClass(categoryClasses[index]);
         span.addClass('category');
         span.css({
            'background-color': 'hsl(' + hue + ', ' + saturation + '%, ' + lightness + '%)',
            'color': '#fff'
         });
         span.text(category);

         tr.find('.title').append(span);
      });*/

      table.append(tr);
      i++;
   });

   container.empty();

   if (table.find('tr').length > 0) {
      container.append(table);
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
      ov: dom.data('ov') || '',
      url: dom.data('url')
   };

   options.locationRegex = new RegExp(options.location, 'i');
   options.titleRegex = new RegExp(options.title, 'i');

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
