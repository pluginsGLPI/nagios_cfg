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


$no=new PluginNagiosObject();

if  (isset($_POST['save_opts'])) {
  die("ObjectForm Call");
  
  $no->getFromDB($_POST['id']);
  /* save general information */
  $no->update($_POST);
  
  die();
  /* save field */
  foreach($_POST['field_id'] as $idx => $field_id ) {

    if (!isset($_POST["field_$field_id"]))
       $_POST["field_$field_id"]='';

   
    $field_value=$_POST["field_$field_id"];
    
    if (is_array($field_value)) {
      if ($field_value[0]){
          $input['value']=implode($field_value,':');
      } else {
          $input['value']='';
      }
    } else {
      $input['value']=$field_value;
    }
          
    $input['plugin_nagios_fields_id']=$field_id;
    $input['plugin_nagios_objects_id']=$_POST['id'];

    if (isset($_POST['flag_'.$field_id]) && $_POST['flag_'.$field_id]!='')
      $input['flag']=$_POST['flag_'.$field_id];

    $pv=new PluginNagiosObjectValue;
    $pv->getFromObjectIds($_POST['id'],$field_id);

    if (isset($pv->fields['id'])) {
      $input['id']=$pv->getID();

      if ((!$input['value'] && strlen($input['value'])==0) or ($input['value']=='[:EMPTY:]') ) {
        $pv->delete($input);
	
      } else {
        $pv->update($input);
	
      }
    } else {
      unset($input['id']);
      if (strlen($input['value'])>0 and $input['value']!='[:EMPTY:]') {
	  $pv->add($input);
	  

      }
    }
   }
  
  Html::back();

} 


if ($_GET['id']) {
 $no->getFromDB($_GET['id']);
 $oo=PluginNagiosObject::getNagiosObject($no->fields['type']);
 $oo->fields=$no->fields;
 Html::header($oo::getTypeName(), $_SERVER['PHP_SELF'], "plugins", get_class($oo) );
 $oo->display(array('id' =>$_GET["id"]));
 Html::footer();
} 


?>
