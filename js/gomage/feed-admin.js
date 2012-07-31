 /**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 2.1
 * @since        Class available since Release 1.0
 */

Validation.add('gomage-feed-validate-interval', 'Time range is small for Upload Interval',		
function(v) {
	
	if (v*1 > 6)
	{	
		return true;
	}
	
	var control = $('upload_hour');
	var text = control.options[control.selectedIndex].innerHTML;
	var from = text.substring(0,2)*1;
	
	var control = $('upload_hour_to');
	var text = control.options[control.selectedIndex].innerHTML;
	var to = text.substring(0,2)*1;
	
	if (from == to) return false;
	
	if (!to) to = 24;
	
	if (from > to)
	{
		if (((24-from) + to - 1) < v*1)
			return false;
		else
			return true;	
	}	
	else
	{	
		if ((to - from) < v*1) 
			return false;
		else
			return true;
	}	
	
					
});

function gomagefeed_setinterval(control, element_id)
{
	if (control.value <= 6)
	{
		$(element_id).selectedIndex = 0;
		$(element_id).enable();
	}	
	else
	{
		$(element_id).selectedIndex = 0;
		$(element_id).disable();
	}	
}

GomageFeedAdminSettings = Class.create({
	system_sections: null,
	
	initialize:function(data){
		this.system_sections = data.data;
		this.url = data.url;
				
		if ($('feed_system'))
		{	
			$('feed_system').options[$('feed_system').options.length] = new Option('-Select-', '', false, false);
			$('feed_section').options[$('feed_section').options.length] = new Option('-Select-', '', false, false);
				
			if (!this.system_sections.size)
			{	
				for (var key in this.system_sections) {			
					$('feed_system').options[$('feed_system').options.length] = new Option(key, key, false, false);
				}
			}
		}
	},
	setSystem: function(value){
		
		$('feed_section').options.length = 0;
		$('feed_section').options[$('feed_section').options.length] = new Option('-Select-', '', false, false);
		
		var data = this.system_sections[value];
		if (typeof(data) != 'undefined'){
			data.each(function(option, i) { 			
				$('feed_section').options[$('feed_section').options.length] = new Option(option, value + '/' + option, false, false);
			});
		}
		
	},
	submit: function(section, file){
		
		if (section && !file)
		{	
			alert('Please select Section');
			return;
		}	
		
		params = {file: file,
				  section: section}; 
			
		var request = new Ajax.Request(this.url,
		  {
		    method:'GET',
		    parameters:params,
		    onSuccess: function(transport){
				
				var response = eval('('+(transport.responseText || false)+')');
				 
		    	if (response.error)
		    	{
		    		alert(response.error_text);
		    	}	
		    	else
		    	{		    		
		    		$('mapping-table-body').innerHTML = response.feed;
		    	}	
		      
		    },
		    onFailure: function(){
		    	alert("Import failure");
		    }
		  });		
	}
});