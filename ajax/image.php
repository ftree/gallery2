<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * changed by Florian Tree <florian_tree@gmx.at>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('gallery2');
session_write_close();

list($owner, $img) = explode('/', $_GET['file'], 2);
$ownerView = new \OC\Files\View('/' . $owner . '/files');
if ($owner !== OC_User::getUser()) {
    \OC\Files\Filesystem::initMountPoints($owner);
    list($shareId, , $img) = explode('/', $img, 3);
    if (OCP\Share::getItemSharedWith('gallery', $shareId)) {
        $sharedGallery = $ownerView->getPath($shareId);
        if ($img) {
            $img = $sharedGallery . '/' . $img;
        } else {
            $img = $sharedGallery;
        }
    } else {
        OC_JSON::error('no such file');
        die();
    }
}

$mime = $ownerView->getMimeType($img);
list($mimePart,) = explode('/', $mime);
if ($mimePart === 'image') {
	$local = $ownerView->getLocalFile($img);
	$rotate = false;
	if (is_callable('exif_read_data')) { //don't use OC_Image here, using OC_Image will always cause parsing the image file
		$exif = @exif_read_data($local, 'IFD0');
		if (isset($exif['Orientation'])) {
			$rotate = ($exif['Orientation'] > 1);
		}
	}

    // Here you can set the ImageSize for viewing, so that the images are not shown in the original size
    $size = 1024;
    if ($size == 0) {
        if ($rotate) {
            $image = new OC_Image($local);
            $image->fixOrientation();
            $image->show();
        } else { //use the original file if we dont need to rotate, saves having to re-encode the image
            header('Content-Type: ' . $mime);
            readfile($local);
        }
    } else {
        $image = new OC_Image($local);
        if ($rotate) {
            $image->fixOrientation();
        }
        $image->resize($size);
        $image->show();
    }

}
