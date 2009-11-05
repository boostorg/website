#!/bin/sh

cd ${HOME}/www.boost.org/archives/incoming

wget -q -O boost-build.zip http://lvk.cs.msu.su/~ghost/boost_build_nightly/boost-build.zip
wget -q -O boost-build.tar.bz2 http://lvk.cs.msu.su/~ghost/boost_build_nightly/boost-build.tar.bz2

cd ${HOME}/www.boost.org/archives

for result in `find incoming -name '*.zip'` ; do
    f=`basename ${result}`
    mv -f live/${f} old
    mv -f ${result} live
    rm -f old/${f}
done
for result in `find incoming -name '*.tar.bz2'` ; do
    f=`basename ${result}`
    mv -f live/${f} old
    mv -f ${result} live
    rm -f old/${f}
done

/usr/bin/python24 /home/grafik/www.boost.org/testing/webcheck.py \
 "--yank=http://beta.boost.org/doc/libs" \
 "--yank=http://beta.boost.org/development/tests" \
 "--yank=http://beta.boost.org/development/webcheck" \
 "--yank=http://validator.w3.org/check" \
 "--yank=http://jigsaw.w3.org/css-validator/check/referer" \
 "--yank=http://tinyurl.com/" \
 "--yank=http://www.open-std.org/jtc1/sc22/wg21/docs/mailings/" \
 "--base-only" \
 "--quiet" \
 "--output=/home/grafik/www.boost.org/testing/webcheck" \
 "--force" \
 "http://beta.boost.org/" 2>&1

day=`date "+%w"`
case "$day" in
    [123456])
    # All but Sunday...
    ;;

    [0])
    # Sunday only...
    ;;
esac
