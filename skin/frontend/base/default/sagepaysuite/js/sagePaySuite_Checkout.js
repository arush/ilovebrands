notifyThreedError = function(msg){
	Control.Window.windows.each(function(w){
		if(w.container.visible()){
			w.close();
		}
	});
	if((typeof checkout.accordion == 'object')){
		checkout.accordion.openSection('opc-payment');
	}
	alert(msg);
}

restoreOscLoad = function(){
		//Hide loading indicator on OSC//
		var loading_osc = $$('div.onestepcheckout-place-order-loading');
		if(loading_osc.length){
			$('onestepcheckout-place-order').removeClassName('grey').addClassName('orange');
			$('onestepcheckout-place-order').removeAttribute('disabled');
			loading_osc.invoke('hide');
			already_placing_order = false;
		}
		//Hide loading indicator on OSC//
}

if(typeof EbizmartsSagePaySuite == 'undefined') {
    var EbizmartsSagePaySuite = {};
}
EbizmartsSagePaySuite.Checkout = Class.create();
EbizmartsSagePaySuite.Checkout.prototype = {

	initialize: function(config){
		this.config 		= config;
		this.servercode			= 'sagepayserver';
		this.directcode			= 'sagepaydirectpro';
		this.paypalcode			= 'sagepaypaypal';
		this.formcode			= 'sagepayform';
		this.code               = '';

		if(this.getConfig('review')){
			this.getConfig('review').saveUrl = SuiteConfig.getConfig('global', 'sgps_saveorder_url');
			this.getConfig('review').onSave = this.reviewSave.bindAsEventListener(this);
		}else if(this.getConfig('osc')){

		Event.stopObserving($('onestepcheckout-form'));
		$('onestepcheckout-form')._submit = $('onestepcheckout-form').submit;
		$('onestepcheckout-form').submit = function(){ this.reviewSave(); }.bind(this);

		}else if(this.getConfig('msform')){
			this.getConfig('msform').observe('submit', function(evmsc){Event.stop(evmsc);this.reviewSave(evmsc);}.bind(this));
		}

		var blFormMAC = $('multishipping-billing-form');
		if(blFormMAC){
			blFormMAC.observe('submit', function(_event_){ Event.stop(_event_); this.setPaymentMethod(); }.bind(this));
		}

		var paymentSubmit = this.getPaymentSubmit();
		if(paymentSubmit) {

			 if(this.getCurrentCheckoutStep() == 'opc-review'){//Magento 1.5.x+
			 	this.setPaymentMethod(true);
			 }else{
			 	paymentSubmit.observe('click', this.setPaymentMethod.bindAsEventListener(this));
			 }

		}

	},
	evalTransport: function(transport){
		try { response = eval('('+transport.responseText+')') } catch(e) { response = {} }
                return response;
	},
	getConfig: function(instance){
		return (this.config[instance] != 'undefined' ? this.config[instance] : false);
	},
	getCurrentCheckoutStep: function(){
		return this.getConfig('checkout').accordion.currentSection;
	},
	getPaymentSubmit: function(){
		var elements 	= $$("#opc-payment [onclick]");
		 for(var i=0; i<elements.length; i++) {
		 	// IE problems with readAttribute/getAttribute returning invalid results
		 	var attrubutes = [elements[i].readAttribute('onclick'), elements[i].getAttribute('onclick')];
		 	for(var j=0; j<attrubutes.length; j++) {
		 		if(Object.isString(attrubutes[j]) && -1 !== attrubutes[j].search(/payment\.save/)) {
					return elements[i];
				}
		 	}
        }
        return false;
	},
	getShippingMethodSubmit: function(){
		var elements 	= $$("#opc-shipping_method [onclick]");
		 for(var i=0; i<elements.length; i++) {
		 	var attrubutes = [elements[i].readAttribute('onclick'), elements[i].getAttribute('onclick')];
		 	for(var j=0; j<attrubutes.length; j++) {
		 		if(Object.isString(attrubutes[j]) && -1 !== attrubutes[j].search(/shippingMethod\.save/)) {
					return elements[i];
				}
		 	}
        }
        return false;
	},
	getPaymentMethod: function(){

		var form = null;

		if($('multishipping-billing-form')){
			form = $('multishipping-billing-form');
		}else if(this.getConfig('osc')){
			form = this.getConfig('oscFrm');
		}else if((typeof this.getConfig('payment')) != 'undefined'){
			form = $(this.getConfig('payment').form);
		}

		if(form === null){
			return this.code;
		}

		var checkedPayment = null

		form.getInputs('radio', 'payment[method]').each(function(el){
			if(el.checked){
				checkedPayment = el.value;
				throw $break;
			}
		});

		if(checkedPayment != null){
			return checkedPayment;
		}

        return this.code;
	},
	isFormPaymentMethod: function(){
		return (this.getPaymentMethod() === this.formcode);
	},
	isServerPaymentMethod: function(){
		return (this.getPaymentMethod() === this.servercode || ($('suite_ms_payment_method') && $('suite_ms_payment_method').getValue()==this.servercode));
	},
	isDirectPaymentMethod: function(){
		return (this.getPaymentMethod() === this.directcode);
	},
	growlError: function(msg){
		alert(msg);
		return;
		try{
			var ng = new k.Growler({location:"tc"});
			ng.error(msg, {life:10});
		}catch(grwlerror){
			alert(msg);
		}
	},
	growlWarn: function(msg){
		alert(msg);
		return;
		try{
			var ng = new k.Growler({location:"tc"});
			ng.warn(msg, {life:10});
		}catch(grwlerror){
			alert(msg);
		}
	},
	isDirectTokenTransaction: function(){
		var tokenRadios = $$('div#payment_form_sagepaydirectpro ul.tokensage li.tokencard-radio input');
		if(tokenRadios.length){
			if(tokenRadios[0].disabled === false){
				return true;
			}
		}
		return false;
	},
	isServerTokenTransaction: function(){
		var tokenRadios = $$('div#payment_form_sagepayserver ul.tokensage li.tokencard-radio input');
		if(tokenRadios.length){
			if(tokenRadios[0].disabled === false){
				return true;
			}
		}
		return false;
	},
	getServerSecuredImage: function(){
		return new Element('img', {'src':SuiteConfig.getConfig('server', 'secured_by_image'), 'style':'margin-bottom:5px'});
	},
	setShippingMethod: function(){
		try{
				if($('sagepaydirectpro_cc_type')){
					$('sagepaydirectpro_cc_type').selectedIndex = 0;
				}
		}catch(ser){
			alert(ser);
		}
	},
	setPaymentMethod: function(modcompat){

		// Remove Server InCheckout iFrame if exists
		if($('sagepaysuite-server-incheckout-iframe')){
			$('checkout-review-submit').show();
			$('sagepaysuite-server-incheckout-iframe').remove();
		}

		if(this.isServerPaymentMethod()){

			if( parseInt(SuiteConfig.getConfig('global','token_enabled')) === 1 && ($('remembertoken-sagepayserver').checked === true) ){

			$('sagepayserver-dummy-link').writeAttribute('href', SuiteConfig.getConfig('server','new_token_url'));
				if(this.isServerTokenTransaction()){

					if($('multishipping-billing-form')){
						$('multishipping-billing-form').submit();
					}

					return;
				}
				 var lcontwmt = new Element('div',{className: 'lcontainer'});
				 var heit = parseInt(SuiteConfig.getConfig('server','token_iframe_height'))+80;
				 lcontwmt.setStyle({'height':heit.toString() + 'px'});

				 var wmt = new Control.Modal('sagepayserver-dummy-link',{
						     className: 'modal',
						     iframe: true,
						     closeOnClick: false,
						     insertRemoteContentAt: lcontwmt,
						     height: SuiteConfig.getConfig('server','token_iframe_height'),
						     width: SuiteConfig.getConfig('server','token_iframe_width'),
						     fade: true,
						     afterClose: function(){
						     	this.getTokensHtml();
				 			 }.bind(this)
				 })
				wmt.container.insert(lcontwmt);

				wmt.container.down().insert(this.getServerSecuredImage());
				wmt.container.setStyle({'height':heit.toString() + 'px'});
			 	wmt.open();

				if(this.getConfig('checkout') && (modcompat == undefined)){
					this.getConfig('checkout').accordion.openSection('opc-payment');
				}
				return;
			}

		}else if(this.isDirectPaymentMethod() && parseInt(SuiteConfig.getConfig('global','token_enabled')) === 1 && ($('remembertoken-sagepaydirectpro').checked === true)){

			if(this.isDirectTokenTransaction()){
				return;
			}

			try{
	            if(new Validation(this.getConfig('payment').form).validate() === false){
	                return;
	            }
            }catch(one){}

			if(this.getConfig('osc')){
				var valOsc = new VarienForm('onestepcheckout-form').validator.validate();
				if(!valOsc){
					return;
				}
			}

			var pmntForm = (this.getConfig('osc') ? this.getConfig('oscFrm') : $('co-payment-form'));
			new Ajax.Request(SuiteConfig.getConfig('direct','sgps_registerdtoken_url'),{
					method:"post",
					parameters: Form.serialize(pmntForm),
					onSuccess:function(f){

						try{
							this.getTokensHtml();

							var d=f.responseText.evalJSON();

							if(d.response_status=="INVALID"||d.response_status=="MALFORMED"||d.response_status=="ERROR"||d.response_status=="FAIL"){
								if(this.getConfig('checkout')){
									this.getConfig('checkout').accordion.openSection('opc-payment');
								}
								this.growlWarn("An error ocurred with Sage Pay Direct:\n" + d.response_status_detail.toString());

								if(this.getConfig('osc')){
									$('onestepcheckout-place-order').removeClassName('grey').addClassName('orange');
									$$('div.onestepcheckout-place-order-loading').invoke('remove');
									return;
								}

							}else if(d.response_status == 'threed'){
								$('sagepaydirectpro-dummy-link').writeAttribute('href', d.url);
							}

							if(this.getConfig('osc')){
								this.reviewSave({'tokenSuccess':true});
								return;
							}

						}catch(alfaEr){

							if(this.getConfig('checkout')){
								this.getConfig('checkout').accordion.openSection('opc-payment');
							}
							this.growlError(f.responseText.toString());
						}

					}.bind(this)
			});

		}

	},
	getTokensHtml: function(){

		new Ajax.Updater(('tokencards-payment-' + this.getPaymentMethod()), SuiteConfig.getConfig('global', 'html_paymentmethods_url'), {
						 parameters: { payment_method: this.getPaymentMethod() },
						 onComplete:function(){
                                               if($$('a.addnew').length > 1){
                                                   $$('a.addnew').each(function(el){
                                                      if(!el.visible()){
                                                          el.remove();
                                                      }
                                                   })
                                               }
                                               toggleNewCard(2);

										     	if($('onestepcheckout-form') && this.isServerPaymentMethod()){
											     	toggleNewCard(1);

											     	var tokens = $$('div#payment_form_sagepayserver ul li.tokencard-radio input');
											     	if(tokens.length){
												     	tokens.each(function(radiob){
															radiob.disabled = true;
															radiob.removeAttribute('checked');
														});
														tokens.first().writeAttribute('checked', 'checked');
														tokens.first().disabled = false;
														$('onestepcheckout-form').submit();
													}else{
														this.resetOscLoading();
													}

										     	}
						 }.bind(this)
		});

	},
	resetOscLoading: function(){
		restoreOscLoad();
	},
	reviewSave: function(transport){

		if((typeof transport) == 'undefined'){
		var transport = {};
		}

		//OSC\\
		if((typeof transport.responseText) == 'undefined' && $('onestepcheckout-form')){

			if(this.isFormPaymentMethod()){
				new Ajax.Request(SuiteConfig.getConfig('global', 'sgps_saveorder_url'),{
						method:"post",
						parameters: Form.serialize($('onestepcheckout-form')),
						onSuccess:function(f){
							var d = f.responseText.evalJSON();
							if(d.response_status == 'ERROR'){
								alert(d.response_status_detail);
								this.resetOscLoading();
								return;
							}

							setLocation(SuiteConfig.getConfig('form','url'));
						}
				});
				return;
			}

			if((this.isDirectPaymentMethod() || this.isServerPaymentMethod()) && parseInt(SuiteConfig.getConfig('global','token_enabled')) === 1){
				if((typeof transport.tokenSuccess) == 'undefined'){
					this.setPaymentMethod();

					if(!this.isDirectTokenTransaction() && !this.isServerTokenTransaction() && (($('remembertoken-sagepaydirectpro') && $('remembertoken-sagepaydirectpro').checked === true) || ($('remembertoken-sagepayserver') && $('remembertoken-sagepayserver').checked === true))){
						return;
					}
				}
			}

            if(parseInt($$('div.onestepcheckout-place-order-loading').length) || (typeof transport.tokenSuccess != 'undefined' && true === transport.tokenSuccess)){

				if(Ajax.activeRequestCount > 1 && (typeof transport.tokenSuccess) == 'undefined'){
					return;
				}
				var slPayM = this.getPaymentMethod();

				if(slPayM == this.paypalcode){
					new Ajax.Request(SuiteConfig.getConfig('global', 'sgps_saveorder_url'),{
							method:"post",
							parameters: Form.serialize($('onestepcheckout-form')),
							onSuccess:function(f){
								setLocation(SuiteConfig.getConfig('paypal', 'redirect_url'));
							}
					});
					return;
				}

				if(slPayM == this.servercode || slPayM == this.directcode){
					new Ajax.Request(SuiteConfig.getConfig('global', 'sgps_saveorder_url'),{
							method:"post",
							parameters: Form.serialize($('onestepcheckout-form')),
							onSuccess:function(f){
								this.reviewSave(f);
								transport.element().removeClassName('grey').addClassName('orange');
								$$('div.onestepcheckout-place-order-loading').invoke('hide');
							}.bind(this)
					});
					return;
				}else{
                   $('onestepcheckout-form')._submit();
                   return;
				}

              }else{
              	return;
              }
		//OSC\\
		}else if((typeof transport.responseText) == 'undefined' && this.getConfig('msform')){
				var ps = $H({'payment[method]': 'sagepayserver'});

				if($('sagepay_server_token_cc_id')){
					ps.set('payment[sagepay_token_cc_id]', $('sagepay_server_token_cc_id').getValue());
				}

				new Ajax.Request(SuiteConfig.getConfig('global', 'sgps_saveorder_url'),{
						method:"post",
						parameters: ps,
						onSuccess:function(f){
							this.reviewSave(f);
						}.bind(this)
				});
				return;

		}else{
			try{
				var response = this.evalTransport(transport);
			}catch(notv){
				suiteLogError(notv);
			}
		}

		if((typeof response.response_status != 'undefined') && response.response_status != 'OK' && response.response_status != 'threed' && response.response_status != 'paypal_redirect'){

			this.resetOscLoading();

			this.growlWarn("An error ocurred with Sage Pay:\n" + response.response_status_detail.toString());
			return;
		}

		if(response.response_status == 'paypal_redirect'){
			setLocation(response.redirect);
			return;
		}

		if(this.getConfig('osc') && response.success && response.response_status == 'OK' && (typeof response.next_url == 'undefined')){
			setLocation(SuiteConfig.getConfig('global','onepage_success_url'));
			return;
		}

		if(!response.redirect || !response.success) {
			this.getConfig('review').nextStep(transport);
			return;
		}

		if(this.isServerPaymentMethod()){

			$('sagepayserver-dummy-link').writeAttribute('href', response.redirect);

			 var rbButtons = $('review-buttons-container');

			 var lcont = new Element('div',{className: 'lcontainer'});
			 var heit = parseInt(SuiteConfig.getConfig('server','iframe_height'));
			 if(Prototype.Browser.IE){
			 	heit = heit-65;
			 }

			var wtype = SuiteConfig.getConfig('server','payment_iframe_position').toString();
			if(wtype == 'modal'){

				 var wm = new Control.Modal('sagepayserver-dummy-link',{
						     className: 'modal',
						     iframe: true,
						     closeOnClick: false,
						     insertRemoteContentAt: lcont,
						     height: SuiteConfig.getConfig('server','iframe_height'),
						     width: SuiteConfig.getConfig('server','iframe_width'),
						     fade: true,
						     afterOpen: function(){
								if(rbButtons){
									rbButtons.addClassName('disabled');
								}
				 			 },
						     afterClose: function(){
						     	if(rbButtons){
						     		rbButtons.removeClassName('disabled');
						     	}
				 			 }
				 });
			 	wm.container.insert(lcont);
				wm.container.down().setStyle({'height':heit.toString() + 'px'});
				wm.container.down().insert(this.getServerSecuredImage());
				wm.open();

			}else if(wtype == 'incheckout'){

				var iframeId = 'sagepaysuite-server-incheckout-iframe';
				var paymentIframe = new Element('iframe', {'src': response.redirect, 'id': iframeId});

				if(this.getConfig('osc')){
					var placeBtn = $('onestepcheckout-place-order');

					placeBtn.hide();

					$('onestepcheckout-form').insert( { after:paymentIframe } );
					$(iframeId).scrollTo();

				}else{

					if( (typeof $('checkout-review-submit')) == 'undefined' ){
						var btnsHtml  = $$('div.content.button-set').first();
					}else{
						var btnsHtml  = $('checkout-review-submit');
					}

					btnsHtml.hide();
					btnsHtml.insert( { after:paymentIframe } );

				}

			}

		}else if(this.isDirectPaymentMethod() && (typeof response.response_status != 'undefined') && response.response_status == 'threed'){

			 $('sagepaydirectpro-dummy-link').writeAttribute('href', response.redirect);

			 var lcontdtd = new Element('div',{className: 'lcontainer'});
			 var dtd = new Control.Modal('sagepaydirectpro-dummy-link',{
			     className: 'modal sagepaymodal',
                 closeOnClick: false,
                 insertRemoteContentAt: lcontdtd,
			     iframe: true,
			     height: SuiteConfig.getConfig('direct','threed_iframe_height'),
			     width: SuiteConfig.getConfig('direct','threed_iframe_width'),
			     fade: true,
			     afterOpen: function(){

				     if(true === Prototype.Browser.IE){
				     	var ie_version = parseFloat(navigator.appVersion.split("MSIE")[1]);
						if(ie_version<8){
							return;
						}
				     }

				     try{
				     	var daiv = this.container;

				     	if($$('.sagepaymodal').length > 1){
				     		$$('.sagepaymodal').each(function(elem){
				     			if(elem.visible()){
				     				daiv = elem;
				     				throw $break;
				     			}
				     		});
				     	}

						daiv.down().down('iframe').insert({before:new Element('div', {'id':'sage-pay-direct-ddada','style':'background:#FFF'}).update(
							SuiteConfig.getConfig('direct','threed_after').toString() + SuiteConfig.getConfig('direct','threed_before').toString())});

						}catch(er){}

						if(false === Prototype.Browser.IE){
							daiv.down().down('iframe').setStyle({'height':(parseInt(daiv.down().getHeight())-60)+'px'});
							daiv.setStyle({'height':(parseInt(daiv.down().getHeight())+57)+'px'});
						}else{
							daiv.down().down('iframe').setStyle({'height':(parseInt(daiv.down().getHeight())+116)+'px'});
						}

	 			 },
			     afterClose: function(){
                     if($('sage-pay-direct-ddada')){
                     	$('sage-pay-direct-ddada').remove();
                     }
					$('sagepaydirectpro-dummy-link').writeAttribute('href', '');
	 			 }
			 });
			 dtd.container.insert(lcontdtd);
			 dtd.open();

		}else if(this.isDirectPaymentMethod()){
			new Ajax.Request(SuiteConfig.getConfig('direct','sgps_registertrn_url'),{
					onSuccess:function(f){

						try{

							var d=f.responseText.evalJSON();

							if(d.response_status=="INVALID"||d.response_status=="MALFORMED"||d.response_status=="ERROR"||d.response_status=="FAIL"){
								this.getConfig('checkout').accordion.openSection('opc-payment');
								this.growlWarn("An error ocurred with Sage Pay Direct:\n" + d.response_status_detail.toString());
							}else if(d.response_status == 'threed'){
								$('sagepaydirectpro-dummy-link').writeAttribute('href', d.url);
							}

						}catch(alfaEr){
							this.growlError(f.responseText.toString());
						}

					}.bind(this)
			});
		}else{
			this.getConfig('review').nextStep(transport);
			return;
		}
	}
}

try{
	Event.observe(window,"load",function(){

	$(document.body).insert(new Element('a', { 'id': 'sagepayserver-dummy-link', 'href': '#', 'style':'display:none' }).update('&nbsp;'));
	$(document.body).insert(new Element('a', { 'id': 'sagepaydirectpro-dummy-link', 'href': '#', 'style':'display:none' }).update('&nbsp;'));

	var msCont = $('suite_ms_payment_method');

		if( !msCont && (SuiteConfig.getConfig('global', 'ajax_review') == '2') && ((typeof window.review) != 'undefined') ){
	        var SageServer = new EbizmartsSagePaySuite.Checkout(
	                {
	                        'checkout':             window.checkout,
	                        'review':               window.review,
	                        'payment':              window.payment,
	                        'billing':              window.billing,
	                        'accordion':            window.accordion
	                }
	        );
        }else if(!msCont && ($$('div.shopping-cart-totals').length != 1) && $('onestepcheckout-form')){
	        var SageServer = new EbizmartsSagePaySuite.Checkout(
	                {
	                        'osc': $('onestepcheckout-place-order'),
	                        'oscFrm': $('onestepcheckout-form')
	                }
	        );

        }else if(msCont && (msCont.getValue() == 'sagepayserver')){
	        var SageServer = new EbizmartsSagePaySuite.Checkout(
	                {
	                	'msform': $$('div.multiple-checkout')[0].down(2)
	                }
	        );
        }

		if(parseInt(SuiteConfig.getConfig('global','valid')) === 0){
			if(SuiteConfig.getConfig('direct','mode') == "live" || SuiteConfig.getConfig('server','mode') == "live"){
				new PeriodicalExecuter(function(){ alert(SuiteConfig.getConfig('global','not_valid_message')); }, 10);
			}else{
				var invalidG = new k.Growler({location:"bl"}).error('<strong>'+SuiteConfig.getConfig('global','not_valid_message')+'</strong>', {life:14400});
			}
		}
	})
}catch(er){ suiteLogError(er); }


    addValidationClass = function(obj){
        if(obj.hasClassName('validation-passed')){
            obj.removeClassName('validation-passed');
        }
        obj.addClassName('validate-issue-number');
    }
    paypalClean = function(reverse){
    	var ccTypeContainer = $('sagepaydirectpro_cc_type');
       	var sf = 'div#payment_form_sagepaydirectpro';
       	var sfls = $$(sf+' input, '+sf+' select, '+sf+' radio, '+sf+' checkbox');

       	if(reverse){
	       	//sfls.invoke('enable');
	       	//Just hide items wose parent is visible, these prevents enabling hiden token card elements
	       	sfls.each(function(item){
	       		if(item.up().visible() === true){
	       			item.enable();
	       		}
	       	});

	       	sfls.invoke('show');

	       	$$(sf+' label, '+sf+' a[class!="addnew"]').invoke('show');

	       	//ccTypeContainer.show();
	       	//ccTypeContainer.disabled = false;
	       	ccTypeContainer.addClassName('validate-ccsgpdp-type-select');
       	}else{
	       	sfls.invoke('disable');
	       	sfls.invoke('hide');

	       	$$(sf+' label, '+sf+' a').invoke('hide');

	       	ccTypeContainer.show();
	       	ccTypeContainer.disabled = false;
	       	ccTypeContainer.removeClassName('validate-ccsgpdp-type-select');
       	}

    }
    changecsvclass = function(obj) {
        var ccTypeContainer = $('sagepaydirectpro_cc_type');
        var ccCVNContainer = $('sagepaydirectpro_cc_cid');

        fillSagePayTestData();

        if(ccTypeContainer.value == 'PAYPAL'){//PayPal MARK integration
        	paypalClean(false);
        }else{
        	paypalClean(true);
        }

        if(ccTypeContainer)
        {
            if(ccTypeContainer.value == 'LASER' && ccCVNContainer.hasClassName('required-entry'))
            {
                if(ccCVNContainer) {
                    ccCVNContainer.removeClassName('required-entry');
                }
            }
            if(ccTypeContainer.value != 'LASER' && !ccCVNContainer.hasClassName('required-entry'))
            {
                if(ccCVNContainer) {
                    ccCVNContainer.addClassName('required-entry');
                }
            }
        }
    }

    Validation.addAllThese([
        ['validate-ccsgpdp-number', 'Please enter a valid credit card number.', function(v, elm) {
                // remove non-numerics
			try{
           var ccTypeContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_number')) + '_cc_type');
                if (ccTypeContainer && typeof Validation.creditCartTypes.get(ccTypeContainer.value) != 'undefined'
                        && Validation.creditCartTypes.get(ccTypeContainer.value)[2] == false) {
                    if (!Validation.get('IsEmpty').test(v) && Validation.get('validate-digits').test(v)) {
                        return true;
                    } else {
                        return false;
                    }
                }

                if (ccTypeContainer.value == 'OT' ||  ccTypeContainer.value == 'UKE' || ccTypeContainer.value == 'DELTA' || ccTypeContainer.value == 'MAESTRO' || ccTypeContainer.value == 'SOLO' || ccTypeContainer.value == 'SWITCH' || ccTypeContainer.value == 'LASER' || ccTypeContainer.value == 'JCB' || ccTypeContainer.value == 'DC') {
                     return true;
                }

                return validateCreditCard(v);
                }catch(_error){return true;}
            }],
        ['validate-ccsgpdp-cvn', 'Please enter a valid credit card verification number.', function(v, elm) {
        		try{
                var ccTypeContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_cid')) + '_cc_type');
                var ccCVNContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_cid')) + '_cc_cid');
                if(ccTypeContainer)
                {
                    if(ccTypeContainer.value == 'LASER' && ccCVNContainer.hasClassName('required-entry'))
                    {
                        if(ccCVNContainer) {
                            ccCVNContainer.removeClassName('required-entry');
                        }
                    }
                    if(ccTypeContainer.value != 'LASER' && !ccCVNContainer.hasClassName('required-entry'))
                    {
                        if(ccCVNContainer) {
                            ccCVNContainer.addClassName('required-entry');
                        }
                    }
                }
                else
                {
                    return true;
                }
                if (!ccTypeContainer && ccTypeContainer.value != 'LASER') {
                    return true;
                }
                var ccType = ccTypeContainer.value;

                switch (ccType) {
                    case 'VISA' :
                    case 'MC' :
                        re = new RegExp('^[0-9]{3}$');
                        break;
                    //case 'AMEX' :
                    //    re = new RegExp('^[0-9]{4}$');
                    //    break;
                    case 'MAESTRO':
                    case 'SOLO':
                    case 'SWITCH':
                        re = new RegExp('^([0-9]{1}|^[0-9]{2}|^[0-9]{3})?$');
                        break;
                    default:
                        re = new RegExp('^([0-9]{3}|[0-9]{4})?$');
                        break;
                }

                if (v.match(re) || ccType == 'LASER') {
                    return true;
                }

                return false;
                                }catch(_error){return true;}
            }],
            ['validate-ccsgpdp-type', 'Credit card number doesn\'t match credit card type', function(v, elm) {
            try{
                    // remove credit card number delimiters such as "-" and space
                    elm.value = removeDelimiters(elm.value);
                    v         = removeDelimiters(v);

                    var ccTypeContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_number')) + '_cc_type');
                    if (!ccTypeContainer) {
                        return true;
                    }
                    var ccType = ccTypeContainer.value;

                    // Other card type or switch or solo card
                    if (ccType == 'OT' ||  ccType == 'UKE' || ccType == 'DELTA' || ccType == 'MAESTRO' || ccType == 'SOLO' || ccType == 'SWITCH' || ccType == 'LASER' || ccType == 'JCB' || ccType == 'DC') {
                        return true;
                    }
                    // Credit card type detecting regexp
                    var ccTypeRegExp = {
                        'VISA': new RegExp('^4[0-9]{12}([0-9]{3})?$'),
                        'MC': new RegExp('^5[1-5][0-9]{14}$'),
                        'AMEX': new RegExp('^3[47][0-9]{13}$')
                    };

                    // Matched credit card type
                    var ccMatchedType = '';
                    $H(ccTypeRegExp).each(function (pair) {
                        if (v.match(pair.value)) {
                            ccMatchedType = pair.key;
                            throw $break;
                        }
                    });

                    if(ccMatchedType != ccType) {
                        return false;
                    }

                    return true;
                                    }catch(_error){return true;}
                }],
         ['validate-ccsgpdp-type-select', 'Card type doesn\'t match credit card number', function(v, elm) {
                try{var ccNumberContainer = $(elm.id.substr(0,elm.id.indexOf('_cc_type')) + '_cc_number');
                return Validation.get('validate-ccsgpdp-type').test(ccNumberContainer.value, ccNumberContainer);
                                }catch(_error){return true;}
            }],
         ['validate-issue-number', 'Issue Number must have at least two characters', function(v, elm) {
				try{
                if(v.length > 0 && !(v.match(new RegExp('^([0-9]{1}|[0-9]{2})$')))){
                    return false;
                }

                return true;
                                }catch(_error){return true;}
            }]
    ]);

