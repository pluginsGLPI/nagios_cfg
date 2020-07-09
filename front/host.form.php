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

$hostItem= new PluginNagiosHost();
$objectLinkItem=new PluginNagiosObjectLink();

$hostItem->processForm($_POST);

if (isset($_POST['update']) or isset($_POST['save_opts']) ) {

  Html::back();
  
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {

  $hostItem->redirectToList();
    
} else if (isset($_POST['add'])) {

  Html::redirect($_SERVER['HTTP_REFERER']."?id=".$hostItem->getID());
  
} else if (isset($_POST["clone_item"])) {

  if (!isset($_POST['item']['PluginNagiosObjectLink']))
    Html::back();

    $hostItem->getFromDB($_POST["plugin_nagios_objects_id"]);

  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $service_id)  { 

      $service=new PluginNagiosService;
      $service->getFromDB($service_id);

    $cloned_service=PluginNagiosService::cloneItem($service_id,"_clone", "","");
    if ($cloned_service && $cloned_service->fields['id'] ) {

      $cloned_service->fields['alias'] = $cloned_service->fields['alias']."_clone";
      $cloned_service->fields['name'] = $service->fields['name'];
      $cloned_service->fields['is_disabled'] = 0;
      $cloned_service->update($cloned_service->fields);

      //create Link  
      $tmpo=new PluginNagiosObjectLink();
      $dat['plugin_nagios_objects_id']=$_POST['plugin_nagios_objects_id'];
      $dat['items_id']=$cloned_service->getID();
      $dat['itemtype']='PluginNagiosService';
      $tmpo->add($dat);

    }
  }

  Html::back(); 

} else if (isset($_POST["disable_item"])) {


 if (!isset($_POST['item']['PluginNagiosObjectLink']))
    Html::back();

    $hostItem->getFromDB($_POST["plugin_nagios_objects_id"]);    

  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $service_id)  { 

    if(Session::haveRight("plugin_nagios_admin",1)){
      $service=new PluginNagiosService;
        $service->getFromDB($service_id);
        $service->fields['is_disabled']=1;
        $service->update($service->fields);
      }else{
      $Mainservices  = PluginNagiosObjectLink::getServicesForObject($_POST["plugin_nagios_objects_id"]);

      $found = false;
      foreach ($Mainservices as $key => $value) {
        if($service_id == $value['id']){
          $found = true;
          break;
        }
      }

      if($found){
        $service=new PluginNagiosService;
        $service->getFromDB($service_id);
        $service->fields['is_disabled']=1;
        $service->update($service->fields);
      }else{
        //clone and disable service 
        $cloned_service=PluginNagiosService::cloneItem($service_id,"_clone", "","");
        if ($cloned_service && $cloned_service->fields['id'] ) {

          $cloned_service->fields['alias'] = $cloned_service->fields['alias']."_clone";
          $cloned_service->fields['name'] = $service->fields['name'];
          $cloned_service->fields['is_disabled'] = 1;
          $cloned_service->update($cloned_service->fields);

          //create Link  
          $tmpo=new PluginNagiosObjectLink();
          $dat['plugin_nagios_objects_id']=$_POST['plugin_nagios_objects_id'];
          $dat['items_id']=$cloned_service->getID();
          $dat['itemtype']='PluginNagiosService';
          $tmpo->add($dat);

        }

      }
      }


    }

    Html::back(); 

}
 else if (isset($_POST["enable_item"])) {


 if (!isset($_POST['item']['PluginNagiosObjectLink']))
    Html::back();

    $hostItem->getFromDB($_POST["plugin_nagios_objects_id"]);    

  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $service_id)  { 

          if(Session::haveRight("plugin_nagios_admin",1)){
      $service=new PluginNagiosService;
        $service->getFromDB($service_id);
        $service->fields['is_disabled']=0;
        $service->update($service->fields);
      }else{
      $Mainservices  = PluginNagiosObjectLink::getServicesForObject($_POST["plugin_nagios_objects_id"]);

      $found = false;
      foreach ($Mainservices as $key => $value) {
        if($service_id == $value['id']){
          $found = true;
          break;
        }
      }

      if($found){
        $service=new PluginNagiosService;
        $service->getFromDB($service_id);
        $service->fields['is_disabled']=0;
        $service->update($service->fields);
      }else{
        //clone and disable service 
        $cloned_service=PluginNagiosService::cloneItem($service_id,"_clone", "","");
        if ($cloned_service && $cloned_service->fields['id'] ) {

          $cloned_service->fields['alias'] = $cloned_service->fields['alias']."_clone";
          $cloned_service->fields['name'] = $service->fields['name'];
          $cloned_service->fields['is_disabled'] = 1;
          $cloned_service->update($cloned_service->fields);

          //create Link  
          $tmpo=new PluginNagiosObjectLink();
          $dat['plugin_nagios_objects_id']=$_POST['plugin_nagios_objects_id'];
          $dat['items_id']=$cloned_service->getID();
          $dat['itemtype']='PluginNagiosService';
          $tmpo->add($dat);

        }

      }
      }
    }

    Html::back(); 

} else if (isset($_POST["addservicetoitem"])) {
    
  $_POST['is_model']=0;
  $_POST['type']='ST';
  $_POST['is_disabled'] = 0;
   
  $service=new PluginNagiosService;
  $service->add($_POST);
   
  $_POST['items_id']=$service->getID();

  //$object_link->check(-1, CREATE, $_POST);
  if ($objectLinkItem->add($_POST)) {
    Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosHost", 4, "setup",
   //TRANS: %s is the user login
             sprintf(__('%s adds a service to a host'), $_SESSION["glpiname"]));
  }
  
  Html::back();
} else if (isset($_POST['delete_item_services'])) {
  
  $services=PluginNagiosObjectLink::getServicesForObject($_POST['plugin_nagios_objects_id']);
    
  $serviceItem=new PluginNagiosService;
  if (!count($services)) {
    Html::back();
  }
  
  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $val)  {
  
    reset($services);
    $service_found=false;
    foreach($services  as $service)
      if ($service['id']==$val)
        $service_found=true;
  
    if ($service_found) {
      $serviceItem->delete(array('id'=>$val),1);
      Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosService", 4, "setup",
                 //TRANS: %s is the user login
                 sprintf(__('%s delete a service to a host'), $_SESSION["glpiname"]));   
    }
  }
  Html::back(); 
} else if ( isset($_POST['computer_import_service']) ) {

  if (!isset($_POST['item']['PluginNagiosObjectLink']))
	Html::back();

  $hostItem->getFromDB($_POST["plugin_nagios_objects_id"]);

  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $service_id)  { 
	$service=new PluginNagiosService;
	$service->getFromDB($service_id);
	$data=array();
	$data['is_model']=0;
	$data['parent_objects_id']=$service_id;
        $data['entities_id']=$_POST['entities_id'];
	$data['name']=$hostItem->fields['name'];
	$data['alias']=$service->fields['alias'];
        $data['type']='ST';
	$new_service=new PluginNagiosService();
	$new_service->add($data);

	$object_link=new PluginNagiosObjectLink();
	$data=array();
	$data['items_id']=$new_service->fields['id'];
	$data['plugin_nagios_objects_id']=$hostItem->getID();
        $data['itemtype']="PluginNagiosService";
	$object_link->add($data);
  }
  Html::back();
} else if ( isset($_POST['addroletoitem']) ) {
   $objectLinkItem->add($_POST);
   Html::back();
      
} else if (isset($_POST['delete_item_roles'])) {
  $ol=new PluginNagiosObjectLink;
  foreach ( $_POST['item']['PluginNagiosObjectLink'] as $idx => $val)  {
        $ol->delete(array('id'=>$idx));
        Event::log($_POST["plugin_nagios_objects_id"], "PluginNagiosRole", 4, "setup",
                 //TRANS: %s is the user login
                 sprintf(__('%s delete a role to a host'), $_SESSION["glpiname"]));
   }
  Html::back();

} 




if (isset($_GET['_in_modal'])) {
   Html::popHeader($hostItem->getTypeName(1),$_SERVER['PHP_SELF']);
   if (isset($_GET['with_tab']) && $_GET['with_tab']==1) {
//     $ht->display(array('id' =>$_GET["id"]));
     $hostItem->taborientation="horizontal";
     $hostItem->display(array('id' =>$_GET["id"]));

   } else {
     $hostItem->showForm($_GET["id"]);
   }
   Html::popFooter();

} else { 

  Html::header($hostItem::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosHost" );
  $hostItem->display(array('id' =>$_GET["id"]));
  Html::footer();
} 




?>
