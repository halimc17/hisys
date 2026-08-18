function getdetail(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	// form();
	param  = 'method=html';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;
	tujuan = 'bgt_slave_2rekapbyyperkebun_popup.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdetailexcel(pt,kdorg,tt,ip,divisi,prd,tipe,akun,jenis,bi,real) {
	ev='event';
	param  = 'method=excel';
	param += '&pt=' + pt;
	param += '&kdorg=' + kdorg;
	param += '&tt=' + tt;
	param += '&ip=' + ip;
	param += '&divisi=' + divisi;
	param += '&prd=' + prd;
	param += '&tipe=' + tipe;
	param += '&akun=' + akun;
	param += '&jenis=' + jenis;
	param += '&bi=' + bi;
	param += '&real=' + real;
	
	printnopopup("bgt_slave_2rekapbyyperkebun_popup.php?" + param);
}