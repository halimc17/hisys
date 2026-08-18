<?
	session_start();
	ini_get('session.save_path');
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Index extends OWL_Controller {
		// public $log_secret;
		// public $appName, $appPath, $appCache, $appConfig;
		// public $username, $api_key, $token, $client_secret, $client_id;
		public function __construct() {
			parent::__construct();
			$this->load->model("Authorize");
			$this->load->model("Miot","gateway");
			
			if (!empty($_SESSION['username']) & isset($_SESSION)) {
				var_dump($_SESSION);
				exit();
			}
			$this->username = $_SESSION['username'] ?? 'owl.developer';// username / UUID Device
			$this->api_key = $_SESSION['api_key'] ?? '';
			$this->token = $_SESSION['token'] ?? '';
			$this->client_secret = $_SESSION['client_secret'] ?? '';
			$this->client_id = $_SESSION['client_id'] ?? '';
			
			// Check if the application name is provided in the URI
			$this->log_secret = load_class('log_secret', 'modules');

			$this->response['message'] = "Initializing application setup...<br>";
			$this->response['error'] = false;
			
			$appReady = array('dashboard', 'map', 'publish', 'mharvest');
			if (!isset($this->uri->segments[1]) or !in_array(strtolower($this->uri->segments[1]), $appReady)) {
				$this->appName = '';
				$this->response['error'] = true;
				$this->response['message'] = "Plase insert the App Name or App Name not found in the list of available applications: ".implode(", ", $appReady);
			} else if(in_array(strtolower($this->uri->segments[1]), $appReady)) {
				// Set the application name based on the first segment of the URI
				$this->appName = strtolower($this->uri->segments[1]);
				// Set the application path based on the application name
				$this->appPath = APPPATH.'cache'.DIRECTORY_SEPARATOR.$this->appName.DIRECTORY_SEPARATOR;
				if (!is_dir($this->appPath)) {
					// Create the application cache directory if it does not exist
					try {
						if (!@mkdir($this->appPath, 0755, true)) {
							$this->response['error'] = true;
							$this->response['message'] .= "Failed to create cache directory: ".$this->appPath."<br>";
						} else {
							$this->response['error'] = false;
							$this->response['message'] .= "Cache directory created successfully: ".$this->appPath."<br>";
						}
					} catch (Exception $e) {
						$this->response['error'] = true;
						$this->response['message'] .= "Failed to create cache directory: ".$e->getMessage()."<br>";
					}
				}
			}

			$this->page();
		}
		
		function page() {
			try {
			
				if ($this->response['error'] === true) {
					echo $this->response['message'];
					exit();
				} else {
					$this->load_application();
				}
							include(VIEWPATH.'publish.php');
			include(VIEWPATH.'publish_dis.php');

			} catch (Exception $e) {
				// $this->response['error'] = true;
				// $this->response['message'] .= "Failed to load URI library: ".$e->getMessage()."<br>";
				print_r($e->getMessage());
				print_r($this->response['message']);
				exit();
			}
			
			// if ($this->response['error'] === true) {
			// 	echo $this->response['message'];
			// 	exit();
			// } else {
			// 	$this->load_application();
			// }

			// echo "
			// 	<div style='position: fixed;top: 0;left: 0;'>
			// 		Welcome to the application: ".$this->appName."
			// 		<br>Cache directory: ".$this->appPath."
			// 		<br>Modules directory: ".APPPATH.'modules'.DIRECTORY_SEPARATOR."
			// 		<br>System directory: ".BASEPATH."
			// 		<br>View directory: ".VIEWPATH."
			// 		<br>Application path: ".APPPATH."	
			// 		<br>Application name: ".$this->appName."
			// 		<br>Client ID: ".$this->client_id."
			// 		<br>Client Secret: ".$this->client_secret."
			// 		<br>Client Key: ".$this->api_key."
			// 		<br>Token: ".$this->token."
			// 	</div>
			// ";

			// include(VIEWPATH.'publish.php');
			// $this->page_loader = load_class($this->appName.'_generator', 'modules');
			// if(method_exists($this->page_loader, 'load')){
			// 	$this->page_loader->load();
			// }else{	
			// 	$this->response['error'] = true;
			// 	$this->response['message'] = ".method load not found.";
			// }
			// include(VIEWPATH.'publish_dis.php');
			/*
			1st Load Data JSON
				database yang di Publish
				list data Publish // MAP - DASHBOARD (dari APPname)
				$PATH JSON dari list
						1. Load JSON All
						2. $listCollection = [collectionJson,...];
						3. tampilkan yang array pertama atau penentuan sequence
				
				$listCollection[0] ?? '';

				2. load source generator JSON to view 


			*/

		}
		
		function load_application() {
			$result = array(
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'api_key' => $this->api_key,
				'token' => $this->token,
				'username' => $this->username,
				'app_name' => $this->appName,
				'app_path' => $this->appPath
			);
			if($this->client_id == '') {
				$this->client_id = @$this->uri->segments[2] ?? '';
			}

			// Check if the application name is provided in the URI
			$headers = apache_request_headers();
			if (!empty($_SESSION['client_secret'])) {
				// If the client secret is already set in the session, use it
				$this->client_secret = $_SESSION['client_secret'];//'1234-0000-0000-0001';
			} else if (!empty($headers['client_secret'])) {
				$this->client_secret = $headers['client_secret'];//'1234-0000-0000-0001';
			} else {
				$this->client_secret = @$this->post('client_secret');//'1234-0000-0000-0001';
			}

			// Check if the client secret is provided in the request
			if ($this->client_id == '') {
				$this->get_client_secret();
			} else {
				if ($this->client_secret == '' ) {
					// echo "Plase insert the client Secret";
					$this->get_client_secret();
					session_destroy();
					exit();
				} else {
					// echo "Client Secret: ".$this->client_secret."<br>";
					// exit();
					if ($data = $this->getClientKey()) {
						if ($api = $this->getkeyauth() and !empty($api->api_key)) {
							$this->api_key = @$api->api_key;
							$this->token = @$api->token;

							$_SESSION['client_secret'] = $this->client_secret;

							$result = array(
								'client_id' => $this->client_id,
								'client_secret' => $this->client_secret,
								'api_key' => $this->api_key,
								'token' => $this->token,
								'username' => $this->username,
								'app_name' => $this->appName,
								'app_path' => $this->appPath
							);

							$this->appIndex($result);
						} else {
							$this->get_client_secret();

							session_destroy();
							exit();
						}
					} else {
						$this->get_client_secret();
						
						session_destroy();
						exit();
					}
				}
			}
			// return $result;
		}
		
		function get_client_secret() {
			$this->log_secret->index(
				array(
					'client_id' => $this->client_id,
					'client_secret' => $this->client_secret
				)
			);
		}
		
		function appIndex($appProfile) {
			return $this->log_secret->process($appProfile);
		}
		
		private function getClientKey() {
			$result = false;
			$_POST['token'] = $this->client_secret;
			$res = $this->gateway->checkToken($this->client_secret);
			if ($res['error'] == false) {
				$result = $res['data'];
			}

			return $result;
		}
		
		private function getkeyauth() {
			$result = new stdClass();
			$_POST['client_secret'] = $this->client_secret;
			$_POST['client_id'] = $this->client_id;
			$_POST['username'] = $this->username;
			$data =  $this->Authorize->get_apikey();
			if (!empty($data['api_key'])) {
				$this->api_key = $data['api_key'];
				$this->token = $this->gettoken($data['api_key']);
				$result->api_key = $this->api_key;
				$result->token = $this->token;
			}

			return $result;
		}
		
		function gettoken($api_key) {
			return $this->Authorize->token($api_key);
		}
	}
