/*===========================================================================
    Copyright (c) 2007 Matias Capeletto

    Use, modification and distribution is subject to the Boost Software
    License, Version 1.0. (See accompanying file LICENSE_1_0.txt or copy at
    http://www.boost.org/LICENSE_1_0.txt)
===========================================================================*/

var boostscript;

(function() {

function Namespace(oLibrary,nId,sFilePath,aInclude)
{
    this.id   = nId;   this.path   = sFilePath;
    this.used = false; this.loaded = false;

    this.include  = aInclude ? aInclude : new Array();
    oLibrary.namespace[nId] = this;
}

function boostscript_library()
{
    this.namespace = new Array();
    var id = 0;

/************************************************************************/
/*  Modify this section to add new components to the library            */
/*  Do not forget to add an 'add_component' call in the listing         */
/*  below including the file dependencies                               */
/*                                                                      */
/*                                                                      */

    this.common                         = new Namespace(this,id++,
                                              'common.js'
    );

    this.load_file                      = new Namespace(this,id++,
                                              'load_file.js'
    );

    this.cookies                        = new Namespace(this,id++,
                                              'cookies.js'
    );

    this.nested_links                   = new Namespace(this,id++,
                                              'nested_links.js',
        new Array( // Requires
                      this.common,
                      this.load_file
        )
    );

    this.style_switcher                 = new Namespace(this,id++,
                                              'style_switcher.js',
        new Array( // Requires
                      this.common,
                      this.cookies,
                      this.load_file
        )
    );

/*                                                                      */
/*                                                                      */
/************************************************************************/

}

function safari_browser()
{
    return ( navigator.vendor.indexOf('Apple') != -1 );
}

function include_components( aUsedComponents, sUserBaseUrl )
{
    insert_needed_includes( boostscript.namespace, aUsedComponents,
                            format_base_url(sUserBaseUrl) );
}

function insert_needed_includes( aComponents, aUsedComponents, sBaseUrl )
{
    for(var i = 0, len = aUsedComponents.length; i < len; i++)
    {
        find_needed_includes( aUsedComponents[i] );
    }

    if( safari_browser() )
    {
        write_insertion_included_scripts( sBaseUrl );
    }
    else
    {
        dom_insertion_included_scripts( sBaseUrl );
    }
}

function find_needed_includes( oComp )
{
    if( oComp.used ) return;
    oComp.used = true;
    var aInclude = oComp.include;
    for(var i = 0, len = aInclude.length; i < len; i++ )
    {
       find_needed_includes( aInclude[i] );
    }
}

function dom_insertion_included_scripts( sBaseUrl )
{
    var namespace = boostscript.namespace;
    var oHead = document.getElementsByTagName("head")[0];
    for(var i = 0, len = namespace.length; i < len ; i++ )
    {
        if( namespace[i].used )
        {
            var newScript  = document.createElement('script');
            newScript.type = 'text/javascript';
            newScript.src  = format_url( namespace[i].path, sBaseUrl );
            oHead.appendChild( newScript );
        }
    }
}

function write_insertion_included_scripts( sBaseUrl )
{
    var namespace = boostscript.namespace;
    var sScriptsHtml = '';
    for(var i = 0, len = namespace.length; i < len ; i++ )
    {
        if( namespace[i].used )
        {
            sScriptsHtml += '<script type="text/javascript" scr="'      +
                            format_url( namespace[i].path, sBaseUrl ) +
                            '"></script>\n';
        }
    }
    document.write( sScriptsHtml );
}

function format_base_url(sBaseUrl)
{
    return ( sBaseUrl != '' && sBaseUrl.charAt(sBaseUrl.length-1)!='/' ) ?
             ( sBaseUrl + '/' ) : sBaseUrl;
}

function format_url(sUrl,sBaseUrl)
{
    return ( sUrl.substring(0,7) == 'http://' ) ? sUrl : ( sBaseUrl + sUrl );
}

function async_call( oNamespace, oFunc )
{
    if( ! oNamespace.loaded )
    {
        setTimeout( function() { async_call( oNamespace, oFunc ); }, 200 );
    }
    else
    {
        oFunc();
    }
}

boostscript = new boostscript_library();
boostscript.init = include_components;
boostscript.async_call = async_call;
boostscript.call = function(n,f,p1,p2,p3,p4,p5)
{
    async_call( n,
        function()
        {
            n[f](p1,p2,p3,p4,p5);
        }
    );
};

})();
