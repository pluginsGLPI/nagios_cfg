<?php

include ('../../../inc/includes.php');

Session::checkRight("plugin_nagios", READ);

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$hg = new PluginNagiosHostGroup();

$hg->processForm($_POST);


if (isset($_POST['update']) or isset($_POST['save_opts']) ) {

  Html::back();
  
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {

  $hg->redirectToList();
  
} else if (isset($_POST['add'])) {

  Html::redirect($_SERVER['HTTP_REFERER']."?id=".$hg->getID());
  
}

if (isset($_GET['_in_modal'])) {
   Html::popHeader($hg->getTypeName(1),$_SERVER['PHP_SELF']);
   $hg->showForm($_GET["id"]);
   Html::popFooter();
} else if (isset($_POST['purge']) or isset($_POST['delete']) ) {
  $hg->redirectToList();

} else {

 Html::header($hg::getTypeName(), $_SERVER['PHP_SELF'], "plugins", "PluginNagiosHostgroup" );

 $hg->display(array('id' =>$_GET["id"]));

 Html::footer();
}


?>
