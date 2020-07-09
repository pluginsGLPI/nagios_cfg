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

$roleItem= new PluginNagiosRole();
$objectLinkItem=new PluginNagiosObjectLink();

$roleItem->processForm($_POST);

if (isset($_POST['update']) or isset($_POST['save_opts']) ) {

  Html::back();
  
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {

  $roleItem->redirectToList();
  
} else if (isset($_POST['add'])) {

  Html::redirect($_SERVER['HTTP_REFERER']."?id=".$roleItem->getID());
  
} else if (isset($_POST["addservicetoitem"])) {

   $_POST['is_model']=0;
   $_POST['type']='ST';
    $_POST['is_disabled'] = 0;

   
   $service=new PluginNagiosService;
   $service->add($_POST);
   
   $_POST['items_id']=$service->getID();

   //$object_link->check(-1, CREATE, $_POST);
   if ($objectLinkItem->add($_POST)) {
      Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosRole", 4, "setup",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a service to a role'), $_SESSION["glpiname"]));
   }
   
   Html::back();
  
}  else if (isset($_POST['delete_item_services'])) {

  $service=new PluginNagiosService;

  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $val)  {
        
        $service->delete(array('id'=>$val),1);
        Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosService", 4, "setup",
                 //TRANS: %s is the user login
                 sprintf(__('%s delete a service to a role'), $_SESSION["glpiname"]));
        
   }
   Html::back();

} else if (isset($_POST['computer_import_service'])  ) {

  if (!isset($_POST['item']['PluginNagiosObjectLink']))
        Html::back();
        
  $roleItem->getFromDB($_POST["plugin_nagios_objects_id"]);

  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $service_id)  { 
        $service=new PluginNagiosService;
        $service->getFromDB($service_id);
        $data=array();
        $data['is_model']=0;
        $data['parent_objects_id']=$service_id;
        $data['entities_id']=$_POST['entities_id'];
        $data['name']=$roleItem->fields['name'];
        $data['alias']=$service->fields['alias'];
        $data['type']='ST';
        $new_service=new PluginNagiosService();
        $new_service->add($data);

        $object_link=new PluginNagiosObjectLink();
        $data=array();
        $data['items_id']=$new_service->fields['id'];
        $data['plugin_nagios_objects_id']=$roleItem->getID();
        $data['itemtype']="PluginNagiosService";
        $object_link->add($data);
  }

  Html::back();

} 



if (isset($_GET['_in_modal']) or isset($_GET['with_tab']) ) {
   Html::popHeader($roleItem->getTypeName(1),$_SERVER['PHP_SELF']);
   if (isset($_GET['with_tab']) && $_GET['with_tab']==1) {
//     $ht->display(array('id' =>$_GET["id"]));
     Session::initNavigateListItems("PluginNagiosRole",$roleItem->getTypeName(1), '#');

     $roleItem->taborientation="horizontal";
     $roleItem->display(array('id' =>$_GET["id"],'_in_modal'=>1,'with_tab'=>1));

   } else {
     $roleItem->showForm($_GET["id"]);
   }
   Html::popFooter();

} else { 

  Html::header($roleItem::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosRole" );
  $roleItem->display(array('id' =>$_GET["id"]));
  Html::footer();
} 







?>
