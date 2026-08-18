function getValue(id) {
	var tmp = document.getElementById(id);
	if (tmp) {
		if (tmp.options) {
			return tmp.options[tmp.selectedIndex].value;
		} else if (tmp.nodeType == 'checkbox') {
			if (tmp.checked == true) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return tmp.value;
		}
	} else {
		return false;
	}
}
function getUnit(){
	var workField = document.getElementById('unitId');
	var param = "kdPt=" + getValue('kdPt')+'&proses=getUnit';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					workField.innerHTML = con.responseText;
					 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text('keu_slave_2apschedule.php', param, respon);
}




// var showPerPage = 10;

// /* Search
//  * Filtering Data
//  */
// function searchTrans() {
// 	var notrans = document.getElementById('sNoTrans'),
// 	jenis = document.getElementById('sJenis'),
// 	where = '[["' + jenis.options[jenis.selectedIndex].value + '","' + notrans.value + '"]]';
// 	goToPages(1, showPerPage, where);
// }
// /* Paging
//  * Paging Data
//  */
// function defaultList() {
// 	goToPages(1, showPerPage);
// }
// function goToPages(page, shows, where) {
// 	if (typeof where != 'undefined') {
// 		var newWhere = where.replace(/'/g, '"');
// 	}
// 	var workField = document.getElementById('workField');
// 	var param = "page=" + page;
// 	param += "&shows=" + shows + "&tipe=KB";
// 	if (typeof where != 'undefined') {
// 		param += "&where=" + newWhere;
// 	}
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					workField.innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=showHeadList', param, respon);
// }
// function choosePage(obj, shows, where) {
// 	var pageVal = obj.options[obj.selectedIndex].value;
// 	goToPages(pageVal, shows, where);
// }
// /* Halaman Manipulasi Data
//  * Halaman add, edit, delete
//  */
// function showAdd() {
// 	var workField = document.getElementById('workField');
// 	var param = "";
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					workField.innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=showAdd', param, respon);
// }
// function showEditFromAdd() {
// 	var workField = document.getElementById('workField');
// 	var trans = document.getElementById('noinvoice');
// 	var param = "noinvoice=" + trans.value;
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					workField.innerHTML = con.responseText;
// 					showDetail();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=showEdit', param, respon);
// }
// function showEdit(num) {
// 	var workField = document.getElementById('workField');
// 	var trans = document.getElementById('noinvoice_' + num);
// 	var param = "numRow=" + num + "&noinvoice=" + trans.innerHTML;
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					workField.innerHTML = con.responseText;
// 					showDetail();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=showEdit', param, respon);
// }
// /* Manipulasi Data
//  * add, edit, delete
//  */
// function addDataTable() {
// 	// if(getValue('nopo')=='') {
// 	//     alert(notifpopilih);
// 	//     return;
// 	// }
// 	// var param = "noinvoice="+getValue('noinvoice')+"&noinvoicesupplier="+getValue('noinvoicesupplier')+"&tanggal="+getValue('tanggal')+"&tipeinvoice="+getValue('tipeinvoice');
// 	// param += "&nopo="+getValue('nopo')+"&keterangan="+getValue('keterangan')+"&nilaiinvoice="+getValue('nilaiinvoice');
// 	// param += "&jatuhtempo="+getValue('jatuhtempo')+"&nofp="+getValue('nofp');
// 	// param += "&noakun="+getValue('noakunh')+"&uangmuka="+getValue('uangmuka')+"&kodesupplier="+getValue('supplier');
// 	// param += "&matauang="+getValue('matauang')+"&kodeorg="+getValue('kodeorg')+"&unit="+getValue('unit');
// 	var file = document.getElementById("upload").files[0];
// 	var formdata = new FormData();
// 	formdata.append("file", file);
// 	formdata.append("fileupload", getValue('upload'));
// 	formdata.append("noinvoice", getValue('noinvoice'));
// 	formdata.append("noinvoicesupplier", getValue('noinvoicesupplier'));
// 	formdata.append("tanggal", getValue('tanggal'));
// 	formdata.append("tipeinvoice", getValue('tipeinvoice'));
// 	formdata.append("nopo", getValue('nopo'));
// 	formdata.append("keterangan", getValue('keterangan'));
// 	formdata.append("nilaiinvoice", getValue('nilaiinvoice'));
// 	formdata.append("jatuhtempo", getValue('jatuhtempo'));
// 	formdata.append("nofp", getValue('nofp'));
// 	formdata.append("noakun", getValue('noakun'));
// 	formdata.append("uangmuka", getValue('uangmuka'));
// 	formdata.append("kodesupplier", getValue('supplier'));
// 	formdata.append("matauang", getValue('matauang'));
// 	formdata.append("keterangan2", getValue('keterangan2'));
// 	formdata.append("tanggalinvoice", getValue('tanggalinvoice'));
// 	formdata.append("tanggalnofp", getValue('tanggalnofp'));
// 	formdata.append("npwp", getValue('npwp'));
// 	formdata.append("kodeorg", getValue('kodeorg'));
// 	formdata.append("unit", getValue('unit'));
// 	if (document.getElementById("statusdoc").checked == true) {
// 		statusdoc = '1';
// 	} else {
// 		statusdoc = '0';
// 	}
// 	formdata.append("statusdoc", statusdoc);
// 	// formdata.append("kodesupplier", getValue('kodesupplier'));
// 	// formdata.append("syaratbayar", getValue('syaratbayar'));
// 	// formdata.append("noakun", getValue('noakun'));
// 	var con = createXMLHttpRequest();
// 	con.open("POST", "keu_slave_tagihan.php?proses=add", true);
// 	con.onreadystatechange = eval(respon);
// 	con.send(formdata);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					alert('Added Data Header');
// 					//showEditFromAdd();
// 					//defaultList();
// 					showDetail();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	//post_response_text('keu_slave_tagihan.php?proses=add', param, respon);
// }
// function editDataTable() {
// 	// var param = "noinvoice="+getValue('noinvoice')+"&noinvoicesupplier="+getValue('noinvoicesupplier')+"&tanggal="+getValue('tanggal')+"&tipeinvoice="+getValue('tipeinvoice');
// 	// param += "&nopo="+getValue('nopo')+"&keterangan="+getValue('keterangan')+"&nilaiinvoice="+getValue('nilaiinvoice');
// 	// param += "&jatuhtempo="+getValue('jatuhtempo')+"&nofp="+getValue('nofp');
// 	// param += "&noakun="+getValue('noakun')+"&uangmuka="+getValue('uangmuka');
// 	// param += "&matauang="+getValue('matauang')+"&kodeorg="+getValue('kodeorg');
// 	// param += "&retensi="+getValue('retensi');
// 	var file = document.getElementById("upload").files[0];
// 	var formdata = new FormData();
// 	formdata.append("file", file);
// 	formdata.append("noinvoice", getValue('noinvoice'));
// 	formdata.append("noinvoicesupplier", getValue('noinvoicesupplier'));
// 	formdata.append("tanggal", getValue('tanggal'));
// 	formdata.append("tipeinvoice", getValue('tipeinvoice'));
// 	formdata.append("nopo", getValue('nopo'));
// 	formdata.append("keterangan", getValue('keterangan'));
// 	formdata.append("nilaiinvoice", getValue('nilaiinvoice'));
// 	formdata.append("jatuhtempo", getValue('jatuhtempo'));
// 	formdata.append("nofp", getValue('nofp'));
// 	formdata.append("nofp", getValue('nofp'));
// 	formdata.append("noakun", getValue('noakun'));
// 	formdata.append("uangmuka", getValue('uangmuka'));
// 	formdata.append("matauang", getValue('matauang'));
// 	formdata.append("keterangan2", getValue('keterangan2'));
// 	formdata.append("tanggalinvoice", getValue('tanggalinvoice'));
// 	formdata.append("tanggalnofp", getValue('tanggalnofp'));
// 	formdata.append("npwp", getValue('npwp'));
// 	formdata.append("kodeorg", getValue('kodeorg'));
// 	formdata.append("kodesupplier", getValue('supplier'));
// 	if (document.getElementById("statusdoc").checked == true) {
// 		statusdoc = '1';
// 	} else {
// 		statusdoc = '0';
// 	}
// 	formdata.append("statusdoc", statusdoc);
// 	var con = createXMLHttpRequest();
// 	con.open("POST", "keu_slave_tagihan.php?proses=edit", true);
// 	con.onreadystatechange = eval(respon);
// 	con.send(formdata);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					defaultList();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=edit', param, respon);
// }
// /*
//  * Detail
//  */
// function showDetail() {
// 	var detailField = document.getElementById('detailField');
// 	var notrans = document.getElementById('noinvoice').value;
// 	var param = "noinvoice=" + notrans + "&nopo=" + getValue('nopo') + "&tipeinvoice=" + getValue('tipeinvoice');
// 	param += "&unit=" + getValue('unit');
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					detailField.innerHTML = con.responseText;
// 					document.getElementById('matauang').disabled = true;
// 					document.getElementById('kurs').disabled = true;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan_detail.php?proses=showDetail', param, respon);
// }
// function deleteData(num) {
// 	var notrans = document.getElementById('noinvoice_' + num).innerHTML;
// 	var param = "noinvoice=" + notrans;
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					var tmp = document.getElementById('tr_' + num);
// 					tmp.parentNode.removeChild(tmp);
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=delete', param, respon);
// }
// function printPDF(ev) {
// 	// Prep Param
// 	param = "proses=pdf";
// 	showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
// 		" src='keu_slave_tagihan_print.php?" + param + "'></iframe>", '800', '400', ev);
// 	var dialog = document.getElementById('dynamic1');
// 	dialog.style.top = '50px';
// 	dialog.style.left = '15%';
// }
// /* Update No Urut di halaman absensi
//  */
// function updNoUrut() {
// 	var tabBody = document.getElementById('mTabBody');
// 	var nourut = document.getElementById('nourut');
// 	var maxNum = 0;
// 	if (tabBody.childNodes.length > 0) {
// 		for (i = 0; i < tabBody.childNodes.length; i++) {
// 			var tmp = document.getElementById('nourut_' + i);
// 			if (tmp.innerHTML > maxNum) {
// 				maxNum = tmp.innerHTML;
// 			}
// 		}
// 	}
// 	nourut.value = parseInt(maxNum) + 1;
// }
// function updPO() {
// 	type = document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
// 	if (type == 'p' || type == 'k' || type == 'ffb') {
// 		document.getElementById('matauang').disabled = true;
// 		document.getElementById('kurs').disabled = true;
// 		document.getElementById('matauang').selectedIndex = 0;
// 		document.getElementById('kurs').value = 1;
// 	} else {
// 		document.getElementById('matauang').disabled = false;
// 		document.getElementById('kurs').disabled = false;
// 		document.getElementById('matauang').selectedIndex = 0;
// 		document.getElementById('kurs').value = 1;
// 	}
// 	document.getElementById('nopo').value = '';
// 	document.getElementById('nopo').disabled = false;
// 	document.getElementById('supplier').value = '';
// 	document.getElementById('matauang').value = 'IDR';
// 	document.getElementById('kurs').value = '1';
// 	document.getElementById('nilaiinvoice').value = '0';
// 	document.getElementById('uangmuka').value = '0';
// 	document.getElementById('noakun').value = '';
// }
// function updInvoice() {
// 	var invoice = document.getElementById('nilaiinvoice');
// 	var param = "nopo=" + getValue('nopo');
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					if (con.responseText != '') {
// 						invoice.value = con.responseText;
// 						invoice.value = _formatted(invoice);
// 						invoice.setAttribute('disabled', 'disabled');
// 					} else {
// 						invoice.value = 0;
// 						invoice.removeAttribute('disabled');
// 					}
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=updInvoice', param, respon);
// }
// //jamhari
// function searchNopo(title, ev, langCari) {
// 	isi = document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].value;
// 	tipe = document.getElementById('tipeinvoice').options[document.getElementById('tipeinvoice').selectedIndex].text;
// 	tanggal = document.getElementById('tanggal').value;
// 	if (tanggal == '') {
// 		alert(notiftagihtanggal);
// 		return;
// 	}
// 	cekDtPo(langCari, title, ev);
// }
// function viewDetailData2(numRow, ev) {
// 	// Prep Param
// 	var noinvoice = document.getElementById('noinvoice_' + numRow).getAttribute('value');
// 	param = 'noinv=' + noinvoice + '&proses=getDetail';
// 	showDetailData(noinvoice);
// 	tujuan = 'keu_slave_2tagihan.php';
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//alert(con.responseText);
// 					document.getElementById('containerData').innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function viewDetailData(noinvoice) {
// 	param = 'noinv=' + noinvoice + '&proses=getDetail';
// 	showDetailData(noinvoice);
// 	tujuan = 'keu_slave_2tagihan.php';
// 	post_response_text(tujuan, param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//alert(con.responseText);
// 					document.getElementById('containerData').innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function showDetailData(noinvoice) {
// 	width = '500px';
// 	height = '450px';
// 	content = "<div id=containerData></div>";
// 	ev = 'event';
// 	title = noinvoice;
// 	showDialog1(title, content, width, height, ev);
// }
// function findNopo() {
// 	txt = trim(document.getElementById('no_brg').value);
// 	jnsInvoice = document.getElementById('tipeinvoice').value;
// 	tanggal = document.getElementById('tanggal').value;
// 	unit = document.getElementById('unit').value;
// 	//document.getElementById('tipeinvoice').disabled=true;
// 	param = 'txtfind=' + txt + '&jnsInvoice=' + jnsInvoice + '&tanggal=' + tanggal + '&unit=' + unit;
// 	if (jnsInvoice == 'um') {
// 		jeniscari = document.getElementById('jeniscari');
// 		jeniscari = jeniscari.options[jeniscari.selectedIndex].value;
// 		param += '&jeniscari=' + jeniscari;
// 	}
// 	tujuan = 'keu_slave_getpotagihan.php';
// 	post_response_text(tujuan + '?' + 'proses=getPo', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//alert(con.responseText);
// 					document.getElementById('container2').innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function cekDtPo(langCari, title, ev) {
// 	jnsInvoice = document.getElementById('tipeinvoice').value;
// 	tanggal = document.getElementById('tanggal').value;
// 	param = 'jnsInvoice=' + jnsInvoice;
// 	tujuan = 'keu_slave_tagihan_detail.php';
// 	post_response_text(tujuan + '?' + 'proses=cekData', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					if (parseInt(con.responseText) != 0) {
// 						doc = "No. ";
// 						content = "<fieldset><legend>" + langCari + " " + tipe + "</legend>" + langCari +
// 							" " + doc + "<input type=text class=myinputtext id=no_brg>";
// 						contentjenis = "<select id=jeniscari style='width:150px'><option value='k'>Contractor</option><option value='p'>PO</option></select>";
// 						if (jnsInvoice == 'um') {
// 							content = content + contentjenis + "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
// 						} else {
// 							content = content + "<button class=mybutton onclick=findNopo()>Find</button></fieldset><div id=container2></div>";
// 						}
// 						content = content + "<input type='hidden' id='jnsInvoice' value=" + isi + ">";
// 						width = '500';
// 						height = '400';
// 						showDialog1(title + tipe, content, width, height, ev);
// 						findNopo();
// 					} else {
// 						document.getElementById('nopo').value = '';
// 						document.getElementById('nopo').disabled = true;
// 					}
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function setPo(np, nilai, jns, ppn, namasupplier, noakun, untdt, matauang, kurs) {
// 	document.getElementById('nopo').value = np;
// 	document.getElementById('nilaiinvoice').value = nilai;
// 	document.getElementById('tipeinvoice').disabled = false;
// 	jk = document.getElementById('supplier');
// 	for (x = 0; x < jk.length; x++) {
// 		if (jk.options[x].value == namasupplier) {
// 			jk.options[x].selected = true;
// 		}
// 	}
// 	jkunit = document.getElementById('unit');
// 	for (x = 0; x < jkunit.length; x++) {
// 		if (untdt != '') {
// 			if (jkunit.options[x].value == untdt) {
// 				jkunit.options[x].selected = true;
// 			}
// 		}
// 	}
// 	//document.getElementById('supplier').value=namasupplier;
// 	if (typeof matauang != 'undefined') {
// 		document.getElementById('matauang').value = matauang;
// 	}
// 	if (typeof kurs != 'undefined') {
// 		document.getElementById('kurs').value = kurs;
// 	}
// 	closeDialog();
// 	getnpwp();
// 	//document.getElementById('noakun').value=noakun;
// }
// function postingData(row) {
// 	noinvoice = document.getElementById('noinvoice_' + row).innerHTML;
// 	param = 'noinvoice=' + noinvoice;
// 	tujuan = 'keu_slave_tagihanPosting.php';
// 	if (confirm(notifpostingpenagihan))
// 		post_response_text(tujuan + '?' + 'proses=getPo', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//alert(con.responseText);
// 					x = document.getElementById('tr_' + row);
// 					//x.cells[6].innerHTML=''
// 					x.cells[12].innerHTML = "<img class='zImgBtn' title=Lengkap' src='images/skyblue/posted.png'>";
// 					x.cells[11].innerHTML = '';
// 					x.cells[10].innerHTML = ''
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function detailPDF(numRow, ev) {
// 	// Prep Param
// 	var noinvoice = document.getElementById('noinvoice_' + numRow).getAttribute('value');
// 	param = "proses=pdf&noinvoice=" + noinvoice;
// 	showDialog1('Print PDF', "<iframe frameborder=0 style='width:795px;height:400px'" +
// 		" src='keu_slave_tagihan_print_detail.php?" + param + "'></iframe>", '', '', ev);
// 	var dialog = document.getElementById('dynamic1');
// 	dialog.style.top = '50px';
// 	dialog.style.left = '15%';
// }
// function detailFile(noinvoice, ev) {
// 	// Prep Param
// 	param = "proses=file&noinvoice=" + noinvoice;
// 	showDialog1('Print File', "<iframe frameborder=0 style='width:795px;height:400px'" +
// 		" src='keu_slave_tagihan_print_detail.php?" + param + "'></iframe>", '800', '400', ev);
// 	var dialog = document.getElementById('dynamic1');
// 	dialog.style.top = '50px';
// 	dialog.style.left = '15%';
// }
// function deleteFile(noinvoice) {
// 	param = 'noinvoice=' + noinvoice;
// 	tujuan = 'keu_slave_tagihan.php';
// 	if (confirm('Anda yakin hapus file?'))
// 		post_response_text(tujuan + '?' + 'proses=deletefile', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					document.getElementById('divFile1').style.display = "none";
// 					document.getElementById('divFile2').style.display = "none";
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function getAknAsset() {
// 	kdasset = document.getElementById('kodeasset');
// 	kdasset = kdasset.options[kdasset.selectedIndex].value;
// 	param = 'kdasset=' + kdasset;
// 	tujuan = 'keu_slave_tagihan_detail.php';
// 	post_response_text(tujuan + '?' + 'proses=getAknAsset', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					kdakn = document.getElementById('noakun');
// 					for (i = 0; i < kdakn.length; i++) {
// 						if (kdakn.options[i].value == con.responseText) {
// 							kdakn.options[i].selected = true;
// 						}
// 					}
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function getunit(kodeorg) {
// 	kdpt = kodeorg.value;
// 	param = 'kdpt=' + kdpt + '&proses=getunit';
// 	post_response_text('keu_slave_tagihan.php', param, respon);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					// === Success Response
// 					document.getElementById('unit').innerHTML = con.responseText;
// 					getnpwp();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function getnpwp(supplier) {
// 	kodeorg = document.getElementById('kodeorg').value;
// 	param = 'kodeorg=' + kodeorg + '&proses=getnpwp';
// 	post_response_text('keu_slave_tagihan.php', param, respon);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					// === Success Response
// 					document.getElementById('npwp').innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function getpajak() {
// 	noakun = document.getElementById('noakun');
// 	noakun = noakun.options[noakun.selectedIndex].value;
// 	nilaiinvoice = document.getElementById('nilaiinvoice').value;
// 	param = 'noakun=' + noakun + '&nilaiinvoice=' + nilaiinvoice;
// 	tujuan = 'keu_slave_tagihan_detail.php';
// 	post_response_text(tujuan + '?' + 'proses=getpajak', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					dt = con.responseText.split("##");
// 					pajak = dt[0];
// 					nilaiinvoice = dt[1];
// 					nilai = pajak * nilaiinvoice;
// 					// alert(pajak+' '+nilaiinvoice+' '+nilai);
// 					document.getElementById('pajak').value = pajak;
// 					document.getElementById('nilai').value = nilai;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function postingData(row) {
// 	noinvoice = document.getElementById('noinvoice_' + row).innerHTML;
// 	param = 'noinvoice=' + noinvoice;
// 	tujuan = 'keu_slave_tagihanPosting.php';
// 	if (confirm(notifpostingpenagihan))
// 		post_response_text(tujuan + '?' + 'proses=getPo', param, respog);
// 	function respog() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//alert(con.responseText);
// 					x = document.getElementById('tr_' + row);
// 					//x.cells[6].innerHTML=''
// 					x.cells[12].innerHTML = "<img class='zImgBtn' title=Lengkap' src='images/skyblue/posted.png'>";
// 					x.cells[11].innerHTML = '';
// 					x.cells[10].innerHTML = ''
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }
// function fakturpajak(row) {
// 	noinvoice = document.getElementById('noinvoice_' + row).innerHTML;
// 	content = "<div id=formpost  style=\"height:10px;width:325px;\"></div>";
// 	//content+="<div id=formCariBarang></div>";
// 	title = 'Faktur Pajak';
// 	height = '40';
// 	width = '325';
// 	showDialog2(title, content, width, height, 'event');
// 	getformfp(noinvoice, row);
// }
// function getformfp(noinvoice, row) {
// 	var param = "noinvoice=" + noinvoice + "&row=" + row;
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					document.getElementById('formpost').innerHTML = con.responseText;
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=showformfp', param, respon);
// }
// function savefp(noinvoice, row) {
// 	historynofp = document.getElementById('historynofp').value;
// 	historytanggalfp = document.getElementById('historytanggalfp').value;
// 	param = "noinvoice=" + noinvoice + "&row=" + row + "&historynofp=" + historynofp + "&historytanggalfp=" + historytanggalfp;
// 	//alert(param);
// 	if (historynofp == '') {
// 		alert('Factur Number must be filled');
// 		return;
// 	}
// 	if (historytanggalfp == '') {
// 		alert('Date must be filled');
// 		return;
// 	}
// 	post_response_text('keu_slave_tagihan.php?proses=savefp', param, respon);
// 	function respon() {
// 		if (con.readyState == 4) {
// 			if (con.status == 200) {
// 				busy_off();
// 				if (!isSaveResponse(con.responseText)) {
// 					alert(con.responseText);
// 				} else {
// 					//=== Success Response
// 					//alert('Posting Berhasil');
// 					x = document.getElementById('tr_' + row);
// 					x.cells[15].innerHTML = '';
// 					closeDialog();
// 				}
// 			} else {
// 				busy_off();
// 				error_catch(con.status);
// 			}
// 		}
// 	}
// }