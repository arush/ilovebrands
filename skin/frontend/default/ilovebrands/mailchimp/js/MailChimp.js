
    function checkParent(id){
    	var item = (id == 'is_subscribed' || id == 'subscription')? id : "list["+id+"]";

    	if($(item)){
	    	$(item).checked = true;
	    	$(item).value = 1;
    	}else{
    		if(id == 'is_subscribed'){
	    		checkParent('subscription');
    		}
    	}
    }

    function unCheckGroups(field) {
	    if($(field).select("input").length){
			$(field).select("input").each(function(input, index){
				if(input.type == 'checkbox' || input.type == 'radio'){
					input.checked = false;
				}
			});
		}
		if($(field).select("select").length){
			$(field).select("select").each(function(select, index){
				if(select.type == 'select-one'){
					select.value = select.down('option').value;
				}
			});
		}
   	}

if (!window.Mailchimp) {
    window.Mailchimp = {};
}

Mailchimp.general= {
    initialize: function (element,fieldset) {
		if($(element)){
			this.element = $(element);
			this.fieldset = fieldset;
		    this.onElementMouseClick = this.handleMouseClick.bindAsEventListener(this);
		    this.element.observe('click', this.onElementMouseClick);
		}
    },

    handleMouseClick: function (evt) {
		if(this.element.checked == false)
	        unCheckGroups(this.fieldset);
    },

    hide: function (id) {
		if($(id)){
			this.element = $(id);
			if(this.element.type == 'checkbox' || this.element.type == 'radio'){
				$(id).checked = true;
				this.container = this.element.up('li');
				this.container.hide();
			}
		}
    }
};