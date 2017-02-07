<?php
require_once 'header.php';

$mrF = new \Src\FtpBackBkp($argv);
$mrF->backBkps();

echo "\n\n FINALIZADO, OCORRERAM {$mrF->errorC} ERROS"; 

echo "\n\n";
