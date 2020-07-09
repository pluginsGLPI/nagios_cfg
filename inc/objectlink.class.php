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


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Item_Ticket Class
 *
 *  Relation between Tickets and Items
**/
class PluginNagiosObjectLink extends CommonDBRelation{

   static $rightname="plugin_nagios";

   // From CommonDBRelation
   static public $itemtype_1          = 'PluginNagiosObject';
   static public $items_id_1          = 'plugin_nagios_objects_id';

   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;



   static function getNagiosIDForType($ids,$item_type='Computer') {
      global $DB;
	$result=array();

	$query= "select items_id,plugin_nagios_objects_id from `glpi_plugin_nagios_objectlinks` where ";
	if (is_array($ids)) {
		if (count($ids)==0)
			return null;
		$query.="  items_id in (".implode(",",$ids).") ";
	} else
		$query.="  items_id='$ids' ";
       $query.=" AND itemtype='$item_type' ";

       foreach ($DB->request($query) as $data) {
          $result[$data['items_id']] = $data['plugin_nagios_objects_id'];
       }

	return $result;
   }

   static function getServicesForObject($objects_id, $condition='') {
      global $DB;

      $services = array();
      $query  = "SELECT `glpi_plugin_nagios_objects`.*,
                        `glpi_plugin_nagios_objectlinks`.`id` AS IDD,
                        `glpi_plugin_nagios_objectlinks`.`id` AS linkID
                 FROM `glpi_plugin_nagios_objectlinks`
                 LEFT JOIN `glpi_plugin_nagios_objects` ON (`glpi_plugin_nagios_objects`.`id` = `glpi_plugin_nagios_objectlinks`.`items_id`)
                 WHERE `glpi_plugin_nagios_objectlinks`.`plugin_nagios_objects_id` = '$objects_id' AND `glpi_plugin_nagios_objectlinks`.`itemtype`='PluginNagiosService' ";
      if (!empty($condition)) {
         $query .= " AND $condition ";
      }
      $query.=" ORDER BY `glpi_plugin_nagios_objects`.`name`,`glpi_plugin_nagios_objects`.`alias` ";

      

      foreach ($DB->request($query) as $data) {
         $services[$data['IDD']] = $data;
      }
      return $services;
   }


   static function getRolesForObject($objects_id, $condition='') {
      global $DB;

      $roles = array();
      $query  = "SELECT `glpi_plugin_nagios_objects`.*,
                        `glpi_plugin_nagios_objectlinks`.`id` AS IDD,
                        `glpi_plugin_nagios_objectlinks`.`id` AS linkID
                 FROM `glpi_plugin_nagios_objectlinks`
                 LEFT JOIN `glpi_plugin_nagios_objects` ON (`glpi_plugin_nagios_objects`.`id` = `glpi_plugin_nagios_objectlinks`.`items_id`)
                 WHERE `glpi_plugin_nagios_objectlinks`.`plugin_nagios_objects_id` = '$objects_id' AND `glpi_plugin_nagios_objectlinks`.`itemtype`='PluginNagiosRole' ";
      if (!empty($condition)) {
         $query .= " AND $condition ";
      }
      $query.=" ORDER BY `glpi_plugin_nagios_objects`.`name`";



      foreach ($DB->request($query) as $data) {
         $roles[$data['IDD']] = $data;
      }
      return $roles;
   }




   static function getInconsistentServiceForHost($item,$new_parent_id) {
     global $DB;

     $services=array();
    
     if (!$item->fields['parent_objects_id'])
	return $services;

     $query="SELECT DISTINCT glpi_plugin_nagios_objects.id AS wrong_service_id , v11.* FROM glpi_plugin_nagios_objectlinks".
           " LEFT JOIN `glpi_plugin_nagios_objects` ON (`glpi_plugin_nagios_objects`.`id` = `glpi_plugin_nagios_objectlinks`.`items_id`".
           "          AND  itemtype='PluginNagiosService' and plugin_nagios_objects_id='".$item->getId()."' )".
           " LEFT JOIN `glpi_plugin_nagios_objects` AS v11 on (v11.id=glpi_plugin_nagios_objects.parent_objects_id) ".
           " WHERE glpi_plugin_nagios_objects.type='ST' and v11.is_model=0 ".
           " AND v11.id NOT IN ( SELECT items_id FROM glpi_plugin_nagios_objectlinks WHERE itemtype='PluginNagiosService' and plugin_nagios_objects_id='".$new_parent_id."' )";

     foreach ($DB->request($query) as $data) {
         $services[$data['id']] = $data;
      }
     
     return $services;





   }

   static function getHostForItem($item_id,$item_type) {

      global $DB;
      $host=new PluginNagiosHost();
      $query  = "SELECT `glpi_plugin_nagios_objects`.*
                 FROM `glpi_plugin_nagios_objectlinks`
                 LEFT JOIN `glpi_plugin_nagios_objects` ON (`glpi_plugin_nagios_objects`.`type`='HT' and  `glpi_plugin_nagios_objects`.`id` = `glpi_plugin_nagios_objectlinks`.`plugin_nagios_objects_id`)
                 WHERE `glpi_plugin_nagios_objectlinks`.`items_id` = '$item_id' AND `glpi_plugin_nagios_objectlinks`.`itemtype`='$item_type' ";


      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) == 1) {
            $host->fields = $DB->fetch_assoc($result);
            $host->post_getFromDB();
         }
      }
      return $host;
   }


   static function getHostsForType($item_type,$options="") {

      global $DB;
      $hosts=array();
      $query  = "SELECT `glpi_plugin_nagios_objects`.*
                 FROM `glpi_plugin_nagios_objectlinks`,`glpi_plugin_nagios_objects` 
                 WHERE `glpi_plugin_nagios_objects`.`type`='HT' AND 
                       `glpi_plugin_nagios_objects`.`id` = `glpi_plugin_nagios_objectlinks`.`plugin_nagios_objects_id` AND
                       `glpi_plugin_nagios_objectlinks`.`itemtype`='$item_type' ";

      if (isset($options['restrict_entities'])) {
	if (!count($options['restrict_entities']))
		return array();

	$query.=" AND `glpi_plugin_nagios_objectlinks`.`items_id`  in ( select id from ".getTableForItemType($item_type)." WHERE entities_id in ('".implode("','",$options['restrict_entities'])."') ) "; 
      }

      if (isset($options['conditions'])) 
	$query.='  AND '.$options['conditions'];
      foreach ($DB->request($query) as $data) {
         $tmp_o=new PluginNagiosHost();
         $tmp_o->fields=$data;
         $hosts[$data['id']]=$tmp_o;
      }

      return $hosts;


   }

   static function addRole($object_id,$role_id) {
     global $DB;
     
      $query="SELECT * FROM `glpi_plugin_nagios_objects` where id in (select plugin_nagios_objects_id as id from `glpi_plugin_nagios_objectlinks`
                 WHERE plugin_nagios_objects_id='$object_id' AND items_id='$role_id' and itemtype='PluginNagiosRole' ".
                 "UNION ALL select plugin_nagios_objects_id as id from glpi_plugin_nagios_links where plugin_nagios_objects_id='$object_id' and itemtype='PluginNagiosRole' and items_id='$role_id' ) ";
    
     
      $res=$DB->query($query);
     
      if ($DB->numrows($res)>0 ) {
   
        return false;
        
      }  
        
      
      $pv=new PluginNagiosObjectLink;
      $data['plugin_nagios_objects_id']=$object_id;
      $data['items_id']=$role_id;
      $data['itemtype']='PluginNagiosRole';
      $pv->add($data);
         
      return true;
   }
   
   static function removeRole($object_id,$role_id=0) {
     global $DB;

      if ($role_id)
     	 $query="select * from glpi_plugin_nagios_objectlinks where plugin_nagios_objects_id='$object_id'  and items_id='$role_id' and itemtype='PluginNagiosRole'";
      else
	 $query="select * from glpi_plugin_nagios_objectlinks where plugin_nagios_objects_id='$object_id'  and itemtype='PluginNagiosRole'";
      
      
      foreach ($DB->request($query) as $data) {
         $tmp_o=new PluginNagiosObjectLink();
         $tmp_o->delete(array('id'=>$data['id']),true);
         
      }
               
      return true;
   }
  

  
 

   /**  Show groups of a user
    *
    * @param $user   User object
   **/
   static function showServicesForItem(PluginNagiosObject $no) {
      global $CFG_GLPI;

      $rand    = mt_rand();

      $ID=$no->getID();

           $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>0);

           echo "<script type='text/javascript'>";
           echo "var nagios = $(document).nagios(".json_encode($js_options).");";
           echo "</script>";


      $service = new PluginNagiosService();
      if (Session::haveRightsOr(self::$rightname,array(CREATE,UPDATE)))
	      $service->showFormForItem($no);
      echo "<br>";
      $services  = self::getServicesForObject($ID);

      $parents=$no->getParents();

      echo "<div class='spaced'>";
      echo "<form method='post' id='massiveaction_form$rand' action='".Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($no->fields['type']),1)."' >";
      echo "<input type='hidden' name='plugin_nagios_objects_id' value='$ID'>";
      echo Html::hidden("entities_id",array("value"=>$no->fields['entities_id']));
      echo "<table class='tab_cadrehov' >";
      echo " <tr class='tab_bg_1'  style='text-align:left' >
          <th  colspan='2' style='width:100px'></th>
          <th style='text-align:left;width:200px'>Services</th>
          <th style='text-align:left;width:200px' >From TPL-Host</th>
    <th style='text-align:left;width:70%'>From TPL-Supervision</th>
          <th style='text-align:left;width:200px'>Disabled Service</th>
          </tr>";

      $used_service=array();
      foreach($services as $id=>$service) {
        if ($service['parent_objects_id']) 
		$used_service[$service['parent_objects_id']]=1;
        
        $srand=mt_rand();
        $url=Toolbox::getItemTypeFormURL("PluginNagiosService",1)."?id={$service['id']}&from_host=".$no->getID();
        echo "<tr>";
  echo "<td style='width:80px'><input type='checkbox' name='item[PluginNagiosObjectLink][$id]' value='{$service['id']}'></td>";

  echo "<td style='width:20px'>";
        if ($service['parent_objects_id'])  {
           $po=new PluginNagiosService();
           $po->getFromDB($service['parent_objects_id']);
           if (!$po->fields['is_model']) { 
             echo " <div class='puce-overload' />";
           } else {
       echo "<div class='puce-local'/>";
     }
        } else {
           echo "<div class='puce-local'/>";
        }


        echo "</td>";
        Ajax::createIframeModalWindow("plugin_nagios_service_modal_$srand",$url,array("reloadonclose"=>false,'width'=>1250) ) ;
        echo "<td><a href='#' onclick=\"$('#plugin_nagios_service_modal_$srand').dialog('open');\">{$service['alias']}</a>";
  echo "</td>";
      
  echo "<td></td><td></td>";

       

  $onchange="nagios.disabled_service(".$service['id'].",this.checked);";

        echo "<td><input type='checkbox' ";
  echo ($service['is_disabled']) ? "checked" : "";

        echo " name='disabled' onchange=\"$onchange\"  value='1'></td>";
  echo "</tr>";
      }


      foreach($parents as $child_id=>$child_item) {
  if ($child_id==$no->getID())
    continue ;
  
  $services  = self::getServicesForObject($child_id);
        foreach($services as $id=>$service) {
          if (isset($used_service[$service['id']]))
    continue;
          if ($service['parent_objects_id']) 
    $used_service[$service['parent_objects_id']]=1;

          $srand=mt_rand();
          echo "<tr><td>";
          $urlo=Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($child_item->fields['type']) ,1)."?id=$child_id&with_tab=1&tabnum=15";
          Ajax::createIframeModalWindow("plugin_nagios_item_modal_obj_$srand", $urlo , array("reloadonclose"=>false,'width'=>1150) ) ;
          $urls=Toolbox::getItemTypeFormURL("PluginNagiosService",1)."?id={$service['id']}&from_host=".$child_id;
          Ajax::createIframeModalWindow("plugin_nagios_item_modal_serv_$srand", $urls , array("reloadonclose"=>false,'width'=>1150) ) ;
          
          echo "<input type='checkbox' name='item[PluginNagiosObjectLink][$id]' value='{$service['id']}'></td>";
          echo "<td style='width:15px'><div class='puce-herited'/></td>";
          echo "<td  style='text-align:left'><a href='#' onclick=\"$('#plugin_nagios_item_modal_serv_$srand').dialog('open');\">{$service['alias']}</a></td>";
          echo "<td><div class='puce-local'/><a href='#' onclick=\"$('#plugin_nagios_item_modal_obj_$srand').dialog('open');\">".$child_item->fields['name']."</a></td><td></td>";
    echo "<td></td>";
    echo "</tr>";
        }
      }
     

      $roles=self::getRolesForObject($ID);
      foreach($roles as $link_id => $child_item) {
        $services  = self::getServicesForObject($child_item['id']);
        foreach($services as $id=>$service) {
          if (isset($used_service[$service['id']]))
                continue;
          $srand=mt_rand();
          echo "<tr><td>";
          $urlo=Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($child_item['type']) ,1)."?id=".$child_item['id']."&with_tab=1&tabnum=15";
          Ajax::createIframeModalWindow("plugin_nagios_item_modal_obj_$srand", $urlo , array("reloadonclose"=>false,'width'=>1150) ) ;
          $urls=Toolbox::getItemTypeFormURL("PluginNagiosService",1)."?id={$service['id']}&from_host=".$child_item['id'];
          Ajax::createIframeModalWindow("plugin_nagios_item_modal_serv_$srand", $urls , array("reloadonclose"=>false,'width'=>1150) ) ;
          
          echo "<input type='checkbox' name='item[PluginNagiosObjectLink][$id]' value='{$service['id']}'></td>";
          echo "<td style='width:15px'><div class='puce-herited'/></td>";
          echo "<td  style='text-align:left'><a href='#' onclick=\"$('#plugin_nagios_item_modal_serv_$srand').dialog('open');\">{$service['alias']}</a></td>";
          echo "<td></td><td><div class='puce-local'/><a href='#' onclick=\"$('#plugin_nagios_item_modal_obj_$srand').dialog('open');\">".$child_item['name']."</a></td>";
    echo "<td></td>";
          echo "</tr>";
        }
      }

      reset($parents);
      foreach($parents as $child_id=>$child_item) {
        if ($child_id==$no->getID())
                continue ;

        $roles=self::getRolesForObject($child_id);

        foreach($roles as $link_id => $role_item) {
  
          $services  = self::getServicesForObject($role_item['id']);
          foreach($services as $id=>$service) {
            if (isset($used_service[$service['id']]))
                  continue;
            $srand=mt_rand();
            echo "<tr><td>";
            $urlo2=Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($role_item['type']) ,1)."?id=".$role_item['id']."&with_tab=1&tabnum=15";
            Ajax::createIframeModalWindow("plugin_nagios_item_modal_obj2_$srand", $urlo2 , array("reloadonclose"=>false,'width'=>1150) ) ;
            
            $urlo=Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($child_item->fields['type']) ,1)."?id=".$child_item->fields['id']."&with_tab=1&tabnum=15";
            Ajax::createIframeModalWindow("plugin_nagios_item_modal_obj_$srand", $urlo , array("reloadonclose"=>false,'width'=>1150) ) ;
            
            $urls=Toolbox::getItemTypeFormURL("PluginNagiosService",1)."?id={$service['id']}&from_host=".$role_item['id'];
            Ajax::createIframeModalWindow("plugin_nagios_item_modal_serv_$srand", $urls , array("reloadonclose"=>false,'width'=>1150) ) ;
            echo "<input type='checkbox' name='item[PluginNagiosObjectLink][$id]' value='{$service['id']}'></td>";
            echo "<td style='width:15px'><div class='puce-herited'/></td>";
            echo "<td  style='text-align:left'><a href='#' onclick=\"$('#plugin_nagios_item_modal_serv_$srand').dialog('open');\">{$service['alias']}</a></td>";
            echo "<td><div class='puce-local'/><a href='#' onclick=\"$('#plugin_nagios_item_modal_obj2_$srand').dialog('open');\">".$child_item->fields['name']."</a></td>";
            echo "<td><div class='puce-herited'/><a href='#' onclick=\"$('#plugin_nagios_item_modal_obj2_$srand').dialog('open');\">".$role_item['name']."</a></td>";
      echo "<td></td>";
      echo "</tr>";
          }
       }
      }

      echo "<tr><td colspan=6 style='text-align:right'><div class='puce-local'/>Local&nbsp;&nbsp;<div class='puce-overload'/>Overload&nbsp;&nbsp<div class='puce-herited'/>Herited</td></tr>";
      echo "</table>";
      $massiveactionparams['ontop'] = false;
      Html::openArrowMassives("massiveaction_form$rand", true);
      if (Session::haveRight("plugin_nagios",UPDATE))
        Html::closeArrowMassives(array('computer_import_service'=>'Surcharger','delete_item_services' => __('Delete'),'clone_item' => __('Cloner')));
      else
              Html::closeArrowMassives();
      Html::closeForm();
      echo "</div>";
     
   }




   static function showRolesForItem(PluginNagiosObject $no) {
      global $CFG_GLPI;

      $rand    = mt_rand();

      $ID=$no->getID();


      $role = new PluginNagiosRole();
      if (Session::haveRightsOr(self::$rightname,array(CREATE,UPDATE)))
              $role->showFormForItem($no);
      $roles  = self::getRolesForObject($ID);

      $parents=$no->getParents();

      echo "<div class='text-align:left'></div>";
      echo "<form method='post' id='massiveaction_form$rand' action='".Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($no->fields['type']),1)."' >";
      echo "<input type='hidden' name='plugin_nagios_objects_id' value='$ID'>";
      echo Html::hidden("entities_id",array("value"=>$no->fields['entities_id']));
      echo "<table class='tab_cadrehov'  >";
      echo "<tr><th colspan=3 style='text-align:left'>".__("Supervision Template")."</th><th style='text-align:left;width:20%'>From Host</th></tr>";
      $used_roles=array();
      foreach($roles as $id=>$role) {
        if ($role['parent_objects_id'])
                $used_roles[$role['parent_objects_id']]=1;

        $srand=mt_rand();
        $url=Toolbox::getItemTypeFormURL("PluginNagiosRole",1)."?id={$role['id']}&with_tab=1&from_host=".$no->getID();
        echo "<tr>";
        echo "<td width='100px' ><input type='checkbox' name='item[PluginNagiosObjectLink][$id]' value='{$role['id']}'></td>";
        echo "<td style='width:15px'><div class='puce-local'/></td>";
        Ajax::createIframeModalWindow("plugin_nagios_role_modal_$srand",$url,array("reloadonclose"=>true,'width'=>1250) ) ;
        echo "<td style='text-align:left'><a href='#' onclick=\"$('#plugin_nagios_role_modal_$srand').dialog('open');\">{$role['name']}</a></td>";
        echo "<td></td>";
        echo "</tr>";
      }


      foreach($parents as $child_id=>$child_item) {
        if ($child_id==$no->getID())
                continue ;

        $roles  = self::getRolesForObject($child_id);
        foreach($roles as $id=>$role) {
          if (isset($used_roles[$role['id']]))
                continue;
          $srand=mt_rand();
          echo "<tr><td>";
          $url=Toolbox::getItemTypeFormURL(PluginNagiosObject::getClassForType($child_item->fields['type']),1)."?id=$child_id&with_tab=1&tabnum=15";
          Ajax::createIframeModalWindow("plugin_nagios_item_modal_$srand", $url , array("reloadonclose"=>true,'width'=>1150) ) ;
          //echo "<input type='checkbox' name='item[PluginNagiosObjectLink][$id]' value='{$role['id']}'>";
          echo "</td>";
          echo "<td style='width:15px'><div class='puce-herited' /></td>";
          echo "<td  style='text-align:left'><a href='#' onclick=\"$('#plugin_nagios_item_modal_$srand').dialog('open');\">{$role['name']}</a></td>";
          echo "<td>".$child_item->fields['name']."</td>";
          echo "</tr>";
        }
      }
      echo "<tr><td colspan=4 style='text-align:right'><div class='puce-local'/>Local &nbsp;&nbsp<div class='puce-herited'/>Herited</td></tr>";
      echo "</table>";
      $massiveactionparams['ontop'] = false;
      Html::openArrowMassives("massiveaction_form$rand" );
      if (Session::haveRight("plugin_nagios",UPDATE))
              Html::closeArrowMassives(array('delete_item_roles' => __('Delete')));
      else
              Html::closeArrowMassives();
      Html::closeForm();
   }





}
?>
