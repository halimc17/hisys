function savedetail(currRow,maxRow){
	param = "kodevhc="+getValue('kodevhc');
	
	beratmuatan= getValue('beratmuatan'+currRow);
	param += "&beratmuatan="+beratmuatan;
	
	notransaksi= getValue('notransaksi'+currRow);
	param += "&notransaksi="+notransaksi;
	
	jenispekerjaan= getValue('jenispekerjaan'+currRow);
	param += "&jenispekerjaan="+jenispekerjaan;
	
	alokasibiaya= getValue('alokasibiaya'+currRow);
	param += "&alokasibiaya="+alokasibiaya;
	
	kmhmawal= getValue('kmhmawal'+currRow);
	param += "&kmhmawal="+kmhmawal;
	
	kmhmakhir= getValue('kmhmakhir'+currRow);
	param += "&kmhmakhir="+kmhmakhir;
	
	jumlah= getValue('jumlah'+currRow);
	param += "&jumlah="+jumlah;
		

	post_response_text('tool_slave_reset.php?proses=simpan', param, respon);
	if(currRow!=undefined){
		document.getElementById('row' + currRow).style.backgroundColor='cyan';
	}
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                    unlockScreen();
                } else {
					currRow+=1;
                    if((currRow>maxRow) || (maxRow == undefined)){
						alertify.alert("Data telah selesai dilakukan update, jika sudah pernah dilakukan <b>SIKLUS / PROSES AKHIR BULAN</b> harap <b>diulangi</b>.");
					} else {
						savedetail(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	totalbaris= getValue('totalbaris');
	alertify.confirm("Warning","Anda yakin ???",
		function(){
			savedetail(1,totalbaris);
		},
		function(){
			return;
		}
	).set('resizable',false).resizeTo(100,250);
	
}

function hitunghm(no,kmhmakhir){
	totalbaris= getValue('totalbaris');
	kmhmawal  = getValue('kmhmawal'+no);
	
	jumlah = parseFloat(kmhmakhir)-parseFloat(kmhmawal);
	document.getElementById('kmhmakhir'+no).setAttribute("value",kmhmakhir);
	document.getElementById('jumlah'+no).setAttribute("value",jumlah);
	
	e = parseFloat(no)+1;
	
	for(i=e;i<=totalbaris;i++){
		awal = kmhmakhir;
		document.getElementById('kmhmawal'+i).setAttribute("value",kmhmakhir);
		jumlah= getValue('jumlah'+i);
		
		kmhmakhir = parseFloat(awal)+parseFloat(jumlah);
		document.getElementById('kmhmakhir'+i).setAttribute("value",kmhmakhir);
	}
}
function preview(){
	kodevhc = document.getElementById('kodevhc').value;
	tanggal = document.getElementById('tanggal').value;
	notransaksi = document.getElementById('notransaksi').value;
	
    param = "kodevhc="+kodevhc;
	param += "&tanggal="+tanggal;
	param += "&notransaksi="+notransaksi;
	post_response_text('tool_slave_reset.php?proses=preview', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function resetDt(){
    var param = "kodevhc="+getValue('kodevhc')+"&kmhmakhir="+getValue('kmhmakhir');
	post_response_text('tool_slave_reset.php?proses=reset', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    alert("Proses Reset KM/HM untuk "+getValue('kodevhc')+" berhasil");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getKmHmAkhir() {
	param = "kodevhc="+getValue('kodevhc');
	param += "&tanggal="+getValue('tanggal');
	post_response_text('tool_slave_reset.php?proses=getKm', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    data = con.responseText.split("####");
					
                    document.getElementById('kmhmakhir').value=data[0];
                    document.getElementById('notransaksi').innerHTML=data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}