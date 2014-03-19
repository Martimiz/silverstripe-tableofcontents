<?php
/**
 * The TableOfContents module will automatically add a table of contents to your 
 * page, by collecting the designated headers from your your Content. To enable 
 * replace the $Contents placeholder in you page template by: 
 * 
 *	$ContentPlusTOC
 * 
 * See README.md for description of features and configuration options.
 */

class TableOfContents extends DataExtension {

	private static $db = array(
	    'TOCEnable'      => 'Boolean',
	    'TOCTitle'       => 'Varchar(255)',
	    'UseHeaderTypes' => 'Varchar(255)'
	);

	protected static $header_tag          = 'h4';
	protected static $toggle              = false;
	protected static $use_php             = false;
	protected static $add_back_to_top     = false;
	protected static $use_shortcode_links = false;
	protected static $except_first_header = false;
	protected static $template            = 'TableOfContents';
		
	
	public function updateCMSFields(FieldList $fields) {
		
		$this->getSettings();
		
		$tab = _t('TableOfContents.TOC', 'TableOfContents');
		
		$headerTypes = array(
			'h1' => _t('TableOfContents.HEADING_1', 'Heading 1'), 
			'h2' => _t('TableOfContents.HEADING_2', 'Heading 2'), 
			'h3' => _t('TableOfContents.HEADING_3', 'Heading 3'), 
			'h4' => _t('TableOfContents.HEADING_4', 'Heading 4'), 
			'h5' => _t('TableOfContents.HEADING_5', 'Heading 5'), 
			'h6' => _t('TableOfContents.HEADING_6', 'Heading 6')
		); 

		$fields->addFieldsToTab("Root.$tab", array( 
			$title = HeaderField::create('TOC', _t('TableOfContents.TOC', 'TableOfContents')),
		        LiteralField::create('TOCHelp', '<p class="field">' ._t('TableOfContents.TOCHELP', 'Enable a table of contents on your page, either by using full JavaScript (default) or using PHP') . '<p>'),
			LiteralField::create('TOCzzz', '<p>&nbsp;</p>'),  
			CheckboxField::create('TOCEnable', _t('TableOfContents.TOCENABLE', 'Display table of contents on this page')),
			TextField::create('TOCTitle', _t('TableOfContents.TOCTITLE', 'Title for the table of contents (empty = none)')),
			CheckboxSetField::create('UseHeaderTypes',_t('TableOfContents.USEHEADERTYPES', 'Add the following headertypes to the table of contents'), $headerTypes)
		));		
		$title->setHeadingLevel(2);		
	}
	
	/**
	 * @return typeUse $ContentPlusTOC instead of $Content to display the 
	 * parsed content including anchors and a table of contents (if enabled)
	 * 
	 * @return DBField parsed content
	 */
	public function ContentPlusTOC() {
		
		$content = $this->owner->Content;
		if ($this->owner->TOCEnable) {		
			$content = $this->parseContent($content);
		}
		return DBField::create_field('HTMLText', $content);
	}	
	
	/**
	 * Retreive the config settings from the yml file
	 */
	protected function getSettings() {
		$config = Config::inst()->get('tocconfig', 'global-config');
		if (!empty($config)) {
			if (!empty($config['script-or-php']) && $config['script-or-php'] == 'php') self::$use_php = true;
			if (!empty($config['toggle'])) self::$toggle = true;
			if (!empty($config['add-back-to-top'])) self::$add_back_to_top = true;
			if (!empty($config['header-tag'])) self::$header_tag = $config['header-tag'];
			if (!empty($config['except-first-header'])) self::$except_first_header = true;
			if (!empty($config['template'])) self::$template = $config['template'];
		}
	}	
	
	/**
	 * Extend the Page_Controllers init() method: 
	 * Load the javascript and stylesheets for the Table of Contents
	 * depending on the mode (script or php) and whether toggle is enabled
	 * 
	 * @param type $controller page controller
	 */
	function contentcontrollerInit($controller) {
		if ($this->owner->TOCEnable) {
			$this->getSettings();	

			Requirements::css(TABLEOFCONTENTS_BASE . '/css/TableOfContents.css');
			
			//if (!self::$use_php || self::$toggle) {
				Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery/jquery.js');
				Requirements::javascript(TABLEOFCONTENTS_BASE . '/javascript/TableOfContents.js');				
			//}
			
			// Build the TOC using PHP, and toggle using JavaScript or just scroll
			if (self::$use_php) {

				$func = (self::$toggle)? 'toggleTOC' : 'scrollTOC';
				
				$script = <<<EOD
				( function($) {
					$(document).ready(function() {
						
						$("#Content").{$func}();
					});
				} ) ( jQuery );       	
EOD;
			}
			
			// Build the TOC using JavaScript
			else {

				$backToTop         = (self::$add_back_to_top) ? _t('TableOfContents.BACKTOTOP', '[Back to top]') : '';
				$toggle            = (self::$toggle)? true : false;
				$title             = $this->owner->TOCTitle;
				$headers           = $this->owner->UseHeaderTypes;
				$headerTag         = self::$header_tag;
				$exceptFirstHeader = self::$except_first_header;
				
				if (empty($title) && self::$toggle) $title = _t('TableOfContents.TOCDEFAULTTITLE', 'Table of contents');
				

				$script = <<<EOD
				( function($) {
					$(document).ready(function() {
						
						$("#Content").TOC({ 
							backToTop: '{$backToTop}',
							toggle:    '{$toggle}',	
							title:     '{$title}',
							headers:  '{$headers}',
							headerTag: '{$headerTag}',
							exceptFirstHeader: '{$exceptFirstHeader}'
						});
					});
				} ) ( jQuery );	
EOD;
			}	
			if ($script) Requirements::customScript($script);
		}
	}	
	
	
	/**
	 * Parse the content and add the TOC, anchors and optional backlinks
	 * 
	 * @param type $content - the content to be parsed
	 * @return string parsd content
	 */
	protected function parseContent($content = '') {
		
		$customContent = '';
		
		if (!self::$use_php) {
			
			$html = $this->owner->Content;
			$toc = "<div id=\"TOCcontainer\"></div>";
		}
		else {
	
			$link = $this->owner->Link();
			
			// title for the table of contents. If no title is provided
			// and toggle is enabled, the default title 'Table of contents' 
			// is used. For translations see the language files.
			$title = $this->owner->TOCTitle;
			if (empty($title) && self::$toggle) $title = _t('TableOfContents.TOCDEFAULTTITLE', 'Table of contents');				

			// headersize for the TOC title (h1, h2, or ...)
			$headerSize = self::$header_tag;

			// which headers should be included in the TOC
			if ($this->owner->UseHeaderTypes) {
				$headerMatch = str_replace(array('h',','), array('//h','|'), $this->owner->UseHeaderTypes);
			} else {
				$headerMatch = '//h1|//h2';
			}
			
			// use DOMDocument to parse the content
			$doc = new DOMDocument('1.0', 'UTF-8');
			
			$doc->substituteEntities  = false;
			$doc->strictErrorChecking = false;
			$doc->formatOutput        = false;

			$doc->loadHTML($content);

			$xpath = new DOMXpath($doc);	
			$headerNodes = $xpath->query($headerMatch);

			if (!empty($headerNodes)) {
				
				// prepare TOCData for the template
				$tocData = array(
					'TOCTitle'     => $title,
					'TOCHeaderTag' => $headerSize,
					'TOCItems'     => new ArrayList()
				);
				
				// Now walk through the included headers in the content
				$counter = 1;
				foreach($headerNodes as $node) {

					$cssClass   =  'toc' . $node->nodeName;
					$title      =  $node->nodeValue;
					$anchorID   =  $this->owner->generateURLSegment($title);
					
					// create an TOC item for the template
					$itemArray = array(
					    'Title'      => $title,
					    'CssClass'   => $cssClass,
					    'Link'       => $link . '#' .$anchorID
					);
					$item = new ArrayData($itemArray);
					$tocData['TOCItems']->push($item);
					
					// create an anchor for this node in Content
					$anchor = $doc->createElement('a');
					$anchor->setAttribute('id', $anchorID);
					$node->appendChild($anchor);

					// optional: create back to top links
					if (self::$add_back_to_top && ($counter > 1 || !self::$except_first_header)) {				
						$backtext = $doc->createTextNode(_t('TableOfContents.BACKTOTOP', '[Back to top]'));
						$back     = $doc->createElement('a');

						$back->setAttribute('class', 'backToTop');
						$back->setAttribute('href', "$link#toc");
						$back->setAttribute('title', _t('TableOfContents.BACKTOTOP', '[Back to top]'));
						$back->appendChild($backtext);	

						$parentNode = $node->parentNode;
						$parentNode->insertBefore($back, $node);
					}
					$counter++;
				}
			}			
			
			// Ready the TOC object for the template engine and render.
			$tocData = new ArrayData($tocData);
			$toc = $tocData->renderWith(self::$template);			
			
			// loadHTML will add the doctype + header, html, body, to create a proper 
			// html doc. If you only want the 'body', you can use the body node as a param
			// this will leave the body in, but str_replace will take care of that:
			// (php 5.3+)
			//$bodyNode = $doc->getElementsByTagName('body')->item(0);
			//$html     = $doc->saveHTML($bodyNode);
			
			$html     = $doc->saveHTML();
			$html     = str_replace(array('<body>','</body>'), '', $html);		

			// Domdocument thinks <a href="[sitetree_link,id=27]"> invalid and
			// will convert [ and ] to %5B and %5D. Seems a bit harsh to do 
			// urldecode on the entire content, but...
			$html     = urldecode($html); 
		}	

		return $toc . $html;
	}
	
	
	public function getTOCHeaderTag() {
		return self::$header_tag;
	}
	
	public function getTOCTitle() {
		$title = $this->owner->record['TOCTitle'];
		if (empty($title) && self::$toggle) $title = _t('TableOfContents.TOCDEFAULTTITLE', 'Table of contents');
	}
}	

