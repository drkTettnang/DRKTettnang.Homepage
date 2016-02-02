
if (drk && drk.config && drk.config.piwikUrl) {
   var currentUser = window.location.pathname.replace(/.+@user-([a-z]+)\.html/i, '$1');
   currentUser = (currentUser !== window.location.pathname) ? currentUser : null;
   var _paq = _paq || [];
   if (drkException && drkException === 'notFoundExceptions') {
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
