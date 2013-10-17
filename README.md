# TableOfContents module for SilverStripe CMS #

## Introduction ##

The TableOfContents module creates a table of contents for the current 
page's Content area on the fly, that becomes visible right away in the split 
view of the CMS.

#### features ####
 
 * automatic creation of links and page anchors
 * back-to-top links (optional)
 * toggle open/close (optional)
 * smooth scroll
 * fully configurable/stylable.
 * custom selection of header tags to include

## Installation ##

Copy the module to the root of your SilverStripe install and give it any name 
you want. Then visit <yourdomain>/dev/build?flush=1

## Usage ##
To enable the use of a Table of contents on your page, replace the `$Contents` placeholder in your template by `$ContentPlusTOC`. Something like:

    <div id="Content" class="content">$ContentPlusTOC</div>
   
Then on your Pages' `Table Of Contents tab`, check '* Display table of contents on this page *'.    

## Global configuration options ##
The global configuration is set in _config/TableOfContents.yml, in the tableofcontents-config section:

#### Toggle the table of contents ####

Toggling the table of contents open/close can be enabled/disabled: 

    tocconfig:
      global-config:
        toggle: true
        ...

#### Header size for the table of contents title ####

    tocconfig:
      global-config:
        header-tag: 'h3'
        ...

#### Back-to-top links ####

Back-to-top links can be set to the headertags on the page by adding the following.

    tocconfig:
      global-config:
        add-back-to-top: true
        ...

The text in these links can be changed by altering the translation in the language files. For example, in /lang/en.yml:

    en:
      TableOfContents:
        BACKTOTOP: '[Back to top]'

You can also change the appearance of these links by adding styles to your CSS (a, classname: backToTop)

#### No back-to-top for first header ####

You can prevent a back-to-top link being added to the first headertag if the toc is not that big:

    tocconfig:
      global-config:
        except-first-header: true
        ...

#### A custom template ####

By default the template `templates/Includes/TableOfContents.ss` is used to 
generate the Table of contents. To use your custom template:

    tocconfig:
      global-config:
        template: MyTableOfContens
        ...

#### Use JavaScript ####

The Table of contents is inserted using php by default. You can however still use 
the earlier JavaScript version, that might be easier on the resources in 
some cases(?):

    tocconfig:
      global-config:
        script-or-php: 'script'
        ...

## Per page configuration options ##

Find the 'Table Of Contents' Tab on your page,
      
#### Enable ####

Check 'Display table of contents on this page', then save and publish.

#### Included headers ####

By default headers h1, h2 and h3 are included in your table of contents, but
you can define your own selection for each page

#### Title ####
You can define your own title for the table of contents. If left empty while toggle is enabled, The default title is used. You can edit the default title in your language file:

    en:
      TableOfContents:
        ...
        TOCDEFAULTTITLE: 'Table of contents'      

## Styling ##

The table of contents is fully stylable: see css/TableOfContents.css  

## Todo ##
Turn the Table of contents into a nested list (?)


## Requirements ##

 * SilverStripe Framework 3.1+ and CMS 3.1+

## Maintainers ##

 * Martine Bloem (Martimiz)
 
 Please report any issues as an 'issue' on GitHub.