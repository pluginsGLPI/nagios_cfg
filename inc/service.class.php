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


class PluginNagiosService extends PluginNagiosObject {

  static $nagios_type = PluginNagiosObject::NAGIOS_SERVICE_TYPE;
  
  static $rightname="plugin_nagios";
  

  /* define general fields */
  static $FIELDS_STD_VIEW=array(
                            array( "libel"=>'General options' ,
                                    "fields"=>array('use','servicegroups','check_command','notes') ),
                            array( "libel"=>'Check options',
                                    "fields"=> array('check_period',
                                                     'max_check_attempts',
                                                     'check_interval',
						     'retry_interval',
                                                     'active_checks_enabled',
                                                     'passive_checks_enabled'))
                               );


  static $FIELDS_NOTIF_VIEW=array(
                            array( "libel"=>'Notification options' ,
                                    "fields"=>array('contacts','contact_groups','notification_interval','notification_period','notification_options','notifications_enabled') ),
                               );



  static function getTypeName($nb=0) {
    return "Services";
  }

  static function getMenuContent() {

        $menu['title']='Nagios - Modèles de services';
        $menu['page']="/plugins/nagios/front/service.php";
        $menu['links']['search']="/plugins/nagios/front/service.php";
        $menu['links']['add']="/plugins/nagios/front/service.form.php";

        return $menu;
  }


   function getSearchOptions() {
      
      $tab=parent::getSearchOptions();

      $tab[30001] = [
         'id'                 => '30001',
         'table'              => 'glpi_plugin_nagios_servicegroups_view',
         'field'              => 'name',
         'name'               => __('Linked ServiceGroups'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'itemlink_type'      => 'PluginNagiosServiceGroup',
      ];

      $tab[30002] = [
         'id'                 => '30002',
         'table'              => 'glpi_plugin_nagios_services_view',
         'field'              => 'name',
         'name'               => __('Linked Services'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'itemlink_type'      => 'PluginNagiosService',
      ];



        return $tab;   
} 

  function showNagiosDef($options=array()) {

      if(!$this->fields['is_disabled']){
                if (isset($options['html']) && $options['html']==1) {
             $start_tag="<font style='font-weight:bold;color:#888888'>";
             $end_tag="</font>";
             $buf="<font style='font-weight:bold;color:green'>define service {</font>\n";
        } else {
             $start_tag=$end_tag="";
             $buf="define service {\n";
        }


        if ($this->fields['is_model']) {
           $buf.=" {$start_tag}name$end_tag {$this->fields['name']}\n";
           $buf.=" {$start_tag}register$end_tag 0\n";
        } else {
     if (isset($options['force_name']))
          $buf.=" {$start_tag}host_name$end_tag {$options['force_name']}\n";
     else
    $buf.=" {$start_tag}host_name$end_tag {$this->fields['name']}\n";
  }
        if ( $this->fields['alias'] )  $buf.=" {$start_tag}service_description$end_tag ".html_entity_decode($this->fields['alias'])."\n";
        if ( $this->fields['desc'] )   $buf.=" {$start_tag}notes$end_tag ".html_entity_decode($this->fields['desc'])."\n";
        
        $buf.=PluginNagiosObjectValue::showNagiosdef($this,$options);
        $buf.=PluginNagiosMacro::showNagiosdef($this,$options);  

        $buf.="}";


        return $buf;
      }

      return "";

  }



  function showFormForItem($item,$options=array() ) {
     
      $rand    = mt_rand();
      $ID=$item->getID();

      $this->initForm(-1, $options);
      echo "<div id='searchcriterias' style='width:60%' >";
      echo "<form name='hosttemplate_services_form$rand' id='hosttemplate_services_form$rand' method='post'";
      echo " action='".Toolbox::getItemTypeFormURL(self::getClassForType($item->fields['type']))."'>";
      echo "<input type='hidden' name='plugin_nagios_objects_id' value='$ID'>";
      echo "<input type='hidden' name='itemtype' value='PluginNagiosService'>";
      echo "<input type='hidden' name='entities_id' value='{$item->fields['entities_id']}'>";
      echo "<input type='hidden' name='name' value='".$item->fields['name']."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>Service:</th>";
      echo " <td>". Html::input("alias",array('value'=>$this->fields['alias']))."</td>";
      echo "<td>As child of</td>";
      echo " <td>";
      PluginNagiosService::Dropdown(array('name'=>'parent_objects_id','condition'=>" is_model=1"));
      echo "</td>";
      echo "<td class='tab_bg_2 center'>";
      echo "<input type='submit' name='addservicetoitem' value=\"".__('Add')."\"class='submit'>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
      return true;	

  }



}

