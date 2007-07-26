/*===========================================================================
    Copyright (c) 2007 Matias Capeletto

    Use, modification and distribution is subject to the Boost Software
    License, Version 1.0. (See accompanying file LICENSE_1_0.txt or copy at
    http://www.boost.org/LICENSE_1_0.txt)
===========================================================================*/

/* Requires: common.js */
/* Requires: cookies.js */
/* Requires: load_file.js */

(function() {

/* Based on http://alistapart.com/stories/alternate/ */

function set_active_stylesheet(sSelected)
{
    var aLinks = document.getElementsByTagName('link');
    for(var i = 0, len = aLinks.length; i < len; i++)
    {
        var oLink  = aLinks[i];
        var sTitle = oLink.getAttribute('title');
        if( oLink.getAttribute('rel').indexOf('style') != -1 && sTitle )
        {
            oLink.disabled = true;
            if ( sTitle == sSelected )
            {
                oLink.disabled = false;
            }
        }
    }
}

function get_active_stylesheet()
{
    var aLinks = document.getElementsByTagName('link');
    for(var i = 0; i < aLinks.length; i++)
    {
        var oLink  = aLinks[i];
        var sTitle = oLink.getAttribute('title');
        if( oLink.getAttribute('rel').indexOf('style') != -1 &&
            sTitle && ! oLink.disabled )
        {
            return sTitle;
        }
    }
    return null;
}

function get_preferred_stylesheet()
{
    var aLinks = document.getElementsByTagName('link');
    for(var i = 0; i < aLinks.length; i++)
    {
        var oLink  = aLinks[i];
        var sTitle = oLink.getAttribute('title');
        var oRel   = oLink.getAttribute('rel');
        if( oRel.indexOf('style') != -1 &&
            oRel.indexOf('alt'  ) == -1 &&
            sTitle                          )
        {
            return sTitle;
        }
    }
    return null;
}

function include_alternate_stylesheets(sXmlUrl,sUserBaseUrl)
{
    boostscript.load_file.load_xml(sXmlUrl, function(oXml) {

    var sBaseUrl = sUserBaseUrl ?
                   boostscript.common.format_base_url( sUserBaseUrl ) : './';

    var oBaseUrlNode = oXml.getElementsByTagName('base')[0];
    if( oBaseUrlNode != null )
    {
        sBaseUrl += boostscript.common.format_base_url(
            oBaseUrlNode.getAttribute('href')
        );
    }

    var oHead = document.getElementsByTagName("head")[0];

    var aStyles = oXml.getElementsByTagName('style');
    for( var i = 0, len = aStyles.length; i < len ; i++ )
    {
        var oStyle     = aStyles[i];
        var sPref      = oStyle.getAttribute('preferred');
        var bPreferred = sPref ? sPref == 'true' : false;

        var cssNode   = document.createElement('link');

        cssNode.type  = 'text/css';
        cssNode.rel   = ( (!bPreferred) ? 'alternate ' : '' ) + 'stylesheet';
        cssNode.href  = boostscript.common.format_url(
                            oStyle.getAttribute('href'),sBaseUrl
                        );
        cssNode.title = oStyle.getAttribute('title')

        oHead.appendChild(cssNode);
    }

    }, true );
}

function insert_style_selector(sId,sXmlUrl)
{
    boostscript.load_file.load_xml(sXmlUrl, function(oXml) {

    var sStyleSwitcherBox =  '<div class="ss-options">'                    ;

    var aStyles = oXml.getElementsByTagName('style');
    for( var i = 0, len = aStyles.length; i < len ; i++ )
    {
        var sTitle = aStyles[i].getAttribute('title');
        sStyleSwitcherBox += '<a href="#" '                                +
           'onclick="boostscript.style_switcher.set_active_stylesheet(\''  +
                                               sTitle                      +
                                         '\'); return false;" >'           +
                                 '<div class="ss-option-' + sTitle + '">'  +
                                 '</div>'                                  +
                             '</a>'                                        ;
    }

    document.getElementById(sId).innerHTML = sStyleSwitcherBox + '</div>';

    }, true );
}

function load_user_stylesheet(e)
{
    var sCookie = boostscript.cookies.read('style');
    set_active_stylesheet( sCookie ? sCookie : get_preferred_stylesheet() );
}

function save_user_stylesheet(e)
{
    boostscript.cookies.create( 'style', get_active_stylesheet(), 365 );
}

window.onload   = load_user_stylesheet;
window.onunload = save_user_stylesheet;

// Public Interface

boostscript.style_switcher.include_alternate_stylesheets = include_alternate_stylesheets;
boostscript.style_switcher.insert_style_selector         = insert_style_selector;
boostscript.style_switcher.set_active_stylesheet         = set_active_stylesheet;
boostscript.style_switcher.load_user_stylesheet          = load_user_stylesheet;

boostscript.style_switcher.loaded = true;

})();


