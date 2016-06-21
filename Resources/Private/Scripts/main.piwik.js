var _paq = _paq || [];

$(function() {
   if ($('#trackingCode').length === 0) {
      return;
   }

   var currentUser = window.location.pathname.replace(/.+@user-([a-z]+)\.html/i, '$1');
   currentUser = (currentUser !== window.location.pathname) ? currentUser : null;

   if (typeof drkException === 'string' && drkException === 'notFoundExceptions') {
      _paq.push(['setDocumentTitle', '404/URL = ' + encodeURIComponent(document.location.pathname + document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);
   }
   _paq.push(['removeDownloadExtensions', "jpeg|jpg"]);
   _paq.push(['trackPageView']);
   _paq.push(['enableLinkTracking']);

   var u = $('#trackingCode').attr('data-host');
   u='//'+u+'/';

   _paq.push(['setTrackerUrl', u + 'piwik.php']);
   _paq.push(['setSiteId', $('#trackingCode').attr('data-siteId')]);
   var d = document,
      g = d.createElement('script'),
      s = d.getElementsByTagName('script')[0];
   g.type = 'text/javascript';
   g.async = true;
   g.defer = true;
   g.src = u + 'piwik.js';
   s.parentNode.insertBefore(g, s);
});
