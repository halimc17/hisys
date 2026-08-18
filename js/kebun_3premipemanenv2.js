function gettanggal(){
	prd  = document.getElementById('prd').value;
	tahap= document.getElementById('tahap').value;
	
	tahun = prd.substr(0,4);
	bulan = prd.substr(5,2);
	if(tahap=='1'){
		tglawal = "01";
		tglakhir = "15";
	}else{
		tglawal = "16";
		var date = new Date(tahun, parseFloat(bulan)-1, 1);
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
		var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
		tglakhir = lastDay.getDate();
	}
	
	document.getElementById('tgl1').value=tglawal+"-"+bulan+"-"+tahun;
	document.getElementById('tgl2').value=tglakhir+"-"+bulan+"-"+tahun;
}

function add_new_data(){
	document.getElementById('detail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	//document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	batallist();
	loaddata(0);
}

function prevdata(afd,tgl1,jenis,unit) {
	// title = "Preview Detail";
	// width = '';
	// height = '';
	// ev ='event';
	// content = "<fieldset><legend>Form</legend><div id=prevdatacont style='overflow:auto;width:100%;height:auto;' ></div></fieldset>";
	// showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic4').style.top = (pos[1] + 800)+ 'px';
	// document.getElementById('dynamic4').style.left = (pos[0] - 500) + 'px';
	
	kgbrondol = document.getElementById('kgbrondol').value;
	
	param = 'proses=prevdata';
	param += '&tgl1=' + tgl1;
	param += '&afd=' + afd;
	param += '&jenis=' + jenis;
	param += '&kgbrondol=' + kgbrondol;
	param += '&unit=' + unit;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//document.getElementById('prevdatacont').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prevrekappnn(div, tgl,unit) {
	// title = "Preview Detail";
	// width = '';
	// height = '';
	// ev ='event';
	// content = "<fieldset><legend>Form</legend><div id=containerd style='overflow:auto;width:650px;height:auto;' ></div></fieldset>";
	// showDialog1(title, content, width, height, ev);
	
	if(div=='%%'){
		div=unit;
	}else{
		div=div;
	}
	
	param = 'method=html' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'kebun_slave_rekappnn.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containerd').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdetailkg(tgl1,tgl2,tt,afd, ev,id,jnstgl) {
	// title = "Preview Detail";
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Form</legend><div id=prev1 style='overflow:auto;width:800px;height:auto;' ></div></fieldset>";
	// showDialog1(title, content, width, height, ev);

	param = 'proses=getdetailkg';
	param += '&tgl1=' + tgl1;
	param += '&tgl2=' + tgl2;
	param += '&tt=' + tt;
	param += '&afd=' + afd;
	param += '&id=' + id;
	param += '&jnstgl=' + jnstgl;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//document.getElementById('prev1').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdetailkgpks(tgl1,afd,unit,mill,id,ev) {
	// title = "Preview Detail";
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Form</legend><div id=prev1 style='overflow:auto;width:800px;height:auto;' ></div></fieldset>";
	// showDialog1(title, content, width, height, ev);

	param = 'proses=getdetailkgpks';
	param += '&tgl1=' + tgl1;
	param += '&afd=' + afd;
	param += '&unit=' + unit;
	param += '&mill=' + mill;
	param += '&id=' + id;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//document.getElementById('prev1').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewdata(blok,tgl1, ev) {
	// title = "";
	// width = '800';
	// height = '';
	// content = "<fieldset><legend>Form</legend><div id=prev style='overflow:auto;width:770px;height:auto;' ></div></fieldset>";
	// showDialog2(title, content, width, height, ev);

	param = 'proses=previewdata&blok=' + blok;
	param += '&tgl1=' + tgl1;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.alert(con.responseText);
					//document.getElementById('prev').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	document.getElementById('contloaddata').style.display="";
	
	prdlist = document.getElementById('prdlist').value;
	unitlist = document.getElementById('unitlist').value;
	afdlist = document.getElementById('afdlist').value;
	param = 'proses=loaddata&page=' + page;
	if (prdlist != '') {
		param += '&prdlist=' + prdlist;
	}
	if (unitlist != '') {
		param += '&unitlist=' + unitlist;
	}
	if (afdlist != '') {
		param += '&afdlist=' + afdlist;
	}
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('printContainerlist').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
					
					document.getElementById('contpivot').style.display="none";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batal() {
	document.getElementById('prd').value = '';
	document.getElementById('unit').value = '';
	document.getElementById('afd').value = '';
	document.getElementById('printContainer').innerHTML = '';
}
function batallist() {
	//document.getElementById('prdlist').value = '';
	document.getElementById('unitlist').value = '';
	document.getElementById('afdlist').value = '';
	document.getElementById('contloaddata').style.display="";
	document.getElementById('contpivot').style.display="none";
	loaddata();
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
function del(notransaksi, prd, unit,tgl1,tgl2) {
	param = 'proses=deleteTrans&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	param += '&tgl1=' + tgl1;
	param += '&tgl2=' + tgl2;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	if (confirm(' Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
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
function form() {
	width = '';
	height = '';
	content = "<div id=containerView style=\"width:100%;max-height:450px;overflow:auto;\"></div>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function view(notransaksi, prd, unit,divisi,tipe) {
	//form();
	param = 'proses=view&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit+ '&tipe=' + tipe+ '&divisi=' + divisi;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerView').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function viewdetail(notransaksi, prd, unit,divisi,tipe) {
	//form();
	param = 'proses=viewdetail&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit+ '&tipe=' + tipe+ '&divisi=' + divisi;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerView').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewdetail2(notransaksi, prd, unit,divisi,tipe) {
	// form();
	param = 'proses=viewdetail2&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit+ '&tipe=' + tipe+ '&divisi=' + divisi;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containerView').innerHTML = con.responseText;
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function previewexceldetail(notransaksi, prd, unit,divisi,tipe){
	param = 'proses=viewdetail' + '&prd=' + prd + '&unit=' + unit+ '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
	tujuan = 'kebun_slave_save_3premipemanenv2.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	
	printFile(param,tujuan,title,ev);
}

function previewexceldetail2(notransaksi, prd, unit,divisi,tipe){
	param = 'proses=viewdetail2' + '&prd=' + prd + '&unit=' + unit+ '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
	tujuan = 'kebun_slave_save_3premipemanenv2.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	
	printFile(param,tujuan,title,ev);
}

function previewexcel(notransaksi, prd, unit,divisi,tipe){
	param = 'proses=view' + '&prd=' + prd + '&unit=' + unit+ '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
	tujuan = 'kebun_slave_save_3premipemanenv2.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	
	printFile(param,tujuan,title,ev);
}

function unposting(notransaksi, prd, unit, baris) {
	param = 'proses=unposting&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	if (confirm('Anda yakin ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containerView').innerHTML = con.responseText;
					// document.getElementById('tr_' + baris).cells[18].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[19].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[20].innerHTML = "<img src=images/application/application_delete.png class=resicon class=zImgBtn height='30'  title='Please Reload Frame'>";
					// document.getElementById('tr_' + baris).cells[21].innerHTML = "<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Please Reload Frame'>";
					alertify.alert('Unposting Success.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(notransaksi, prd, unit, baris) {
	param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	tujuan = 'kebun_slave_save_posting3premipemanen.php';
	if (confirm('Posting akan memakan waktu cukup lama dan pastikan koneksi anda stabil, ingin tetap melanjutkan ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('containerView').innerHTML = con.responseText;
					// document.getElementById('tr_' + baris).cells[17].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[18].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[19].innerHTML = '';
					// document.getElementById('tr_' + baris).cells[20].innerHTML = "<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted'>";
					alertify.alert('Posting Success.');
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deleteTrans(maxRow) {
	notransaksi = document.getElementById('notransaksi').value;
	prd = document.getElementById('prd').value;
	unit = document.getElementById('unit').value;
	tgl1 = document.getElementById('tgl1').value;
	tgl2 = document.getElementById('tgl2').value;
	tahap = document.getElementById('tahap').value;
	param = 'proses=deleteTrans&maxRow=' + maxRow + '&notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit;
	param += '&tgl1=' + tgl1;
	param += '&tgl2=' + tgl2;
	param += '&tahap=' + tahap;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					saveAll(maxRow);
					//savebyjson(data);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function savebyjson(data) {
	notransaksi   = document.getElementById('notransaksi').value;
	prd           = document.getElementById('prd').value;
	unit          = document.getElementById('unit').value;
	afd           = document.getElementById('afd').value;
	tahap         = document.getElementById('tahap').value;
	tgl1          = document.getElementById('tgl1').value;
	tgl2          = document.getElementById('tgl2').value;
	
	if (prd == '' || unit == '' || afd == '') {
		alertify.alert("Data tidak lengkap");
		return;
	} else {
		param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&afd=' + afd +'&tahap=' + tahap;
		param += "&proses=savedata";
		param += '&tgl1=' + tgl1;
		param += '&tgl2=' + tgl2;
		param += '&data=' + JSON.stringify(data);
		tujuan = 'kebun_slave_save_3premipemanenv2.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
		//lockScreen('wait');
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {					
					alertify.alert('Done');
					document.getElementById('printContainer').innerHTML = '';
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

maxf = 0
sekarang = 1;
function saveAll(maxRow) {
	maxf = maxRow;
	loopsave(1, maxRow);
}
function loopsave(currRow, maxRow) {
	notransaksi   = document.getElementById('notransaksi').value;
	prd           = document.getElementById('prd').value;
	unit          = document.getElementById('unit').value;
	afd           = document.getElementById('afd').value;
	tahap         = document.getElementById('tahap').value;
	tgl1          = document.getElementById('tgl1').value;
	tgl2          = document.getElementById('tgl2').value;
	topografi     = document.getElementById('topografi_' + currRow).innerHTML;
	tglpnn        = document.getElementById('tglpnn_' + currRow).innerHTML;
	rowkary       = document.getElementById('rowkary_' + currRow).innerHTML;
	rowmdr        = document.getElementById('rowmdr_' + currRow).innerHTML;
	rowkrn        = document.getElementById('rowkrn_' + currRow).innerHTML;
	rowtt         = document.getElementById('rowtt_' + currRow).innerHTML;
	rowjjg        = document.getElementById('rowjjg_' + currRow).innerHTML;
	rowkg         = document.getElementById('rowkg_' + currRow).innerHTML;
	rowkgbss      = document.getElementById('rowkgbss_' + currRow).innerHTML;
	rowkglb1      = document.getElementById('rowkglb1_' + currRow).innerHTML;
	rowrplb1      = document.getElementById('rowrplb1_' + currRow).innerHTML;
	rowkgbrd      = document.getElementById('rowkgbrd_' + currRow).innerHTML;
	rowrpbrd      = document.getElementById('rowrpbrd_' + currRow).innerHTML;
	rowtopo       = document.getElementById('rowtopo_' + currRow).innerHTML;
	rowdenda      = document.getElementById('rowdenda_' + currRow).innerHTML;
	tambahan      = document.getElementById('rowtambah_' + currRow).innerHTML;
	potbrd      = document.getElementById('potbrd_' + currRow).value;
	if (prd == '' || unit == '' || afd == '') {
		alertify.alert("Data tidak lengkap");
		return;
	} else {
		param = 'notransaksi=' + notransaksi + '&prd=' + prd + '&unit=' + unit + '&afd=' + afd + '&rowkary=' + rowkary + '&rowmdr=' + rowmdr + '&rowtt=' + rowtt + '&tglpnn=' + tglpnn + '&rowjjg=' + rowjjg + '&rowkg=' + rowkg + '&rowkgbss=' + rowkgbss + '&rowkglb1=' + rowkglb1 + '&rowrplb1=' + rowrplb1 + '&rowkgbrd=' + rowkgbrd + '&rowrpbrd=' + rowrpbrd + '&rowdenda=' + rowdenda + '&rowkrn=' + rowkrn + '&topografi=' + topografi+ '&rowtopo=' + rowtopo+ '&tahap=' + tahap;
		param += "&proses=savedata";
		param += '&tgl1=' + tgl1;
		param += '&tgl2=' + tgl2;
		param += '&potbrd=' + potbrd;
		param += '&tambahan=' + tambahan;
		tujuan = 'kebun_slave_save_3premipemanenv2.php';
		post_response_text(tujuan, param, respog);
		document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
		//lockScreen('wait');
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
					unlockScreen();
				} else {
					document.getElementById('row' + currRow).style.display = 'none';
					currRow += 1;
					sekarang = currRow;
					if (currRow > maxRow) {
						alertify.alert('Done');
						document.getElementById('printContainer').innerHTML = '';
						loaddata(0);
					} else {
						loopsave(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gethitungpremi(currRow){
	perpot        = document.getElementById("perpot").value;
	kgbruto       = document.getElementById('rowkgbruto_' + currRow).innerHTML;
	potbrd        = document.getElementById('potbrd_' + currRow).value;
	basiskg       = document.getElementById('rowkgbss_' + currRow).innerHTML;
	hargalbbss    = document.getElementById('rowhargarplb1_' + currRow).innerHTML;
	premibrondol  = document.getElementById('rowrpbrd_' + currRow).innerHTML;
	premihadir    = document.getElementById('rowtopo_' + currRow).innerHTML;
	premitambah   = document.getElementById('rowtambah_' + currRow).innerHTML;
	premitambahold= document.getElementById('rowtambahold_' + currRow).innerHTML;
	denda         = document.getElementById('rowdendalama_' + currRow).value;
	hargabrondol  = document.getElementById('rowhargabrd_' + currRow).innerHTML;
	kgbruto       = remove_comma_var(kgbruto);
	potbrd        = remove_comma_var(potbrd);
	basiskg       = remove_comma_var(basiskg);
	hargalbbss    = remove_comma_var(hargalbbss);
	premibrondol  = remove_comma_var(premibrondol);
	premihadir    = remove_comma_var(premihadir);
	denda         = remove_comma_var(denda);
	hargabrondol  = remove_comma_var(hargabrondol);
	premitambahold= remove_comma_var(premitambahold);
	if(potbrd==''){potbrd=0;}
	if(hargabrondol=='' || hargabrondol==0){alertify.alert("Harga Rupiah / Kg Brondolan belum ada, silahkan tambah di Kebun - Setup - Ongkos Panen"); return;}
	
	if(perpot==1){
		kgnetto = parseFloat(kgbruto)-parseFloat(potbrd);
		document.getElementById('rowkg_' + currRow).innerHTML=numberFormat(kgnetto,2);
		
		kglbbss = parseFloat(kgnetto)-parseFloat(basiskg);
		document.getElementById('rowkglb1_' + currRow).innerHTML=numberFormat(kglbbss,2);
		 
		rppremilb = parseFloat(hargalbbss)*parseFloat(kglbbss);
		document.getElementById('rowrplb1_' + currRow).innerHTML=numberFormat(rppremilb,2);
		
		rpdendabrd = parseFloat(hargabrondol)*parseFloat(potbrd);
		//document.getElementById('rowdenda_' + currRow).innerHTML=numberFormat(rpdendabrd,2);
		document.getElementById('rowkgbrd_' + currRow).innerHTML=numberFormat(potbrd,2);
		//document.getElementById('rowrpbrd_' + currRow).innerHTML=0;
		document.getElementById('rowrpbrd_' + currRow).innerHTML=numberFormat(rpdendabrd,2);
		
		//gtotal = (parseFloat(premitambah)+parseFloat(rppremilb)+parseFloat(premibrondol)+parseFloat(premihadir)+parseFloat(rpdendabrd))-parseFloat(denda);
		gtotal = (parseFloat(premitambah)+parseFloat(rppremilb)+parseFloat(premihadir)+parseFloat(rpdendabrd))-parseFloat(denda);
		document.getElementById('gtotal_' + currRow).innerHTML=numberFormat(gtotal,2);
	}else if(perpot==3){
		kgnetto = parseFloat(kgbruto)-parseFloat(potbrd);
		document.getElementById('rowkg_' + currRow).innerHTML=numberFormat(kgnetto,2);
		
		kglbbss = parseFloat(kgnetto)-parseFloat(basiskg);
		document.getElementById('rowkglb1_' + currRow).innerHTML=numberFormat(kglbbss,2);
		 
		rppremilb = parseFloat(hargalbbss)*parseFloat(kglbbss);
		document.getElementById('rowrplb1_' + currRow).innerHTML=numberFormat(rppremilb,2);
		
		rpdendabrd = parseFloat(hargabrondol)*parseFloat(potbrd);
		document.getElementById('rowdenda_' + currRow).innerHTML=0;
		document.getElementById('rowkgbrd_' + currRow).innerHTML=0;
		document.getElementById('rowrpbrd_' + currRow).innerHTML=0;
		
		gtotal = parseFloat(premitambah)+parseFloat(rppremilb)+parseFloat(premibrondol)+parseFloat(premihadir)-parseFloat(denda);
		document.getElementById('gtotal_' + currRow).innerHTML=numberFormat(gtotal,2);
	}else{
		//kgnetto = parseFloat(kgbruto)-parseFloat(potbrd);
		//document.getElementById('rowkg_' + currRow).innerHTML=numberFormat(kgnetto,2);
		
		//kglbbss = parseFloat(kgnetto)-parseFloat(basiskg);
		//document.getElementById('rowkglb1_' + currRow).innerHTML=numberFormat(kglbbss,2);
		 
		//rppremilb = parseFloat(hargalbbss)*parseFloat(kglbbss);
		rppremilb = document.getElementById('rowrplb1_' + currRow).innerHTML;
		rppremilb = remove_comma_var(rppremilb);
		
		rpdendabrd = parseFloat(hargabrondol)*parseFloat(potbrd);
		document.getElementById('rowdenda_' + currRow).innerHTML='';
		document.getElementById('rowdenda_' + currRow).innerHTML=numberFormat((parseFloat(denda)+parseFloat(rpdendabrd)),2);
		
		gtotal = (parseFloat(premitambah)+parseFloat(rppremilb)+parseFloat(premibrondol)+parseFloat(premihadir))-(parseFloat(denda)+parseFloat(rpdendabrd));
		document.getElementById('gtotal_' + currRow).innerHTML=numberFormat(gtotal,2);
	}
	
	
	
	tglpnn     = document.getElementById('tglpnn_' + currRow).innerHTML;
	rowmdr     = document.getElementById('rowmdr_' + currRow).innerHTML;
	rowkrn     = document.getElementById('rowkrn_' + currRow).innerHTML;
	rowkary    = document.getElementById('rowkary_' + currRow).innerHTML;
	rowblok    = document.getElementById('rowblok_' + currRow).innerHTML;
	notransaksi= document.getElementById('notransaksi').value;
	prd        = document.getElementById('prd').value;
	
	param = 'proses=addpotonganbrd';
	param += '&potbrd=' + potbrd;
	param += '&blok=' + rowblok;
	param += '&tglpnn=' + tglpnn;
	param += '&rowmdr=' + rowmdr;
	param += '&rowkrn=' + rowkrn;
	param += '&rowkary=' + rowkary;
	param += '&notransaksi=' + notransaksi;
	param += '&prd=' + prd;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('recal').style.display='';
					document.getElementById('proses').style.display='none';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function zpreviewdata(val,recal) {
	param = '';
	var e = val.split('##');
	for (i = 1; i < e.length; i++) {
		var tmp = document.getElementById(e[i]);
		if (i == 1) {
			param += e[i] + "=" + getValue(e[i]);
		} else {
			param += "&" + e[i] + "=" + getValue(e[i]);
		}
	}
	param +='&recal=' + recal;
	param +='&proses=preview';
	tujuan='kebun_slave_3premipemanenv2.php';
	
	post_response_text(tujuan,param,respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('printContainer').innerHTML=con.responseText;
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function prevbrd(blok,tgl,jenis) {
	param = 'proses=prevdata';
	param += '&tgl1=' + tgl;
	param += '&blok=' + blok;
	param += '&jenis=' + jenis;
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respon);

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.minimalDialog || alertify.dialog('minimalDialog',function(){
						return {
							main:function(content){
								this.setContent(content); 
							}
						};
					});
					alertify.minimalDialog(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function pivot() {
	param  = '';
	kodeorg = document.getElementById('unitlist').value;
	periode = document.getElementById('prdlist').value;
	divisi = document.getElementById('afdlist').value;
	
	validate([
        ["prdlist","Periode tidak boleh kosong."]
    ]);
	
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&divisi=' + divisi;
	param += '&proses=pivot';
	
	tujuan = 'kebun_slave_save_3premipemanenv2.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					dt = "";
					isi = con.responseText.split("####");
					
					$(function(){
						var renderers  = $.extend($.pivotUtilities.renderers,$.pivotUtilities.subtotal_renderers,$.pivotUtilities.c3_renderers,$.pivotUtilities.plotly_renderers);
						
						var dataClass  = $.pivotUtilities.SubtotalPivotData;
						var derivers   = $.pivotUtilities.derivers;
						var my_aggregators = {
								"Integer Sum": $.pivotUtilities.aggregators["Integer Sum"],
								"Sum": $.pivotUtilities.aggregators["Sum"],
								"Sum over Sum": $.pivotUtilities.aggregators["Sum over Sum"],
								"Count": $.pivotUtilities.aggregators["Count"],
								"Count Unique Values": $.pivotUtilities.aggregators["Count Unique Values"],
								"Average": $.pivotUtilities.aggregators["Average"]
							};

						$("#contpivot").pivotUI(JSON.parse(isi[0]),{
							dataClass: dataClass,
							renderers: renderers,
							aggregators: my_aggregators,
							rows: JSON.parse(isi[1]),
							cols: JSON.parse(isi[2]),
							aggregatorName: "Integer Sum",
							vals: JSON.parse(isi[3]),
							rendererName: "Table",
							rendererOptions: {
								rowSubtotalDisplay: {
									displayOnTop: false
								}
							},
							inclusions: {"JENIS":["RP"]},
							sorters: {"DATA": $.pivotUtilities.sortAs(["JJG","KG","POT BRD (Kg)","KG LB","RP LB","RP BRD","KEHADIRAN","TAMBAHAN","DENDA"])}
						});
					});
					document.getElementById('contpivot').style.display="";
					document.getElementById('contloaddata').style.display="none";
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
