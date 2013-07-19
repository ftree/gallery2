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
if ($owner !== OC_User::getUser()) {
	\OC\Files\Filesystem::initMountPoints($owner);
	$parts = explode('/', $img, 3);
	if (count($parts) === 3) {
		list($shareId, , $img) = $parts;
	} else {
		$shareId = $parts[0];
		$img = '';
	}
	if (OCP\Share::getItemSharedWith('gallery2', $shareId)) {
		$ownerView = new \OC\Files\View('/' . $owner . '/files');
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

$image = new \OCA\Gallery\AlbumThumbnail('/' . $img, $owner);
$image->show();
