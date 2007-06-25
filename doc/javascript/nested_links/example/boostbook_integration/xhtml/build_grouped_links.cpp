#include <iostream>
#include <fstream>
#include <string>
#include <functional>
#include <algorithm>

#include <boost/property_tree/ptree.hpp>
#include <boost/property_tree/xml_parser.hpp>
#include <boost/optional.hpp>
#include <boost/none.hpp>

using namespace boost::property_tree;
using namespace boost;
using namespace std;


optional<ptree&> find_toc( ptree& html )
{
    ptree& pbody = html.get_child("html.body");
    for( ptree::iterator i = pbody.begin(), ie = pbody.end();
         i != ie ; ++i )
    {
        std::cout << i->second.get<string>("<xmlattr>","") << std::endl;
        if( i->second.get<string>("<xmlattr>","") == "body" )
        {
            ptree& pc = i->second.get_child("div.div.div");
            for( ptree::iterator ic = pc.begin(), iec = pc.end();
                ic != iec ; ++ic )
            {
                if( i->second.get<string>("<xmlattr>","") == "toc" )
                {
                    return i->second.get_child("dl");
                }
            }
        }
    }
    return none;
}

int main()
{
    ptree html;

    std::string in_name = "index.html";
/*
    ifstream inhtml( in_name.c_str(), ios_base::in );
    if( !inhtml )
    {
        std::cout << std::endl << "dow!" << std::endl;
    }
*/
    read_xml(in_name,html);

    optional<ptree&> toc = find_toc(html);
    if( toc )
    {
        std::cout << std::endl << "great!" << std::endl;
    }


    return 0;
}

