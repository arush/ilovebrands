document.observe('dom:loaded', function(){


	if ($('postcode')) {
		Event.observe($('postcode'), 'blur', function(e){
			shutl.times();
		});
	
	}

	Ajax.Responders.register({
		onComplete: function(request) {
			if (request.url.indexOf('progress') >= 0) {
				shutl.dateSelector();	
			
				if ($('s_method_shutl_shutl_later') && $('shipping-method-buttons-container').getStyle('display') == 'none') {
					$('s_method_shutl_shutl_later').checked = 'checked';
					$('shutlOptions').setStyle({ 'display' : 'block' });					
					$('shipping-method-buttons-container').show();
				
				}
				$$('#co-shipping-method-form input.radio').each(function(obj){
				
					Event.observe(obj, 'click', function(){
						if (this.value == 'shutl_shutl_later') {
							$('shutlOptions').show();
						} else {
							$('shutlOptions').hide();								
						}
					});
				});
			}
			if (request.url.indexOf('saveShipping') >= 0 || request.url.indexOf('saveBilling') >= 0) {

				shutl.dateSelector();	
				shutl.times();

			}
			if (request.url.indexOf('shutl/message') < 0) {
				shutl.messages();			
			}


		}
	
	});


	$$('a.shutl_service').each(function(obj){
		Event.observe(obj, 'click', function(e){
			shutl.about(this.href);
			Event.stop(e);
		});
		
	});
	
	
	if ($('shipping-zip-form')) {

		var ajax = new Ajax.Request(
			'/shutl/quote/form', {
				method: 'post',
				onSuccess: function(transport) {

					$$('#shipping-zip-form .buttons-set .button').each(function(obj){
						obj.insert({ 'before' : transport.responseText });
						shutl.dateSelector();
						shutl.times();												
					});
					
					$$('#shipping-zip-form .method').each(function(obj){
					
						Event.observe(obj, 'click', function(){
					
							if (this.value == 'shutl_later') {
								$('shutlLaterOptions').show();
							} else {
								$('shutlLaterOptions').hide();								
							}
						});
					});


					if ($('country') && $('shutlOptions')) {
				
						if ($('country').options[$('country').selectedIndex].value.toLowerCase() != 'gb') {
							$('shutlOptions').hide();	 			
						}
					
						Event.observe($('country'), 'change', function(e){
							if (this.options[this.selectedIndex].value.toLowerCase() == 'gb') {
								$('shutlOptions').show();
							} else {
								$('shutlOptions').hide();	 		
							}
					
						});
					}					
					
				}
			}
		);

		$('shipping-zip-form').action = '/shutl/cart/estimatePost';
		Event.observe($('shipping-zip-form'), 'submit', function() {
			Event.stop();
			this.action = '/shutl/cart/estimatePost';
			this.submit();
		});

	}

	
});

var shutl = new Object();
shutl.about = function(href) {
	document.body.insert('<div class="shutl overlay loading"></div>');
	document.body.insert('<div class="shutl content"><a class="shutl close"><span>close</span></a></div>');	
	document.body.setStyle({ overflow: 'hidden' });
	
	var dimensions = shutl.windowSize();


	$$('.shutl.overlay').each(function(obj){
		obj.setStyle(dimensions);
		obj.setStyle({ opacity: 0 });
		obj.fade({ duration: 1.0, from: 0, to: 0.7 });

		Event.observe(obj, 'click', function(){
			document.body.setStyle({ overflow: 'auto' });
			this.next().remove();						
			this.remove();

		});
		
	});


	$$('.shutl.close').each(function(obj){
	
		Event.observe(obj, 'click', function(){
			document.body.setStyle({ overflow: 'auto' });
			this.up().previous().remove();						
			this.up().remove();

		});
		
	});

	var ajax = new Ajax.Request(
		href, 
		{
			method: 'post',
			onSuccess: function(transport) {


				$$('.shutl.content').each(function(obj){
					obj.insert(transport.responseText);
				
					var offsets = document.viewport.getScrollOffsets();
				
					var left = ((parseInt(dimensions.width) - parseInt(obj.getStyle('width'))) / 2) + offsets.left + 'px';
					var top = ((parseInt(dimensions.height) - parseInt(obj.getStyle('height'))) / 2) + offsets.top + 'px';


					obj.setStyle({ opacity: 0 });
					obj.setStyle({ left: left, top: top });

					obj.fade({ duration: 1.0, from: 0, to: 1 });
					
					$$('.shutl.quote .postcode').each(function(obj){
						Event.observe(obj, 'focus', function(e){
							if (this.value == 'Enter postcode here') {
								this.value = '';
							}
						})
						Event.observe(obj, 'blur', function(e){
							if (this.value == '') {
								this.value = 'Enter postcode here';
							} else if (this.value != 'Enter postcode here') {
								shutl.times();
							}
						})
					
					});

					if ($('date') && $('time')) {
						shutl.dateSelector();					
						shutl.times();																	
						Event.observe($('date'), 'focus', function(){
						
							$$('.shutl.quote .results').each(function(obj){
								obj.hide();
							});
						});
	
						Event.observe($('time'), 'change', function(){
							$$('.shutl.quote .results').each(function(obj){
								obj.hide();
							});
						});					
					}
					$$('.shutl.quote form').each(function(obj){
						Event.observe(obj, 'submit', function(e){
							var quoteInfo = this.action + 'postcode/' + escape(this['postcode'].value);

							for( i = 0; i < this['method'].length; i++ ){
								if(this['method'][i].checked == true ) {
									quoteInfo += '/method/' + escape(this['method'][i].value);							
									break;
								}
							}
							
							if ($('date') && $('time')) {
							
								var date = escape(this['date'].value.replace(/\//g, '-'));
	
								quoteInfo += '/date/' + date;
								quoteInfo += '/time/' + escape(this['time'].value);							


							}
							
							
							shutl.getQuote(quoteInfo);
							Event.stop(e);
						});	
					});
					
					$$('.shutl.quote .method').each(function(obj){
					
						Event.observe(obj, 'click', function(){

							$$('.shutl.quote .results').each(function(obj){
								obj.hide();
							});

						
							if (this.value == 'shutl_later') {
								$('shutlLaterOptions').show();
							} else {
								$('shutlLaterOptions').hide();								
							}
						});
					});
					
				});
				
			}
		}
	);
}


shutl.getQuote = function(url) {
	var oldLabel = '';
	$$('.shutl.quote .get_quote').each(function(obj){
		oldLabel = obj.value;
		obj.value = 'Loading...';
		obj.disabled = 'disabled';
	});

	var ajax = new Ajax.Request(
		shutl.url(url), 
		{
			method: 'post',
			onSuccess: function(transport) {

				$$('.shutl.quote .get_quote').each(function(obj){
					obj.disabled = '';
					obj.value = oldLabel;					
				});


				$$('.shutl.quote .results').each(function(obj){
					obj.innerHTML = '';
					obj.insert(transport.responseText);	
					obj.show();
					
				});
			}
		}
	);
}
shutl.windowSize = function(w) {
	var offsets = document.viewport.getScrollOffsets();
    return {
    	width: document.viewport.getWidth() + 'px', 
    	height: document.viewport.getHeight() + 'px',
    	left: offsets.left + 'px',
    	top: offsets.top + 'px'
    
    }
}

shutl.update = function(callback) {

	var date = escape(document.forms['co-shipping-method-form']['date'].value.replace(/\//g, '-'));
//	$$('#shutlOptions button.button span span')[0].innerHTML = 'loading...';
	$('shipping-method-buttons-container').hide();	
	var params = '';
	params += '/date/' + date;
	params += '/time/' + escape(document.forms['co-shipping-method-form']['time'].options[document.forms['co-shipping-method-form']['time'].selectedIndex].value);
	var method = document.forms['co-shipping-method-form']['method'];
	for (var i = 0; i < method.length; i++) {
		if(method[i].checked) {
			params += '/method/' + escape(method[i].value);		
			break;
		}
	}
	var ajax = new Ajax.Request(
		shutl.url('/shutl/cart/update' + params), {
			method: 'post',
			onSuccess: function(transport) {
				var calledBack = eval(callback + '()');
								
			}
		}
	);
}

shutl.hideDateSelector = function() {

	$$('#dateSelector').each(function(obj){
		obj.hide();
	
	});
	
	shutl.times();	

}

shutl.dateSelector = function() {
	$$('#dateSelector table td.selectable').each(function(obj){
		Event.observe(obj, 'click', function(){
		
			$$('#dateSelector table td.selectable').each(function(td){
				td.removeClassName('selected');
			});
			obj.addClassName('selected');						
			$$('#date')[0].value = obj.select('span')[0].innerHTML;
			
		});
	});
	
	Event.observe($('date'), 'blur', function(e){

		window.setTimeout('shutl.hideDateSelector()', 200);

	});
	
	Event.observe($$('#date')[0], 'click', function(e){
		$$('#shutlLaterOptions #dateSelector').each(function(obj){

			if (!$('shipping-zip-form')) {
				obj.setStyle({
					marginTop: ((obj.getHeight()+5) * -1) + 'px'
				});
			} else {
				obj.setStyle({
					marginTop: ((27) * -1) + 'px'
				});
			}
			obj.show();						
		});
	
	});

}

shutl.running = false;

shutl.messages = function() {
	var url = '/shutl/message/';

	var ajax = new Ajax.Request(
		shutl.url(url), {
			method: 'post',
			onSuccess: function(transport) {			
				if ($('shutlMessages')) {
					$('shutlMessages').innerHTML = transport.responseText;				
				}

			}
		}
	);
}

shutl.times = function() {
	if (!$('time')) { return; }
	if (shutl.running) { return; }
	shutl.running = true;

	$('time').options[0].value = '';
	$('time').options[0].innerHTML = 'loading';
	$('time').disabled = true;
	var date = escape($('date').value.replace(/\//g, '-'));
	var url = '/shutl/quote/time/date/' + date;
	if ($('postcode')) { url += '/postcode/' + escape($('postcode').value); }

	if ($('id').value != '') { url += '/id/' + escape($('id').value); }
	var ajax = new Ajax.Request(
		shutl.url(url), {
			method: 'post',
			onSuccess: function(transport) {
			
				shutl.running = false;
				if (transport.responseText != '0') {
					$('time').replace(transport.responseText);
					
					Event.observe($('time'), 'change', function(){
						if ($('co-shipping-method-form')) {
							$('time').options[$('time').selectedIndex].innerHTML = 'loading...';
							shutl.update('shipping.save');											
						}

					});
				} else {
					$('time').options[0].value = '';
					$('time').options[0].innerHTML = 'No available time slots';
					
				}
			}
		}
	);
	

}

shutl.url = function(url) {
	var returnUrl = url;
	if (returnUrl.indexOf('http') < 0 && returnUrl.indexOf('/index.php') < 0) {
		returnUrl = '/index.php' + returnUrl;
	}
	return returnUrl;
}