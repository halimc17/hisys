<?
session_start();
ini_get('session.save_path');

//session_start();
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends OWL_Controller{
	public function __construct(){
		parent::__construct();
		if(!$this->sec_sys_serv()){
			$headers = apache_request_headers();
			if($headers['api_key']){
				header('HTTP/1.0 203 Non-Authoritative', true, 2031);
				header('message: Your Session has Expired');
				exit();
			}else{
				$this->redirect('login');
			}
		}
		$this->load->model('Menumap');
		$this->load->model('Privilegemap');
	}
	function index(){
		include(VIEWPATH.'master.php');
		//$this->load->view('master');

	 }
	 function checkPriviledge(){
		if(!$this->sec_sys_serv()){
			exit();
		}
		header('Content-Type: application/json; charset=utf-8');
		$priv  = array();
		$$res = $this->Privilegemap->auth("WHERE namauser = '".$_SESSION['standard']['username']."' AND menuid = '".$this->get('id')."' LIMIT 1");
		foreach ($res as $key => $value) {
			$priv[$key]['r'] = $value['r'];
			$priv[$key]['w'] = $value['w'];
			$priv[$key]['u'] = $value['u'];
			$priv[$key]['d'] = $value['d'];
			$priv[$key]['p'] = $value['p'];
		}
		echo json_encode($priv);
	 }

	 function load_childmenu(){
		if(!empty($this->get('jumper')) or !empty($this->get('refresh'))){
			if(!empty($this->get('jumper'))){
				$listMenu = $this->Menumap->jumper($_SESSION['standard']['username'],$this->get('jumper')); 
			}else{
				$listMenu = $this->Menumap->refresh($_SESSION['standard']['username'],$this->get('refresh')); 
			}
			if(count($listMenu) > 0){
				echo json_encode(array_shift($listMenu));
			}
		}else{
			$listMenu = $this->Menumap->loadMenu($_SESSION['standard']['username'],$this->get('parent')); 
			$datamenu = $this->getChildMenuByArr($listMenu,(int)$this->get('parent'),"");
			echo "<div id='columnmenu_0_".(int)$this->get('parent')."' class='col-listmenu'>";
			echo "<ul id='listmenu_0_".(int)$this->get('parent')."' class='listmenu'>";
			echo $datamenu;
			echo "</ul>";
			echo "</div>";
		}
	 }
	 function getChildMenuByArr($listMenu=array(),$idmenu){
		$result = $dataArr;
		if(count($listMenu) > 0){
			foreach($listMenu as $menu){
				//$result[] = $menu->id."/".$menu->parent.": ".$menu->caption;
				if($menu->class=='devider'){
					$result.="<li class='qmdivider'><span class=\"qmdivider qmdividerx\" ></span>";
				}else if($menu->class=='title'){
					if($result != ""){
						$result.= "</ul>";
						$result.= "<ul id='listmenu_".$menu['id']."_".$menu['induk']."' class='listmenu'>";
					}
					$result.="<li class=\"qmtitle\" ><span>".$menu['caption2']."</span>";
				}else{
					$class = "class=\"menulistico\"";
					if($menu['icon_path'] != ""){
						$icon = $this->base_template()."assets/images/icon/".$menu['icon_path'].".svg";
					}else{
						$icon = $this->base_template()."assets/images/navigasi/application.png";
					}
					
					$ch = "ch=\"".$menu['class'].",".$menu['action'].",".$menu['id']."\"";
					$hrf = "javascript:void(0);";
					$iconImage = "";
					if($menu['type']=='parent'){			
						$hrf = "javascript:void(0);";
						$ch = "ch=\"".$menu['id']."\"";
						$icon = $this->base_template()."assets/images/navigasi/folder-fully.png";
						$class = "class=\"arrow menulistparentico\"";
					}
					$iconImage = "<img src='".$icon."' style='border:0px;vertical-align:middle;height:15px'>";
					$result.= "<li ".$class." ".$ch.">".$iconImage."<a id='menu_".$menu['id']."' parentid='".$menu['induk']."' href=\"".$hrf."\"> ".$menu['caption2']."</a>";	
				}
				$result.="</li>";
				
				
			}
		}
		return $result;
	}
	function load_childmenu_ERP(){
			$cell="a.*";
			$clicked = array('click','owlproject');
			$query = "select ".$cell." from ".$this->dbname.".menu a
			left join ".$this->dbname.".auth b on b.menuid = a.id and b.namauser = '".$_SESSION['standard']['username']."' and b.status = '1'
			where a.parent = '".$master_id."' and a.hide='0'  and b.menuid is not null order by a.urut";
			
			if(!empty($this->get('jumper')) or !empty($this->get('refresh'))){
					
				if(!empty($this->get('jumper'))){
					$finder = " a.caption like '%".$this->get('jumper')."%' ";
				}else if(!empty($this->get('refresh'))){
					$finder = " a.action = '".$this->get('refresh')."' ";
				}
				$query = "select ".$cell." from ".$this->dbname.".menu a
				left join ".$this->dbname.".auth b on b.menuid = a.id and b.namauser = '".$_SESSION['standard']['username']."' and b.status = '1'
				where ".$finder." and a.hide='0' and a.class in ('".implode("','",$clicked)."') and a.type = 'list' and b.menuid is not null order by a.urut limit 1";
			}
			// echo $query;
			// print_r($_SESSION['access_type']);
			$str_m2=$this->owlPDO->query($query);
			$str_m2->setFetchMode(PDO::FETCH_OBJ);
			$listMenu = array();
			while($bar_m2=$str_m2->fetch()){
				if(count($privillage) > 0){
					if(in_array($bar_m2->id,$privillage)){
						$listMenu[$bar_m2->parent][] = $bar_m2;
					}
				}else{
					if($access_level == $bar_m2->access_level){
						$listMenu[$bar_m2->parent][] = $bar_m2;
					}
				}
				//$listMenu[$bar_m2->parent][] = $bar_m2;
			}
			if(!empty($this->get('jumper')) or !empty($this->get('refresh'))){
				if(count($listMenu) > 0){
					$dataList = array_shift($listMenu);
					

					//$dataList[0]['ch'] = $dataList[0]['class'].",".$dataList[0]['action'].",".$dataList[0]['id'];
					echo json_encode($dataList[0]);
				}
				exit();
			}

			function getChildMenuByArr($listMenu=array(),$idmenu,$dataArr="",$baseUrl = ""){
				$result = $dataArr;
				if(isset($listMenu[$idmenu]) and count(@$listMenu[$idmenu]) > 0){
					foreach($listMenu[$idmenu] as $menu){
						//$result[] = $menu->id."/".$menu->parent.": ".$menu->caption;
						if($menu->class=='devider'){
							$result.="<li class='qmdivider'><span class=\"qmdivider qmdividerx\" ></span>";
						}else if($menu->class=='title'){
							if($result != ""){
								$result.= "</ul>";
								$result.= "<ul id='listmenu_".$menu->id."_".$idmenu."' class='listmenu'>";
							}
							$result.="<li class=\"qmtitle\" ><span>".$menu->caption2."</span>";
						}else{
							$class = "class=\"menulistico\"";
							$icon = $baseUrl."assets/images/navigasi/application.png";
							// if(!empty($bar_m1->icon) or $bar_m1->icon == ""){
							// 	$icon = $baseUrl."assets/images/navigasi/application.png";
							// }else{
							// 	$icon = $baseUrl.$bar_m1->icon;
							// }
							$ch = "ch=\"".$menu->class.",".$menu->action.",".$menu->id."\"";
							$hrf = "javascript:void(0);";
							$iconImage = "";
							if($menu->type=='parent'){			
								$hrf = "javascript:void(0);";
								//$onclick = "onclick=\"loadChildMenu('".$menu->id."');\"";
								$ch = "ch=\"".$menu->id."\"";
								// if(!empty($bar_m1->icon) or $bar_m1->icon == ""){
								// 	$icon = $baseUrl."assets/images/navigasi/folder-fully.png";
								// }
								$icon = $baseUrl."assets/images/navigasi/folder-fully.png";
								$class = "class=\"arrow menulistparentico\"";
								
							}
							$iconImage = "<img src='".$icon."' style='border:0px;vertical-align:middle;height:15px'>";
							$result.= "<li ".$class." ".$ch.">".$iconImage."<a id='menu_".$menu->id."' parentid='".$menu->parent."' href=\"".$hrf."\"> ".$menu->caption2."</a>";	
						}
						$result.="</li>";
						
						
					}
				}
				return $result;
			}
			$baseUrl = $this->base_template();
			$datamenu = getChildMenuByArr($listMenu,$master_id,"",$baseUrl);
			echo "<div id='columnmenu_0_".$master_id."' class='col-listmenu'>";
			echo "<ul id='listmenu_0_".$master_id."' class='listmenu'>";
			echo $datamenu;
			echo "</ul>";
			echo "</div>";
	 }

}