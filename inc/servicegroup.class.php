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


class PluginNagiosServiceGroup extends PluginNagiosObject {

  static $nagios_type = PluginNagiosObject::NAGIOS_SERVICEGROUP_TYPE;

    /* define general fields */
  static $FIELDS_STD_VIEW=array(
                            array( "libel"=>'General options' ,
                                    "fields"=>array('use','members','notes') ),
                               );

  static $FIELDS_NOTIF_VIEW=array(
                            array( "libel"=>'Notification options' ,
                                    "fields"=>array() ),
                               );



  static function getTypeName($nb=0) {
    return "ServiceGroup";
  }


  static function getMenuContent() {

        $menu['title']="Nagios - Groupes de services";
        $menu['page']="/plugins/nagios/front/servicegroup.php";
        $menu['links']['search']="/plugins/nagios/front/servicegroup.php";
        $menu['links']['add']="/plugins/nagios/front/servicegroup.form.php";

        return $menu;
  }


  function showNagiosDef($options=array()) {

        if (isset($options['html']) && $options['html']==1) {

	  $start_tag="<font style='font-weight:bold;color:#888888'>";
                $end_tag="</font>";
                $buf="<font style='font-weight:bold;color:orange'>define servicegroup {</font>\n";
        } else {
                $start_tag=$end_tag="";
                $buf="define servicegroup {\n";
        }
        
        if ($this->fields['is_model']) {
           $buf.=" {$start_tag}servicegroup_name{$end_tag} {$this->fields['name']}\n";
           $buf.=" {$start_tag}register{$end_tag} 0\n";
        } else {
           $buf.=" {$start_tag}servicegroup_name{$end_tag} ".$this->fields['name']."\n";
        }
        if ( $this->fields['alias'] )  $buf.=" {$start_tag}alias{$end_tag} {$this->fields['alias']}\n";
        if ( $this->fields['desc'] )   $buf.=" {$start_tag}notes{$end_tag} {$this->fields['desc']}\n";

        $buf.=PluginNagiosObjectValue::showNagiosdef($this,$options);
        $buf.=PluginNagiosMacro::showNagiosdef($this,$options);

        $buf.="}";


        return $buf;
  }


}

