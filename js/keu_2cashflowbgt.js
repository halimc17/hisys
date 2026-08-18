function detailsubtotal(kodeurut,per,pt,regional,unit,tipe,ev,sumber) {
	param = 'method=previewsubtotal' + '&kodeurut=' + kodeurut + '&per=' + per + '&pt=' + pt + '&regional=' + regional + '&unit=' + unit + '&tipe=' + tipe + '&sumber=' + sumber;
	tujuan	='keu_2cashflowbgt_slave_detail.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				}else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}


function detail(kodeurut,per,pt,regional,unit,tipe,ev,sumber) {
	param = 'method=preview' + '&kodeurut=' + kodeurut + '&per=' + per + '&pt=' + pt + '&regional=' + regional + '&unit=' + unit + '&tipe=' + tipe+ '&sumber=' + sumber;
	tujuan	='keu_2cashflowbgt_slave_detail.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				}else {
					alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
				}
			}else {
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
	tujuan	='keu_2cashflowbgt_slave.php';
	if(tipe!='html'){
		judul=tipe;
		ev='event';
		alertify.popup(judul,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='keu_2cashflow_slave.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	}
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				}else {
					if(tipe=='html'){
						document.getElementById('container').innerHTML=con.responseText;
						leftFixedTable();
					}
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

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

function getUnit() {
    regional = document.getElementById('regional').value;
    pt = document.getElementById('pt').value;
    param = 'proses=getUnit' + '&regional=' + regional + '&pt=' + pt;
    //alert(param);
    tujuan = 'keu_slave_2jurnal_option.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    document.getElementById('gudang').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);

            }
        }
    }
}
