var vdh = new Object();
vdh.timeOut = 0;
vdh.windowSize = function(w) {
	var offsets = document.viewport.getScrollOffsets();
    return {
    	width: document.viewport.getWidth() + 'px', 
    	height: document.viewport.getHeight() + 'px',
    	//docHeight: htmlHeight + 'px',
    	left: offsets.left + 'px',
    	top: offsets.top + 'px'
    
    }
}
vdh.docSize = function(w) {
	var offsets = document.viewport.getScrollOffsets();
	var htmlHeight = getDocHeight();
	//alert(htmlHeight);
	// var wrapperHeight = jQuery("wrapper").css('height');
    return {
    	width: document.viewport.getWidth() + 'px', 
    	// height: document.viewport.getHeight() + 'px',
    	height: htmlHeight + 'px',
    	left: offsets.left + 'px',
    	top: offsets.top + 'px'
    
    }
}


vdh.queue = {
	requestHandler: function(transport) {
		vdh.queue.sending = false;
		if (transport.responseText == '') {
			var nextUrl = false;
			for(var i = 0; i < vdh.urls.length; i++) {
				if (!vdh.urls[i].loaded) {
					vdh.urls[i].loaded = true;
					nextUrl = true;
					vdh.queue.send(vdh.urls[i].url);
					break;
				}
			}
			if (!nextUrl) {
				document.body.setStyle({ overflow: 'auto' });
				$$('.vdh.close').each(function(obj){		
					obj.up().previous().remove();						
					obj.up().remove();
					for(var i = 0; i < vdh.urls.length; i++) {				
						vdh.urls[i].loaded = false;
					}					
				
				});
			}
		} else {
			$$('.vdh.content').each(function(obj){
				obj.setStyle({display: 'block'});
				
				
				/*
				*  insert cookie based close button
				*  to do: betalive/suppressbeta is hard coded
				*/
				
				var formAttrs = {
	                'id'   : 'lightbox_closer',
	                'name'   : 'lightbox_closer',
	                'action' : '/popup/form/page/index/betalive/suppressbeta/true'
	            };
	            var inputAttrs = {
	                'id'   : 'lightbox-close-button',
	                'class' : 'lightbox-close-button',
	                'type' : 'submit',
	                'name' : 'submit',
	                'value' : '' /*this is needed to remove the default browser text */
	            };
				var hiddenInputAttrs = {
	                'type'   : 'hidden',
	                'name' : 'suppress',
	                'value' : 'true'
	            };
	
				var divAttrs = {
	                'id'   : 'lightbox-close',
	                'class'   : 'lightbox-close'
	            };
				
				/*
				*  insert tooltip
				*  
				*/
				
				var toolDivAttrs = {
	                'id'   : 'popover',
	                'class'   : 'popover top'
	            };
	            
	            var arrowDivAttrs = {
	                'id'   : 'arrow',
	                'class'   : 'arrow'
	            };
				var popInnerAttrs = {
	                'id'   : 'popover-inner',
	                'class'   : 'popover-inner'
	            };
	            var popTitleAttrs = {
	                'id'   : 'popover-title',
	                'class'   : 'popover-title'
	            };
	            var popContentAttrs = {
	                'id'   : 'popover-content',
	                'class'   : 'popover-content'
	            };
				
	 
	            var divClose = new Element('div', divAttrs);
	 			var formClose = new Element('form', formAttrs);
	 			var inputClose = new Element('input', inputAttrs);
	 			var hiddenInputClose = new Element('input', hiddenInputAttrs);
	 			
	 			// insert close tooltip
	            var toolDiv = new Element('div', toolDivAttrs);
	            var arrowDiv = new Element('div', arrowDivAttrs);
	            var popInnerDiv = new Element('div', popInnerAttrs);
	            var popTitleDiv = new Element('h3', popTitleAttrs);
	            popTitleDiv.update('Close forever');
	            var popContentDiv = new Element('p', popContentAttrs);
				popContentDiv.update('<p>Click here to permanently dismiss this dialog.</p>');

				// obj.innerHTML = '<a style="display:none;" class="vdh close"><span>close</span></a>';		
				obj.insert({after:divClose});
	            $('lightbox-close').insert(formClose);
				$('lightbox_closer').insert(hiddenInputClose);
				$('lightbox_closer').insert(inputClose);
				
				obj.insert({after:toolDiv});
				$('popover').insert(arrowDiv);
				$('popover').insert(popInnerDiv);
				$('popover-inner').insert(popTitleDiv);
				$('popover-inner').insert(popContentDiv);
				
				/* end insert cookie based close button */
				
				obj.insert(transport.responseText);			
			
				var dimensions = vdh.windowSize();
				var offsets = document.viewport.getScrollOffsets();
			
				/*content positioning*/
				var left = ((parseInt(dimensions.width) - parseInt(obj.getStyle('width'))) / 2) + offsets.left + 'px';
				var top = ((parseInt(dimensions.height) - parseInt(obj.getStyle('height'))) / 2) + offsets.top + 'px';
				/*close positioning*/				
				var closeRight = ((parseInt(dimensions.width) - parseInt(obj.getStyle('width'))) / 2) - 10 + offsets.left + 'px';
				var closeTop = ((parseInt(dimensions.height) - parseInt(obj.getStyle('height'))) / 2) -10 + offsets.top + 'px';
				/*tooltip positioning*/
				var toolRight = ((parseInt(dimensions.width) - parseInt(obj.getStyle('width'))) / 2) - (parseInt($('popover').getStyle('width')) / 2) - 4 + offsets.left + 'px';
				var toolTop = ((parseInt(dimensions.height) - parseInt(obj.getStyle('height'))) / 2) - (parseInt($('popover').getStyle('height'))) - 23 + offsets.top + 'px';
				
				obj.setStyle({ opacity: 0 });
				obj.setStyle({ left: left, top: top });
				$('lightbox-close').setStyle({ right: closeRight, top: closeTop});
				
				$('popover').setStyle({right: toolRight, top: toolTop});
				setTimeout(fadeOutTooltip,5000);

				/* build tooltip hover, to-do: build in prototype, just couldn't do it  */				
				$j("#lightbox-close").hover(
				  function () {
				    $j("#popover").fadeIn(500);
				  },
				  function () {
				    $j("#popover").fadeOut(500);
				  }
				);
								
				obj.fade({ duration: 0.15, from: 0, to: 1 });
				
				
				vdh.formListener();			
				vdh.closeListener();							
			});
			
			
		}
		vdh.count();
		vdh.queue.iterate();
	},
	queue: [],
	sending: false,
	send: function(url) {
		this.queue.push(url);
		if (!this.sending) {
			this.sending = true;
			this.iterate();			
		}
	},

	iterate: function() {
		url = this.queue.pop();
		if (url) {

			$$('.vdh.overlay').each(function(obj){

				if (vdh.popupCount > 0) {
					obj.setStyle({ display: 'block'});

				}
			});		
			if (url.request) {
				var ajax = url.request({
					onSuccess: vdh.queue.requestHandler
				});
			} else {
				var ajax = new Ajax.Request(
					url, {
					method: 'post',
					onSuccess: vdh.queue.requestHandler
				});
			}
		}
	}
};
vdh.formListener = function() {
	$$('.vdh.content form').each(function(obj){
		Event.observe(obj, 'submit', function(e){
			e.stop();
			var postData = '';
			for (var i = 0; i < this.elements.length; i++) {
				if (postData != '') { postData += '/'; }
				postData += this.elements[i].name + '/' + this.elements[i].value;
			}
			vdh.queue.send(this.action + '/' + postData);

		});
	});	
}

vdh.closeListener = function() {
	$$('.vdh.close').each(function(obj){

		Event.observe(obj, 'click', function(){
			document.body.setStyle({ overflow: 'auto' });
			this.up().previous().remove(); /* remove overlay */		
			this.up().next().remove(); /*remove cookie based close button */
			this.up().next().remove(); /*remove cookie based close button */
			this.up().remove();
			
			for(var i = 0; i < vdh.urls.length; i++) {				
				vdh.urls[i].loaded = false;
			}					

		});

	});	
}

vdh.trim = function(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
vdh.popup = function(suppress) {

	if (suppress) { return; }

	document.body.insert('<div class="vdh overlay loading"></div>');
	document.body.insert('<div class="vdh content"></div>');	
	document.body.setStyle({ overflow: 'hidden' });


	var overlayDimensions = vdh.docSize();

	$$('.vdh.overlay').each(function(obj){

		obj.setStyle(overlayDimensions);
		obj.setStyle({ opacity: 0 });
		obj.setStyle({top:0}); //arush
		obj.fade({ duration: 0.15, from: 0, to: 0.7 });

		Event.observe(obj, 'click', function(){
			document.body.setStyle({ overflow: 'auto' });
			this.next().next().remove(); /* remove tooltip */						
			this.next().next().remove(); /* remove cookie based close button */						
			this.next().remove();
			this.remove();
			for(var i = 0; i < vdh.urls.length; i++) {				
				vdh.urls[i].loaded = false;
			}			

		});

	});



	if (vdh.urls[0].loaded) {
		vdh.urls[0].loaded = false;
	}
	vdh.urls[0].loaded = true;	
	vdh.queue.send(vdh.urls[0].url);

}
vdh.count = function() {
	var ajax = new Ajax.Request('/popup/form/count', {
		method: 'post',
		onSuccess: function(transport) {
			vdh.popupCount = transport.responseText;
			$('popupCounter').innerHTML = vdh.popupCount;				
			if (vdh.popupCount > 0 /* && vdh.loggedIn */) { // ARUSH EDIT doesn't matter if you're logged in or not
				var delayHeader = function() {
					$('popupMessages').setStyle({ display: 'block' });
				};
			} else {
				var delayHeader = function() { $('popupMessages').setStyle({ display: 'none' });
				};
			}
			setTimeout(delayHeader,2000);

		}
	});

}
vdh.popupCount = 0;

document.observe('dom:loaded', function(){
	Event.observe($('popupMessages'), 'click', function(){
		if (vdh.popupCount > 0) {
			clearTimeout(vdh.timeOut);
			vdh.popup(false);			
		}

	});

});

Ajax.Responders.register({
	onComplete: function(request) {
		if (request.url.indexOf('/popup/form/page/index') >= 0) {
			var scripts = new Array();
		
			var matched = request.transport.responseText.match(/<script(.*)>[\s\S]*<\/script>/gi);
			if (matched != null) { scripts = matched; }
			
			for (var i = 0; i < scripts.length; i++) {
				var tmp = scripts[i].replace(/<script(.*)>([\s\S]*)<\/script>/gi, "$2");
				eval(tmp);
			}
			
		}
	}
});

function fadeOutTooltip() {
    $('popover').fade({ duration: 0.15, from: 1, to: 0 });
}




function getDocHeight() {
    return Math.max(
        jQuery(document).height(),
        jQuery(window).height(),
        /* For opera: */
        document.documentElement.clientHeight
    );
}
