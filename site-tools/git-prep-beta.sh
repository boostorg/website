#!/bin/sh -e

# Goto the root of the website
cd $(dirname $0)/..

# Check if tree is clean
if ! git diff-index --quiet HEAD --
then
    echo "Tree is dirty."
    exit 1
fi

git fetch -q
git checkout -q beta

if ! git merge -q --ff-only origin/beta
then
    echo "Unable to fast forward merge from origin/beta."
    echo
    echo "Changes on origin/beta:"
    echo

    git log --pretty=oneline beta...origin/beta

    echo
    echo "Changes on beta:"
    echo

    git log --pretty=oneline origin/beta...beta

    exit 1
fi

if [ $(git rev-parse HEAD) != $(git merge-base origin/master beta) ]
then
    echo "Unmerged changes on beta:"
    echo

    git log --pretty=oneline origin/master...beta

    exit 1
fi

#TODO: Should I also merge changes from local master?
git merge -q --ff-only origin/master
