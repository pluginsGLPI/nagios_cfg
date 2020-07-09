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

Session::checkRight("plugin_nagios", READ);

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$serviceItem= new PluginNagiosService();

$serviceItem->processForm($_POST);

if (isset($_POST['update']) or isset($_POST['save_opts']) ) {

  Html::back();
  
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {

  $serviceItem->redirectToList();
  
} else if (isset($_POST['add'])) {

  Html::redirect($_SERVER['HTTP_REFERER']."?id=".$serviceItem->getID());
  
}



if (isset($_GET['_in_modal']) or isset($_GET['with_tab']) or (isset($_GET['form_host'])) ) {

   Html::popHeader($serviceItem->getTypeName(1),$_SERVER['PHP_SELF']);
   if ((isset($_GET['with_tab']) && $_GET['with_tab']==1) or (isset($_GET['from_host']))) {
//     $ht->display(array('id' =>$_GET["id"]));
     Session::initNavigateListItems("PluginNagiosService",$serviceItem->getTypeName(1), '#');

     $serviceItem->taborientation="horizontal";
     $serviceItem->display(array('id' =>$_GET["id"],"from_host"=>$_GET['from_host'],'_in_modal'=>1,'with_tab'=>1));

   } else {
     $serviceItem->showForm($_GET["id"]);
   }
   Html::popFooter();

} else { 

  Html::header($serviceItem::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosService" );
  $serviceItem->display(array('id' =>$_GET["id"]));
  Html::footer();
} 






?>
