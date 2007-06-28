/*===========================================================================
    Copyright (c) 2007 Matias Capeletto

    Use, modification and distribution is subject to the Boost Software
    License, Version 1.0. (See accompanying file LICENSE_1_0.txt or copy at
    http://www.boost.org/LICENSE_1_0.txt)
===========================================================================*/

/* Common Functions and configuration */

(function() {

// Add the base url if it is relative

function format_url(sUrl,sBaseUrl)
{
    return ( sUrl.substring(0,7) == 'http://' ) ? sUrl : ( sBaseUrl + sUrl );
}

// Add '/' to the end if necesary

function format_base_url(sBaseUrl)
{
    return ( sBaseUrl!='' && sBaseUrl.charAt(sBaseUrl.length-1)!='/' ) ?
             ( sBaseUrl + '/' ) : sBaseUrl;
}

// Public Interface

boostscript.common.format_url      = format_url;
boostscript.common.format_base_url = format_base_url;

boostscript.common.loaded = true;

})();
