<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * changed by Florian Tree  <florian_tree@gmx.at>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('gallery2');

if (isset($_GET['path']) && $_GET['path'] != '') {
    list($user, $path) = explode('/', $_GET['path'], 2);
} else {
    $path = '';
    $user = \OC_User::getUser();
}

$View = new \OC\Files\View('/' . $user . '/files');

$dirs = array();
$images = array();
// SubDirs only for the own Folders
if ($user == \OC_User::getUser()) {
    $dirs = $View->getDirectoryContent($path,'httpd');
    foreach ($dirs as &$dir) {
        $dir['path'] = $user . substr($dir['path'],5);
    }
}

if ($user == \OC_User::getUser()) {
    $images = $View->getDirectoryContent($path,'image');
    foreach ($images as &$image) {
        $image['origpath'] = $image['path'];
        // remove the "/files" part
        $image['path'] = $user . substr($image['path'],5);
    }
} else {
    list($source, $path) = explode('/', $path, 2);

    $query = \OC_DB::prepare(
        'SELECT `fileid`, `storage`, `path`, `parent`, `name`, `mimetype`, `mimepart`,'
            .' `size`, `mtime`, `encrypted`'
            .' FROM `*PREFIX*filecache` WHERE `fileid` = ?');
    $result = $query->execute(array($source));
    $data = $result->fetchRow();
    $sharedGallery = substr($data['path'],5);

    $images = $View->getDirectoryContent($sharedGallery,'image');
    foreach ($images as &$image) {
        $image['origpath'] = $image['path'];
        // remove the "/files" part
        $image['path'] = $user . '/'. $source . '/'. $data['name'] . '/' . $image['name'];
    }
}

$retSources = array();
$shared = array();
$users = array();
$displayNames = array();

if ($path == '') {
    try {
        $sharedSources = OCP\Share::getItemsSharedWith('gallery',-1);
    } catch (Exception $e) {

    }

    foreach ($sharedSources as $sharedSource) {
        $owner = $sharedSource['uid_owner'];
        if (array_search($owner, $users) === false) {
            $users[] = $owner;
        }
        \OC\Files\Filesystem::initMountPoints($owner);
        $ownerView = new \OC\Files\View('/' . $owner . '/files');
        $path = $ownerView->getPath($sharedSource['item_source']);
        if ($path) {
            $shareName = basename($path);
            $sharedSource['path'] = $owner.'/'.$sharedSource['item_source'].'/'.$shareName;
            if ($path) {
                $shareName = basename($path);
                $shareView = new \OC\Files\View('/' . $owner . '/files' . $path);
                $shareDirs = $shareView->getDirectoryContent($path,'httpd');
            }
            $retSources[$owner][] = $sharedSource;
        }
    }

    foreach ($users as $user) {
        $displayNames[$user] = \OCP\User::getDisplayName($user);
    }
}

OCP\JSON::setContentTypeHeader();
echo json_encode(array('images' => $images, 'albums' => $dirs, 'users' => $users, 'displayNames' => $displayNames, 'sharealbums' => $retSources));
