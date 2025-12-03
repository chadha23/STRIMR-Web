<?php 


class Config{
    private static $pdo=null;

    public static function getConnexion(){

        if(!isset(self::$pdo)){
                $servername="localhost";
                $dbname="STRIMR";
                $username="root";
                $password="";
                try{
                self::$pdo=new PDO("mysql:host=$servername;dbname=$dbname",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
                ]);
            }
           
         catch(Exception $e ){
            die("Database Connection Error: " . $e->getMessage());
        }
        }
        
return self::$pdo;
       
    }
    
}

?>
