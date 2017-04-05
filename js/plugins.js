// jQuery outerHTML
(function($){
		  
	$.fn.extend({
		outerHTML : function( value ){	
			// Replaces the content
			if( typeof value === "string" ){
				var $this = $(this),
					$parent = $this.parent();
					
				var replaceElements = function(){
					
					// For some reason sometimes images are not replaced properly so we get rid of them first
					var $img = $this.find("img");
					if( $img.length > 0 ){
						$img.remove();
					}
					
					var element;
					$( value ).map(function(){
						element = $(this);
						$this.replaceWith( element );
					})
					
					return element;
					
				}
				
				return replaceElements();
				
			// Returns the value
			}else{
				return $("<div />").append($(this).clone()).html();
			}
	
		}
	});

})(jQuery);
