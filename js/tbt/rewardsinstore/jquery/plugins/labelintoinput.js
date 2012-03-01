jQuery.fn.labelIntoInput = function(options) {
	var settings = {
 		'color':'gainsboro'
	};
	
	return this.each(function(){
		if ( options ) { 
			$.extend( settings, options );
		}	
	   
		var $this = $(this);
		originalColor = $this.css('color');
		$value = $('label[for='+$this.attr('name')+']').html();
		$('label[for='+$this.attr('name')+']').hide();
		$this.data('inputState', $value);
		$this.css({color:settings['color']});
		$this.focus(function() {
			if ($this.val() == $this.data('inputState')) {
				$this.css({color:originalColor});
				$this.val('');
			}
		});
		$this.blur(function() {
			if ($this.val() == '') {
		 		$this.val($this.data('inputState'));
		 		$this.css({"color":settings['color']});
		    }
		});
	});
}