<?php
/**
 * Copyright (c) 2013 Lukas Reschke <lukas@statuscode.ch>
 * changed by Florian Tree <florian_tree@gmx.at>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$this->create('ready_js', 'js/ready.js')
	->actionInclude('gallery2/js/ready.php');