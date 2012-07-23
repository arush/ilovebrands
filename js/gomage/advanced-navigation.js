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

navigationLoadInProccess = false;

var GanUrl = { 
  
	encode : function (string) { 
		return escape(this._utf8_encode(string)); 
	}, 
  
	decode : function (string) { 
		return this._utf8_decode(unescape(string)); 
	}, 
  
	_utf8_encode : function (string) { 
		string = string.replace(/\r\n/g,"\n"); 
		var utftext = ""; 
 
		for (var n = 0; n < string.length; n++) { 
 
			var c = string.charCodeAt(n); 
 
			if (c < 128) { 
				utftext += String.fromCharCode(c); 
			} 
			else if((c > 127) && (c < 2048)) { 
				utftext += String.fromCharCode((c >> 6) | 192); 
				utftext += String.fromCharCode((c & 63) | 128); 
			} 
			else { 
				utftext += String.fromCharCode((c >> 12) | 224); 
				utftext += String.fromCharCode(((c >> 6) & 63) | 128); 
				utftext += String.fromCharCode((c & 63) | 128); 
			} 
 
		} 
 
		return utftext; 
	}, 
  
	_utf8_decode : function (utftext) { 
		var string = ""; 
		var i = 0; 
		var c = c1 = c2 = 0; 
 
		while ( i < utftext.length ) { 
 
			c = utftext.charCodeAt(i); 
 
			if (c < 128) { 
				string += String.fromCharCode(c); 
				i++; 
			} 
			else if((c > 191) && (c < 224)) { 
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63)); 
				i += 2; 
			} 
			else { 
				c2 = utftext.charCodeAt(i+1); 
				c3 = utftext.charCodeAt(i+2); 
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63)); 
				i += 3; 
			} 
 
		} 
 
		return string; 
	} 
}

function startLoadNavigationData(more_products){
	
	if(navigationLoadInProccess){
		return false;
	}
	
	navigationLoadInProccess = true;
	
	if (more_products && typeof(gan_more_type_ajax) != 'undefined'){
		var more_button = $('gan-more-button');
		var loadinfo = document.createElement('div');
		loadinfo.innerHTML = '<img src="'+loadimage+'" />'+gomage_navigation_loadinfo_text;
		loadinfo.id = "navigation_loadinfo_more";
		new Insertion.After(more_button, loadinfo);
		more_button.hide();
		return navigationLoadInProccess;
	}
	
	var overlay = $('advanced-navigation-overlay');
	
	if($$('div.category-products').length > 0){
		
    	var element = $$('div.category-products')[0];
    
    }else if($$('div.col-main p.note-msg').length > 0){
    	
    	var element = $$('div.col-main p.note-msg')[0];
    
    }else if(typeof(gan_static_navigation_url) != 'undefined' && gan_static_navigation_url && $$('div.main').length > 0){
    	
    	var element = $$('div.main')[0];
    	    	
    }else {
    	navigationLoadInProccess = false;
    	return;
    }
			
	if(!overlay){
		overlay = $(document.createElement('div'));
		overlay.id = 'advanced-navigation-overlay';
		document.body.appendChild(overlay);
	}
	
	var offsets = element.cumulativeOffset();	
	overlay.setStyle({
		'top'	    : offsets[1] + 'px',
		'left'	    : offsets[0] + 'px',
		'width'	    : element.offsetWidth + 'px',
		'height'	: element.offsetHeight + 'px',
		'position'  : 'absolute',
		'display'   : 'block',
		'zIndex'	: '2000' // Value mast be more than any element from content have
	});
	
	var loadinfo = document.createElement('div');
	
	if(loadimagealign == 'bottom'){
	
		loadinfo.innerHTML = gomage_navigation_loadinfo_text+'<img src="'+loadimage+'" alt="" class="align-'+loadimagealign+'"/>';
	
	}else{
		
		loadinfo.innerHTML = '<img src="'+loadimage+'" alt="" class="align-'+loadimagealign+'"/>'+gomage_navigation_loadinfo_text;
		
	}
	
	loadinfo.id = "navigation_loadinfo";
	loadinfo.className = "gan-loadinfo";
	
	document.body.appendChild(loadinfo);
	
	return navigationLoadInProccess;
	
}

function stopLoadNavigationData(){
	
	var overlay = $('advanced-navigation-overlay');	
	if(overlay){
		overlay.style.display = 'none';
	}	
	if ($('navigation_loadinfo')){
		document.body.removeChild($('navigation_loadinfo'));
	}	
	if ($('navigation_loadinfo_more')){	
		$('navigation_loadinfo_more').remove();
	}		
	if ($('gan-more-button')){
		$('gan-more-button').show();
	}		
	return navigationLoadInProccess = false;	
}
	     
function generateUrlWithParams (url, params) {
	
	var query    = {},
		keys     = url.split('?')[1].split('&'),
		key      = '',
		glue     = '',
		strQuery = '',
		i        = -1;
	while (++i < keys.length) {
		key = keys[i].split('=');
		query[key[0]] = key[1];
	}
		
	for (attrname in query) {
		if (!params.hasOwnProperty(attrname))
			params[attrname] = query[attrname]; 
	}	
	params['ajax'] = 0;

	for (key in params) {
		strQuery += glue + key + '=' + params[key];
		glue = '&';
	}
	if (strQuery != '') {
		url = url.split('?')[0] + '?' + strQuery;
	}
	return url;
}

function submitNavigationForm(form, url, is_ajax) {
	
	url = GanUrl.decode(url);
	
	var from = $(form).down('input.navigation-from');
	var to = $(form).down('input.navigation-to');
	
	var re = /^[0-9]*$/;
	if (!re.test($(from).value))
	{
		alert('Please use only numbers (0-9) in this field.');
		return false;
	}	
	if (!re.test($(to).value))
	{
		alert('Please use only numbers (0-9) in this field.');
		return false;
	}
		
	if ((parseFloat($(from).value) > parseFloat($(to).value)) ||
		 ((parseFloat($(from).value) == 0 && parseFloat($(to).value) == 0)))
	{
		alert('Filter range is invalid.');
		return false;
	}
	
	var form_values = {};
	form_values[$(from).name] = $(from).value;
	form_values[$(to).name] = $(to).value;
	
    is_ajax = typeof(is_ajax) != 'undefined' ? is_ajax : true;
    
	var url = url.replace(/&amp;/ig, '&');
	
	var elements = form.elements;
	
	var params = Object.clone(navigationOpenFilters);
	
	for(var i=0;i< elements.length;i++){
		
		element = elements[i];
		
		switch(element.nodeName){
			
			case 'INPUT': case 'SELECT': case 'TEXTAREA':
				
				if(element.value){
				
					params[element.name] = element.value;
				
				}
				
			break;
			
		}
		
	}
	
	if(!url){
		url = form.action;
	}
	
	if (!is_ajax) {
		setLocation(generateUrlWithParams(url, params));
	} else if(startLoadNavigationData()){
	
		var query    = {},
		new_query    = {},
		keys     = url.split('?')[1].split('&'),
		key      = '',
		glue     = '',
		strQuery = '',
		i        = -1;
		while (++i < keys.length) {
			key = keys[i].split('=');
			query[key[0]] = key[1];
		}
			
		for (attrname in query) {
			if (!form_values.hasOwnProperty(attrname))
				new_query[attrname] = query[attrname]; 
		}	
		
		for (key in new_query) {
			strQuery += glue + key + '=' + new_query[key];
			glue = '&';
		}
		if (strQuery != '') {
			url = url.split('?')[0] + '?' + strQuery;
		}
		
		if (gomage_navigation_urlhash){
			GanSetHashUrl(url, params);
		}

		var request = new Ajax.Request(url,
		  {
		    method:'GET',
		    parameters:params,
		    onSuccess: function(transport){
		    	
		    	var response = eval('('+(transport.responseText || false)+')');
		    	
		    	if ($('gan_tmp_shop_by')){
		    		$('gan_tmp_shop_by').remove();
		    	}
		    	
		    	replaceNavigationBlock(response.navigation);
				replaceProductsBlock(response);
				replaceLeftRightNavigationBlocks('gan-left-nav-main-container', response.navigation_left);
				replaceLeftRightNavigationBlocks('gan-right-nav-main-container', response.navigation_right);
				ganReplaceMoreButton(response.navigation_more);
				
				try{
					if(response.eval_js){
						eval(response.eval_js);
						ganInitSliders();
						navigation_eval_js = response.eval_js;				
					}
					if (response.eval_js_procart){
						eval(response.eval_js_procart);
					}
				}catch(e){				
				}
															
				stopLoadNavigationData();
		      
		    },
		    onFailure: function(){
		    	stopLoadNavigationData();
		    }
		  });
	  
	}
	
}

function GanSetHashUrl(url, params){
			
    var vars = new Array(); 
    var hash = new Object();    
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    var hash_str = '';
    
    for(var i = 0; i < hashes.length; i++)
    {
        vars = hashes[i].split('=');
        if (vars[0] != 'ajax' && vars[0] != 'q'){
        	hash[vars[0]] = GanUrl.decode(vars[1]);
        }
    }    
    
    if (params){
    	for(var key in params){
    		if (params.hasOwnProperty(key)){
    			hash[key] = params[key];
    		}
    	}
    }
    
    for(var key in hash){
    	if (hash.hasOwnProperty(key)){
    		hash_str += key + '=' + hash[key] + '&';
    	}
    }
    
    if (hash_str){
    	hash_str += 'gan_data=true';
    }
            
    window.location.hash = hash_str;    
}

function setNavigationUrl(url, more_products, need_scroll){
	
	url = GanUrl.decode(url);
	
	is_ajax = typeof(is_ajax) != 'undefined' ? is_ajax : true;
	var url = url.replace(/&amp;/ig, '&');
	
	if (gomage_navigation_urlhash){
		GanSetHashUrl(url);
	}

	if(startLoadNavigationData(more_products)){

	var request = new Ajax.Request(url,
	  {
	    method:'get',
	    parameters:navigationOpenFilters,
	    onSuccess: function(transport){

	    	var response = eval('('+(transport.responseText || false)+')');
	    	
	    	if ($('gan_tmp_shop_by')){
	    		$('gan_tmp_shop_by').remove();
	    	}

	    	if (more_products){
	    		ganAddMoreProducts(response);
	    	}else{
		    	replaceNavigationBlock(response.navigation);		    	
				replaceProductsBlock(response, need_scroll);				
				replaceLeftRightNavigationBlocks('gan-left-nav-main-container', response.navigation_left);				
				replaceLeftRightNavigationBlocks('gan-right-nav-main-container', response.navigation_right);				
				ganReplaceMoreButton(response.navigation_more);				
	    	}
			try{			
				if(response.eval_js){
					eval(response.eval_js);
					ganInitSliders();
					navigation_eval_js = response.eval_js;				
				}				
				if (response.eval_js_procart){
					eval(response.eval_js_procart);
				}
			}catch(e){				
			}
			
			if (typeof(gan_static_navigation_url) != 'undefined' && gan_static_navigation_url){		
				gan_static_navigation_url = undefined;
			}
			
			stopLoadNavigationData();
	      
	    },
	    onFailure: function(){
	    	setLocation(url); //trying redirect to url
	    }
	  });
	  
	}	
}

function replaceProductsBlock(response, need_scroll){

	var content = response.product_list;
	
	if (typeof(gan_static_navigation_url) != 'undefined' && gan_static_navigation_url){       	
    	if ($$('div.col-main').length > 0){
    		var col_main = $$('div.col-main')[0];
    		col_main.innerHTML += '<div class="category-view">' + content + '</div>';
    	}
    	return;
    }
	
	var replace_toolbar = false;
	
	if($$('div.category-products').length > 0){
    	element = $$('div.category-products')[0];
    	if (element.select('div.toolbar').length == 0){
    		replace_toolbar = true;    		
    	}
    }else if($$('div.col-main p.note-msg').length > 0){
    	element = $$('div.col-main p.note-msg')[0];
    }else{
    	return;
    }

    if (content && content.toElement){
    	content = content.toElement();
    }else if (!Object.isElement(content)) {

      content = Object.toHTML(content);
      var tempElement = document.createElement('div');
      content.evalScripts.bind(content).defer();
      content = content.stripScripts();
      tempElement.innerHTML = content;

      el =  getElementsByClassName('category-products', tempElement);

      if (el.length > 0){
         content = el[0];
      }else{
         el = getElementsByClassName('note-msg', tempElement);
         if (el.length > 0){
            content = el[0];
            if (gan_shop_by_area == 1){
            	var shop_by_content = Object.toHTML(response.navigation);        
            	shop_by_content.evalScripts.bind(shop_by_content).defer();
            	shop_by_content = shop_by_content.stripScripts();                
    			var tempElement = document.createElement('div');
    	        tempElement.innerHTML = shop_by_content;
    	        var shop_by = getElementsByClassName('block-layered-nav', tempElement);
    	        if (shop_by.length > 0)
    	        {
    	        	shop_by = shop_by[0];
    	        	shop_by.id = 'gan_tmp_shop_by';
    	        	new Insertion.Before(element, shop_by);
    	        }	
            }
         }else{
            return;
         }
      }
    }
    element.parentNode.replaceChild(content, element);
    
    if (replace_toolbar && $$('div.category-products').length > 0){
    	ganReplaceToolbal(response.product_list);        
    }   
    
    if (typeof(need_scroll) != 'undefined' && need_scroll){
    	if ($$('div.category-products').length > 0){
    		var category_products = $$('div.category-products')[0];
    		category_products.scrollTo();
    	}
    }
}

function ganReplaceToolbal(content){	
	var content_toolbar = Object.toHTML(content);        
    content_toolbar.evalScripts.bind(content_toolbar).defer();
    content_toolbar = content_toolbar.stripScripts();                
	var toolbars =  $$('div.toolbar');
	for (var i = 0; i < toolbars.length; i++) {    		
		var tempElement = document.createElement('div');
        tempElement.innerHTML = content_toolbar;
        var toolbar =  getElementsByClassName('toolbar', tempElement);
        if (toolbar.length > 0)
        {
        	toolbar = toolbar[0];        		
    		el = toolbars[i];        		 
    		el.parentNode.replaceChild(toolbar, el);    		
        }
	}
}

function getElementsByClassName(classname, node) {
	  var a = [];
	  var re = new RegExp('\\b' + classname + '\\b');
	  var els = node.getElementsByTagName("*");
	  for(var i=0,j=els.length; i<j; i++){
	     if(re.test(els[i].className)){
	    	 a.push(els[i]);
	     }
	  } 
	  return a;
}


function replaceNavigationBlock(content){
	
    if (typeof(gan_static_navigation_url) != 'undefined' && gan_static_navigation_url){       	
    	if ($$('div.col-left').length > 0 && (gan_shop_by_area == 0)){
    		if ($('gan-left-nav-main-container')){
    			var content = Object.toHTML(content);        
    		    content.evalScripts.bind(content).defer();
    		    content = content.stripScripts();                
    			var tempElement = document.createElement('div');
    	        tempElement.innerHTML = content;
    	        element = getElementsByClassName('block-layered-nav', tempElement);
    	        if (element.length > 0)
    	        {
    	        	element = element[0];
    	        	new Insertion.After($('gan-left-nav-main-container'), element);
    	        }
    		}else{
	    		var col_left = $$('div.col-left')[0];
	    		col_left.innerHTML = content + col_left.innerHTML;
    		}
    	}
    	if ($$('div.col-right').length > 0 && (gan_shop_by_area == 2)){
    		if ($('gan-right-nav-main-container')){
    			var content = Object.toHTML(content);        
    		    content.evalScripts.bind(content).defer();
    		    content = content.stripScripts();                
    			var tempElement = document.createElement('div');
    	        tempElement.innerHTML = content;
    	        element = getElementsByClassName('block-layered-nav', tempElement);
    	        if (element.length > 0)
    	        {
    	        	element = element[0];
    	        	new Insertion.After($('gan-right-nav-main-container'), element);
    	        }
    		}else{
	    		var col_right = $$('div.col-right')[0];
	    		col_right.innerHTML = content + col_right.innerHTML;
    		}
    	}
    	return;
    }
	
    var element = $$('div.block-layered-nav')[0];
    
    if (typeof(element) == 'undefined'){
    	return;
    }
    
    if (content && content.toElement){
    	
    	content = content.toElement();
    	
    }else if (!Object.isElement(content)) {
    	
      content = Object.toHTML(content);
      
      var tempElement = document.createElement('div');
      content.evalScripts.bind(content).defer();
      tempElement.innerHTML = content;
      content = tempElement.firstChild;
      
    }
    
    element.parentNode.replaceChild(content, element);    
}


function replaceLeftRightNavigationBlocks(element, content)
{
	var element = $(element);
	
	if (content && element)
	{	
	    if (content && content.toElement){
		    	
		    	content = content.toElement();
		    	
		}else if (!Object.isElement(content)) {
		    	
		      content = Object.toHTML(content);
		      
		      var tempElement = document.createElement('div');
		      content.evalScripts.bind(content).defer();
		      tempElement.innerHTML = content;
		      content = tempElement.firstChild;
		      
		 }	    
		 element.parentNode.replaceChild(content, element);
	} 
}


function initSlider(code, min, max, curr_min, curr_max, url, is_ajax){

	if(min == max){
		max++;
		if(curr_min == curr_max){
			curr_max++;
		}

	}
	var handles = [code+'-handle-from', code+'-handle-to'];

	var init_hidden = false;
	if ($('advancednavigation-filter-content-' + code) && !$('advancednavigation-filter-content-' + code).visible()){
		init_hidden = true;
		$('advancednavigation-filter-content-' + code).show();
	}
	var s1 = new Control.Slider(handles,code+'-track', {axis:'horizontal',alignY:0, range: $R(min,max), sliderValue: [curr_min, curr_max],restricted: true,  spans: [code+"-square_slider_span"]});
	if (init_hidden){
		$('advancednavigation-filter-content-' + code).hide();
	}
	s1.options.onChange = function(value){

			if (isNaN(value[0]) || isNaN(value[1]))
		    {
		    	return false;
		    }

			$(code+'-filter-form').elements[code+'_from'].value = parseInt(value[0]);
			$(code+'-filter-form').elements[code+'_to'].value = parseInt(value[1]);

			if(min == value[0] && max == value[1]){

				//setNavigationUrl(url);
				submitNavigationForm($(code+'-filter-form'), url, is_ajax);

			}else{

				submitNavigationForm($(code+'-filter-form'), url, is_ajax);

			}

			var htmlvalue = parseInt(value[0]) + ' - ' + parseInt(value[1]);

			if(value[0] >= 0 && value[1] >= 0){

			$(code+'-value-from').innerHTML = parseInt(1*value[0]);
			$(code+'-value-to').innerHTML = parseInt(1*value[1]);

			}

	        $(code+'-value').innerHTML = htmlvalue;

	};
	
	s1.options.onSlide = function(value){
					    
		    if (isNaN(value[0]) || isNaN(value[1]))
		    {
		    	return false;
		    }	
		
			var htmlvalue = parseInt(value[0]) + ' - ' + parseInt(value[1]);
			
			
			if(value[0] >= 0 && value[1] >= 0){
			$(code+'-value-from').innerHTML = parseInt(1*value[0]);
			$(code+'-value-to').innerHTML = parseInt(1*value[1]);
			}
	        $(code+'-value').innerHTML = htmlvalue;
	};
	
	s1.draw = function(event)
	{
		var pointer = [Event.pointerX(event), Event.pointerY(event)];
	    var offsets = Position.cumulativeOffset(this.track);
	    pointer[0] -= this.offsetX + offsets[0];
	    pointer[1] -= this.offsetY + offsets[1];
	    this.event = event;
	        
	    var value = this.translateToValue( this.isVertical() ? pointer[1] : pointer[0] );
	    
	    if (isNaN(value)) return false;
	    
	    this.setValue(value);
	    
	    if (this.initialized && this.options.onSlide)
	      this.options.onSlide(this.values.length>1 ? this.values : this.value, this);
	};

    var slider_data = new Object();
    var htmlvalue = parseInt(s1.values[0]) + ' - ' + parseInt(s1.values[1]);

    slider_data.code = code;
    slider_data.from = parseInt(s1.values[0]);
    slider_data.to = parseInt(s1.values[1]);
    slider_data.htmlvalue = htmlvalue;

    gan_slider_datas.push(slider_data);
}

function ganAddMoreProducts(response){
	ganReplaceToolbal(response.product_list);
	
	if($$('div.category-products').length > 0){
    	var category_products = $$('div.category-products')[0];
    	category_products.select('ul.products-grid').each(
    			function (e) {
    				e.removeClassName("last");
    			}
    		);
    	category_products.select('ol.products-list li.item').each(
    			function (e) {
    				e.removeClassName("last");
    			}
    		);
	}	
	
	var content = Object.toHTML(response.product_list);
    var tempElement = document.createElement('div');
    content.evalScripts.bind(content).defer();
    content = content.stripScripts();
    tempElement.innerHTML = content;

    var element = getElementsByClassName('category-products', tempElement);
    if (element.length > 0){
    	element = element[0];       	
    	var more_button = $('gan-more-button');        
        var container = more_button.up();
        
        var elements = getElementsByClassName('products-grid', element);                
        elements.each(function (e) {
    				container.insertBefore(e, more_button);
    			});	
        var elements = getElementsByClassName('products-list', element);                
        elements.each(function (e) {
    				$('products-list').innerHTML += e.innerHTML;
    			});
        
    }
			
	ganReplaceMoreButton(response.navigation_more);
}

function ganReplaceMoreButton(navigation_more){	
	var more_button = $('gan-more-button');
	var element = null;
	
	if (navigation_more){
		var content = Object.toHTML(navigation_more);        
	    content.evalScripts.bind(content).defer();
	    content = content.stripScripts();                
		var tempElement = document.createElement('div');
        tempElement.innerHTML = content;
        element = getElementsByClassName('gan-more-button', tempElement);
        if (element.length > 0)
        {
        	element = element[0];
        }
	}    
	
	if (more_button){
		if (element){			        		    		        		 
	       	more_button.parentNode.replaceChild(element, more_button);    			        		
		}else{
			more_button.remove();
		}
	}else{			
		if (element && $$('div.toolbar-bottom').length > 0 && $$('div.category-products').length > 0){
			var category_products = $$('div.category-products')[0];
			if (category_products.select('div.toolbar').length == 0){
				category_products.appendChild(element);
			}else{
				var toolbar_bottom = $$('div.toolbar-bottom')[0];
				var container = toolbar_bottom.up(); 
				container.insertBefore(element, toolbar_bottom);
			}
		}
	}
}