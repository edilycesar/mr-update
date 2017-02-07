<?php
namespace Src;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Description of Database
 *
 * @author edily
 */
class Database {
    
    public $driver = 'mysql';
    public $host = 'localhost';
    public $database = 'database';
    public $username = '';
    public $password = '';
    public $charset = 'utf8';
    public $collation = 'utf8_unicode_ci';
    public $prefix =  '';
    public $dbAlias = "db1";
    public $lastInsertId = 0;
    private $capsule;    

    public function __construct($config) 
    {
        if ( !empty($this->username) ){
            return;
        }
        
        $this->configure($config);
        
        $this->capsule = new Capsule;
        
        $this->capsule->addConnection(array(
            'driver'    => $this->driver,
            'host'      => $this->host,
            'database'  => $this->database,
            'username'  => $this->username,
            'password'  => $this->password,
            'charset'   => $this->charset,
            'collation' => $this->collation,
            'prefix'    => $this->prefix
        ));
        
        // Make this Capsule instance available globally via static methods... (optional)
        $this->capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $this->capsule->bootEloquent();
    }
    
    private function configure($config) 
    {
        $this->driver = $config['dbDriver'];
        $this->host = $config['dbHost'];
        $this->database = $config['dbName'];
        $this->username = $config['dbUser'];
        $this->password = $config['dbPass'];
        $this->charset = $config['dbCharset'];
        $this->collation = $config['dbCollation'];
        $this->prefix = $config['dbPrefix'];
    }
    
    public function select($query)
    {
        return $this->capsule->connection()->select($query);
    }
    
    public function selectOne($query)
    {
        return $this->capsule->connection()->selectOne($query);
    }
    
    public function insert($query)
    {
        if ($this->capsule->connection()->insert($query) === true) {
            return $this->capsule->connection()->getPdo()->lastInsertId();
        }         
        return false;
    }
    
    public function update($query)
    {
        return $this->capsule->connection()->update($query);
    }
    
    public function delete($query)
    {
        return $this->capsule->connection()->delete($query);
    }
    
    public function run($query)
    {
        return $this->capsule->connection()->statement($query);
    }
}
