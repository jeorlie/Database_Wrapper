<?php 
if(!defined('ENGINE')) die('Denied!');


class Database_Wrapper {
    private $connection;

    function __construct($arr)
    {
        
        $con['connection'] = isset($arr['connection'])? $arr['connection']: '';
        $con['server'] = isset($arr['server']) ? $arr['server']: '';
       
        switch($con['connection']) {
            case 'sqlite':
                    if(!file_exists($con['server'])) die('Database file not found.');
                    if(!is_writable($con['server'])) die('Database file not writable.');
                    $this->connection = new PDO('sqlite:'. $con['server']);
                break;
            default:
            die('Invalid database connection type');
                break;
        }
    }

    function query($sql) {
        return $this->connection->query($sql);
    }

    function execute($type, $sql, $params) {
        if(!$run = $this->connection->prepare($sql)) {
            return array('success' => false, 'message' => 'SQL statement prepare failed.');
        } 
        if(!$run->execute($params)) {
            return array('success' => false, 'message' => 'SQL statement execute failed.');
        }
        switch($type){
            case 'select':
                $fetch = $run->fetchAll(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'affected' => count($fetch),
                    'rows' => $fetch
                ];
            break;
            case 'update':
            case 'delete':
                return [
                    'success' => true,
                    'affected' => $run->rowCount()
                ];
            break;
            case 'insert':
                return ['success' => true, 'affected' => $run->rowCount(), 'last_insert_id' => $this->connection->lastInsertId()];
            break;
            default:
                return ['success' => false, 'affected' => 0, 'message' => 'Invalid switch case. Default thrown'];
            
        }
    }

    function insert($table, $arrParams){
        $indexField = array_keys($arrParams);
        $sql = "INSERT INTO ". $table."(". implode(',', $indexField).")VALUES(";
        $paramsField = array_values($arrParams);
        if(count($arrParams) > 1) {
            $sql .= str_repeat('?,', count($arrParams) - 1). '?);';
        } else {
            $sql.= "?);";
        }
        return $this->execute('insert', $sql, $paramsField);        
    }

}