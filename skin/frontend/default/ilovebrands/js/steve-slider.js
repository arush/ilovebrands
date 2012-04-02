document.observe('dom:loaded', function(){

	$$('.sliders .holder .slider').each(function(obj){
		var calculatedWidth = obj.select('ul li').length * parseInt(obj.select('ul li')[0].getStyle('width'));
		var margins = parseInt(obj.getStyle('margin-left'));
		margins += parseInt(obj.getStyle('margin-right'));
		obj.setStyle({ width: calculatedWidth + margins + 100 + 'px' });
	});
	
	$$('.sliders .holder .left').each(function(obj){
		obj.observe('click', function(){
			var target = obj.up().firstDescendant().firstDescendant().childElements()[obj.up().firstDescendant().firstDescendant().childElements().length-1];

			var lastChild = document.createElement('li');
			lastChild.innerHTML = target.innerHTML;
			target.remove();
			obj.up().firstDescendant().firstDescendant().insert({top: lastChild });
			target = lastChild;
			target.setStyle({ marginLeft: (parseInt(target.getStyle('width'))*-1) + 'px' })			
			var start = parseInt(target.getStyle('margin-left'));
			var end = start + parseInt(target.getStyle('width'));
			
			new Effect.Tween(
				target, start, end, { duration: 0.2	},
				function(p) { this.setStyle({marginLeft : p + "px" }) }
			);
		});	
	});
	$$('.sliders .holder .right').each(function(obj){
		obj.observe('click', function(){
			var target = obj.up().firstDescendant().firstDescendant().firstDescendant();
			var start = parseInt(target.getStyle('margin-left'));
			var end = start + (parseInt(target.getStyle('width')) * -1);
			new Effect.Tween(
				target, start, end, {
					duration: 0.2, afterFinish: function(){
						
						// if (end != parseInt(target.getStyle('width'))*-1) {
							var lastChild = document.createElement('li');
							lastChild.innerHTML = target.innerHTML;
							target.remove();
							obj.up().firstDescendant().firstDescendant().insert(lastChild);
						//}
					}
				},
				function(p) { this.setStyle({marginLeft : p + "px" }) }
			);
		});
	});
	

});

var count;

function doFade(el) {
		count = 0;
		nextLi = parseInt(el);
		el = "#" + el;
		
		fadeThings = jQuery("#popup-slide").children("ul").children("li").eq(nextLi).find(".fadein");
		
		var numFaders = fadeThings.size();
		
		var target;
		
		timer = setInterval(function() {
			if(count >= numFaders) { 
				clearInterval(timer);
			 }
			 else {
			 	target = fadeThings.eq(count);
			 	startFade(target);
				count++;
			 }
		}, 500);
		
	}

function startFade(el) {
		jQuery(el).animate({
			opacity: 1
			},500);

	}	
	