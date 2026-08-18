function getbank(rekeningd){
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param = 'method=getbank'+'&unit='+unit;
	if(rekeningd!=0){
		param+='&rekening='+rekeningd;
	}
	post_response_text('keu_slave_posisisaldo.php', param, respon);
	
	function respon() 
	{
		if (con.readyState == 4)
		{
			if (con.status == 200)
			{
				busy_off();
                if (!isSaveResponse(con.responseText))
				{
					alert(con.responseText);
                }
				else
				{
					// === Success Response
                    document.getElementById('rekening').innerHTML = con.responseText;
                }
            }
			else
			{
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	rekening=document.getElementById('rekening').options[document.getElementById('rekening').selectedIndex].value;
	tanggal=document.getElementById('tanggal').value;
	jam=document.getElementById('jam').value;
	tanggal_lama=document.getElementById('tanggal_lama').value;
	jam_lama=document.getElementById('jam_lama').value;
	
	estimasi=document.getElementById('estimasi').value;
	saldoberjalan=document.getElementById('saldoberjalan').value;
	keterangan=document.getElementById('keterangan').value;
	method=document.getElementById('method').value;
	param='unit='+unit+'&rekening='+rekening+'&method='+method+'&tanggal_lama='+tanggal_lama+'&jam_lama='+jam_lama+'&jam='+jam;
	param+='&tanggal='+tanggal+'&saldoberjalan='+remove_comma_var(saldoberjalan)+'&estimasi='+remove_comma_var(estimasi)+'&keterangan='+keterangan;
	tujuan='keu_slave_posisisaldo.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					cancel();
					loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}
function deldata(kdorg,rekeningd,tgl,jam){
	param='unit='+kdorg+'&rekening='+rekeningd+'&method=deldata'+'&tanggal='+tgl+'&jam='+jam;
	tujuan='keu_slave_posisisaldo.php';
	if(confirm(bahasa.notifandayakin)){
		post_response_text(tujuan, param, respog);	
	}
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
                    //alert(con.responseText);
					cancel();
					loadData();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}
function cancel(){
    document.getElementById('unit').disabled=false;
	document.getElementById('unit').value='';
	document.getElementById('rekening').disabled=false;
	document.getElementById('rekening').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('tanggal_lama').value='';
	document.getElementById('jam_lama').value='00:00';
	document.getElementById('saldoberjalan').value='';
	document.getElementById('estimasi').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('method').value='insert';		
}



function loadData(num){
	tglcr=document.getElementById('tanggalCari').value;
	rekcr=document.getElementById('rekeningCari');
	rekcr=rekcr.options[rekcr.selectedIndex].value;
	createdCrDt=document.getElementById('createdCari');
	createdCrDt=createdCrDt.options[createdCrDt.selectedIndex].value;
    param='method=loadData';
	param+='&page='+num+'&tanggalCari='+tglcr+'&rekeningCari='+rekcr+'&createdCari='+createdCrDt;
	tujuan='keu_slave_posisisaldo.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					isdt = con.responseText.split("####");
                    document.getElementById('container').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
				}
  			}else{
  				busy_off();
          error_catch(con.status);
  			}
  		}	
  	}
}

//'".$bar['kodeorg']."','".$bar['norekening']."','".tanggalnormal($bar['tanggal'])."','".number_format($bar['posisisaldo'],2)."','".$bar['keterangan']."'
function fillfield(unit,rekeningd,tanggal,posisisaldo,estimasi,keterangan,jam){
	document.getElementById('unit').value=unit;
	document.getElementById('unit').disabled=true;
	document.getElementById('rekening').disabled=true;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('jam').value=jam;
	document.getElementById('tanggal_lama').value=tanggal;
	document.getElementById('jam_lama').value=jam;
	document.getElementById('saldoberjalan').value=posisisaldo;
	document.getElementById('estimasi').value=estimasi;
	document.getElementById('keterangan').value=keterangan;
	getbank(rekeningd);
}
//'".$bar['kodeorg']."','".$bar['norekening']."','".$bar['tanggal']."','".$optnama[$kodebank]."-".$norek."','".tanggalnormal($bar['tanggal'])."',event
function showDetailData(nmbank,tgl) {
    width = '500px';
    height = '450px';
    content = "<div id=containerData></div>";
    ev = 'event';
    title = "Detail ("+nmbank+") "+tgl;
    showDialog1(title, content, width, height, ev);
}
function previewdata(kdorg,rekeningd,tanggal,nmbank,tgl,jam, ev) {
    // Prep Param
    param = 'unit=' +kdorg+'&method=getDetail'+'&rekening='+rekeningd+'&tanggal='+tanggal+'&rektmp='+nmbank+'&tgl='+tgl+'&jam='+jam;
    showDetailData(nmbank,tgl);
    tujuan = 'keu_slave_posisisaldo.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('containerData').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}







