/*===========================================================================
    Copyright (c) 2007 Matias Capeletto

    Use, modification and distribution is subject to the Boost Software
    License, Version 1.0. (See accompanying file LICENSE_1_0.txt or copy at
    http://www.boost.org/LICENSE_1_0.txt)
===========================================================================*/

(function() {

// File Cache

var file_cache = new Array();

// Load an xml file, and pass it to the callback function when it is ready

function load_xml(sUrl, oCallback, bCached )
{
    if( bCached )
    {
        var oXml = file_cache[sUrl];
        if( oXml )
        {
            oCallback(oXml);
            return;
        }
    }
    function add_to_cache( oXml )
    {
        if( bCached )
        {
            file_cache[sUrl] = oXml;
        }
    }


    if (document.implementation && document.implementation.createDocument)
    {
        oXml = document.implementation.createDocument("", "", null);
        oXml.onload = function() { 
		add_to_cache(oXml);
            oCallback(oXml);
        };
        oXml.load(sUrl);

    }
    else if (window.ActiveXObject)
    {
        oXml = new ActiveXObject("Microsoft.XMLDOM");
        oXml.onreadystatechange = function () 
        {
            if (oXml.readyState == 4) 
            { 
                 add_to_cache(oXml);
                 oCallback(oXml);
            }
        };
        oXml.load(sUrl);
    }
    else if( window.XMLHttpRequest )
    {
        var XMLHttpRequestObject = new XMLHttpRequest();
        XMLHttpRequestObject.open("GET", sUrl);
        XMLHttpRequestObject.onreadystatechange = function()
        {
            if (XMLHttpRequestObject.readyState == 4)
            {
                var oXml = XMLHttpRequestObject.responseXML;
                add_to_cache(oXml);
                oCallback(oXml);
                delete XMLHttpRequestObject;
            }
        }
        XMLHttpRequestObject.send(null);
    }
    else
    {
        // unsupported browser
    }
}

// Public Interface

boostscript.load_file.load_xml = load_xml;

boostscript.load_file.loaded = true;

})();
