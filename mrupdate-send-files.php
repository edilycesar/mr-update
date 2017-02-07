<?php
require_once 'header.php';

$mrF = new \Src\FtpBase($argv);
$mrF->transmite();

echo "\n\n FINALIZADO, OCORRERAM {$mrF->errorC} ERROS"; 

echo "\n\n";
