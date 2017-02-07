<?php
require_once 'header.php';

$mrF = new \Src\DbBase($argv);
$mrF->execute();

echo "\n\n FINALIZADO, OCORRERAM {$mrF->errorC} ERROS"; 

echo "\n\n";
