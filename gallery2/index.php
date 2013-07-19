<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * changed by Florian Tree <florian_tree@gmx.at>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('gallery2');
OCP\App::setActiveNavigationEntry('gallery_index');

OCP\Util::addScript('gallery2', 'gallery');
OCP\Util::addScript('gallery2', 'slideshow');
OCP\Util::addStyle('gallery2', 'styles');

$tmpl = new OCP\Template('gallery2', 'index', 'user');
$tmpl->printPage();
