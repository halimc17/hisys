function detail(karyawanid,thnnilai,penilaian) {
    param  = 'method=detail';
    param += '&tipeprint=html';
    param += '&karyawanid='+karyawanid+'&tahun='+thnnilai;
    param += '&penilaian='+penilaian;
    tujuan = 'sdm_slave_pas.php';
	
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function detailcvmm(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=html';
    tujuan = 'sdm_slave_coreman.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {                    
                    title = 'Data Detail';
                    tujuan = tujuan + "?" + param;
                    alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}
function detailkpi(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=html';
    tujuan = 'sdm_slave_2kpi.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {                    
                    alertify.popup().set({'resizable':true,'maximizable':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}
function getkodeorg(){
	pt = document.getElementById('pt').value;
	
	param  = 'method=getkodeorg';
	param += '&pt=' + pt;
	
	tujuan = 'sdm_slave_2laporankpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kodeorg').innerHTML = con.responseText;
					//alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function preview(){
	pt           = document.getElementById('pt').value;
	kodeorg      = document.getElementById('kodeorg').value;
	karyawanid   = document.getElementById('karyawanid').value;
	departemen   = document.getElementById('departemen').value;
	kodegolongan = document.getElementById('kodegolongan').value;
	jabatan      = document.getElementById('jabatan').value;
	penilaian    = document.getElementById('penilaian').value;
	tahun        = document.getElementById('tahun').value;
	kpi          = document.getElementById('kpi').value;
	cv           = document.getElementById('cv').value;
	mm           = document.getElementById('mm').value;
	pas          = document.getElementById('pas').value;
	statusmm     = document.getElementById('statusmm').value;
	tipekaryawan     = document.getElementById('tipekaryawan').value;
	
	param  = 'method=preview';
	param += '&pt=' + pt;
	param += '&kodeorg=' + kodeorg;
	param += '&karyawanid=' + karyawanid;
	param += '&departemen=' + departemen;
	param += '&kodegolongan=' + kodegolongan;
	param += '&jabatan=' + jabatan;
	param += '&penilaian=' + penilaian;
	param += '&tahun=' + tahun;
	param += '&kpi=' + kpi;
	param += '&cv=' + cv;
	param += '&mm=' + mm;
	param += '&pas=' + pas;
	param += '&statusmm=' + statusmm;
	param += '&tipekaryawan=' + tipekaryawan;
	
	tujuan = 'sdm_slave_2laporankpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Notifikasi",con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML = con.responseText;
					leftFixedTable();
					showheader();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function excel() {
	pt           = document.getElementById('pt').value;
	kodeorg      = document.getElementById('kodeorg').value;
	karyawanid   = document.getElementById('karyawanid').value;
	departemen   = document.getElementById('departemen').value;
	kodegolongan = document.getElementById('kodegolongan').value;
	jabatan      = document.getElementById('jabatan').value;
	penilaian    = document.getElementById('penilaian').value;
	tahun        = document.getElementById('tahun').value;
	kpi          = document.getElementById('kpi').value;
	cv           = document.getElementById('cv').value;
	mm           = document.getElementById('mm').value;
	pas          = document.getElementById('pas').value;
	statusmm     = document.getElementById('statusmm').value;
	tipekaryawan     = document.getElementById('tipekaryawan').value;
	
	param  = 'method=preview';
	param += '&tipe=excel';
	param += '&pt=' + pt;
	param += '&kodeorg=' + kodeorg;
	param += '&karyawanid=' + karyawanid;
	param += '&departemen=' + departemen;
	param += '&kodegolongan=' + kodegolongan;
	param += '&jabatan=' + jabatan;
	param += '&penilaian=' + penilaian;
	param += '&tahun=' + tahun;
	param += '&kpi=' + kpi;
	param += '&cv=' + cv;
	param += '&mm=' + mm;
	param += '&pas=' + pas;
	param += '&statusmm=' + statusmm;
	param += '&tipekaryawan=' + tipekaryawan;
	
	printnopopup("sdm_slave_2laporankpi.php?" + param);
	
	//showDialog1('Report Ms.Excel', "<iframe frameborder=0 style='width:895px;height:400px'" +" src='sdm_slave_2laporankpi.php?" + param + "'></iframe>", '500', '500', 'event');
}