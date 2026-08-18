function getunit() {
	kodept=document.getElementById('kodept').value;
	method = 'getunit';
	param='';
	param += '&kodept=' + kodept + '&method=' + method;
	tujuan = 'keu_2agingaccounting_slave.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
                } else {
                    document.getElementById('kodeunit').innerHTML = con.responseText;
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}



function detail(kodeurut,periode,kodept,regional,kodeunit,tipe,ev,digit) {
	param = 'method=preview' + '&kodeurut=' + kodeurut + '&periode=' + periode + '&kodept=' + kodept + '&regional=' + regional + '&kodeunit=' + kodeunit + '&tipe=' + tipe + '&digit=' + digit;

	tujuan	='keu_2laporankeuangankonsollabarugiv2_detail_slave.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert("Informasi", con.responseText);
				}
				else {
					if(tipe=='html'){
						alertify.popup2().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					}else{
						tujuanx=tujuan+"?"+param; 
						printnopopup(tujuanx);
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



function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}


function getlaporan(tipe){
	kodept	=document.getElementById('kodept').value;
	kodeunit    =document.getElementById('kodeunit').value;
	periode =document.getElementById('periode').value;
	digit =document.getElementById('digit').value;
	tipelaporan =document.getElementById('tipelaporan').value;
	param='kodept='+kodept+'&kodeunit='+kodeunit+'&periode='+periode+'&digit='+digit+'&tipe='+tipe+'&tipelaporan='+tipelaporan;

	if(tipelaporan=='default'){
		tujuan='keu_2laporankeuangankonsollabarugiv2_slave.php';
	}else if(tipelaporan=='rekap'){
        tujuan='keu_2laporankeuangankonsollabarugiv2_rekap_slave.php';
    }else{
		tujuan='keu_2laporankeuangankonsollabarugiv2_rinciakun_slave.php';
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
						leftFixedTable();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}