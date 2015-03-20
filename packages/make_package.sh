#!/bin/bash
###
# Matware Libraries
#
# @version $Id:
# @package Matware.Libraries
# @copyright Copyright (C) 2004 - 2015 Matware. All rights reserved.
# @author Matias Aguirre
# @email maguirre@matware.com.ar
# @link http://www.matware.com.ar/
# @license GNU General Public License version 2 or later; see LICENSE
#

PROJECT="matware-libraries"
VERSION="1.0.0"

RELEASE_DIR=`pwd`
#DIR="com_$PROJECT"
COM_WEBSERVICES="com_webservices"
LIB_MATWARE="lib_matware"
PLG_NAME="plg_sys_matware"
PLG_NAME_OAUTH="plg_auth_oauth2"

rm -rf *.zip

# Make component package
mkdir ${COM_WEBSERVICES}
cp -r ../administrator/components/${COM_WEBSERVICES}/ ${COM_WEBSERVICES}/admin
cp ${COM_WEBSERVICES}/admin/webservices.xml ${COM_WEBSERVICES}/.
cp ${COM_WEBSERVICES}/admin/install.php ${COM_WEBSERVICES}/.
cp -r ../api ${COM_WEBSERVICES}/admin/install/.
cp -r ../components ${COM_WEBSERVICES}/admin/install/.
cp -r ../etc ${COM_WEBSERVICES}/admin/install/.
cp -r ../media/${COM_WEBSERVICES}/ ${COM_WEBSERVICES}/.
zip -r ${COM_WEBSERVICES}.zip ${COM_WEBSERVICES}/

# Make library package
cp -r ../libraries/matware/ .
zip -r ${LIB_MATWARE}.zip matware/
rm -rf matware/

# Make oauth2 plugin package
cp -r ../plugins/authentication/oauth2/ .
zip -r ${PLG_NAME_OAUTH}.zip oauth2/

# Make oauth2 plugin package
cp -r ../plugins/system/matware/ .
zip -r ${PLG_NAME}.zip matware/

# Create final package
cd ..
zip -r pkg_${PROJECT}-${VERSION}.zip pkg_matware.xml packages/
cd packages/

# Move final package
mv ../pkg_${PROJECT}-${VERSION}.zip .

# Cleanup
rm -rf ${COM_WEBSERVICES} matware/ oauth2/
rm ${COM_WEBSERVICES}.zip ${LIB_MATWARE}.zip ${PLG_NAME_OAUTH}.zip ${PLG_NAME}.zip

# create symlink
rm -rf pkg_${PROJECT}-latest.zip
ln -s pkg_${PROJECT}-${VERSION}.zip pkg_${PROJECT}-latest.zip
