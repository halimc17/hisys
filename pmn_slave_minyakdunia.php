<?php 
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


$switcher = "";
if(isset($_GET['switcher'])){
	$switcher = $_GET['switcher'];
}
//print_r(count($_POST));
//exit("ERROR:");
switch($switcher){
	default:
	  $str="select * from ".$dbname.".pmn_hargaminyakdunia";
	  $resx = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	  $resx->setFetchMode(PDO::FETCH_OBJ);
	  $html = "";
		  $no = 0;
		  while($barx=$resx->fetch())
		  {
				$html .= "<tr>";
				$html .= "<td>".($no+1)."</td>";
				$html .= "<td>".$barx->nama."</td>";
				$html .= "<td>".$barx->bulan."</td>";
				$html .= "<td>".$barx->buka."</td>";
				$html .= "<td>".$barx->tertinggi."</td>";
				$html .= "<td>".$barx->terendah."</td>";
				$html .= "<td>".$barx->bid."</td>";
				$html .= "<td>".$barx->ask."</td>";
				$html .= "<td>".$barx->lastdone."</td>";
				$html .= "<td>".$barx->setprice."</td>";
				$html .= "<td>".$barx->change."</td>";
				$html .= "<td>".$barx->oi."</td>";
				$html .= "<td>".$barx->vol."</td>";
				$html .= "</tr>";
			$no++;
		  }
		 
		if($no == 0){
			  $html .= "<tr>";
			  $html .= "<td colspan='12'>No Data</td>";
			  $html .= "</tr>";
		}
	  
	   echo $html;
	break;
	case 'getdata':
		//http://www.bursamalaysia.com/market/derivatives/prices/prices_f
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://www.bursamalaysia.com/market/derivatives/prices/prices_f",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			 "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	break;
	case 'updatedata':
	
		
		$post = array();
			$str="INSERT INTO ".$dbname.".`pmn_hargaminyakdunia` 
					(`nama`, `bulan`, `buka`, `tertinggi`,`terendah`,`bid`,`ask`,`lastdone`,`setprice`,`change`,`oi`,`vol`)
				values ";
			for($i=0; $i<count($post); $i++){			
			$str .= "('".$post[$i][1]."',
				'".$post[$i][2]."',
				'".$post[$i][3]."',
				'".$post[$i][4]."',
				'".$post[$i][5]."',
				'".$post[$i][6]."',
				'".$post[$i][7]."',
				'".$post[$i][8]."',
				'".$post[$i][9]."',
				'".$post[$i][10]."',
				'".$post[$i][11]."',
				'".$post[$i][12]."'
				),";
			}	
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
	break;
}
?>