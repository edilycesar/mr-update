<?php
namespace Src;

/**
 * Description of FTP
 *
 * @author edily
 */
class FTPTransmission 
{
    private $host;
    private $user;
    private $pass;
    private $conn;
    private $port;
    private $timeout;
    private $currentDir = ".";
    private $dirRemove = array();
    private $passive = true;
    public $msg = "";
    public $errorMsg = "";
    public $errorC = 0;
    public $errorsPaths = array();

    public function setConfig($host, $user, $pass, $port = 21, $timeout = 30) 
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
        $this->timeout = $timeout;
    }
    
    public function connect() 
    {
        $this->conn = ftp_connect($this->host, 21, 8);
        if(!$this->conn){
            $this->msg = "Não foi possível conectar";
            echo "\n *** ERRO " . $this->msg;
            return false;
        }
        
        if(!ftp_login($this->conn, $this->user, $this->pass)){
            $this->msg = "Erro de login";
            echo "\n *** ERRO " . $this->msg;  
            return false;
        }
        
        ftp_pasv($this->conn, $this->passive);
        
        $this->msg = "Conectado ao servidor FTP com sucesso! ";
        return true;	
    }
    
    public function listCurrentDir() 
    {
        echo "\n Dir: " . $this->currentDir . "\n";
        $buff = ftp_rawlist($this->conn, $this->currentDir); 
        print_r($buff);
    }
    
    public function setCurrentDir($dir) 
    {
        $this->currentDir = $dir; 
        
    }
    
    public function rename($src, $dest) 
    {
        if (!ftp_rename($this->conn, $src, $dest)) {
            $this->msg = "Erro ao renomear arquivo ({$src}, {$dest})";
            echo "\n *** ERRO " . $this->msg;  
            return false;
        }
        return true;
    }
    
    public function create($path) 
    {
        if (!ftp_mkdir($this->conn, $path)) {
            $this->msg = "Erro ao criar arquivo ({$path})";
            echo "\n *** ERRO " . $this->msg;  
            return false;
        }
        return true;
    }
    
    public function upload($orig, $dest, $prefix = '') 
    {
        $orig = UrlTools::stripDoubleBars($orig);
        $dest = UrlTools::stripDoubleBars($dest);
        if (is_dir($orig)) {
            
            if ($this->create($dest) === false) {
                return false;
            }
            
            $d = dir($orig);
            while (($f = $d->read() ) !== false) {
                if ($f !== "." && $f !== "..") {
                    $fileO = $orig . "/" . $f;
                    $fileD = $dest . "/" . $f;                                        
                    $this->upload($fileO, $fileD, $prefix);
                }                
            }            
        } else {        
            echo "\n  " . $prefix . $orig . " => " . $dest;
            
            if (!ftp_put($this->conn, $dest, $orig, FTP_ASCII)) {
                $this->msg = "Erro ao enviar arquivo (Dest: {$dest}, Orig: {$orig})";
                echo "\n *** ERRO " . $this->msg;  
                array_push($this->errorsPaths, array($orig, $dest));
                return false;
            }            
        }
        
        return true;
    }    
    
//    private function getLastName($path) 
//    {
//        //app/media/dados/htdocs/edily/emissor-nfe/app
//        $cols = explode("/", $path);
//        return $cols[count($cols) - 1];
//    }
//    
//    private function stripDoubleBars($path) 
//    {
//        while (strpos($path, "//") !== false) {
//            $path = str_replace("//", "/", $path);
//        }
//        return $path;
//    }
    
    public function disconnect() 
    {
        return ftp_close($this->conn);
    }
    
    public function compare($local, $remote) 
    {
        $sizeRem = ftp_size($this->conn, $remote);
        $sizeLoc = filesize($local);
        
        echo "\n Loc size: " . $sizeLoc;
        echo "\n Rem size: " . $sizeRem;
        
        return $sizeLoc !== $sizeRem ? false : true;
    }
    
    public function removeFind($path, $search = '', $searchIgnore = '') 
    {
        echo "\n Dir: " . $this->currentDir;
        $files = ftp_nlist($this->conn, $this->currentDir);
        
        if ($files === false) {
            $this->errorAdd("Erro ao obter lista de diretórios (removeFind, ftp_nlist)");
        } else {
            foreach ($files as $file) {
                if (!empty($searchIgnore) && strpos($file, $search . $searchIgnore) !== false) {
                    echo "\n Ignorando: " . $file;
                } elseif (strpos($file, $search) !== false) {                
                    $filepath = $this->currentDir . "/" . $file;
                    $filepath = UrlTools::stripDoubleBars($filepath);
                    $this->removeFiles($filepath);
                }
            }
            $this->removeDirs($this->dirRemove);
        }    
    }
    
    public function isDir($path) 
    {
        $path = UrlTools::stripDoubleBars($path);
        $size = ftp_size($this->conn, $path);
        return $size == -1 ? true : false;
    }
    
    public function removeFiles($path) 
    {
        $path = UrlTools::stripDoubleBars($path);        
        echo "\n Acessando: " . $path;
        if ($this->isDir($path) === false) {     
            echo " [REMOVENDO]";
            ftp_delete($this->conn, $path);
        } else { 
            
            array_push($this->dirRemove, $path);
            
            $files = ftp_nlist($this->conn, $path);
            
            if ($files === false) {
                $this->errorAdd("Erro ao listar dir {$path} (ftp_nlist) ");
            } else {
                foreach ($files as $file) {
                    if ($file != "." && $file != "..") {             
                        $filepath = $path . "/" . $file;
                        $filepath = UrlTools::stripDoubleBars($filepath);
                        $this->removeFiles($filepath);
                    }
                }
            }    
        }    
    }  
    
    public function removeDirs(Array $dirs) 
    {        
        foreach ($dirs as $dir) {
            echo "\n Removendo diretorio: " . $dir;
            if ($this->isDir($dir) === true) {
                if (@ !ftp_rmdir($this->conn, $dir)) {
                    $this->errorAdd("Erro ao remover dir {$dir} (ftp_rmdir) ");
                }
            }
        }        
    }   
    
    private function errorAdd($msg) 
    {
        $this->errorC++;
        $this->errorMsg .= "\n" . $msg;
    }
    
    public function find($path, $search = '') 
    {
        echo "\n Dir: " . $this->currentDir . "(Searching: " . $search . ")";
        
        $files = ftp_nlist($this->conn, $this->currentDir);
        
        foreach ($files as $file) {
            if (strpos($file, $search) !== false) {                
                $filepath = $this->currentDir . "/" . $file;
                $filepath = UrlTools::stripDoubleBars($filepath);
                return $filepath;
            }
        }
        
        return false;
    }
}