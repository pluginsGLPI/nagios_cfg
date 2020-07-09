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

class PluginNagiosCommand extends commonDropdown {

  static $rightname='plugin_nagios'; 

  static function canCreate() {
    return Session::haveRightsOr(self::$rightname, array(CREATE));
  }

  static function canUpdate() {
    return Session::haveRightsOr(self::$rightname, array(CREATE,UPDATE,PURGE));
  }


  static function getMenuContent() {
        return array("title"=>"Nagios - Commandes",'page'=>"/plugins/nagios/front/command.php",
            'links'=>array('search'=> "/plugins/nagios/front/command.php",
                           'add'   => "/plugins/nagios/front/command.form.php"  ) );
  }



  static function getTypeName($nb=0) {
    return _n("Command","Commands",$nb, "nagios");
  }

  function showForm($ID,$options=array()) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo " <td>" . _("Name") ."*</td>";
      echo " <td>";
      echo Html::hidden("id"  ,array('value'=>$this->fields['id']));

      echo Html::input("name",array('value'=>$this->fields['name'],"size"=>"50"));
      echo " </td>";

      echo " <td>"._("Entity")."*</td>";
      echo " <td>";
      Entity::Dropdown(array('value'=>$this->fields['entities_id']));
      echo " </td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>"._("Command")."</td>";
      echo "<td colspan='3'>";
      echo "<textarea cols='100' rows='10' name='line' id='line'>".$this->fields["line"]."</textarea>";
      echo " </td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td>"._("Description")."</td>";
      echo " <td>";
      echo Html::input("desc",array('value'=>$this->fields['desc'],"size"=>"100"));
      echo " </td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td>"._("Global ?")."</td>";
      echo " <td>";
      Dropdown::showYesNo('is_global',$this->fields['is_global'],-1, array());
      echo "</td>";
      echo "</tr>";


      if ($this->canUpdate())  
	      $this->showFormButtons($options);
      echo "</table>";
      Html::closeForm();

      return true;


  }

  function getNbArgs() {
	if (preg_match_all('/\$ARG\d+\$/i',$this->fields['line'],$matches)) {
		foreach($matches[0] as $args)
			$result[$args]=1;
		return count($result);
	}

  }

  function showNagiosDef() {
	$buf="define command {\n";
	$buf.=" command_name ".$this->fields['name']."\n";
        $buf.=" command_line ".html_entity_decode($this->fields['line'])."\n";
	#if ( $this->fields['desc']) $buf.=" notes ".$this->fields['desc']."\n";
	$buf.="}\n";

	return $buf;

  }

     function getSearchOptions() {
      $tab = array();
      $tab['common'] = static::getTypeName();
      $tab[1]['table']     = $this->getTable();
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = _('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type'] = 'PluginNagiosCommand';


      $tab[3]['table']     = $this->getTable();
      $tab[3]['field']     = 'line';
      $tab[3]['name']      = _("Command Line");

      $tab[4]['table']     = $this->getTable();
      $tab[4]['field']     = 'desc';
      $tab[4]['name']      = _("Description");

      $tab[5]['table']     = $this->getTable();
      $tab[5]['field']     = 'is_global';
      $tab[5]['name']      = _("Global");
      $tab[5]['datatype']     = 'bool';


      return $tab;

  }




}

