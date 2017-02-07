<?php

namespace Src;

/**
 * Description of FtpBackBkp
 *
 * @author edily
 */
class FtpBackBkp extends FtpBase 
{   
    public function __construct($argV) 
    {
        parent::__construct($argV);
        $this->validateArguments();
    }
    
    private function validateArguments() 
    {
        if (!isset($this->arguments['-s'])) {            
            throw new \Exception("Cadê o argumento -s ???");
        }
    }

    public function backBkps() 
    {
        $t = count($this->uConfig['uConfig']);
        $c = 1;
        foreach ($this->uConfig['uConfig'] as $key => $uConfigI) {            
            $this->backBkpsItem($uConfigI, $c, $t);
            $c++;
        }     
    }
    
    private function backBkpsItem($uConfigI, $i = 0, $t = 0) 
    {
        echo "\n Host: " . $uConfigI['host'] . " Usuário: " . $uConfigI['user']; 
        
        $this->ftp = new \Src\FtpTransmission();
        $this->ftp->setConfig($uConfigI['host'], $uConfigI['user'], $uConfigI['pass'], $uConfigI['port'], 30);
        
        if ($this->ftp->connect() !== true) {
            $this->errorC++;
        } else {
            foreach ($uConfigI['folders'] as $dir) {
                $path = $uConfigI['basePath'] . "/" . $dir;
                $pathBase = UrlTools::removeLastFile($path);
                echo "\n  Recupera: " . $path; 
                $this->ftp->setCurrentDir($pathBase);
                
                $search = $this->bkpFilePrefix . $this->arguments['-s'];
                $findPath = $this->ftp->find($pathBase, $search);
                if ($findPath !== false) {    
                    $garbagePath = $path . $this->garbagePrefix . date('Y-m-d_H:i:s');
                    echo "\n  Rename: " . $path . " => " . $garbagePath; 
                    $this->ftp->rename($path, $garbagePath);
                    $this->ftp->rename($findPath, $path);
                } else {
                    echo "\n  Não encontrado: " . $path; 
                }
            }
            $this->ftp->disconnect();               
        }        
        
        if ($this->ftp->errorC > 0) {
            $this->errorAdd("ERRO FTP: " . $this->ftp->errorMsg);
        }
    }  
    
}
