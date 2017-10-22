#!/usr/bin/env bash

if [[ -z "$TRAVIS" ]]; then
	echo "Script is only to be run by Travis CI" 1>&2
	exit 1
fi

if [[ -z "$TRAVIS_TAG" ]]; then
	echo "Build tag is required" 1>&2
	exit 0
fi

if [[ -z "$WP_ORG_PASSWORD" ]]; then
	echo "WordPress.org password not set" 1>&2
	exit 1
fi

if [[ -z "$WP_ORG_USERNAME" ]]; then
	echo "WordPress.org username not set" 1>&2
	exit 1
fi

if [[ -z "$PLUGIN" ]]; then
	echo "WordPress.org plugin not set" 1>&2
	exit 1
fi

# main config
CURRENTDIR=`pwd`
MAINFILE="woocommerce-digital-checkout.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="/tmp/$PLUGIN" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/$PLUGIN/" # Remote SVN repo on wordpress.org, with no trailing slash

# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy Wordpress Plugin"
echo
echo ".........................................."
echo

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks

NEWVERSION1=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`
echo "readme.txt version: $NEWVERSION1"
echo "$GITPATH$MAINFILE"
NEWVERSION2=`grep "Version:" $GITPATH$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" -ne "$NEWVERSION2" ]; then echo "Version in readme.txt & $MAINFILE don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and $MAINFILE. Let's proceed..."

#Check if the commited tag matches the readme

if [[ "$TRAVIS_TAG" != $NEWVERSION1 ]]; then
	echo "Build tag is required" 1>&2
	exit 0
fi

#Check if the tag already exists on SVN

TAG=$(svn ls "https://plugins.svn.wordpress.org/$PLUGIN/tags/$NEWVERSION1")
error=$?
if [ $error == 0 ]; then
    # Tag exists, don't deploy
    echo "Tag already exists for version $NEWVERSION1, aborting deployment"
    exit 1
fi

echo
echo "Creating local copy of SVN repo ..."
# Checkout the SVN repo
svn co $SVNURL $SVNPATH

# Move out the trunk directory to a temp location
mv $SVNPATH/trunk /tmp/svn-trunk
# Create trunk directory
mkdir $SVNPATH/trunk
# Copy our new version of the plugin into trunk
rsync -r -p ~/woocommerce-digital-checkout/* $SVNPATH/trunk

##DEAL WITH TRAVIS HERE..

# Copy all the .svn folders from the checked out copy of trunk to the new trunk.
# This is necessary as the Travis container runs Subversion 1.6 which has .svn dirs in every sub dir
cd $SVNPATH/trunk/
TARGET=$(pwd)
cd ../../svn-trunk/

echo
echo current dir is..
echo $CURRENTDIR

# Find all .svn dirs in sub dirs
SVN_DIRS=`find . -type d -iname .svn`

for SVN_DIR in $SVN_DIRS; do
    SOURCE_DIR=${SVN_DIR/.}
    TARGET_DIR=$TARGET${SOURCE_DIR/.svn}
    TARGET_SVN_DIR=$TARGET${SVN_DIR/.}
    if [ -d "$TARGET_DIR" ]; then
        # Copy the .svn directory to trunk dir
        cp -r $SVN_DIR $TARGET_SVN_DIR
    fi
done

#END DEAL WITH TRAVIS

# Back to builds dir
cd $SVNPATH

echo "Changing directory to SVN and committing to trunk"

#Ignore files
svn propset svn:ignore -R "bin/deploy.sh
README.md
.git
.gitignore
.travis.yml
phpcs.ruleset.xml
phpunit.xml.dist
tests
bin" "$SVNPATH"

svn status --no-ignore

cd $SVNPATH/trunk/

# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn ci --no-auth-cache --username=$WP_ORG_USERNAME --password $WP_ORG_PASSWORD -m "Deploying $NEWVERSION1"

echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn ci --username=$WP_ORG_USERNAME --password $WP_ORG_PASSWORD -m "Tagging version $NEWVERSION1"

echo "*** FIN ***"