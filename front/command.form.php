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

$co= new PluginNagiosCommand();

//$ht->processForm($_POST);

if (isset($_POST['add'])) {
        unset($_POST['id']);
        $_POST['comment']=$_POST['line'];
	if ($co->add($_POST))
           Event::log($co->fields['id'], "PluginNagiosCommand", 4, "Inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a new command'), $_SESSION["glpiname"]));
}

if (isset($_POST['update'])) {
         $_POST['comment']=$_POST['line'];
          if ($co->update($_POST))
	   Event::log($co->fields['id'], "PluginNagiosCommand", 4, "Inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s update command'), $_SESSION["glpiname"]));
          Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('Item successfully updated'),$_POST['name']));
}


if (isset($_GET['_in_modal'])) {
   Html::popHeader($co->getTypeName(1),$_SERVER['PHP_SELF']);
   $co->showForm($_GET["id"]);
   Html::popFooter();
} else { 

 Html::header($co::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosCommand" );

 $co->display(array('id' =>$_GET["id"]));

 Html::footer();
}


?>
