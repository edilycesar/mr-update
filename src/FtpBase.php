<?php
namespace Src;

/**
 * Description of Arg
 *
 * @author edily
 */
class FtpBase {
    
    public $uConfig;
    public $transErrors = array();
    public $errorMsg = "";
    public $errorC = 0;
    
    protected $ftp;
    protected $bkpFilePrefix = "_mrupdt-bkp_";
    protected $garbagePrefix = "_mrupd-garbage_";
    protected $delIgnore; 
    protected $arguments = array();
    protected $time;

    public function __construct($argV) 
    {
        $this->time = time();
        $this->makeConfigs($argV);        
    }
    
    protected function makeConfigs($argV) 
    {
        foreach ($argV as $key => $value) {
            
            $this->arguments[$value] = isset($argV[$key+1]) ? $argV[$key+1] : '';
            
            if ($value == "-c") {
                $this->makeConfig($argV[$key+1]);
            } elseif ($value == "-i") {
                $this->delIgnore = $argV[$key+1];
            }
        }
    }
    
    protected function makeConfig($filename) 
    {
        if (!file_exists($filename)) {
            $this->msg = "Cadê o arquivo de configuração???";
            throw new \Exception($this->msg);            
        }
        
        try {
            $config = file_get_contents($filename);
            $this->uConfig = json_decode($config, true); 
        } catch (Exception $exc) {
            echo "Erro json: " . $exc->getMessage();
        }
    }
    
    public function transmite() 
    {        
        $t = count($this->uConfig['uConfig']);
        $c = 1;
        foreach ($this->uConfig['uConfig'] as $key => $uConfigI) {            
            $this->transmiteItem($uConfigI, $c, $t);
            $c++;
        }     
        
        if (count($this->transErrors) > 0) {
            echo "\n ERROS de transmissão: \n";
            print_r($this->transErrors);
        }
    }
    
    protected function transmiteItem($uConfigI, $i = 0, $t = 0) 
    {
        echo "\n Host: " . $uConfigI['host'] . " Usuário: " . $uConfigI['user']; 
        
        $this->ftp = new \Src\FtpTransmission();
        $this->ftp->setConfig($uConfigI['host'], $uConfigI['user'], $uConfigI['pass'], $uConfigI['port'], 30);
        
        if ($this->ftp->connect() !== true) {
            $this->errorC++;
        } else {
            foreach ($uConfigI['folders'] as $dir) {
                $path = $uConfigI['basePath'] . "/" . $dir;
                echo "\n  Dir: " . $path; 
                $this->ftp->setCurrentDir($path);
                //$this->ftp->listCurrentDir();
                $this->backupDir($path);
                $prefix = " {$i}/{$t} [{$uConfigI['name']}] ";
                $this->uploadDir($path, $prefix);                
            }
        }        
        $this->ftp->disconnect();        
    }
    
    private function getBkpFullName($path = '') 
    {
        return $path . $this->bkpFilePrefix . date('Y-m-d_H-i-s', $this->time);
    }
    
    protected function backupDir($dir) 
    {
        $dir2 = $this->getBkpFullName($dir);
        echo "\n  Renomeando: " . $dir . " => " . $dir2;
        if ($this->ftp->rename($dir, $dir2) === false) {
            $this->errorC++;
        }
    }
    
    protected function uploadDir($dir, $prefix = '') 
    {
        //$dirName = str_replace("/", "", $dir);
        $dirName = $this->stripInitBars($dir);
        $orig = $this->uConfig['srcPaths'][$dirName];
        
        $dest = $dir;
        
        $this->ftp->upload($orig, $dest, $prefix);
        
        echo "\n";
        
        if ( count($this->ftp->errorsPaths) > 0 ) {
            array_push($this->transErrors, $this->ftp->errorsPaths); 
        }
    }
    
    protected function stripInitBars($path) 
    {
        while (strpos($path, "/") !== false && (int)strpos($path, "/") === 0) {
            $path = substr($path, 1);
        }
        return $path;
    }
    
    public function delBkps() 
    {
        $t = count($this->uConfig['uConfig']);
        $c = 1;
        foreach ($this->uConfig['uConfig'] as $key => $uConfigI) {            
            $this->delBkpsItem($uConfigI, $c, $t);
            $c++;
        }     
    }
    
    protected function delBkpsItem($uConfigI, $i = 0, $t = 0) 
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
                echo "\n  Remove: " . $pathBase; 
                $this->ftp->setCurrentDir($pathBase);
                $this->ftp->removeFind($path, $this->bkpFilePrefix, $this->delIgnore);
                if (isset($this->arguments['-g'])) {
                    echo "\n\n Removendo lixo \n\n"; 
                    $this->ftp->removeFind($path, $this->garbagePrefix);
                }
            }
            $this->ftp->disconnect();               
        }        
        
        if ($this->ftp->errorC > 0) {
            $this->errorAdd("ERRO FTP: " . $this->ftp->errorMsg);
        }
    }  
    
    protected function errorAdd($msg) 
    {
        $this->errorC++;
        $this->errorMsg .= "\n" . $msg;
    }
}