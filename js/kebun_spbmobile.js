maxf 		= 0
sekarang 	= 1;

function gantidivisi() {
	kdOrg 	= document.getElementById('idKbn').value;


	// param 	= 'kdOrg=' + kdOrg + '&tgl=' + tgl + '&intex=' + getValue('intex') + '&proses=getData';
	param 	= 'kdOrg=' + kdOrg + '&proses=gantidivisi';
	tujuan 	= 'kebun_slave_spbmobile.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}

function saveData() {
	kdOrg 	= document.getElementById('idKbn').value;
	divisi 	= document.getElementById('divisi').value;
	tgl 	= document.getElementById('tglData').value;

	if (tgl == '') {
		alert("Please Insert Date/Tanggal");
		return;
	}

	// param 	= 'kdOrg=' + kdOrg + '&tgl=' + tgl + '&intex=' + getValue('intex') + '&proses=getData';
	param 	= 'kdOrg=' + kdOrg + '&tgl=' + tgl  + '&divisi=' + divisi + '&proses=getData';
	tujuan 	= 'kebun_slave_spbmobile.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					//alert(con.responseText);
					document.getElementById('result').style.display = 'block';
					document.getElementById('list_ganti').innerHTML = con.responseText;
					document.getElementById('idKbn').disabled = true;
					//document.getElementById('tglData').disabled=true;
					//document.getElementById('dtl_pem').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}

function prosesData2(nospb) {
	noSpb 	= nospb;
	param 	= 'noSpb=' + noSpb + '&proses=PostingData2';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					//alert(con.responseText);
					/*document.getElementById('tglData').value='';
					document.getElementById('idKbn').value='';*/
					saveData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Are You Sure Want Posting This Data")){
		post_response_text(tujuan, param, respon);
	}
}

function cancelSave() {
	document.getElementById('list_ganti').innerHTML = '';
	document.getElementById('idKbn').disabled = false;
	document.getElementById('tglData').disabled = false;
	document.getElementById('dtl_pem').disabled = false;
	document.getElementById('idKbn').value = '';
	document.getElementById('tglData').value = '';
	document.getElementById('divisi').value = '';
	document.getElementById('result').style.display = 'none';
}

//Umar
var presesing;
var editProsesing;
var editDatapengirim;
function viewData(param, title, content, ev) {
	width = '1000';
	height = '';
	//showDialog1(title, content, width, height, ev);
	ar = param.split("###");
	//dataDetail(ar[0], ar[1], ar[2]);
	noSpb 	= ar[0];
	noTrans = ar[1];
	tanggal	= ar[2];
	param 	= 'noSpb=' + noSpb + '&noTrans=' + noTrans + '&tglQR=' + tanggal + '&proses=ShowData';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup2("Detail Data",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('90%','80%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}

function editQRTrans(tanggal,nospb,nospb_parent,divisi=""){
	let tujuan = 'kebun_slave_spbmobile.php';
	let param  = 'proses=editQRTrans';
	param  	   += '&tglQR=' + tanggal;
	param  	   += '&noTrans=' + nospb;
	param  	   += '&divisi=' + divisi;
	param  	   += '&noTransparent=' + nospb_parent;
	editDatapengirim = $$.newWindow(tujuan+"?"+param, "Edit QR", "editqrtrans", true);
}
function selectDataListDetail(dataL){
	var data = dataL.split(",");
	tr = document.querySelectorAll('[identifikasi]');
	for(i=0; i<tr.length; i++){
		if(data.includes(tr[i].getAttribute('identifikasi'))){
			tr[i].style.background = "yellow";
		}else{
			tr[i].style.background = null;
		}
	}
}

function dataDetail(nospb, notrans, tanggal) {
	noSpb 	= nospb;
	noTrans = notrans;
	param 	= 'noSpb=' + noSpb + '&noTrans=' + noTrans + '&tglQR=' + tanggal + '&proses=ShowData';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// Success Response
					//alert(con.responseText);
					/*document.getElementById('tglData').value='';
					document.getElementById('idKbn').value='';*/
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}

function postingSPB(nospb, notransaksi, maxRow) {
	console.log('Memulai Posting SPB, no : ' + nospb);
	param 	= 'noSpb=' + nospb + '&proses=moveHeader';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function response() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					console.log('Pemindahan Data Temporary Berhasil...');
					if (maxRow == '' || maxRow == 0) {
						alert('Data tidak ditemukan, proses dibatalkan !');
						return;
					}

					console.log('Memulai Pemindahan Data Detail Temporary...');
					loopsave(nospb, notransaksi, 1, maxRow);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if(document.getElementById('datacomplete')){
		if (confirm("Proses ini akan memproporsi Kg Pabrik ke blok, lanjutkan ?")) {
			post_response_text(tujuan, param, response);
		}
	}else{
		alert('Data Panen Belum Lengkap');
	}
}

function loopsave(nospb, notransaksi, currRow, maxRow) {
	noTrans = trim(document.getElementById('trans_' + currRow).innerHTML);
	qr  	= trim(document.getElementById('row_' + currRow).getAttribute('identifikasi'));
	trackqr  	= trim(document.getElementById('qr_' + currRow).getAttribute('track-qr'));
	param 	= 'noSpb=' + nospb + '&noTrans=' + notransaksi + '&prestasi=' + noTrans + '&qr=' + qr + '&trackqr=' + trackqr + '&proses=moveDetail';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					console.log('Pemindahan Data Panen : ' + noTrans + ', QR : ' + qr + ' Gagal...');
					document.getElementById('row_' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					console.log('Pemindahan Data Panen : ' + noTrans + ', QR : ' + qr + ' Berhasil...');
					document.getElementById('row_' + currRow).style.backgroundColor = 'cyan';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						console.log('Memulai Posting Data...');
						prosesData(nospb, notransaksi);
					} else {
						loopsave(nospb, notransaksi, currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
	post_response_text(tujuan, param, respog);
}

function prosesData(noSpb, noTrans) {
	param 	= 'noSpb=' + noSpb + '&noTrans=' + noTrans + '&proses=PostingData';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					console.log('Posting Data Berhasil...');
					//getSPBChild(noSpb, noTrans);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	// if (confirm("Are You Sure Want Posting This Data")){
		post_response_text(tujuan, param, respon);
	// }
}

function getSPBChild(noSpb, noTrans) {
	console.log('Memulai Posting Data Anak Dengan No SPB Parent : ' + noSpb);
	param 	= 'noSpb=' + noSpb + '&noTrans=' + noTrans + '&proses=getSPBChild';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					try{
						let data = JSON.parse(con.responseText);
						console.log('Data Child SPB Pabrik : ', data);
						moveHeaderChild(data, data.length, 0);
					}catch(e){
						console.log(e); 
						presesing.close();
						saveData();
						presesing = null;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	// if (confirm("Are You Sure Want Posting This Data")){
		post_response_text(tujuan, param, respon);
	// }
}

function moveHeaderChild(data, max, num){
	param 	= 'noSpb=' + data[num].nospb + '&proses=moveHeaderChild';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function response() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					console.log('Pemindahan Data Child Temporary ' + data[num].qr + ' Gagal...');
					alert(con.responseText);
				} else {
					console.log('Pemindahan Data Child Temporary ' + data[num].qr + ' Berhasil...');
					getTphChild(data, max, num);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, response);
}

function getTphChild(data, max, num){
	param 	= 'noSpb=' + data[num].nospb + '&proses=getTPHChild';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function response() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					let result = JSON.parse(con.responseText);
					console.log('Data Child TPH SPB : ' + data[num].nospb, result);

					moveDetailChild(data, result, result.length, 0, max, num);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, response);
}

function moveDetailChild(data, result, x, z, max, num){
	param 	= 'qr=' + result[z].qr + '&tglQR=' + result[z].tanggal + '&noSpb=' + data[num].nospb + '&proses=moveDetailChild';
	tujuan 	= 'kebun_slave_spbmobile.php';

	function response() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					console.log('Pemindahan Data Child TPH Temporary ' + result[z].qr + ' Berhasil...');
					alert(con.responseText);
				} else {
					console.log('Pemindahan Data Child TPH Temporary ' + result[z].qr + ' Berhasil...');
					z += 1;
					if (x > z) {
						moveDetailChild(data, result, x, z, max, num);
					} else {
						//posting disini
						
						num += 1;
						if (max > num) {
							moveHeaderChild(data, max, num)
						} else {
							presesing.close();
							saveData();
							presesing = null;
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, response);
}

function editQR(ele, nospb,tanggal,noTransparent, ev){
	var notransaksi = "";
	if(document.getElementById('trans_' + ele.getAttribute('nomor'))){
		notransaksi = document.getElementById('trans_' + ele.getAttribute('nomor')).innerHTML;
	}
	var qr 			= "";
	var title		= "Tambah";
	if(document.getElementById(ele.id)){
		qr 			= document.getElementById(ele.id).innerHTML;
		title = "Edit";
	}

	let tujuan = 'kebun_slave_spbmobile.php';
	let param  = 'proses=editQR';
	param  	   += '&tranpnn=' + notransaksi;
	param  	   += '&noTrans=' + nospb;
	param  	   += '&notransparent=' + noTransparent;
	param  	   += '&tgl=' + tanggal;
	param  	   += '&qr=' + qr;
	param  	   += '&nomor=' + ele.getAttribute('nomor');
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup2(title+" QR",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
	editProsesing = $$.newWindow(tujuan+"?"+param, title+" QR", "editqr", true);
	//console.log(editProsesing);
}

function loadGosthQr(ele,nospb,tanggal, prosess){
	let action = 'kebun_slave_spbmobile.php';
	let param  = 'proses='+prosess;
	param  	   += '&noTrans=' + nospb;
	param  	   += '&tgl=' + tanggal;
	$$.get(editProsesing.target.panel,action+"?"+param,function(result){
		document.getElementById(ele).innerHTML = result.response;
		if(prosess != 'loadGoshtQRSPB'){
			loadGosthQr('datagoshtspb',nospb,tanggal,'loadGoshtQRSPB');
		}
	});
}
function prosesInsertQR(qrClass, tanggal, spb){
	var list = document.getElementsByClassName(qrClass);
	var data = new Array();
	var ele = new Array();
	for(var i=0; i<list.length; i++){
		if(list[i].checked == true){
			data.push(list[i].value);
			ele.push(list[i].parentNode.parentNode);
		}
	}
	if(data.length > 0){
		param 	= 'noSpb=' + spb + '&tglQR=' + tanggal + '&proses=prosesInsertQR&qr=' + data.join();
		tujuan 	= 'kebun_slave_spbmobile.php';
		function respon() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						for(var i=0; i<ele.length; i++){
							ele[i].remove();
						}
						editDatapengirim.refresh();
						presesing.refresh();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		post_response_text(tujuan, param, respon);
	}
}

function prosesDeleteQR(qr, qrtemp, tanggal, spb,spbutama, nomor){
	param 	= 'noSpb=' + spb + '&tglQR=' + tanggal + '&proses=prosesDeleteQR&qr=' + qr + '&qrtemp=' + qrtemp+ '&spbutama=' + spbutama;
	tujuan 	= 'kebun_slave_spbmobile.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('qr_' + nomor).parentNode.remove();
					if(typeof editProsesing != 'undefined' && typeof editProsesing.target.panel != 'undefined'){
						editProsesing.close();
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
function prosesDeleteQRParent(qr, tanggal, spb, nomor){ 
	if(nospb_parent != '0'){
		param 	= 'noSpb=' + spb + '&tglQR=' + tanggal + '&proses=prosesUpdateTrans&action=deleteqr&qr=' + qr + '&noTransparent='+nospb_parent;
		tujuan 	= 'kebun_slave_spbmobile.php';
		function respon() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						document.getElementById('qr_' + nomor).remove();
						presesing.refresh();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}

		post_response_text(tujuan, param, respon);
	}else{
		alert("SPB utama tidak dapat di Buang");
	}
}
function prosesUpdateNopol(tanggal,spb,nospb_parent){
	let updatePlatTrans = document.getElementById('updatePlatTrans').value;
	param 	= 'noSpb=' + spb + '&tglQR=' + tanggal + '&proses=prosesUpdateNopol&nopol=' + updatePlatTrans+'&noTransparent='+nospb_parent;
	tujuan 	= 'kebun_slave_spbmobile.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					editDatapengirim.close();
					presesing.refresh();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}

function prosesUpdateQRSPB(tanggal,spb,nospb_parent){
	let divisi = document.getElementById('updateDivisiTrans').value;
	param 	= 'noSpb=' + spb + '&tglQR=' + tanggal + '&proses=prosesUpdateTrans&action=updatedivisi&divisi=' + divisi+'&noTransparent='+nospb_parent;
	tujuan 	= 'kebun_slave_spbmobile.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					editDatapengirim.close();
					presesing.refresh();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}
function prosesUpdateQR(qr, qrtemp, tanggal, spb, nomor){
	let divisi = document.getElementById('updateQR').value;

	param 	= 'noSpb=' + spb + '&tglQR=' + tanggal + '&proses=prosesUpdateQR&qr=' + qr + '&divisi=' + divisi + '&qrtemp=' + qrtemp;
	tujuan 	= 'kebun_slave_spbmobile.php';

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					editProsesing.close();
					document.getElementById('qr_' + nomor).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

	post_response_text(tujuan, param, respon);
}