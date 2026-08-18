function loaddata() {
	tipekar = document.getElementById('tipekar').value;
	unit = document.getElementById('unit').value;
	divisi = document.getElementById('divisi').value;
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	
	param = '';
	param += '&proses=preview';
	param += '&tipekar='+tipekar;
	param += '&unit='+unit;
	param += '&divisi='+divisi;
	param += '&tgl1='+tgl1;
	param += '&tgl2='+tgl2;
	
	tujuan = 'sdm_slave_2premi';
	
	post_response_text(tujuan+'.php',param,respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML = con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getdivisitipe(){
 	unit= document.getElementById('unit').value;
	param='unit='+unit+'&proses=getdivisitipe';	
	tujuan='sdm_slave_2premi.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					isdt = con.responseText.split("####");
                    document.getElementById('divisi').innerHTML = isdt[0];
                    document.getElementById('tipekar').innerHTML = isdt[1];
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}


function printpdf() {
	unit 	= document.getElementById('unit').value;
	divisi 	= document.getElementById('divisi').value;
	tgl1 	= document.getElementById('tgl1').value;
	tgl2 	= document.getElementById('tgl2').value;
	tipekar	= document.getElementById('tipekar').value;
	param	= 'proses=printpdf' + '&unit=' + unit + '&divisi=' + divisi + '&tgl1=' + tgl1 + '&tgl2='+ tgl2 + '&tipekar='+ tipekar;
	tujuan='sdm_slave_2premi.php';
	tujuan = tujuan+'?' + param;
	content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
	width = '1200';
	height = '800';
	title = "";
	showDialog2(title, content, width, height, 'event');
	
}

function loadDetail(nikkary) {
	ele	= document.querySelectorAll('.'+nikkary);
	console.log(ele);
	for (var i = 0; i < ele.length; i++) {
		if (ele[i].style.display == 'none') {
			ele[i].style.display = '';
		} else {
			ele[i].style.display = 'none';
		}
	}


}