<?php

/*
 * Tien Giang Developer
 * @Author TienGiang <br>Mail: nguyentiengiang@outlook.com<br>Phone: +84 1282 303 100
 * @Version Class Version <1/Jan/2014>
 * @Since 1.0
 */

include '../lib/Google/Utils.php';        
include '../lib/Google/Http/Request.php';
include '../lib/Google/Auth/Abstract.php';        
include '../lib/Google/Auth/OAuth2.php';
include '../lib/Google/Auth/AssertionCredentials.php';
include '../lib/Google/Client.php';
include '../lib/Google/Service.php';
include '../lib/Google/Service/Drive.php';

$fileId = $_REQUEST['fid'];
/**
 * Print a file's metadata.
 *
 * @param Google_DriveService $service Drive API service instance.
 * @param string $fileId ID of the file to print metadata for.
 */
function printFile($service, $fileId) {
    try {
        $file = $service->files->get($fileId);

        print "Title: " . $file->getTitle();
        print "Description: " . $file->getDescription();
        print "MIME type: " . $file->getMimeType();
    } catch (Exception $e) {
        print "An error occurred: " . $e->getMessage();
    }
}

/**
 * Download a file's content.
 *
 * @param Google_DriveService $service Drive API service instance.
 * @param File $file Drive File instance.
 * @return String The file's content if successful, null otherwise.
 */
function downloadFile($service, $file) {
    $downloadUrl = $file->getDownloadUrl();
    if ($downloadUrl) {
        $request = new Google_HttpRequest($downloadUrl, 'GET', null, null);
        $httpRequest = Google_Client::$io->authenticatedRequest($request);
        if ($httpRequest->getResponseHttpCode() == 200) {
            return $httpRequest->getResponseBody();
        } else {
            // An error occurred.
            return null;
        }
    } else {
        // The file doesn't have any content stored on Drive.
        return null;
    }
}

$service = new Google_Service(); $service->getClient();
$file = new Google_Service_Drive_DriveFile(); $file->id = "0B9aJu8fbPnPScFlLcGtoUXhLQkE";
echo downloadFile($service, $file);

?>