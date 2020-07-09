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


include ('../../../inc/includes.php');
Session::checkCentralAccess();

$no    = new PluginNagiosObject();
$macro = new PluginNagiosMacro();

if (isset($_POST["add"])) {
   $macro->check(-1, CREATE, $_POST);
   unset($_POST['id']);
   $_POST['name']=strtoupper($_POST['name']);
   if ($macro->add($_POST)) {
      Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosObject", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a macro to an Nagios Object'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST['update'])) {
  $macro->check( $_POST['id'] , UPDATE );
  $_POST['name']=strtoupper($_POST['name']);
  $macro->update($_POST);
  Html::back();


} else if (isset($_POST['delete'])) {
   foreach ( $_POST['item']['PluginNagiosMacro'] as $idx => $val)  {
        $macro->check($idx, PURGE );
   	$macro->delete(array('id'=>$idx));
  
   }
   Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosObject", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s delete  macro(s) to an Nagios Object'), $_SESSION["glpiname"])); 
   Html::back();
}  else if (isset($_GET['_in_modal'])) {
   Html::popHeader($macro->getTypeName(1),$_SERVER['PHP_SELF']);

   if (isset($_GET['is_global']))
	$is_global=$_GET['is_global'];
   else
	$is_global=0;

   $macro->showForm($_GET['id'],array('with_object_id'=>$_GET["plugin_nagios_objects_id"],'is_global'=>$is_global));
   Html::popFooter();
} 


//Html::displayErrorAndDie("lost");

