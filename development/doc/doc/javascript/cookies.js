/*===========================================================================
    Copyright (c) 2007 Matias Capeletto

    Use, modification and distribution is subject to the Boost Software
    License, Version 1.0. (See accompanying file LICENSE_1_0.txt or copy at
    http://www.boost.org/LICENSE_1_0.txt)
===========================================================================*/


(function() {

/* Based on http://www.quirksmode.org/js/cookies.html */

function create_cookie( sName, sValue, nDays )
{
    var sExpires;
    if( nDays )
    {
        var dDate = new Date();
        dDate.setTime( dDate.getTime() + ( nDays * 24*60*60*1000 ) );
        sExpires = "; expires=" + dDate.toGMTString();
    }
    else
    {
        sExpires = "";
    }
    document.cookie = sName + "=" + sValue + sExpires + "; path=/";
}

function read_cookie(sName)
{
    var sNameEq = sName + "=";
    var aCookies = document.cookie.split(';');
    for(var i=0, len = aCookies.length ; i < len ; i++ )
    {
        var oCookie = aCookies[i].replace(/^\s+/g, "");
        if( oCookie.indexOf(sNameEq) == 0 )
        {
            return oCookie.substring( sNameEq.length, oCookie.length );
        }
    }
    return null;
}

// Public Interface

boostscript.cookies.create = create_cookie;
boostscript.cookies.read   = read_cookie;

boostscript.cookies.loaded = true;

})();

