function gettanggal(){
	prd  = document.getElementById('prd').value;
	tahap= document.getElementById('tahap').value;
	
	tahun = prd.substr(0,4);
	bulan = prd.substr(5,2);
	if(tahap=='1'){
		tglawal = "01";
		tglakhir = "15";
	}else{
		tglawal = "16";
		var date = new Date(tahun, parseFloat(bulan)-1, 1);
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
		var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
		tglakhir = lastDay.getDate();
	}
	
	document.getElementById('tgl1').value=tglawal+"-"+bulan+"-"+tahun;
	document.getElementById('tgl2').value=tglakhir+"-"+bulan+"-"+tahun;
}

function viewdetail2(notransaksi, prd, unit,divisi,tipe) {
	unit  = document.getElementById('unit').value;
	divisi= document.getElementById('afd').value;
	prd   = document.getElementById('prd').value;
	tgl1  = document.getElementById('tgl1').value;
	tgl2  = document.getElementById('tgl2').value;
	tahap = document.getElementById('tahap').value;
	
	
	param = 'proses=viewdetail2&tgl1=' + tgl1 + '&prd=' + prd + '&unit=' + unit+ '&tipe=' + tipe+ '&divisi=' + divisi;
	param += '&tgl2=' + tgl2;
	param += '&tahap=' + tahap;
	tujuan = 'kebun_slave_2premipemanenv3.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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

function previewexceldetail2(notransaksi, prd, unit,divisi,tipe){
	unit  = document.getElementById('unit').value;
	divisi= document.getElementById('afd').value;
	prd   = document.getElementById('prd').value;
	tgl1  = document.getElementById('tgl1').value;
	tgl2  = document.getElementById('tgl2').value;
	tahap = document.getElementById('tahap').value;
	
	
	param = 'proses=viewdetail2&tgl1=' + tgl1 + '&prd=' + prd + '&unit=' + unit+ '&tipe=excel&divisi=' + divisi;
	param += '&tgl2=' + tgl2;
	param += '&tahap=' + tahap;
	
	tujuan = 'kebun_slave_2premipemanenv3.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	
	printFile(param,tujuan,title,ev);
}
