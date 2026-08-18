
function getunit(pt) {
	param = 'pt='+pt+'&method=getunit';
	tujuan = 'main_slave_loguser_activity.php';

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('unit').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function preview(tipeprint, ev) {
	var pt = document.getElementById('pt').value;
	var unit = document.getElementById('unit').value;
	var jabatan = document.getElementById('jabatan').value;
	var departemen = document.getElementById('departemen').value;
	var karyawan = document.getElementById('karyawan').value;
	var tgl1 = document.getElementById('tgl1').value;
	var tgl2 = document.getElementById('tgl2').value;

	if (tgl1 == '' || tgl2 == '') {
		alert('Periode tidak boleh kosong !');
		return;
	}

	param = 'pt='+pt+'&unit='+unit+'&jabatan='+jabatan+'&departemen='+departemen+'&karyawan='+karyawan+'&tgl1='+tgl1+'&tgl2='+tgl2+'&tipeprint='+tipeprint+'&method=preview';
	tujuan = 'main_slave_loguser_activity.php';

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (tipeprint == 'html') {
                        document.getElementById('listdata').innerHTML = con.responseText;
                        document.getElementById('loadpreview').style.display = 'block';
                        // leftFixedTable();
                    } else if (tipeprint == 'excel') {
                        tujuan=tujuan+"?"+param;  
                        printnopopup(tujuan);
                    } else if (tipeprint == 'pdf') {
                        title = 'Laporan Log User Activity';
                        tujuan = tujuan + "?" + param;
                        width = 1024;
                        height = 400;
                        content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+"></iframe>";
                        showDialog1(title, content, width, height, ev);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function printnopopup(url) {
    // alert(url);
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}