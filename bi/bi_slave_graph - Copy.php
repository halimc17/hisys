<?
include('../config/connection.php');
include('../lib/nangkoelib.php');


$idmenu = checkPostGet('idmenu', '');
$method = checkPostGet('method', '');

$col=array("1"=>"blue","2"=>"red","3"=>"green","4"=>"yellow",
			"5"=>"pink","6"=>"purple","7"=>"lime","8"=>"magenta",
			"9"=>"teal","10"=>"torquoise","11"=>"greensea","12"=>"emerald",
			"13"=>"nephritis","14"=>"peterriver","15"=>"belizehole","16"=>"amethyst");

switch ($method) {
	
	case'getmenu':
	
		$str="select * from ".$dbname.".menugraph where id='".$idmenu."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);		
		$bar=$res-fetch();
			$judul=$bar['caption'];
	
		$str="select * from ".$dbname.".menugraph where tipe=1 and induk='".$idmenu."' order by  caption asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);		
		$frm[0] .= "<table>
			<tr>
				<td>";//
				while($bar=$res->fetch()){
					$frm[0] .= "<li onclick=getisi('".$bar['file']."'); style='float:left;width:100%;padding-bottom:4px;list-style-type:none;cursor:pointer' ><b>".$bar['caption']."</b></li>";
				}
				
		$frm[0] .= "</td>
			</tr>
		</table>";
		
				$hfrm[0] = $judul;
		drawaccordion($hfrm,$frm);
		echo"<button onclick=clearisi()>CLEAR</button>
				";
		
		//exit("Error:drawaccordion($hfrm,$frm)");
		
	break;
	
	
	
}



?>