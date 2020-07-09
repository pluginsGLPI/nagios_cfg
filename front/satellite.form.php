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

//Session::checkRight("nagios", READ);

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$satellite= new PluginNagiosSatellite();

//$ht->processForm($_POST);

if (isset($_POST['add'])) {
          unset($_POST['id']);
          $satellite->add($_POST);
    }

if (isset($_POST['update'])) {
          $satellite->update($_POST);
}


if (isset($_GET['_in_modal'])) {
   Html::popHeader($satellite->getTypeName(1),$_SERVER['PHP_SELF']);
   $satellite->showForm($_GET["id"]);
   Html::popFooter();
} else { 

 Html::header($satellite::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosSatellite" );

 $satellite->display(array('id' =>$_GET["id"]));

 Html::footer();
}


?>
