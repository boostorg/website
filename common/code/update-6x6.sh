#!/bin/sh

cd ${HOME}/www.boost.org/testing
for result in `find incoming -mmin +5 -name '*.zip'` ; do
  f=`basename ${result}`
  mv -f live/${f} old
  mv -f ${result} live
  rm -f old/${f}
done
