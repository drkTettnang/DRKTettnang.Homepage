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
