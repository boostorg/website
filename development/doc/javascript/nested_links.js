/*===========================================================================
    Copyright (c) 2007 Matias Capeletto

    Use, modification and distribution is subject to the Boost Software
    License, Version 1.0. (See accompanying file LICENSE_1_0.txt or copy at
    http://www.boost.org/LICENSE_1_0.txt)
===========================================================================*/

/*********************** NestedLinks API **********************************

In your html body use it like:
----------------------------------------------------------------------------
<!-- Include the grouped links java script api
     Remember to change this line with the path of your nested_links.js -->

<script type="text/javascript" src="nested_links.js"></script>

<!-- Add a form with an "id" attribute -->

<form id="boost_libs_list">

    <!-- Call the NestedLinks "select box" with the following parameters
        (1) id of the element where the select box will be inserted
        (2) NestedLinks xml definition url
        (3) user base url [optional]
        (4) selected item [optional]                                    -->

    <script type="text/javascript">

        nested_links_select_box('boost_libs_list',
                                'boost_libs_grouped_links.xml');

    </script>

</form>
---------------------------------------------------------------------------

Read the html docs for more information.

**************************************************************************/

/* Requires: common.js */
/* Requires: load_file.js */

(function() {

// Options for drop down list

function construct_select_option(oXmlElement,sClass,
                                 sBaseUrl,sDefaultUrl,sSelected)
{
    var sTag = oXmlElement.getAttribute('tag' );
    var sUrl = oXmlElement.getAttribute('href');
    return '<option ' +
            ((sSelected == sTag) ? 'selected ' : '') +
            'class="' + sClass + '"'  + ' value="' +
            ( sUrl ? boostscript.common.format_url(sUrl,sBaseUrl) : sDefaultUrl ) +
            '" >' + sTag + '</option>\n';
}

// Populate a select block from an xml and insert the result in sId div

function select_box(sId,sXmlUrl,sUserBaseUrl,sSelected)
{
    boostscript.load_file.load_xml(sXmlUrl, function(oEntireXml) {

    var oXml = oEntireXml.getElementsByTagName('nestedLinks')[0];

    // manage parameters

    var sBaseUrl = sUserBaseUrl ? boostscript.common.format_base_url( sUserBaseUrl ) : './';

    var oBaseUrlNode = oXml.getElementsByTagName('base')[0];
    if( oBaseUrlNode )
    {
        sBaseUrl += boost_format_base_url( oBaseUrlNode.getAttribute('href') );
    }

    var sDefaultUrl = sBaseUrl + 'index';
    var oTitle = oXml.getElementsByTagName('title')[0];
    if( sSelected == null && oTitle != null )
    {
        sSelected = oTitle.getAttribute('tag');
        var sUrl  = oTitle.getAttribute('href');
        if( sUrl )
        {
            sDefaultUrl = sUrl;
        }
    }

    // Construct the select box

    var sSelectHtml =
        '<select id="'+sId+'_internal"'                                     +
               ' class="nested-links"'                                      +
               ' size="1"'                                                  +
               ' OnChange="'                                                +
                'boostscript.nested_links.internal_go_to_url'               +
                '(\'' + sId + '_internal\')">\n' ;


    sSelectHtml += construct_select_option(
        oTitle, 'nested-links-title', sBaseUrl, sDefaultUrl, sSelected
    );

    var aGroups = oXml.childNodes;
    for(var ig = 0, glen = aGroups.length; ig < glen; ig++)
    {
        var oGroup = aGroups[ig];
        if( oGroup.nodeName == 'link' )
        {
            sSelectHtml += construct_select_option(
                oGroup,
                'nested-links-first', sBaseUrl, sDefaultUrl, sSelected
            );

            var aItems = oGroup.childNodes;
            for(var ii = 0, ilen = aItems.length; ii < ilen; ii++)
            {
                var oItem = aItems[ii];
                if( oItem.nodeName == 'link' )
                {
                    sSelectHtml += construct_select_option(
                        oItem,
                        'nested-links-second', sBaseUrl, sDefaultUrl, sSelected
                    );
                }
            }
        }
    }

    document.getElementById(sId).innerHTML = sSelectHtml + '</select>\n';

    } );
}

// Action function used when the user selects an option from the drop down list

function go_to_url(sId)
{
    var oe = document.getElementById(sId);
    parent.self.location = oe.options[oe.selectedIndex].value;
}

// Public Interface

boostscript.nested_links.internal_go_to_url = go_to_url;
boostscript.nested_links.select_box = select_box;

boostscript.nested_links.loaded = true;

})();

