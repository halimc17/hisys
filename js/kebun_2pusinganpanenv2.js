function preview(tipeprint, ev) {
    kdorg    = document.getElementById("kdorg").value;
    tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
  
    param = '';
	param += 'method=preview&tgl1='+tgl1;
	param += '&tgl2='+tgl2;
	param += '&kdorg='+kdorg;
	param += '&tipeprint='+tipeprint;

    if(kdorg==''){
		alert('Kode Unit harus diisi.'); return;
	}
	
	if(tgl1=='' || tgl2==''){
		alert('Tanggal pertama dan tanggal kedua harus diisi.'); 
        return;
	}
	
	if(tgl1>tgl2){
		alert('Tanggal pertama tidak boleh lebih besar dari tanggal kedua.'); 
        return;
	}
    
    tujuan = "kebun_slave_2pusinganpanenv2.php";
    if (tipeprint != "html") {
      judul = tipeprint;
      ev = "event";
      printFile(param, tujuan, judul, ev);
    }
    post_response_text(tujuan, param, respog);
  
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("printContainer").innerHTML = con.responseText;
            leftFixedTable();
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }
  
  function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = "";
    height = "";
    content =
      "<iframe frameborder=0 width=100% height=100% src='" +
      tujuan +
      "'></iframe>";
    showDialog1(title, content, width, height, ev);
  }


  function update() {
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	kdorg = document.getElementById('kdorg').value;
	
	param = '';
	param += '&tgl1='+tgl1;
	param += '&tgl2='+tgl2;
	param += '&kdorg='+kdorg;
	if(kdorg==''){
		alert('Kode Unit harus diisi.'); return;
	}
	
	if(tgl1=='' || tgl2==''){
		alert('Tanggal pertama dan tanggal kedua harus diisi.'); return;
	}
	
	if(tgl1>tgl2){
		alert('Tanggal pertama tidak boleh lebih besar dari tanggal kedua.'); return;
	}
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    alert("Done");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
    post_response_text('kebun_slave_3pusingan_otomatis.php?', param, respon);
}
