<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ChiaAnimeMovie;

require '../../lib/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

class ChiaAnimeMovie {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->app = new \Slim\Slim(array(
            'debug' => true,
            'mode' => 'development',
        ));
    }

    public function enable() {
        // connect to the DB
        $this->db = $this->dbConnect();

        // setup the routes
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/getAll', array($this, 'getAll'));
        $this->app->get('/getItem/:id', array($this, 'getItem'));
        $this->app->post('/postItem', array($this, 'postItem'));
        $this->app->put('/putItem/:id', array($this, 'putItem'));
        $this->app->delete('/deleteItem/:id', array($this, 'deleteItem'));
        
        $this->app->response->headers->set('Content-Type', 'application/json');
        
// start Slim
        $this->app->run();
    }
    
    function setRoutes() {
        
    }

    function setFormat($format) {
        
        
    }
    
    function dbConnect() {
        try {
            $conn = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
        return $conn;
    }

    public function index() {
        try {
            echo json_encode(array("Say" => "Hello"));
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }
    
    public function getAll() {
        try {
            $sql = "select * from Film order by name LIMIT 0,10";
            $s = $this->db->query($sql);
            $commodores = $s->fetchAll(\PDO::FETCH_OBJ);
//            $mediaType = $this->app->request()->getMediaType();

            echo json_encode($commodores);
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function getItem($id) {
        try {
            $sql = "select * from Film where id = :id";
            $s = $this->db->prepare($sql);
            $s->bindParam("id", $id);
            $s->execute();
            $commodore = $s->fetch(\PDO::FETCH_OBJ);
            echo json_encode($commodore);
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function postItem() {
        $request = $this->app->request();
        $name = $request->post('name');
        $url = $request->post('url');
        try {
            $sql = "INSERT INTO commodores (name, url) VALUES (:name, :url)";
            $s = $this->db->prepare($sql);
            $s->bindParam("name", $name);
            $s->bindParam("url", $url);
            $s->execute();
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function putItem($id) {
        $request = $this->app->request();
        $name = $request->put('name');
        $url = $request->put('url');

        try {
            $sql = "update commodores set url=:url, name=:name where id=:id";
            $s = $this->db->prepare($sql);
            $s->bindParam("id", $id);
            $s->bindParam("name", $name);
            $s->bindParam("url", $url);
            $s->execute();
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function deleteItem($id) {
        try {
            $sql = "delete from commodores where id=:id";
            $s = $this->db->prepare($sql);
            $s->bindParam("id", $id);
            $s->execute();
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

}

?>
