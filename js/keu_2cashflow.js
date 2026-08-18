/**
 * @author repindra.ginting
 */
  // $stream.="<td align=right width='200px;'  title='Click untuk melihat detail' onclick=\"detail('".$data['nourut']."','".$per."','".$pt."','".$regional."','".$unit."','html','event');\">".number_format(@$data[$per],2)."</td>";
				// }
				/*
function detail(kodeurut,per,pt,regional,unit,tipe,ev) {
	param = 'method=preview' + '&kodeurut=' + kodeurut + '&per=' + per + '&pt=' + pt + '&regional=' + regional + '&unit=' + unit + '&tipe=' + tipe;
	title = "Data Detail";
	// showDialog1(title, "<iframe frameborder=0 style='width:845px;height:395px'" +
	// 	" src='keu_2cashflow_slave_detail.php?" + param + "'></iframe>", '850', '400', ev);
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';

	
	alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
} 
*/


function detailsubtotal(kodeurut,per,pt,regional,unit,tipe,ev) {
	param = 'method=previewsubtotal' + '&kodeurut=' + kodeurut + '&per=' + per + '&pt=' + pt + '&regional=' + regional + '&unit=' + unit + '&tipe=' + tipe;
	tujuan	='keu_2cashflow_slave_detail.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
				}
				else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}


function detail(kodeurut,per,pt,regional,unit,tipe,ev) {
	param = 'method=preview' + '&kodeurut=' + kodeurut + '&per=' + per + '&pt=' + pt + '&regional=' + regional + '&unit=' + unit + '&tipe=' + tipe;
	tujuan	='keu_2cashflow_slave_detail.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
				}
				else {
					alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}
















 
function cancel(){
	document.getElementById('container').innerHTML='';
	document.getElementById('pt').value='';
	document.getElementById('periode').value='';
}	
 
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
//    width='900';
//    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
//    showDialog1(title,content,width,height,ev); 	
   alertify.popup(title,content,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
}

function getlaporanaruskas(tipe){
	pt		=document.getElementById('pt').value;
	regional=document.getElementById('regional').value;
	unit    =document.getElementById('gudang').value;
	periode =document.getElementById('periode').value;        
	param	='pt='+pt+'&regional='+regional+'&unit='+unit+'&periode='+periode+'&tipe='+tipe;
	tujuan	='keu_2cashflow_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		// printFile(param,tujuan,judul,ev);
		alertify.popup(judul,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_2cashflow_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
				}
				else {
					// showById('printPanel');
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
					}
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

 
//onchange baru untuk ambil PT->Regional->unit

//get Regional
function getReg() {
    pt=document.getElementById('pt').value;
    param='proses=getReg'+'&pt='+pt;
    tujuan='keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
						
				} else {
					//alert(con.responseText);
					document.getElementById('regional').innerHTML=con.responseText;  
					getUnit();
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

//get unit
function getUnit() {
    regional=document.getElementById('regional').value;
    pt=document.getElementById('pt').value;
    param='proses=getUnit'+'&regional='+regional+'&pt='+pt;
    //alert(param);
    tujuan='keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);    
    function respog()
    {
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					document.getElementById('gudang').innerHTML=con.responseText;  
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}
