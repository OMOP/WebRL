#!/bin/bash
###############################################################################
#
#	OMOP - Cloud Research Lab
# 
#	Observational Medical Outcomes Partnership
#	©2009 Foundation for the National Institutes of Health (FNIH)
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
#	Date: 		2010/06/14
#
#	Script that run updgrade procedure from 1.6 to 1.8
# 
############################################################################### 


function update_kernel_from_8_to_10()
{
	yum update
	yum clean all

	# This step is required for updating to Fedora 10. 
	rpm -Uhv http://archive.kernel.org/fedora-archive/releases/10/Fedora/i386/os/Packages/fedora-release-10-1.noarch.rpm http://archive.kernel.org/fedora-archive/releases/10/Fedora/i386/os/Packages/fedora-release-notes-10.0.0-1.noarch.rpm

	yum clean all
	yum -y update
}

function install_package_builders()
{
	rpm -i ftp://ftp.pbone.net/mirror/ftp5.gwdg.de/pub/opensuse/repositories/Ports:/DebianBased:/Tools/openSUSE_10.3/i586/dpkg-1.14.4-3.1.i586.rpm
	yum -y install fakeroot
	
	yum -y install rpm-build
}

# Check that command yum list kernel returns following lines:
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # 
# Loaded plugins: fastestmirror
# Loading mirror speeds from cached hostfile
# * fedora: archive.fedoraproject.org
# * updates: archive.fedoraproject.org
#Available Packages
#kernel.i586       2.6.27.41-170.2.117.fc10      updates
#kernel.i686       2.6.27.41-170.2.117.fc10      updates
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # 
# If not uncomment line below. 
update_kernel_from_8_to_10

install_package_builders