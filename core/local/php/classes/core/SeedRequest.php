<?php
/**
 * Simple class for interfacing with Donovan Schönknecht's S3 library
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class SeedRequest {
	protected $request=false,$request_type,$plant_array=array(),$plant;
	
	public function __construct($direct_request=false) {
		if ($direct_request) {
			// skip detect on direct requests
			$this->request = $direct_request;
			$this->request_type = 'direct';
		} else {
			$this->detectRequest();
		}
		if ($this->request) {
			// found something, let's make sure it's legit and do work
			$requested_action = strtolower(trim($this->request['seed_action']));
			unset($this->request['seed_action']);
			if ($requested_action != '' && count($this->request) > 0) {
				$this->buildPlantArray();
				if (isset($this->plant_array[$requested_action])) {
					$file_path = SEED_ROOT.'/classes/plants/'.$this->plant_array[$requested_action];
					$class_name = substr_replace($this->plant_array[$requested_action], '', -4);
					require_once($file_path);
					$this->plant = new $class_name($this->request);
				}
			}
		}
	}
	
	protected function detectRequest() {
		if (!$this->request) {
			// determine correct request source
			if (isset($_POST['seed_action'])) {
				$this->request = $_POST;
				$this->request_type = 'get';
			} else if (isset($_GET['seed_action'])) {
				$this->request = $_GET;
				$this->request_type = 'get';
			} else if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
				$this->request = $_SERVER['argv'];
				$this->request_type = 'commandline';
			}
		}
	}
	
	protected function buildPlantArray() {
		if ($plant_dir = opendir(SEED_ROOT.'/classes/plants/')) {
			while (false !== ($file = readdir($plant_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmpKey = strtolower(substr_replace($file, '', -9));
					$this->plant_array["$tmpKey"] = $file;
				}
			}
			closedir($plant_dir);
		}
	}
} // END class 
?>