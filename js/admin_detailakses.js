function getseluruhnya(total){
	cekall = document.getElementById('chseluruhnya');
	chkorg = document.getElementsByName('chkorg[]');
	for (var i = 0; i < chkorg.length; i++) {
		if(cekall.checked==true){
			chkorg[i].checked = true;
		}else{			
			chkorg[i].checked = false;
		}
	}
	
}
function getorg() {
	var namauser = getValue('namauser');

	var param = "namauser=" + namauser + "&method=getorg";

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					//uncheck dulu
					chkorg = document.getElementsByName('chkorg[]');
					for (var i = 0; i < chkorg.length; i++) {
						if (chkorg[i].checked) {
							chkorg[i].checked = false;
						}
					}
					document.getElementById('chseluruhnya').checked = false;
					document.getElementById('chseluruhnya').disabled = false;
					
					var myarray = con.responseText.split('####');
					chkorg = document.getElementsByName('chkorg[]');
					for (var i = 0; i < myarray.length; i++) {
						for (j = 0; j < chkorg.length; j++) {
							chkorg[j].disabled = false;
							if (chkorg[j].value == myarray[i]) {
								chkorg[j].checked = true;
							}
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text('admin_slave_detailakses.php', param, respon);
}

function simpan() {
	chkorg = document.getElementsByName('chkorg[]');
	var vals = "";
	var countorg = 0;
	for (var i = 0; i < chkorg.length; i++) {
		if (chkorg[i].checked) {
			vals += "####" + chkorg[i].value;
			countorg = countorg + 1;
		}
	}
	org = vals.substring(4);
	var namauser = getValue('namauser');

	var param = "namauser=" + namauser + "&org=" + org + "&method=simpan";

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// === Success Response
					alert('Data Saved');
					batal();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text('admin_slave_detailakses.php', param, respon);
}

function batal() {
	chkorg = document.getElementsByName('chkorg[]');
	for (i = 0; i < chkorg.length; i++) {
		chkorg[i].checked = false;
		chkorg[i].disabled = false;
	}
	document.getElementById('namauser').selectedIndex = 0;
	document.getElementById('chseluruhnya').checked = false;
	document.getElementById('chseluruhnya').disabled = false;
}

function getValue(id) {
	var tmp = document.getElementById(id);
	if (tmp.options) {
		return tmp.options[tmp.selectedIndex].value;
	} else if (tmp.type == 'checkbox') {
		if (tmp.checked) {
			return 1;
		} else {
			return 0;
		}
	} else {
		return tmp.value;
	}
}

function enableAll() {
	document.getElementById('menu').removeAttribute('disabled');
	document.getElementById('input').removeAttribute('disabled');
	document.getElementById('edit').removeAttribute('disabled');
	document.getElementById('delete').removeAttribute('disabled');
	document.getElementById('print').removeAttribute('disabled');
	document.getElementById('approve').removeAttribute('disabled');
	document.getElementById('posting').removeAttribute('disabled');
	document.getElementById('save').removeAttribute('disabled');
}

function disabledAll() {
	document.getElementById('menu').setAttribute('disabled', 'disabled');
	document.getElementById('input').setAttribute('disabled', 'disabled');
	document.getElementById('edit').setAttribute('disabled', 'disabled');
	document.getElementById('delete').setAttribute('disabled', 'disabled');
	document.getElementById('print').setAttribute('disabled', 'disabled');
	document.getElementById('approve').setAttribute('disabled', 'disabled');
	document.getElementById('posting').setAttribute('disabled', 'disabled');
	document.getElementById('save').setAttribute('disabled', 'disabled');
}

/* Function getMenuList
 * Fungsi untuk mengambil hak menu untuk user tertentu
 * O : List Menu ditampilkan pada dropdown
 */
function getMenuList() {
	var tUser = document.getElementById('user');
	var tMenu = document.getElementById('menu');
	var param = "user=" + tUser.value;
	disabledAll();

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					eval("var resp=" + con.responseText + ";");
					tMenu.options.length = 0;

					// Menu
					//tMenu.options[menu.options.length] = new Option('','');
					for (i in resp.menuList) {
						tMenu.options[menu.options.length] = new Option(resp.menuList[i], i);
					}
					tMenu.setAttribute('onchange', 'updCheck()');

					updCheck();

					// Enable
					if (resp.stat == 'success') {
						enableAll();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text('slave_detailakses.php?proses=updContent', param, respon);
}

/* Function updCheck
 * Fungsi update nilai pada checkbox list
 */
function updCheck() {
	var tUser = document.getElementById('user');
	var tMenu = document.getElementById('menu');
	var param = "user=" + tUser.options[tUser.selectedIndex].value +
		"&menu=" + tMenu.options[tMenu.selectedIndex].value;

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					eval('var checkVal =' + con.responseText);
					checkVal[0] == 1 ? document.getElementById('input').checked = true : document.getElementById('input').checked = false;
					checkVal[1] == 1 ? document.getElementById('edit').checked = true : document.getElementById('edit').checked = false;
					checkVal[2] == 1 ? document.getElementById('delete').checked = true : document.getElementById('delete').checked = false;
					checkVal[3] == 1 ? document.getElementById('print').checked = true : document.getElementById('print').checked = false;
					checkVal[4] == 1 ? document.getElementById('approve').checked = true : document.getElementById('approve').checked = false;
					checkVal[5] == 1 ? document.getElementById('posting').checked = true : document.getElementById('posting').checked = false;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text('slave_detailakses.php?proses=updCheck', param, respon);
}

/* Function saveData
 * Fungsi untuk menyimpan data
 */
function saveData() {
	var userVal = getValue('user');
	var menuVal = getValue('menu');
	var inputVal = getValue('input');
	var editVal = getValue('edit');
	var deleteVal = getValue('delete');
	var printVal = getValue('print');
	var approveVal = getValue('approve');
	var postingVal = getValue('posting');

	var param = "user=" + userVal + "&menu=" + menuVal;
	param += "&input=" + inputVal + "&edit=" + editVal + "&delete=" + deleteVal +
	"&print=" + printVal + "&approve=" + approveVal + "&posting=" + postingVal;

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					alert('Data Saved');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text('slave_detailakses.php?proses=save', param, respon);
}