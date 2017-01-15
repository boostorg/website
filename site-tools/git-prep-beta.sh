#!/bin/bash -e

# Do a fast forward merge without checking out the destination branch.
git_ff_merge() {
    from=$1
    to=$2

    if [[ $(git symbolic-ref --short -q HEAD) == $to ]]
    then
        git merge -q --ff-only $from
    else
        git fetch -q . $from:$to
    fi
}

# Goto the root of the website
cd $(dirname $0)/..

# Check if tree is clean
if ! git diff-index --quiet HEAD --
then
    echo "Tree is dirty."
    exit 1
fi

echo "- Fetching from origin"

git fetch -q origin

echo "- Update local beta from origin"

if ! git_ff_merge origin/beta beta
then
    echo "Unable to fast forward merge from origin/beta."
    echo
    echo "Changes on origin/beta:"
    echo

    git log --reverse --pretty=oneline beta..origin/beta

    echo
    echo "Changes on beta:"
    echo

    git log --reverse --pretty=oneline origin/beta..beta

    exit 1
fi

echo "- Check for unmerged changes in beta"

if [ $(git rev-parse beta) != $(git merge-base origin/master beta) ]
then
    echo "Unmerged changes on beta:"
    echo

    git log --reverse --pretty=oneline origin/master..beta

    exit 1
fi

echo "- Update beta from origin/master"

git_ff_merge origin/master beta
git push origin beta
git checkout beta

echo "- Beta is now up to date."
