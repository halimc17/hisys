<?php
/*
 * PHP QR Code encoder
 *
 * Root library file, prepares environment and includes dependencies
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Qrlib {
	public function __construct(){
		$this->initialize();
	}
    function initialize(){
		// $QR_BASEDIR = dirname(__FILE__).DIRECTORY_SEPARATOR;
		// Required libs
		if (file_exists(APPPATH."functions/phpqrcode/qrconst.php")){
			include APPPATH."functions/phpqrcode/qrconst.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrconfig.php")){
			include APPPATH."functions/phpqrcode/qrconfig.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrtools.php")){
			include APPPATH."functions/phpqrcode/qrtools.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrspec.php")){
			include APPPATH."functions/phpqrcode/qrspec.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrimage.php")){
			include APPPATH."functions/phpqrcode/qrimage.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrinput.php")){
			include APPPATH."functions/phpqrcode/qrinput.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrbitstream.php")){
			include APPPATH."functions/phpqrcode/qrbitstream.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrsplit.php")){
			include APPPATH."functions/phpqrcode/qrsplit.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrrscode.php")){
			include APPPATH."functions/phpqrcode/qrrscode.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrmask.php")){
			include APPPATH."functions/phpqrcode/qrmask.php";
		}
		if (file_exists(APPPATH."functions/phpqrcode/qrencode.php")){
			include APPPATH."functions/phpqrcode/qrencode.php";
		}
		
	}

}

