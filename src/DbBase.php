<?php
namespace Src;

/**
 * Description of Arg
 *
 * @author edily
 */
class DbBase {
    
    public $uConfig;
    public $errorC = 0;    
    public $msg;

    private $queryFile;
    private $db;
    
    public function __construct($argV) 
    {
        $this->makeConfigs($argV);        
    }
    
    private function makeConfigs($argV) 
    {
        foreach ($argV as $key => $value) {
            //echo "\n" . $key . $value;
            if ($value == "-c") {
                $this->makeConfig($argV[$key+1]);
            } else if ($value == "-q") {
                $this->setQueryFile($argV[$key+1]);
            }
        }
    }
    
    private function makeConfig($filename) 
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
    
    public function execute() 
    {
        foreach ($this->uConfig['uConfig'] as $uConfigI) {            
            $this->executeItem($uConfigI);
        }     
    }
    
    private function executeItem($uConfigI) 
    {
        echo "\n DB Host: " . $uConfigI['dbHost'] . " DB Usuário: " . $uConfigI['dbUser'];  
        $db = new \Src\Database($uConfigI);
        
        $n = 0;
        while ($query = $this->getSql($n)) {            
            echo "\n  Executando: " . $query;
            $db->run($query);
            $n++;
        }        
    }    
    
    private function setQueryFile($filename) 
    {
        if (!file_exists($filename)) {
            $this->msg = "Cadê o arquivo sql???";
            throw new \Exception($this->msg);
        } else {
            $this->queryFile = $filename;
        }
    }
    
    private function getSql($n = 0) 
    {
        $content = file_get_contents($this->queryFile);
        $rows = explode("\n", $content);
        $c = 0;
        foreach ($rows as $row) {
            $row = trim($row);
            if (!empty($row) && substr($row, 0, 2) != "--") {
                if ($c === $n) {
                    return $row;
                }
                $c++;                
            }
        }
        return false;
    }
}