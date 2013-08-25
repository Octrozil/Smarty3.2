<?php
require '../../distribution/libs/Smarty.class.php';
$publisher = 'Nilesh Kulkarni';
$website = 'http://www.google.com';
$Ä =4;
$smarty = new Smarty;
$smarty->assignByRef('publisher', $publisher);
$smarty->assign('website', $website);
$smarty->display('indexbug.tpl');
print ' <hr>
<h3> Hello, I am index.php. First, I displayed the index.tpl template. </h3>
Now we will see what happened to the content our variables: <br>
The $publisher variable was sent using <b> assign_by_ref </b> method
and now contains <font color=red> ' . $publisher . '</font><br>
and the $website variable was sent using <b> assign </b> method
and now contains <font color=red> ' . $website;
