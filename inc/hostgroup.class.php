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


class PluginNagiosHostGroup extends PluginNagiosObject {

  static $nagios_type = PluginNagiosObject::NAGIOS_HOSTGROUP_TYPE;

    /* define general fields */
  static $FIELDS_STD_VIEW=array(
                            array( "libel"=>'General options' ,
                                    "fields"=>array('use','hostgroups_members','members','notes') ),
                               );

  static $FIELDS_NOTIF_VIEW=array(
                            array( "libel"=>'Notification options' ,
                                    "fields"=>array() ),
                               );

  static function getMenuContent() {

        $menu['title']="Nagios - Groupes d'hôtes";
        $menu['page']="/plugins/nagios/front/hostgroup.php";
        $menu['links']['search']="/plugins/nagios/front/hostgroup.php";
        $menu['links']['add']="/plugins/nagios/front/hostgroup.form.php";

        return $menu;
  }



  static function getTypeName($nb=0) {
    return _n("Hostgroup","Hostgroups",$nb, "nagios"); 
  }
 
  function showNagiosDef($options=array()) {
                        
	 if (isset($options['html']) && $options['html']==1) {

                $start_tag="<font style='font-weight:bold;color:#888888'>";
                $end_tag="</font>";
                $buf="<font style='font-weight:bold;color:orange'>define hostgroup {</font>\n";
        } else {
                $start_tag=$end_tag="";
		$buf="define hostgroup {\n";
		$options['html']=0;
        }

        if ($this->fields['is_model']) {
           $buf.=" {$start_tag}hostgroup_name{$end_tag} {$this->fields['name']}\n";
          // $buf.=" register 0\n";
        } else {
           $buf.=" {$start_tag}hostgroup_name{$end_tag} {$this->fields['name']}\n";
        }
        if ( $this->fields['alias'] )  $buf.=" {$start_tag}alias{$end_tag} {$this->fields['alias']}\n";
        if ( $this->fields['desc'] )   $buf.=" {$start_tag}notes{$end_tag} {$this->fields['desc']}\n";

        $buf.=PluginNagiosObjectValue::showNagiosdef($this,$options);
        $buf.=PluginNagiosMacro::showNagiosdef($this,$options);

        $buf.="}";


        return $buf;
  }


}

