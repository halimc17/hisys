function getDivisi() {
	kodeorg = document.getElementById("unit").value;

	param = "proses=getDivisi"
	param += "&unit=" + kodeorg
	tujuan = "kebun_2laporanupahpremibkm_slave.php"

	post_response_text(tujuan,param,respog)

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('div').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}