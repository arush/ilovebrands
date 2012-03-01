var $j = jQuery.noConflict();
	var w;
	var w_height;
	var image_bg_pause = 1;
		
	$j(function() {
		init();
		
				
		$j(window).scroll(function() {
			w = $j(this).scrollTop();
			var howTop = $j("#how").offset().top;
			
			if(w > $j("#how").data("scrolls")[0] && w < $j("#how").data("scrolls")[1]) {
	      	      
		      window.meeting_rel = w - ($j("#how").data("scrolls")[1] - 750);
		      
		      window.meeting_ratio = (Math.round((window.meeting_rel / ($j("#how").data("scrolls")[2] )) * 100 ) / 40);
			
/* 			console.log("w: %d",w); */
/* 			console.log("del: %d / scroll2: %d = ratio: %d",window.meeting_rel, $j("#how").data("scrolls")[2], window.meeting_ratio);    */

		      $j(".moveable").each(function(index) {
		      				      		
			      	var resetMargin = $j(this).data("original")[0];
			      	var fullMargin = $j(this).data("original")[1];
			      					   	if($j(this).data("moved")=="false") {
			          if(window.meeting_ratio > index) {
			          		$j(this).stop().animate({
			          			marginLeft: fullMargin
			          		},200,function() {
			          				$j(this).data("moved","true");
			          				$j("#third-how").animate({
			          					backgroundColor: "#d2002b"
			          				  }, 500 );
							        new Effect.Morph('sorted-cal', {
							            style: 'background-position:-2425px 0px',
							            duration: 10
							        });

							    });
									
			          	}
			        }
			        else {
						if(window.meeting_ratio < index) {
			          		$j(this).stop().animate({
			          			marginLeft: resetMargin
			          		},200,function() {
			          				$j(this).data("moved","false");
			          				$j("#third-how").animate({
			          					backgroundColor: "#fff"
			          				  }, 200 );
			          				new Effect.Morph('sorted-cal', {
							            style: 'background-position: 0px 0px',
							            duration: .2
							        });
			          			});
			          	}
			        }
			
				});
			}	

			
			if(w > $j("#mags").data("scrolls")[0] && w < $j("#mags").data("scrolls")[1]) {
      
		      window.group_rel = w - ($j("#mags").data("scrolls")[0] + 650);
		      window.group_ratio = (Math.round((window.group_rel / ($j("#mags").data("scrolls")[2] - 650)) * 100) / 25);      
		    
   		      $j(".hood-element").each(function(index) {
		        if($j(this).data("visible")=="false") {
		          if(window.group_ratio > index) $j(this).fadeIn(600,function() {$j(this).data("visible","true")});
		        }
		        else {
		          if(window.group_ratio < index) $j(this).fadeOut(600,function() {$j(this).data("visible","false")});
		        }
		 
		      });
		      
		    }
		   	
		   	if(w > $j("#title-block").data("scrolls")[0]) {
				$j.plax.disable();      			
			}
			if(w < $j("#title-block").data("scrolls")[0]) {
				$j.plax.enable();
			}
		
		});
	});
	
	function set_section_scrolls() {
	  w_height = $j(window).height();
	  $j(".section").each(function(){
	    var scrolls = new Array()
	    scrolls[0] = $j(this).offset().top - w_height;
	    scrolls[1] = $j(this).offset().top /* + $j(this).height() */;
	    scrolls[2] = scrolls[1] - scrolls[0];
	    $j(this).data("scrolls",scrolls);
	  });
	  
	  $j("#vision").each(function(){
	    var scrolls = new Array()
	    scrolls[0] = $j(this).offset().top - w_height;
	    scrolls[1] = $j(this).offset().top + $j(this).height();
	    scrolls[2] = scrolls[1] - scrolls[0];
	    $j(this).data("scrolls",scrolls);
	  });
	  
	  $j("#title-block").each(function(){
	    var scrolls = new Array()
	    scrolls[0] = $j(this).offset().top;
	    $j(this).data("scrolls",scrolls);
	  });
	  
	  $j(".moveable").each(function(){
		var marginBig;
		marginBig = parseInt($j(this).css('marginLeft').replace("px", ""),10);
	    marginBig += 30;
	    marginBig += "px";
	    
	    var original = new Array()
	    original[0] = $j(this).css('marginLeft');
	    original[1] = marginBig;
	    $j(this).data("original",original);
	    $j(this).data("moved","false");
	  });
	  
	}
	
	function init() {
	  set_section_scrolls();
	  $j(window).resize(set_section_scrolls);
	  	
	  $j(".hood-element").each(function(){
	    $j(this).hide();
	    $j(this).data("visible","false");
	  });
 
	  
	}

	$j('#signmeupnow').plaxify({"yRange":30, invert:false});
	$j.plax.enable();
	
	
	function clearDefault(el) {
  		if (el.defaultValue==el.value) el.value = ""
	}

	

	$j(document).ready(function () {
		$j.localScroll();
		$j('a.invisibut').hover(
			function() {
				$j(this).addClass('red');
				},
			function() {
				$j(this).removeClass('red');
			});				
	    
	    $.reject({  
	        reject: { all: true }, // Reject all renderers for demo  
	        display: ['firefox','chrome','safari'],
	        header: 'The Internet must look pretty ugly from where you\'re sitting', // Header Text  
	        paragraph1: 'Internet Explorer (yes, even IE9) is an old and clunky browser, and we can\'t afford to develop custom code to support it', // Paragraph 1  
	        paragraph2: 'Man up and get a real browser, leave IE for the ladies', // Paragraph 2  
	        closeMessage: 'By closing this you understand that your experience may be ruined by your browser.', // Message below close window link  
	        closeCookie: true
	    }); // Customized Text  
	  
	    return false;

});
