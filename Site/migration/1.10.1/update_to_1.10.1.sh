#!/bin/bash
###############################################################################
#
#   OMOP - Cloud Research Lab
# 
#   Observational Medical Outcomes Partnership
#   (c)2009-2010 Foundation for the National Institutes of Health (FNIH)
# 
#   Licensed under the Apache License, Version 2.0 (the "License"); you may not
#   use this file except in compliance with the License. You may obtain a copy
#   of the License at http://omop.fnih.org/publiclicense.
# 
#   Unless required by applicable law or agreed to in writing, software
#   distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
#   WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
#   redistributions of this work or any derivative work or modification based on
#   this work should be accompanied by the following source attribution: "This
#   work is based on work by the Observational Medical Outcomes Partnership
#   (OMOP) and used under license from the FNIH at
#   http://omop.fnih.org/publiclicense.
# 
#   Any scientific publication that is based on this work should include a
#   reference to http://omop.fnih.org.
# 
#   Date: 		2010/12/01
#
#   Script installs sqlplus (to be able to connect to Oracle databases)
# 
############################################################################### 


SVN_USERNAME=omop
SVN_PASSWORD=miJohgah7Ceiram
SVN_ROOT=https://cloud.circle.com.ua:4443/svn/cloud/trunk

function install_sqlplus()
{
    ARCH=$(uname -i)
    ORACLE_CLIENT_BASIC_INSTALLER=oracle-instantclient11.2-basic-11.2.0.2.0.${ARCH}.rpm
	ORACLE_CLIENT_ODBC_INSTALLER=oracle-instantclient11.2-odbc-11.2.0.2.0.${ARCH}.rpm
	ORACLE_CLIENT_SQLPLUS_INSTALLER=oracle-instantclient11.2-sqlplus-11.2.0.2.0.${ARCH}.rpm
	ORACLE_CLIENT_DEVEL_INSTALLER=oracle-instantclient11.2-devel-11.2.0.2.0.${ARCH}.rpm

    if [[ ${ARCH} == "x86_64" ]]; then
        ORACLE_HOME="/usr/lib/oracle/11.2/client64"
    else
        ORACLE_HOME="/usr/lib/oracle/11.2/client"
    fi

    echo t | svn --no-auth-cache --username ${SVN_USERNAME} --password ${SVN_PASSWORD} export ${SVN_ROOT}/Development/InstanceConfiguration/installation/toolinstance/install/oracle.${ARCH}/${ORACLE_CLIENT_BASIC_INSTALLER} ${ORACLE_CLIENT_BASIC_INSTALLER}
    echo t | svn --no-auth-cache --username ${SVN_USERNAME} --password ${SVN_PASSWORD} export ${SVN_ROOT}/Development/InstanceConfiguration/installation/toolinstance/install/oracle.${ARCH}/${ORACLE_CLIENT_ODBC_INSTALLER} ${ORACLE_CLIENT_ODBC_INSTALLER}
    echo t | svn --no-auth-cache --username ${SVN_USERNAME} --password ${SVN_PASSWORD} export ${SVN_ROOT}/Development/InstanceConfiguration/installation/toolinstance/install/oracle.${ARCH}/${ORACLE_CLIENT_SQLPLUS_INSTALLER} ${ORACLE_CLIENT_SQLPLUS_INSTALLER}
    echo t | svn --no-auth-cache --username ${SVN_USERNAME} --password ${SVN_PASSWORD} export ${SVN_ROOT}/Development/InstanceConfiguration/installation/toolinstance/install/oracle.${ARCH}/${ORACLE_CLIENT_DEVEL_INSTALLER} ${ORACLE_CLIENT_DEVEL_INSTALLER}
    echo t | svn --no-auth-cache --username ${SVN_USERNAME} --password ${SVN_PASSWORD} export ${SVN_ROOT}/Development/InstanceConfiguration/installation/toolinstance/install/oracle.${ARCH}/oracle_home oracle_home

    # Perform installation of packages.
	yum -y --nogpgcheck localinstall ${ORACLE_CLIENT_BASIC_INSTALLER}
    yum -y --nogpgcheck localinstall ${ORACLE_CLIENT_ODBC_INSTALLER}
	yum -y --nogpgcheck localinstall ${ORACLE_CLIENT_SQLPLUS_INSTALLER}
    yum -y --nogpgcheck localinstall ${ORACLE_CLIENT_DEVEL_INSTALLER}

	yes | cp -R -f oracle_home/* ${ORACLE_HOME} 
	rm -Rf oracle_home
	chmod a+r -R ${ORACLE_HOME}
	chmod a+x -R ${ORACLE_HOME}/bin
	
	rm -f oracle-instantclient11*
	
	chmod a+x ${ORACLE_HOME}/rdbms
	chmod a+x ${ORACLE_HOME}/rdbms/mesg
	chmod a+x ${ORACLE_HOME}/network
	chmod a+x ${ORACLE_HOME}/network/mesg

    # Configure profile to support Oracle
    rm -f /etc/profile.d/sqlplus.sh

    echo "export ORACLE_HOME=${ORACLE_HOME}" >> /etc/profile.d/sqlplus.sh
    echo "PATH=\$PATH:\$ORACLE_HOME/bin" >> /etc/profile.d/sqlplus.sh
    echo "export LD_LIBRARY_PATH=\$ORACLE_HOME/lib:\$LD_LIBRARY_PATH"  >> /etc/profile.d/sqlplus.sh
    echo "export SQLPATH=\$ORACLE_HOME/lib:\$SQLPATH"  >> /etc/profile.d/sqlplus.sh
    echo "export NLS_LANG=AMERICAN_AMERICA.UTF8"  >> /etc/profile.d/sqlplus.sh
}

function install_omop_oracle_connectivity()
{
	DSN=$1
	SERVER_NAME=$2

	mkdir -p ${ORACLE_HOME}/network/admin
	chmod a+x ${ORACLE_HOME}/network/admin
	cat	> ${ORACLE_HOME}/network/admin/tnsnames.ora <<EOL
${DSN} =
  (DESCRIPTION =
    (ADDRESS = (PROTOCOL = TCP)(HOST = ${SERVER_NAME})(PORT = 1521))
    (CONNECT_DATA =
      (SERVER = DEDICATED)
      (SERVICE_NAME = ${DSN})
    )
  )
EOL

	chmod 666 ${ORACLE_HOME}/network/admin/tnsnames.ora
}

function install_php_s3_library()
{
    echo t | svn --no-auth-cache --username ${SVN_USERNAME} --password ${SVN_PASSWORD} export ${SVN_ROOT}/Development/libraries/s3-php5-curl_0.4.0.tar.gz /usr/share/php/s3-php5-curl_0.4.0.tar.gz
    cd /usr/share/php
    tar -xvf /usr/share/php/s3-php5-curl_0.4.0.tar.gz
    rm -f /usr/share/php/s3-php5-curl_0.4.0.tar.gz
}

install_sqlplus

install_omop_oracle_connectivity "XE" "174.129.186.189"

install_php_s3_library

# Install Zend on the Web RL instance.
yum -y install php-ZendFramework php-ZendFramework-Db-Adapter-Mysqli

# Install oci8

pear install pecl/oci8 <<EOL
instantclient,/usr/lib/oracle/11.2/client/lib
EOL
echo "extension=oci8.so" >> /etc/php.d/oci8.ini
service httpd restart

# Created folder for storing results and give appropriate permissions
mkdir -p /omop1/phase3
chown omop:omop /omop1/phase3

yum -y install dos2unix
