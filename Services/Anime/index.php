<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require '../../lib/Slim/Slim.php';
include "../../lib/NotORM.php";

\Slim\Slim::registerAutoloader();

$dsn = "mysql:dbname=temp2;host=localhost";
$username = "s2admin";
$password = "mdata!6789";

$pdo = new PDO($dsn, $username, $password);
$db = new NotORM($pdo);

$app = new \Slim\Slim(array(
    "MODE" => "development",
    "TEMPLATES.PATH" => "./templates"
        ));

$app->get("/", function() {
            echo "<h1>Hello Slim World</h1>";
        });
        
$app->get("/Film", function () use ($app, $db) {
            $books = array();
            foreach ($db->Film() as $book) {
                $books[] = array(
                    "id" => $book["id"],
                    "name" => $book["name"],
                    "thumb" => $book["thumb"],
                    "summary" => $book["summary"]
                );
            }
            $app->response()->header("Content-Type", "application/json");
            echo json_encode($books);
        });

$app->get("/book/:id", function ($id) use ($app, $db) {
            $app->response()->header("Content-Type", "application/json");
            $book = $db->books()->where("id", $id);
            if ($data = $book->fetch()) {
                echo json_encode(array(
                    "id" => $data["id"],
                    "title" => $data["title"],
                    "author" => $data["author"],
                    "summary" => $data["summary"]
                ));
            } else {
                echo json_encode(array(
                    "status" => false,
                    "message" => "Book ID $id does not exist"
                ));
            }
        });
$app->post("/book", function () use($app, $db) {
            $app->response()->header("Content-Type", "application/json");
            $book = $app->request()->post();
            $result = $db->books->insert($book);
            echo json_encode(array("id" => $result["id"]));
        });
        
$app->put("/book/:id", function ($id) use ($app, $db) {
            $app->response()->header("Content-Type", "application/json");
            $book = $db->books()->where("id", $id);
            if ($book->fetch()) {
                $post = $app->request()->put();
                $result = $book->update($post);
                echo json_encode(array(
                    "status" => (bool) $result,
                    "message" => "Book updated successfully"
                ));
            } else {
                echo json_encode(array(
                    "status" => false,
                    "message" => "Book id $id does not exist"
                ));
            }
        });
$app->delete("/book/:id", function ($id) use($app, $db) {
            $app->response()->header("Content-Type", "application/json");
            $book = $db->books()->where("id", $id);
            if ($book->fetch()) {
                $result = $book->delete();
                echo json_encode(array(
                    "status" => true,
                    "message" => "Book deleted successfully"
                ));
            } else {
                echo json_encode(array(
                    "status" => false,
                    "message" => "Book id $id does not exist"
                ));
            }
        });
$app->run();
?>
