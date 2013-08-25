<?php
session_start();
$_SESSION['REQUEST_TIME'] = microtime();
require_once '../libs/Smarty.class.php';

// Create the controller, this is reusable
$smarty = new Smarty();
$smarty->cache_lifetime = 0;
$smarty->caching = 0;
$smarty->force_compile = true;
$smarty->allow_php_tag = true;

// Create a data set, if you don't like this you can directly input an
// associative array in $dwoo->output()

// Fill it with some data
$smarty->assign('int', 1);
$smarty->assign('string', 'bonjour le monde');
$smarty->assign('date', date('Y-m-d'));

$smarty->assign('tableau', array('un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix'));
$t2d = array(
    array('1un', '1deux', '1trois', '1quatre', '1cinq', '1six', '1sept', '1huit', '1neuf', '1dix'),
    array('2un', '2deux', '2trois', '2quatre', '2cinq', '2six', '2sept', '2huit', '2neuf', '2dix'),
    array('3un', '3deux', '3trois', '3quatre', '3cinq', '3six', '3sept', '3huit', '3neuf', '3dix'),
    array('4un', '4deux', '4trois', '4quatre', '4cinq', '4six', '4sept', '4huit', '4neuf', '4dix'),
    array('5un', '5deux', '5trois', '5quatre', '5cinq', '5six', '5sept', '5huit', '5neuf', '5dix'),
    array('6un', '6deux', '6trois', '6quatre', '6cinq', '6six', '6sept', '6huit', '6neuf', '6dix'),
    array('7un', '7deux', '7trois', '7quatre', '7cinq', '7six', '7sept', '7huit', '7neuf', '7dix'),
    array('8un', '8deux', '8trois', '8quatre', '8cinq', '8six', '8sept', '8huit', '8neuf', '8dix'),
    array('9un', '9deux', '9trois', '9quatre', '9cinq', '9six', '9sept', '9huit', '9neuf', '9dix'),
    array('10un', '10deux', '10trois', '10quatre', '10cinq', '10six', '10sept', '10huit', '10neuf', '10dix')
);
$smarty->assignByRef('tableau2d', $t2d);

$objet = new stdClass();
$objet->nom = 'nom';
$objet->prenom = 'prenom';

$objet->telephone = '030303030303';

$adresse = new stdClass();
$adresse->adresse = 'adresse';
$adresse->codepostal = '54000';
$adresse->ville = 'nancy';
$objet->adresse = $adresse;

$smarty->assign('objet', $objet);

// Outputs the result ...
$smarty->display('index.tpl');
