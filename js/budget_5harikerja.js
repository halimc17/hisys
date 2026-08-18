// JavaScript Document
function savehk(fileTarget, passParam) {
	//statFr=document.getElementById('statFr');
	var passP = passParam.split('##');
	var param = "";
	for (i = 1; i < passP.length; i++) {
		var tmp = document.getElementById(passP[i]);
		if (i == 1) {
			param += passP[i] + "=" + getValue(passP[i]);
		} else {
			param += "&" + passP[i] + "=" + getValue(passP[i]);
		}
	}
	//alert(param);
	//alert(param);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					loadData();
					cancelIsi();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	//
	//  alert(fileTarget+'.php?proses=preview', param, respon);
	post_response_text(fileTarget + '.php', param, respon);

}
function loadData() {
	param = 'method=loadData';
	tujuan = 'log_slave_budget_5harikerja';
	post_response_text(tujuan + '.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					// Success Response
					//var res = document.getElementById(idCont);
					//                    res.innerHTML = con.responseText;
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function fillField(tahunbudget,unit) {
	param = 'method=getData' + '&tahunbudget=' + tahunbudget + '&unit=' + unit;
	tujuan = 'log_slave_budget_5harikerja';
	post_response_text(tujuan + '.php', param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					// Success Response
					ar = con.responseText.split("###");
					document.getElementById('tahunbudget').value = ar[0];
					document.getElementById('hrsetahun').value = ar[1];
					document.getElementById('hrminggu').value = ar[2];
					document.getElementById('hrlibur').value = ar[3];
					document.getElementById('hrliburminggu').value = ar[4];
					//document.getElementById('hkeffektif').value = ar[5];
					document.getElementById('jlhcuti').value = ar[5];
					document.getElementById('s1s2').value = ar[6];
					document.getElementById('h1h2').value = ar[7];
					document.getElementById('p1p3').value = ar[8];
					document.getElementById('mangkir').value = ar[9];
					document.getElementById('unit').value = ar[10];
					
					document.getElementById('oldtahunbudget').value = tahunbudget;
					tambah();
				}

			}
		} else {
			busy_off();
			error_catch(con.status);
		}
	}

}

function cancelIsi() {
	document.getElementById('tahunbudget').value = '';
	document.getElementById('hrsetahun').value = 365;
	document.getElementById('hrminggu').value = '';
	document.getElementById('hrlibur').value = '';
	document.getElementById('hrliburminggu').value = '';
	document.getElementById('method').value = "insert";
	document.getElementById('hkeffektif').value = '';
	
	document.getElementById('jlhcuti').value='';
	document.getElementById('s1s2').value='';
	document.getElementById('h1h2').value='';
	document.getElementById('p1p3').value='';
	document.getElementById('mangkir').value='';
	
	document.getElementById('jlhhkethn').value='';
	document.getElementById('ttlhrlbr').value='';
	document.getElementById('jlhhrabsen').value='';
	document.getElementById('jlhsakit').value='';
	document.getElementById('hkeffektif').value='';
	document.getElementById('persenhke').value='';
}

function tambah() {
	hrsetahun = document.getElementById('hrsetahun').value;
	hrminggu = document.getElementById('hrminggu').value;
	hrlibur = document.getElementById('hrlibur').value;
	hrliburminggu = document.getElementById('hrliburminggu').value;
	
	jlhcuti = document.getElementById('jlhcuti').value;
	
	s1s2 = document.getElementById('s1s2').value;
	h1h2 = document.getElementById('h1h2').value;
	p1p3 = document.getElementById('p1p3').value;
	mangkir = document.getElementById('mangkir').value;
	
	//if (jlhhkethn == '') {jlhhkethn = 0;}
	if (hrsetahun == '') {hrsetahun = 0;}
	//if (ttlhrlbr == '') {ttlhrlbr = 0;}
	if (hrminggu == '') {hrminggu = 0;}
	if (hrlibur == '') {hrlibur = 0;}
	if (hrliburminggu == '') {hrliburminggu = 0;}
	//if (jlhhrabsen == '') {jlhhrabsen = 0;}
	if (jlhcuti == '') {jlhcuti = 0;}
	//if (jlhsakit == '') {jlhsakit = 0;}
	if (s1s2 == '') {s1s2 = 0;}
	if (h1h2 == '') {h1h2 = 0;}
	if (p1p3 == '') {p1p3 = 0;}
	if (mangkir == '') {mangkir = 0;}
	//if (hkeffektif == '') {hkeffektif = 0;}
	//if (persenhke == '') {persenhke = 0;}
	
	thrlbr = (parseFloat(hrminggu)+parseFloat(hrlibur))-parseFloat(hrliburminggu);
	tsijin = parseFloat(s1s2)+parseFloat(h1h2)+parseFloat(p1p3)+parseFloat(mangkir);
	
	
	document.getElementById('jlhhkethn').value=parseFloat(hrsetahun)-thrlbr;
	document.getElementById('ttlhrlbr').value=thrlbr;
	document.getElementById('jlhhrabsen').value=tsijin + parseFloat(jlhcuti);
	document.getElementById('jlhsakit').value=tsijin;
	document.getElementById('hkeffektif').value=(parseFloat(hrsetahun)-thrlbr)-(tsijin + parseFloat(jlhcuti));
	document.getElementById('persenhke').value=numberFormat(((parseFloat(hrsetahun)-thrlbr)-(tsijin + parseFloat(jlhcuti)))/parseFloat(hrsetahun)*100,2);
	
}
function numberFormat(number, digit) {
	number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
	//Seperates the components of the number
	var components = (parseFloat(number).toFixed(digit)).split(".");
	//Comma-fies the first part
	components[0] = components[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	//Combines the two sections
	return components.join(".");
}