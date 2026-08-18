function getdetailkmhm(vhc,prd,barang) {
	// title = vhc;
	// ev = 'event';
	// width = '';
	// height = '';
	// content = "<div id=contdetailkmhm style='overflow:auto;width:700px;height:auto;' ></div>";
	// showDialog5(title, content, width, height, ev);
	
	param = 'kodevhc=' + vhc + '&proses=getdetailkmhm';
	param += "&periode=" + prd;
	param += "&kodebarang=" + barang;
	tujuan = 'vhc_slave_2pakaisparepart_detail.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// document.getElementById('contdetailkmhm').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
