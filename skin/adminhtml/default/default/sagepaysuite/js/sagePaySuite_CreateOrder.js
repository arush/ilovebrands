repeatpaymentSearchGrid = function(){

						new Control.Modal($('sagepayrepeat-search-url').getValue(),{
						     className: 'modal',
						     iframe: true,
						     height: '500',
						     width: '950',
						     fade: true
						}).open();

}

if(typeof EbizmartsSagePaySuite == 'undefined') {
    var EbizmartsSagePaySuite = {};
}
EbizmartsSagePaySuite.AdminOrder = Class.create();
EbizmartsSagePaySuite.AdminOrder.prototype = {
	initialize: function(config){
		this.config 		= config;
		this.servercode			= 'sagepayserver_moto';
		this.directcode			= 'sagepaydirectpro_moto';

		Event.observe(window, 'load', function(){
			$(editForm.formId).writeAttribute('action', SuiteConfig.getConfig('global', 'adminhtml_save_order_url'));
			document.body.insert(new Element('a', { 'id': 'sagepayserver-dummy-link', 'href': '#', 'style':'display:none' }).update('&nbsp;'));
		});

	},
	evalTransport: function(transport){
		try { response = eval('('+transport.responseText+')') } catch(e) { response = {} }
		return response;
	},
	getInstance: function(instance){
		return this.config[instance];
	},
	getPaymentMethod: function(){
		var elements 	= $$("#order-billing_method_form [name='payment[method]']");
        for(var i=0; i<elements.length; i++) {
			if(elements[i].checked) {
				return elements[i].value;
			}
        }
        return null;
	},
	isServerPaymentMethod: function(){
		return (this.getPaymentMethod() === this.servercode);
	},
	isDirectPaymentMethod: function(){
		return (this.getPaymentMethod() === this.directcode);
	},
	orderSave: function(){

		//We only need to show iframe for SERVER
		if(!this.isServerPaymentMethod()){
			order.submit();
			return;
		}

		//Validate form when SERVER protocol is used
        if(window.editForm.validator && window.editForm.validate()){
			var post_server_fields = $('edit_form').select('input', 'select', 'textarea');
			new Ajax.Request(SuiteConfig.getConfig('server','sgps_admin_registertrn_url'),{
					method:"post",
					parameters: $H(Form.serializeElements(post_server_fields, true)),
					onSuccess:function(transport){

						var response = this.evalTransport(transport);
						if((typeof response.next_url) == 'undefined'){
							alert(response.response_status_detail);
							return;
						}

						$('sagepayserver-dummy-link').writeAttribute('href', response.next_url);
					    new Control.Modal('sagepayserver-dummy-link',{
						     className: 'modal',
						     closeOnClick: false,
						     iframe: true,
						     height: SuiteConfig.getConfig('server','iframe_height'),
						     width: SuiteConfig.getConfig('server','iframe_width'),
						     fade: true
						}).open();

					}.bind(this)
			});
        }else{
        	return false;
        }

	}
}