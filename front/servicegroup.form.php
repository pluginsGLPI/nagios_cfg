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

$sg = new PluginNagiosServiceGroup();

$sg->processForm($_POST);

if (isset($_POST['update']) or isset($_POST['save_opts']) ) {

  Html::back();
  
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {
  $sg->redirectToList();
  
} else if (isset($_POST['add'])) {

  Html::redirect($_SERVER['HTTP_REFERER']."?id=".$sg->getID());
  
}



if (isset($_GET['_in_modal'])) {
   Html::popHeader($sg->getTypeName(1),$_SERVER['PHP_SELF']);
   $sg->showForm($_GET["id"]);
   Html::popFooter();
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {
  $sg->redirectToList();

} else {

 Html::header($sg::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosServicegroup" );

 $sg->display(array('id' =>$_GET["id"]));

 Html::footer();
}



?>
