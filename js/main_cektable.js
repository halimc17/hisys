function posisiedit(baris,kolom){
	for(i=0;i<=kolom;i++){
		if(document.getElementById(baris+'_'+i)!=undefined){			
			document.getElementById(baris+'_'+i).disabled=false;
		}
	}
	document.getElementById("tblsave"+baris).style.display="";
}

function simpan(baris,kolom) {
	param="";
	for(i=0;i<kolom;i++){
		var x = document.getElementsByName("label_"+baris+"_"+i)[0].tagName;
		if(x=='TD'){
			isi = document.getElementById(baris+'_'+i).innerHTML;
			param += '&'+baris+'_'+i+'=' + isi;
		}else{
			isi = document.getElementById(baris+'_'+i).value;
			param += '&'+baris+'_'+i+'=' + isi;
		}
	}
	
	db = document.getElementById('db').value;
	tabel = document.getElementById('tabel').value;
	
	param += '&db=' + db + '&proses=update';
	param += '&tabel=' + tabel;
	param += '&baris=' + baris;
	tujuan = 'main_slave_cektable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					for(i=0;i<=kolom;i++){
						if(document.getElementById(baris+'_'+i)!=undefined){			
							document.getElementById(baris+'_'+i).disabled=true;
						}
					}
					document.getElementById("tblsave"+baris).style.display="none";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showtable() {
	db = document.getElementById('db').value;
	user = document.getElementById('user').value;
	pass = document.getElementById('pass').value;
	
	param = 'db=' + db + '&proses=showtable';
	param += '&user=' + user;
	param += '&pass=' + pass;
	tujuan = 'main_slave_cektable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tabel').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showkolom() {
	db = document.getElementById('db').value;
	user = document.getElementById('user').value;
	pass = document.getElementById('pass').value;
	tabel = document.getElementById('tabel').value;
	
	param = 'db=' + db + '&proses=showkolom';
	param += '&user=' + user;
	param += '&pass=' + pass;
	param += '&tabel=' + tabel;
	tujuan = 'main_slave_cektable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('field').innerHTML = con.responseText;
					document.getElementById('field2').innerHTML = con.responseText;
					document.getElementById('field3').innerHTML = con.responseText;
					document.getElementById('field4').innerHTML = con.responseText;
					document.getElementById('field5').innerHTML = con.responseText;
					document.getElementById('order1').innerHTML = con.responseText;
					document.getElementById('order2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tampilkan(mode,baris) {
	db = document.getElementById('db').value;
	user = document.getElementById('user').value;
	pass = document.getElementById('pass').value;
	tabel = document.getElementById('tabel').value;
	txtcari = document.getElementById('txtcari').value;
	txtcari2 = document.getElementById('txtcari2').value;
	txtcari3 = document.getElementById('txtcari3').value;
	txtcari4 = document.getElementById('txtcari4').value;
	txtcari5 = document.getElementById('txtcari5').value;
	field = document.getElementById('field').value;
	field2 = document.getElementById('field2').value;
	field3 = document.getElementById('field3').value;
	field4 = document.getElementById('field4').value;
	field5 = document.getElementById('field5').value;
	order1 = document.getElementById('order1').value;
	order2 = document.getElementById('order2').value;
	orderby1 = document.getElementById('orderby1').value;
	orderby2 = document.getElementById('orderby2').value;
	limit = document.getElementById('limit').value;
	
	param = 'db=' + db + '&proses=tampilkan';
	param += '&user=' + user;
	param += '&pass=' + pass;
	param += '&tabel=' + tabel;
	param += '&txtcari=' + txtcari;
	param += '&txtcari2=' + txtcari2;
	param += '&txtcari3=' + txtcari3;
	param += '&txtcari4=' + txtcari4;
	param += '&txtcari5=' + txtcari5;
	param += '&field=' + field;
	param += '&field2=' + field2;
	param += '&field3=' + field3;
	param += '&field4=' + field4;
	param += '&field5=' + field5;
	param += '&order1=' + order1;
	param += '&order2=' + order2;
	param += '&orderby1=' + orderby1;
	param += '&orderby2=' + orderby2;
	param += '&limit=' + limit;
	param += '&mode=' + mode;
	param += '&baris=' + baris;
	tujuan = 'main_slave_cektable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML = con.responseText;
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tampilkankehalaman() {
	db = document.getElementById('db').value;
	user = document.getElementById('user').value;
	pass = document.getElementById('pass').value;
	tabel = document.getElementById('tabel').value;
	txtcari = document.getElementById('txtcari').value;
	txtcari2 = document.getElementById('txtcari2').value;
	txtcari3 = document.getElementById('txtcari3').value;
	txtcari4 = document.getElementById('txtcari4').value;
	txtcari5 = document.getElementById('txtcari5').value;
	field = document.getElementById('field').value;
	field2 = document.getElementById('field2').value;
	field3 = document.getElementById('field3').value;
	field4 = document.getElementById('field4').value;
	field5 = document.getElementById('field5').value;
	order1 = document.getElementById('order1').value;
	order2 = document.getElementById('order2').value;
	page = document.getElementById('page').value;
	orderby1 = document.getElementById('orderby1').value;
	orderby2 = document.getElementById('orderby2').value;
	limit = document.getElementById('limit').value;
	
	param = 'db=' + db + '&proses=tampilkan';
	param += '&user=' + user;
	param += '&pass=' + pass;
	param += '&tabel=' + tabel;
	param += '&txtcari=' + txtcari;
	param += '&txtcari2=' + txtcari2;
	param += '&txtcari3=' + txtcari3;
	param += '&txtcari4=' + txtcari4;
	param += '&txtcari5=' + txtcari5;
	param += '&field=' + field;
	param += '&field2=' + field2;
	param += '&field3=' + field3;
	param += '&field4=' + field4;
	param += '&field5=' + field5;
	param += '&order1=' + order1;
	param += '&order2=' + order2;
	param += '&orderby1=' + orderby1;
	param += '&orderby2=' + orderby2;
	param += '&limit=' + limit;
	param += '&page=' + page;
	tujuan = 'main_slave_cektable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('printContainer').innerHTML = con.responseText;
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}