 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Available since Release 1.0
 */

navigationOpenFilters = {};
navigation_eval_js = null;
var gan_slider_datas = new Array();

Event.observe(window, 'load', function() {
		ganLoadForPlain();	
	}
);

function ganLoadForPlain() {	
	mainNav("gan_nav_left", {"show_delay":"100","hide_delay":"100"});
	mainNav("gan_nav_top", {"show_delay":"100","hide_delay":"100"});
	mainNav("gan_nav_right", {"show_delay":"100","hide_delay":"100"});
	ganInitSliders();	
	if (typeof(gomage_navigation_urlhash) != 'undefined' && gomage_navigation_urlhash){
		ganPrepareUrl();
	}
	ganInitMoreButton();
	ganInitScrollToTop();
	if (typeof(gan_static_navigation_url) != 'undefined' && gan_static_navigation_url){		
		setNavigationUrl(gan_static_navigation_url);
	}
}

function ganPrepareUrl(){
	var hash_str = window.location.hash;
	if (hash_str){		
		var url = window.location.href;
		url = url.replace(hash_str, '');
				
		var hashes = hash_str.slice(1).split('&');
	    var vars = new Array();	    
	    var hash = new Object();
	    var gan_data = false;
	    var hash_str = '';
	    
	    for(var i = 0; i < hashes.length; i++)
	    {	        
	    	vars = hashes[i].split('=');
	        
	    	if (vars[0] == 'gan_data'){
	    		gan_data = true;
	    		continue;
	    	}
	    	
	        if (vars[0] != 'ajax' && vars[0] != 'gan_data' && vars[0] != 'q'){
	        	hash[vars[0]] = vars[1];
	        }
	    }    
	    
	    for(var key in hash){
	    	if (hash.hasOwnProperty(key)){
	    		hash_str += key + '=' + hash[key] + '&';
	    	}
	    }
		
		if (typeof(setNavigationUrl) == 'function' && gan_data && hash_str){						
			hash_str += 'ajax=1';			
			if (url.indexOf('?') != -1){
				url = url + '&' + hash_str;
			}else{
				url = url + '?' + hash_str;
			}
			setNavigationUrl(url);
		}
	}
}

function ganInitSliders(){
	for(var i=0;i< gan_slider_datas.length;i++){
      $(gan_slider_datas[i].code+'-value-from').innerHTML = gan_slider_datas[i].from;
      $(gan_slider_datas[i].code+'-value-to').innerHTML = gan_slider_datas[i].to;
      $(gan_slider_datas[i].code+'-value').innerHTML = gan_slider_datas[i].htmlvalue;
    }
    gan_slider_datas = new Array();
} 


function showNavigationNote(id, control){	
	var arr = $(control).cumulativeOffset();	
	var in_narrow_by_list = false;
	if ($('narrow-by-list')){
		$('narrow-by-list').childElements().each(function(e){
			if (e.id == id){
				in_narrow_by_list = true;
			}
		});
		var nbl = $('narrow-by-list').cumulativeOffset();
	}		
	$(id).style.left = (parseInt(arr[0]) - (in_narrow_by_list ? parseInt(nbl[0]) : 0 )) + 'px'; 
	$(id).style.top = (parseInt(arr[1]) - (in_narrow_by_list ? parseInt(nbl[1]) : 0 )) + 'px';
	$(id).style.display = 'block';			
}

function hideNavigationNote(){
	
	$$('.filter-note-content').each(function(e){e.style.display = 'none';});
	
}


function navigationOpenFilter(request_var){
	
	var id = 'advancednavigation-filter-content-'+request_var;
	
	if( $(id).style.display == 'none' ){
		
		$(id).style.display = 'block';
		
		if (navigation_eval_js) {
			eval(navigation_eval_js);
			ganInitSliders();
		}	
		
		navigationOpenFilters[request_var+'_is_open'] = true;
		
	}else{
		
		$(id).style.display = 'none' ;
		
		navigationOpenFilters[request_var+'_is_open'] = false;
		
	}	
}

function showAllNavigationAttribute(control, request_var){
	$(control).up('ol').select('li:hidden').each(
		    function (e) {
		        e.show();
		    }
		);
	$(control).up('li').hide();
	navigationOpenFilters[request_var+'_show_all'] = true;
}

function hideNavigationAttribute(control, count, request_var){
	var i = 0;
	$(control).up('ol').select('li').each(
		    function (e) {
		    	i++;
		    	if (i > count){
		    		e.hide();
		    	}
		    	if (e.select('a.gan-attr-more').length > 0){
		    		e.show();
		    	}
		    }
		);
	navigationOpenFilters[request_var+'_show_all'] = false;
}

function ganShowAccordionItem(control){
	$(control).up('ul.gan-accordion-list').select('li.accordion-active').each(
			function (e) {
				e.removeClassName("accordion-active");
			}
		);	
	$(control).up('li.level-top').addClassName('accordion-active');
}

function ganInitMoreButton(){
	var more_button = $('gan-more-button');
	if(more_button){
		if ($$('div.toolbar-bottom').length > 0 && $$('div.category-products').length > 0){
			var category_products = $$('div.category-products')[0];
			if (category_products.select('div.toolbar').length == 0){
				category_products.appendChild(more_button);
			}else{
				var toolbar_bottom = $$('div.toolbar-bottom')[0];
				var container = toolbar_bottom.up(); 
				container.insertBefore(more_button, toolbar_bottom);
			}
		}else{
			more_button.remove();
		}
	}
	
	if (typeof(gan_more_type_ajax) != 'undefined'){	
		Event.observe(window, "scroll", function() {
			var more_button = $('gan-more-button');			
	        if (document.viewport) {
	            var top = document.viewport.getScrollOffsets().top;
	            var height = document.viewport.getHeight();
	            var document_height = Math.max(Math.max(document.body.scrollHeight, document.documentElement.scrollHeight), Math.max(document.body.offsetHeight, document.documentElement.offsetHeight), Math.max(document.body.clientHeight, document.documentElement.clientHeight));
	            if ((document_height - top) <= (3 * height)){
	            	if (more_button && more_button.visible() && more_button.select('button').length > 0){
						var onclick_str = more_button.select('button')[0].attributes["onclick"].nodeValue;							
						globalEval(onclick_str);
					}
	            }
	        }			
		});	
	}
}

function ganInitScrollToTop(){	
	if ($$('div.category-products').length > 0 && $('gan-totop-button')){        
        var left = $$('div.category-products')[0].getDimensions().width + $$('div.category-products')[0].offsetLeft + 20;
        $('gan-totop-button').setStyle({'left' : left + 'px' });        
		Event.observe(window, "scroll", function() {
			var top = document.viewport.getScrollOffsets().top;
            if (top > (document.viewport.getHeight() * 0.8)) {
            	$('gan-totop-button').show();
            } else {
            	$('gan-totop-button').hide();
            }					
		});
		Event.observe(window, "resize", function() {
			var left = $$('div.category-products')[0].getDimensions().width + $$('div.category-products')[0].offsetLeft + 20;
	        $('gan-totop-button').setStyle({'left' : left + 'px' });
		});
	}	
}

function ganScrollToTop(){
	if ($$('div.category-view').length > 0){    		
		var category_view = $$('div.category-view')[0];
		category_view.scrollTo();
	}else if ($$('div.category-products').length > 0){
		var category_products = $$('div.category-products')[0];
		category_products.scrollTo();
	}
}

var globalEval = function globalEval(src){
    if (window.execScript) {
        window.execScript(src);
        return;
    }
    var fn = function() {
        window.eval.call(window,src);
    };
    fn();
};

function ganSHBlockContent(control){
	if ($('gan-block-content')){
		if ($('gan-block-content').hasClassName("gan-hidden")){
			$('gan-block-content').removeClassName("gan-hidden");
			$('gan-block-content').show();
			$(control).innerHTML = 'Hide';
			navigationOpenFilters['gan_bcontent_hide'] = false;
		}else{			
			$('gan-block-content').addClassName("gan-hidden");
			$('gan-block-content').hide();
			$(control).innerHTML = 'Show';
			navigationOpenFilters['gan_bcontent_hide'] = true;
		}
	}
}