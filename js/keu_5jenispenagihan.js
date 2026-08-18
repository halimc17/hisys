function buatbaru(){
    document.getElementById('header').style.display ='block';
    document.getElementById('listdata').style.display ='none';
    document.getElementById('detail').style.display ='none';
    document.getElementById('methodht').value ='saveht';
    hapuscari();
    hapusht();
}

function displaylist(){
    hapusht();
    hapuscari();
    document.getElementById('header').style.display ='none';
    document.getElementById('detail').style.display ='none';
    document.getElementById('listdata').style.display ='block';
    loaddataht(0);
}



function saveht(parameter) {
	tujuan='keu_5jenispenagihan_slave.php';
   	var passP = parameter.split('###');
	var param = "";
	for (i = 1; i < passP.length; i++) {
		var tmp = document.getElementById(passP[i]);
		param += "&" + passP[i] + "=" + getValue(passP[i]);
	}
	method=document.getElementById('methodht').value;
	param += '&method=' + method;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('detail').style.display='block';
					document.getElementById('notransaksi').disabled=true;
					loaddatadt();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function hapuscari(){
	document.getElementById('kodejeniscari').value='';
	document.getElementById('namajeniscari').value=''; 
}

function hapusht(){
	document.getElementById('kodejenis').value='';
	document.getElementById('kodejenis').disabled=false;
	document.getElementById('namajenis').value='';
	document.getElementById('initial').value='';
	document.getElementById('printout').value='';
	document.getElementById('jurnal').value='';
	document.getElementById('jurnalppn').value='';
    document.getElementById('status').value='';
	document.getElementById('methodht').value='saveht';
	document.getElementById('detail').style.display ='none';
}

function hapusdt(){
	document.getElementById('kodebarang').value='';
	document.getElementById('noakunpiutang').value='';
	document.getElementById('noakunsales').value='';
	document.getElementById('noakunuangmuka').value='';
	document.getElementById('noakunppn').value='';
	document.getElementById('noakunklaimmutu').value='';
	document.getElementById('noakunklaimsusut').value='';
	document.getElementById('methodht').value='savedt';
}

function getPage(){
	pg      = document.getElementById('pages');
	pg      = pg.options[pg.selectedIndex].value;
	page   = parseFloat(pg) - 1;
	loaddataht(page);
}

function loaddataht(pagedata){
    kodejenis         = document.getElementById('kodejeniscari').value;
    namajenis    	= document.getElementById('namajeniscari').value;
    param   ='method=loaddataht&pagedata=' + pagedata;
	param += '&kodejenis=' + kodejenis + '&namajenis=' + namajenis;
    tujuan  ='keu_5jenispenagihan_slave.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    data = con.responseText.split("####");
                    document.getElementById('header').style.display ='none';
                    document.getElementById('listdata').style.display ='block';
                    document.getElementById('container').innerHTML = data[0];
                    document.getElementById('footData').innerHTML = data[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}



function editht(kodejenis){
    param='method=editht';
	param += '&kodejenis=' + kodejenis;
    tujuan='keu_5jenispenagihan_slave.php';
    post_response_text(tujuan, param, respon);
    function respon(){
      if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }  else {
					// alert(con.responseText);
					document.getElementById('header').style.display ='block';
					document.getElementById('detail').style.display ='block';
					document.getElementById('listdata').style.display ='none';

					arr=con.responseText.split("###");
					// alert(arr);
					document.getElementById('kodejenis').value=arr[0];
					document.getElementById('namajenis').value=arr[1];
					document.getElementById('initial').value=arr[2];
					document.getElementById('printout').value=arr[3];
					document.getElementById('jurnal').value=arr[4];
					document.getElementById('jurnalppn').value=arr[5];
                    document.getElementById('status').value=arr[6];
					// document.getElementById('nourut').value=arr[5];
					document.getElementById('kodejenis').disabled=true;
					document.getElementById('methodht').value='updateht';
					
					loaddatadt();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}



function loaddatadt(){
    kodejenis         = document.getElementById('kodejenis').value;
    param   ='method=loaddatadt';
	param += '&kodejenis=' + kodejenis;
    tujuan  ='keu_5jenispenagihan_slave.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('listdatadt').innerHTML = con.responseText;
                }
            } else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}




function savedt(parameter) {
	tujuan='keu_5jenispenagihan_slave.php';
   	var passP = parameter.split('###');
	var param = "";
	for (i = 1; i < passP.length; i++) {
		var tmp = document.getElementById(passP[i]);
		param += "&" + passP[i] + "=" + getValue(passP[i]);
	}
	method=document.getElementById('methoddt').value;
	param += '&method=' + method;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					hapusdt();
					loaddatadt();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}


function deleteht(kodejenis){
	param   ='method=deleteht';
	param += '&kodejenis=' + kodejenis;
    tujuan  ='keu_5jenispenagihan_slave.php';
    if(confirm("Delete data "+kodejenis+" ?")){
        post_response_text(tujuan, param, respon);	
    }
    function respon() {
        if(con.readyState==4){
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    hapusht();
                    document.getElementById('container').innerHTML=con.responseText;
                    loaddataht(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function deletedt(kodejenis,kodebarang){
	param   ='method=deletedt';
	param += '&kodejenis=' + kodejenis + '&kodebarang=' + kodebarang;
    tujuan  ='keu_5jenispenagihan_slave.php';
    if(confirm("Delete data "+kodejenis+", data2 "+kodebarang+" ?")){
        post_response_text(tujuan, param, respon);	
    }
    function respon() {
        if(con.readyState==4){
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    hapusdt();
                    document.getElementById('container').innerHTML=con.responseText;
                    loaddatadt(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function detaildata(kodejenis,kodebarang) {
    param = 'method=formdetaildata';
    param += '&kodejenis='+kodejenis;
    param += '&kodebarang='+kodebarang;

	tujuan = 'keu_5jenispenagihan_slave.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi", con.responseText);
				} else {
					alertify.popup('Tambahkan Detail',con.responseText).set({'resizable':true, 'overflow':true}).resizeTo('80%','100%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}