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

$script = new PluginNagiosScript();

//$ht->processForm($_POST);

if (isset($_POST['add'])) {
          $script->add($_POST);
          Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('Item successfully added'),$_POST['name']));
}

if (isset($_POST['update'])) {
          $script->update($_POST);
          Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('Item successfully updated'),$_POST['name']));
}


if (isset($_GET['_in_modal'])) {
   Html::popHeader($script->getTypeName(1),$_SERVER['PHP_SELF']);
   $script->showForm($_GET["id"]);
   Html::popFooter();
} else { 

 Html::header($script::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosScript" );

 $script->display(array('id' =>$_GET["id"]));

 Html::footer();
}


?>
