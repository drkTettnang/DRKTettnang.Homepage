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
