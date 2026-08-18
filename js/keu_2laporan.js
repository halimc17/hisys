function previewhpp(title,ev,unit,per){
	if(unit==''){
		alert('hpp tidak dapat diproses, karna pt masih kosong');return;
		closeDialog;
	}
    content= "<div id=printhpp style=\"max-height:350px;width:max-350;overflow:auto;\"></div>";
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
   
	unit='CPHO';
	per='2019-07'
	tipe='html';
	param='method=preview'+'&unit='+unit+'&per='+per+'&tipe='+tipe;
    tujuan = 'keu_slave_3hpp.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('printhpp').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}


function showhidedetaildata(nourut, totaldetail) {
	// alert(nourut);
	// alert(totaldetail);
	for (i = 1; i <= totaldetail; i++) {
		var row = document.getElementById('detaildata' + nourut + i);
		if (row !== null) {
			if (row.style.display == '') {
				row.style.display = 'none';
			} else {
				row.style.display = '';
			}
		}
	}
}

function getlaporanlabarugi(tipe){
	pt	=document.getElementById('pt').value;
	unit    =document.getElementById('unit').value;
	periode =document.getElementById('periode').value;   
	revisi =document.getElementById('revisi').value;          
	regional=document.getElementById('regional').value;
	tipelaporan=document.getElementById('tipelaporan').value;     	
	param='pt='+pt+'&unit='+unit+'&periode='+periode+'&revisi='+revisi+'&tipe='+tipe;
	param+='&regional='+regional+'&tipelaporan='+tipelaporan;
	tujuan='keu_slave_2labarugi.php';
	
	if(tipelaporan=='detail'){
		 tujuan='keu_slave_2labarugi_rinciakun.php';
	}else{
		 tujuan='keu_slave_2labarugi.php';
	}
	
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		printFile(param,tujuan,judul,ev);
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						if(tipe=='html'){
							document.getElementById('container').innerHTML=con.responseText;
						}
					}
			} else {
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
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('unit').innerHTML=con.responseText;  
				}
			} else {
				busy_off();
				error_catch(con.status);
				
			}
		}	
    } 
}

function lihatDetaillaporanlr(noakuna,noakuns,periode,tipe,pt,unit,ev,codeurut,kodelaporan){
    gudang=document.getElementById('gudang');
    gudang=gudang.options[gudang.selectedIndex].value;
    param = 'method=html' + '&noakuna=' + noakuna + '&noakuns=' + noakuns + '&periode=' + periode + '&tipe=' + tipe+ '&pt=' + pt+ '&unit=' + gudang+ '&codeurut=' + codeurut+ '&kodelaporan=' + kodelaporan;
    title="Data Detail";
     showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px'"+
    " src='keu_slave_2labarugiv2_detail.php?"+param+"'></iframe>",'850','400',ev);	
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';	
}


function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}