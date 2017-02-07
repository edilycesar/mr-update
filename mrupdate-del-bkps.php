<?php
require_once 'header.php';

$mrF = new \Src\FtpBase($argv);
$mrF->delBkps();

if ($mrF->errorC > 0 ) {
    echo "\n\n {$mrF->errorMsg}"; 
}

echo "\n\n FINALIZADO, OCORRERAM {$mrF->errorC} ERROS"; 

echo "\n\n";
