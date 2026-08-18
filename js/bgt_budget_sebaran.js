/**
 * changeUnit
 * Onchange Unit Kerja
 */
function changeUnit() {
	var unit = getValue('unit'),
		div = getById('divisi'),
		kodeblok = getById('blok'),
		refOrg = JSON.parse(getValue('refOrg')),
		firstDiv = "";
	
	div.options.length = 0;
	for(var i in refOrg[unit]) {
		div.options[div.options.length] = new Option(refOrg[unit][i]['nama'],i);
		if(firstDiv=="") firstDiv = i;
	}
	
	kodeblok.options.length = 0;
	for(var i in refOrg[unit][firstDiv]['child']) {
		kodeblok.options[kodeblok.options.length] = new Option(refOrg[unit][firstDiv]['child'][i],i);
	}
	
	getList();
}

/**
 * changeDiv
 * Onchange Division
 */
function changeDiv() {
	var unit = getValue('unit'),
		div = getValue('divisi'),
		kodeblok = getById('blok'),
		refOrg = JSON.parse(getValue('refOrg'));
	
	kodeblok.options.length = 0;
	for(var i in refOrg[unit][div]['child']) {
		kodeblok.options[kodeblok.options.length] = new Option(refOrg[unit][div]['child'][i],i);
	}
	
	getList();
}

function getList() {
	var param="",
		tujuan='bgt_budget_slave_sebaran.php?proses=list',
		tahunbudget = getValue('tahunbudget'),
		kodebudget = getValue('kode'),
		blok = getValue('blok');
    
	param += "tahunbudget="+tahunbudget+"&blok="+blok+
		"&kodebudget="+kodebudget;
	
	if(tahunbudget!='' && kodebudget!='')
		post_response_text(tujuan, param, respog);
	
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					getById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function proses() {
	var param="",
		tujuan='bgt_budget_slave_sebaran.php?proses=proses',
		tbody = getById('tbody');
    
	param += "sebar[01]="+getValue('sebar01');
	param += "&sebar[02]="+getValue('sebar02');
	param += "&sebar[03]="+getValue('sebar03');
	param += "&sebar[04]="+getValue('sebar04');
	param += "&sebar[05]="+getValue('sebar05');
	param += "&sebar[06]="+getValue('sebar06');
	param += "&sebar[07]="+getValue('sebar07');
	param += "&sebar[08]="+getValue('sebar08');
	param += "&sebar[09]="+getValue('sebar09');
	param += "&sebar[10]="+getValue('sebar10');
	param += "&sebar[11]="+getValue('sebar11');
	param += "&sebar[12]="+getValue('sebar12');
	
	var kunci = tbody.getElementsByTagName('tr'),
		flag = false;
	
	for(var i in kunci) {
		if(parseInt(i)==i) {
			var chk = getById('row'+i);
			if(chk.checked==true) {
				param += "&kunci[]="+chk.getAttribute('data-kunci');
				flag = true;
			}
		}
	}
	
	if(flag) post_response_text(tujuan, param, respog);
	
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					getList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}