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
