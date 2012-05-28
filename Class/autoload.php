<?php
function __autoload($class) {
$classes = explode("_",$class);
foreach ($classes as $include)
{
include "Class/".$include.".class.php";
}
}