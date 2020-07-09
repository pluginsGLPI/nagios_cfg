<?php

include ('../../../inc/includes.php');

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header(PluginNagiosHostGroup::getTypeName(2),$_SERVER['PHP_SELF'],"plugins","PluginNagiosHostGroup","");
} else {
   Html::helpHeader("Hostgroup", $_SERVER['PHP_SELF']);
}
//checkTypeRight('PluginExampleExample',"r");

Search::show('PluginNagiosHostGroup');
Html::footer();
?>
