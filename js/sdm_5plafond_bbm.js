//JS

function simpan() {
	unit = document.getElementById('unit').value;
	jbtanId = document.getElementById('jbtanId');
	jbtanId = jbtanId.options[jbtanId.selectedIndex].value;
	tahun = document.getElementById('tahun').value;
	plafond = document.getElementById('plafond').value;
	method = document.getElementById('method').value;
	if (unit == '' || plafond == '' || tahun == '') {
		alert('Field Was Empty');
		return;
	}
	param = 'unit=' + unit + '&jbtanId=' + jbtanId + '&method=' + method + '&tahun=' + tahun + '&plafond=' + plafond;
	tujuan = 'sdm_slave_5plafond_bbm.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cancel();
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cancel() {
	document.getElementById('unit').value = '';
	document.getElementById('tahun').value = '';
	document.getElementById('jbtanId').disabled = false;
	document.getElementById('plafond').value = '';
	document.getElementById('unit').disabled = false;
	document.getElementById('method').value = 'insert';
}

function loaddata() {
	param = 'method=loaddata';
	tujuan = 'sdm_slave_5plafond_bbm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					document.getElementById('container').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function cariBast(num)
{
    var param ='method=loaddata&page='+num;
	tujuan = 'sdm_slave_5plafond_bbm.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}

function edit(unit, station, kodebarang) {
	document.getElementById('unit').value = unit;
	document.getElementById('unit').disabled = true;
	document.getElementById('station').value = station;
	document.getElementById('method').value = 'update';
}

function del(unit, jbtanId, tahun) {
	param = 'method=delete' + '&unit=' + unit + '&jbtanId=' + jbtanId + '&tahun=' + tahun;
	tujuan = 'sdm_slave_5plafond_bbm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}