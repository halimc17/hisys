function getLaporanPrdPabrik(){
    periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
    pabrik=document.getElementById('pabrik').options[document.getElementById('pabrik').selectedIndex].value;
    param='periode='+periode+'&pabrik='+pabrik+'&method=preview';
    tujuan='pabrik_slave_2produksiHarian_v2.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


function laporanPDF(tanggal,sisahariini,pabrik,ev){
	param='tanggal='+tanggal+'&sisahariini='+sisahariini+'&pabrik='+pabrik;
	tujuan = 'pabrik_slave_printProduksi_pdf_v2.php?method=pdf&'+param;	
	
	//display window
	title='Data Produksi Harian ('+tanggal+')';
	width='700';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
	
	var dialog = document.getElementById('dynamic1');
	
	dialog.style.top = ((window.innerHeight/2) - (dialog.offsetHeight/2))+'px';
  	dialog.style.left = ((window.innerWidth/2) - (dialog.offsetWidth/2))+'px';
}

function laporanEXCEL(tanggal,sisatbskemarin,tbsmasuk,pabrik,tipe,ev){
	param='tanggal='+tanggal+'&sisatbskemarin='+sisatbskemarin+'&tbsmasuk='+tbsmasuk+'&pabrik='+pabrik+'&tipe='+tipe;
    tujuan = 'pabrik_slave_2produksiHarian_v2.php?method=excel&'+param;	
    
	//display window
    title='Data Produksi Harian ('+tanggal+')';
    width='1150';
    height='550';
    content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
    showDialog1(title,content,width,height,ev);
	
	var dialog = document.getElementById('dynamic1');
	
	dialog.style.top = ((window.innerHeight/2) - (dialog.offsetHeight/2))+'px';
  	dialog.style.left = ((window.innerWidth/2) - (dialog.offsetWidth/2))+'px';
}


function laporanhtml(tanggal,sisatbskemarin,tbsmasuk,pabrik,tipe,ev){
	param='tanggal='+tanggal+'&sisatbskemarin='+sisatbskemarin+'&tbsmasuk='+tbsmasuk+'&pabrik='+pabrik+'&tipe='+tipe;
    tujuan = 'pabrik_slave_2produksiHarian_v2.php?method=excel&'+param;	
	alert(param);
    alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','85%'); 
}

function laporanhtml(tanggal,sisatbskemarin,tbsmasuk,pabrik,tipe,ev){
	method='excel1';
	param='tanggal='+tanggal+'&sisatbskemarin='+sisatbskemarin+'&tbsmasuk='+tbsmasuk+'&pabrik='+pabrik;
	param += '&tipe=' + tipe+'&method=' + method;
	tujuan = 'pabrik_slave_2produksiHarian_v2.php';
	post_response_text(tujuan, param, respon);
	function respon(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					//alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','85%');
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('45%','85%').show();					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


