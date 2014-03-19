
/** -----------------------------------------------
 * TABLE OF CONTENTS
 *
 * Transform a #table-of-contents div to a nested list
 * 
 * TODO: use the headers from the settings
 *       make it so that html generated toc can still toggle
 * 
 */
(function ( $ ) {

	var settings;

	// Using JavaScript to build the TOC 
	$.fn.TOC = function(options) {
		
		settings = $.extend({
			toggle:    false,
			backToTop: '',
			title:     'Table of contents',
			headers:   'h1,h2,h3',
			headerTag: 'h4',
			exceptFirstHeader: false
		}, options );		
		
		if (this.length > 0) {
			
			// Remove existing anchor redirection in the url
			var pageURL    = window.location.href.replace(/#[a-zA-Z0-9\-\_]*/g, '');
			var tocList    = '';
			var itemCount  = 0;
			var counter    = 1;
			var anchorname = '';
			var anchor     = '';
			var backToTopLink = (settings.backToTop)?  '<a class="backToTop" href="' + pageURL + '#toc" title="' + settings.backToTop + '">' + settings.backToTop + '</a>' : '';
			var display    = (settings.toggle)? 'display: none': '';		

			var headers = $(this).find(settings.headers);
			
			headers.each(function(i) {
				var current = $(this);
				var tagName = current.prop("tagName").toLowerCase();
				if(typeof tagName == "String") tagName = tagName.toLowerCase();
				itemCount++;
				
				// create the links for the toc
				anchorname = 'toc' + counter
				tocList += '<li class="toc' + tagName + '"><a id="link' + i + '" href="'+ pageURL +'#' + anchorname + '" class="scroll" title="' + current.html() + '">' + current.html() + '</a></li>';
				
				// add accompanying anchors to all headers
				anchor = '<a id="'+ anchorname +'"></a>';
				current.prepend(anchor);
				
				// add a back to top link
				if (counter > 1 || !settings.exceptFirstHeader) current.before(backToTopLink);
				
				counter++;
			});

			
			// if no items in the table of contents, don't show anything
			if(itemCount == 0) return false;

			
			var toggleLink = (settings.toggle)? '<span id="ToggleTOC" class="updown">&#9660;</span>' : '';
			
			toc = '<a id="toc"></a><div id="table-of-contents">';
			
			if (settings.title) {
				toc += '<' + settings.headerTag + ' id="TOCTitle">' + settings.title + toggleLink + '</' + settings.headerTag + '>';
			}
			toc += '<ul style="' + display + '">' + tocList + '</ul></div>';

			// Table of content location	
			$("#TOCcontainer").html(toc);
				
				
			clickAnchors();	
				
			// Toggle
			if (settings.toggle) {				
				toggle();
			}
		}
			
		return this;
	}
	
	
	// Using php to build the TOC, and JavaScript to toggle
	$.fn.toggleTOC = function() {
		
		var toggleLink = '<span id="ToggleTOC" class="updown">&#9660;</span>' ;
		$("#TOCTitle").append(toggleLink);
		
		$("#table-of-contents ul").css('display', 'none');
		
		clickAnchors();	
		toggle();
		
		return this;
	}


	// Using php to build the TOC, no toggle, just scrolling
	$.fn.scrollTOC = function() {

		clickAnchors();	
		
		return this;
	}

	// toggle
	function toggle() {

		// Toggle the TOC
		$('#table-of-contents').attr('href', 'javascript:void()').toggle(
			function() {
				$("#table-of-contents ul").animate({'height':'show'}, 200, function(){$('#ToggleTOC').html('&#9650;');})
			},
			function() {
				$("#table-of-contents ul").animate({'height':'hide'}, 200, function(){$('#ToggleTOC').html('&#9660;');})
			}
		);
			
		// Make sure clicking a link won't toggle the TOC
		//$("#table-of-contents li a").click(function (e) { e.stopPropagation(); })

		$("#table-of-contents").css('cursor', 'pointer').addClass('toggled');
	}

	// define the achor clickevents
	function clickAnchors() {

			// Scroll down from the toc
			$("#TOCcontainer #table-of-contents ul li a").on('click', function (event) { 
				event.preventDefault();
				$('html,body').animate({scrollTop:$(event.target.hash).offset().top}, 400);
				return false;
				
			});	


			// Scroll up from the anchors to the TOC
			$(".backToTop").on('click', function(event){
				event.preventDefault();
				$('html,body').animate({scrollTop:$("#TOCcontainer").offset().top}, 400);
			});

	}
}( jQuery ));



