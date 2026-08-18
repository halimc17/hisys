<?
	defined('BASEPATH') or exit('No direct script access allowed');

	class Mmap extends OWL_Model {
		function gps($where = "limit 1") {
			$data = array();
			$q = "SELECT username,latitude,logitude,altitude,waktu FROM ".$this->db->dbname.".gps_location {$where} order by username ASC,waktu ASC";
			$r = $this->fetchdata($q);
			if (count($r) > 0) {
				$data = $r;
			}
			return $data;
		}

		function listuser($user, $tanggal)
		{
			$data = array();
			$q = "SELECT namauser FROM ".$this->db->dbname.".auth where namauser NOT LIKE 'tim.owl%' group by namauser";
			$r = $this->fetchdata($q);
			if (count($r) > 0) {
				$data = $r;
			}
			return $data;
		}
		private function getCenterLatLng($coordinates)
		{
			$x = $y = $z = 0;
			$n = count($coordinates);
			foreach ($coordinates as $point) {
				$lt = $point[0] * pi() / 180;
				$lg = $point[1] * pi() / 180;
				$x += cos($lt) * cos($lg);
				$y += cos($lt) * sin($lg);
				$z += sin($lt);
			}
			$x /= $n;
			$y /= $n;

			return [atan2(($z / $n), sqrt($x * $x + $y * $y)) * 180 / pi(), atan2($y, $x) * 180 / pi()];
		}
		function haversineGreatCircleDistance($ltF, $lgF, $ltT, $lgT, $r = 6371000)
		{
			// convert from degrees to radians
			$ltF = deg2rad($latitudeFrom);
			$lgF = deg2rad($longitudeFrom);
			$ltT = deg2rad($latitudeTo);
			$lgT = deg2rad($longitudeTo);

			$latDelta = $ltT - $ltF;
			$lonDelta = $lgT - $lgF;

			$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
				cos($ltF) * cos($ltT) * pow(sin($lonDelta / 2), 2)));
			return $angle * $r;
		}
		private function distance($lat1, $lon1, $lat2, $lon2, $unit)
		{

			if ($lat2 != null) {
				$theta = $lon1 - $lon2;
				$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
				$dist = acos($dist);
				$dist = rad2deg($dist);
				$miles = $dist * 60 * 1.1515;
				$unit = strtoupper($unit);

				if ($unit == "K") {
					return ($miles * 1.609344);
				} else if ($unit == "N") {
					return ($miles * 0.8684);
				} else {
					return $miles;
				}
			} else {
				return null;
			}
		}
		function user_date($user, $reqTanggal, $reqUser)
		{
			$data = array();
			$addWhere = "";
			$result = NULL;
			$addWhere = "";
			if (!empty($reqUser)) {
				$addWhere .= "and username = '".$reqUser."' ";
			} elseif (!empty($this->post('username'))) {
				$addWhere .= "and username = '".$this->post('username')."' ";
			} else {
				$addWhere .= "and username = '".$user['username']."' ";
			}
			if (!empty($reqTanggal)) {
				$tanggal = $reqTanggal;
			}
			if (!empty($this->post('tanggal'))) {
				$tanggal = $this->post('tanggal');
			}
			$where = "where username NOT LIKE 'tim.owl%' and tanggal = '".$tanggal."' ".$addWhere."";
			if (!empty($tanggal)) {
				$r = $this->gps($where);
				if (count($r) > 0) {
					$lat2 = $lon2 = $timeP = null;
					$firstTime = null;
					foreach ($r as $k => $v) {
						$data[$k] = $v;
						$data[$k]['distance'] = number_format(($this->distance($v['latitude'], $v['logitude'], $lat2, $lon2, "K") * (999.9975145)), 2)." m";
						$seconds = abs(strtotime($timeP ? $timeP : $v['waktu']) - strtotime($v['waktu']));
						$second = fmod(($seconds), 60);
						$minute = floor(($seconds / 60));
						$data[$k]['range'] = $minute." min ".$second." sec";
						$data[$k]['rangeJam'] = 0;
						$timeP = $v['waktu'];
						$lat2 = $v['latitude'];
						$lon2 = $v['logitude'];
						if (!$firstTime == null) {
							$firstTime = $v['waktu'];
						}
					}
					$result = $data;
				}
			}
			return $result;
		}

		function get_gps($user, $reqTanggal, $reqUser, $reqType, $reqVer, $reqVerPrev) {
			$data = array();
			$result = NULL;
			$addWhere = "";

			if (!empty($reqUser)) {
				$addWhere .= " and username = '".$reqUser."'";
			} elseif (!empty($this->post('username'))) {
				$addWhere .= "and username = '".$this->post('username')."' ";
			}

			if (!empty($reqVer)) {
				// $addWhere .= " and waktu > '".$reqUser."'";
				$addWhere .= " and waktu >= '".$reqVer."'";
			}

			if (!empty($reqVerPrev)) {
				// $addWhere .= " and waktu < '".$reqUser."'";
				$addWhere .= " and waktu <= '".$reqVerPrev."'";
			}

			if (!empty($this->post('tipe')) or !empty($reqType)) {
				if (!empty($this->post('tipe'))) {
					$Type = $this->post('tipe');
				} else {
					$Type = $reqType;
				}

				switch ($Type) {
					case 1:
						$addWhere .= " group by TIME_FORMAT(waktu,'%H:%i')";
						break;
					case 2:
						$addWhere .= " group by TIME_FORMAT(waktu,'%H:%i:%s')";
						break;
					default:
						$addWhere .= " group by TIME_FORMAT(waktu,'%H')";
						break;
				}
			} else {
				$addWhere .= " group by TIME_FORMAT(waktu,'%H')";
			}

			if (!empty($reqTanggal)) {
				$tanggal = $reqTanggal;
			}

			if (!empty($this->post('tanggal'))) {
				$tanggal = $this->post('tanggal');
			}

			$where = "where username NOT LIKE 'tim.owl%' and tanggal = '".$tanggal."' ".$addWhere."";
			if (!empty($tanggal)) {
				$r = $this->gps($where);
				if (count($r) > 0) {
					foreach ($r as $v) {
						$d = array();
						$d['lat'] = (float)$v['latitude'];
						$d['lng'] = (float)$v['logitude'];
						$data[$v['username']]['username'] = $v['username'];
						$data[$v['username']]['ver'] = $v['waktu'];
						$data[$v['username']]['color'] = '#'.str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
						$data[$v['username']]['coordinates'][] = $d;
					}
					$result = $data;
				}
			}

			return $result;
		}
	}
