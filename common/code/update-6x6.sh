#!/bin/sh

cd ${HOME}/www.boost.org

wget -q -O gmane.comp.lib.boost.announce.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.announce
wget -q -O gmane.comp.lib.boost.build.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.build
wget -q -O gmane.comp.lib.boost.devel.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.devel
wget -q -O gmane.comp.lib.boost.documentation.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.documentation
wget -q -O gmane.comp.lib.boost.interest.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.interest
wget -q -O gmane.comp.lib.boost.testing.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.testing
wget -q -O gmane.comp.lib.boost.user.rss http://rss.gmane.org/topics/excerpts/gmane.comp.lib.boost.user
