<?php

/**
 *  @package Nagios_plugin
 -------------------------------------------------------------------------
 Nagios plugin for GLPI
 Copyright (C) 2016-2017 by SIMIA
 Author fabien.granjon@simia.fr
 https://github.com/pluginsGLPI/
 -------------------------------------------------------------------------
 LICENSE
      
 This file is part of Nagios Plugin.
 Nagios Plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Nagios Plugin  is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Nagios Plugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
  
*/

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");


$nagios_calendar=new PluginNagiosCalendar();
//Save entityi
if (isset ($_POST['save'])) {
 
     $data['calendars_id']=$_POST['calendars_id'];
     $data['extras']=$_POST['extras'];
     $data['alias']=$_POST['alias'];
     $data['id']=$_POST['id'];

     if ($_POST['id']=='') {
        unset($_POST['id']);
	$nagios_calendar->add($_POST);
     } else
        $nagios_calendar->update($_POST);

     Html::back();

} 

?>

