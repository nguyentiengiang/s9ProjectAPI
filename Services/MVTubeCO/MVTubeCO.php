<?php

\Slim\Slim::registerAutoloader();

/*
 * MVTubeCO 
 */

class MVTubeCO {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->app = new \Slim\Slim(array(
//            'debug' => true,
//            'mode' => 'development',
            'debug' => false,
            'mode' => 'production',
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/json/GetPlaylist', array($this, 'GetPlaylists'));
        $this->app->get('/json/GetYTPlaylist', array($this, 'GetYTPlaylists'));
        $this->app->get('/json/GetSong', array($this, 'GetSongByPlaylist500'));
        $this->app->get('/json/GetSongWP', array($this, 'GetSongByPlaylist500'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
    }

    public function GetPlaylists() {
        $result = null;
        try {
            $db = $this->dbConnect();
            $charts = $db->Playlist(array("isHide = ?" => 0, "typePlaylist = ?" => 1))->select("id, name, image, imageFlat");
            $languages = $db->Playlist(array("isHide = ?" => 0, "typePlaylist = ?" => 2))->select("id, name, image, imageFlat");
            $artists = $db->Playlist(array("isHide = ?" => 0, "typePlaylist = ?" => 3))->select("id, name, image, imageFlat");
            $albums = $db->Playlist(array("isHide = ?" => 0, "typePlaylist = ?" => 4))->select("id, name, image, imageFlat");
            $genres = $db->Playlist(array("isHide = ?" => 0, "typePlaylist = ?" => 5))->select("id, name, image, imageFlat");
            $arrCharts = array();
            $arrLanguages = array();
            $arrArtists = array();
            $arrAlbums = array();
            $arrGenres = array();
            foreach ($charts as $chart) {
                $data = iterator_to_array($chart);
                array_push($arrCharts, $data);
            }            
            foreach ($languages as $language) {
                $data = iterator_to_array($language);
                array_push($arrLanguages, $data);
            }
            foreach ($artists as $artist) {
                $data = iterator_to_array($artist);
                array_push($arrArtists, $data);
            }
            foreach ($albums as $album) {
                $data = iterator_to_array($album);
                array_push($arrAlbums, $data);
            }
            foreach ($genres as $genre) {
                $data = iterator_to_array($genre);
                array_push($arrGenres, $data);
            }
            $result = array("chart" => $arrCharts) + array("language" => $arrLanguages) + 
                    array("artist" => $arrArtists) + array("album" => $arrAlbums) + 
                    array("genre" => $arrGenres);
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public function GetYTPlaylists() {
        $result = null;
        try {
            $db = $this->dbConnect();
            $popularPlaylists = $db->PlaylistYT("typePlaylist = ?", 1)->select("name, imgFlat as imageFlat");
            $artists = $db->PlaylistYT("typePlaylist = ?", 2)->select("name, imgFlat as imageFlat");
            $playlists = $db->PlaylistYT("typePlaylist = ?", 3)->select("name, imgFlat as imageFlat");
            $arrPopularPlaylists = array();
            $arrArtists = array();
            $arrPlaylists = array();
            foreach ($popularPlaylists as $popularPlaylist) {
                $data = iterator_to_array($popularPlaylist);
                array_push($arrPopularPlaylists, $data);
            }
            foreach ($artists as $artist) {
                $data = iterator_to_array($artist);
                array_push($arrArtists, $data);
            }
            foreach ($playlists as $playlist) {
                $data = iterator_to_array($playlist);
                array_push($arrPlaylists, $data);
            }
            $result = array("popularPlaylist" => $arrPopularPlaylists) + array("artist" => $arrArtists) + 
                    array("playlist" => $arrPlaylists);
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public function GetSongByPlaylist() {
        $result = null;
        try {
            $pId = $this->app->request()->get('id');
            $db = $this->dbConnect();
            $songs = $db->Song(array("isHide = ?" => 0, "playlistId = ?" => $pId))->select("name, singer, image, youtubeId, uploader as author, duration, viewCount, rating");
            $arrSongs = array();
            if (count($songs)) {
                foreach ($songs as $song) {
                    $data = iterator_to_array($song);
                    array_push($arrSongs, $data);
                }
                $result = array("song" => $arrSongs);
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Get Songs Fail!');
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public function GetSongByPlaylist500() {
        $result = null;
        try {
            $pId = $this->app->request()->get('id');
            $db = $this->dbConnect();
            $songs = $db->Song(array("isHide = ?" => 0, "playlistId = ?" => $pId))->select("name, singer, image, youtubeId, uploader as author, duration, viewCount, rating")->limit(500, 0);
            $arrSongs = array();
            if (count($songs)) {
                foreach ($songs as $song) {
                    $data = iterator_to_array($song);
                    array_push($arrSongs, $data);
                }
                $result = array("song" => $arrSongs);
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Get Songs Fail!');
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    
}
?>
