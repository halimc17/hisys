var timeOutTracking;

function loadmenu(){
	document.getElementById('menumap').style.display = 'block';
}

function hiddenmenu(){
	document.getElementById('menumap').style.display = 'none';
}

function checkMarkList(id){
	mark = document.getElementById('MARK_'+id.value);
	if(id.checked){
		if(mark.value == '1'){
			document.getElementById('SVGLOCATION_'+id.value).style.display = '';
		}else{
			param = 'method=checkedmap&vChecked='+id.value;
			tujuan = 'bi_slave_map.php';
			function respog(){
				if(con.readyState==4){
					if(con.status == 200){
						busy_off();
						if(!isSaveResponse(con.responseText)){
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							// document.getElementById('svgposition').innerHTML=con.responseText;
							document.getElementById('SVGLOCATION_'+id.value).innerHTML=con.responseText;
							mark.value = '1';
						}
					}else{
						busy_off();
						error_catch(con.status);
					}
				}	
			}
			post_response_text(tujuan, param, respog);
		} 
	}else{
		document.getElementById('SVGLOCATION_'+id.value).style.display = 'none';
	}
}

function getkebun() {
	kodept = document.getElementById('kodept');
	kodept = kodept.options[kodept.selectedIndex].value;
	if (kodept == '') {
		clearTracking();
		clearKodePt();
	} else {
		param = 'method=getkebun&kodept=' + kodept;
		tujuan = 'bi_slave_map.php';

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();

					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						document.getElementById('trkebun').style.display = '';
						document.getElementById('kebun').innerHTML = con.responseText;
						
						getdetailkebun();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}

		post_response_text(tujuan, param, respog);
	}
}

function getdetailkebun() {
	clearTracking();
	clearInterval(timeOutTracking);
	drResize('pane');

	kodept = document.getElementById('kodept');
	kodept = kodept.options[kodept.selectedIndex].value;
	kebun = document.getElementById('kebun');
	kebun = kebun.options[kebun.selectedIndex].value;
	if (kebun == '') {
		clearEstate();
	} else {
		param = 'method=getdetailkebun&kodept=' + kodept + '&kebun=' + kebun;
		tujuan = 'bi_slave_map.php';

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();

					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						// vSplit = con.responseText.split("####");
						vSplit = JSON.parse(con.responseText);
						
						document.getElementById('trdetail').style.display = '';
						document.getElementById('detailpt').style.display = '';
						
						document.getElementById('svgPt').innerHTML = vSplit[0];
						document.getElementById('detailpt').innerHTML = vSplit[1];
						document.getElementById('detailmap').innerHTML = vSplit[2];
						document.getElementById('pane').style.minHeight = '75px';
						document.getElementById('pane').style.height = '75px';
						document.getElementById('addons').style.display = '';
						document.getElementById('svgDetail').innerHTML = '';
						
						var realZoom = panZoom.getSizes().realZoom;
						xx = (vSplit[3]);
						yy = (vSplit[4]);
						panZoom.pan({
							x: -(xx * realZoom) + (panZoom.getSizes().width / 2),
							y: -(yy * realZoom) + (panZoom.getSizes().height / 2)
						});
						panZoom.zoom((251), false);
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}

		post_response_text(tujuan, param, respog);
		
		document.getElementById('showstatusblok').value = 0;
	}
}

function getdetailkebun2(callback = null) {
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	
	//Show detail PT
	param = 'method=getdetailkebun&kodept='+kodept+'&kebun='+kebun;
	tujuan = 'bi_slave_map.php';
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('svgPt').innerHTML=vSplit[0];
					document.getElementById('detailpt').innerHTML=vSplit[1];
					resetactivity();

					if (typeof callback === 'function') {
						callback();
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);    
}

function checkMarkListPt(id){
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	if(id.checked){
		document.getElementById(id.value).style.display = '';
	}else{
		document.getElementById(id.value).style.display = 'none';
	}
}

// function showinfosvg(idsvg,tipesvg,ev,siklus){
	// clearTemporary();
	// if(isClicked){
		// return;
	// }
	// tempStrokeWidth = document.getElementById(idsvg).style.strokeWidth;
	// tempStrokeColor = document.getElementById(idsvg).style.stroke;
	// document.getElementById('tempId').value=idsvg;
	// document.getElementById('tempWidth').value=tempStrokeWidth;
	// document.getElementById('tempColor').value=tempStrokeColor;
	// // var realZoom= panZoom.getSizes().realZoom;
	// // document.getElementById('showscale').innerHTML=realZoom;
	// showstatusblok = document.getElementById('showstatusblok').value;
	// detInfo = "";
	
	// if(showstatusblok == 1){
		// kebun=document.getElementById('kebun');
		// kebun=kebun.options[kebun.selectedIndex].value;
		// periodeawal=document.getElementById('periodeawal2');
		// periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
		// periodeakhir=document.getElementById('periodeakhir2');
		// periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
		// detaillaporan2=document.getElementById('detaillaporan2');
		// detaillaporan2=detaillaporan2.options[detaillaporan2.selectedIndex].value;
		// namafile = document.getElementById('namafile2').value;
		
		// param = 'type=detail&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&detaillaporan='+detaillaporan2+'&idsvg='+idsvg;
		// tujuan = namafile;
		
		// function respog(){
			// if(con.readyState==4){
				// if(con.status == 200){
					// busy_off();
					// if(!isSaveResponse(con.responseText)){
						// alert('ERROR TRANSACTION,\n' + con.responseText);
					// }else{
						// showdetailsvg(idsvg,tipesvg,con.responseText,ev);
					// }
				// }else{
					// busy_off();
					// error_catch(con.status);
				// }
			// }	
		// }
		// post_response_text(tujuan, param, respog);
	// }else if(showstatusblok == 3){
		// kebun=document.getElementById('kebun');
		// kebun=kebun.options[kebun.selectedIndex].value;
		// periodeawal=document.getElementById('periodeawal3');
		// periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
		// periodeakhir=document.getElementById('periodeakhir3');
		// periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
		// kegiatan=document.getElementById('detailkegiatan3');
		// kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
		// noakun=document.getElementById('noakun3');
		// noakun=noakun.options[noakun.selectedIndex].value;
		// detaillaporan=document.getElementById('detaillaporan3').value;
		
		// param = 'type=detail&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&kegiatan='+kegiatan+'&noakun='+noakun+'&detaillaporan='+detaillaporan+'&idsvg='+idsvg;
		// tujuan = 'bi_map_siklus.php';
		
		// function respog(){
			// if(con.readyState==4){
				// if(con.status == 200){
					// busy_off();
					// if(!isSaveResponse(con.responseText)){
						// alert('ERROR TRANSACTION,\n' + con.responseText);
					// }else{
						// showdetailsvg(idsvg,tipesvg,con.responseText,ev);
					// }
				// }else{
					// busy_off();
					// error_catch(con.status);
				// }
			// }	
		// }
		// post_response_text(tujuan, param, respog);
	// }else{
		// showdetailsvg(idsvg,tipesvg,'',ev);
	// }
	// document.getElementById(idsvg).style.strokeWidth = '5';
	// document.getElementById(idsvg).style.stroke = '#FFFF00';
// }

function showinfosvg(idsvg, tipesvg, ev, siklus) {
	document.getElementById('informasi').innerHTML = '';
	
	clearTemporary();
	
	tempStrokeWidth = document.getElementById(idsvg).style.strokeWidth;
	tempStrokeColor = document.getElementById(idsvg).style.stroke;

	document.getElementById('tempId').value = idsvg;
	document.getElementById('tempWidth').value = tempStrokeWidth;
	document.getElementById('tempColor').value = tempStrokeColor;
	document.getElementById(idsvg).style.strokeWidth = '5';
	document.getElementById(idsvg).style.stroke = '#FFFF00';
	
	kodept = document.getElementById('kodept');
	kodept = kodept.options[kodept.selectedIndex].value;
	kebun = document.getElementById('kebun');
	kebun = kebun.options[kebun.selectedIndex].value;

	//area addons informasi
	drResize('pane3');
	
	document.getElementById('addons3').style.display = '';	
	
	showstatusblok = document.getElementById('showstatusblok').value;
	if (showstatusblok == 1) {
		periodeawal = document.getElementById('periodeawal2');
		periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
		periodeakhir = document.getElementById('periodeakhir2');
		periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
		detaillaporan2 = document.getElementById('detaillaporan2');
		detaillaporan2 = detaillaporan2.options[detaillaporan2.selectedIndex].value;
		namafile = document.getElementById('namafile2').value;

		param = 'type=detail&periodeawal=' + periodeawal + '&periodeakhir=' + periodeakhir + '&kebun=' + kebun + '&detaillaporan=' + detaillaporan2 + '&idsvg=' + idsvg;
		tujuan = namafile;
		
		function respog() {
			if (con.readyState==4) {
				if (con.status == 200) {
					busy_off();

					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						showdetailsvg(idsvg, tipesvg, con.responseText, kodept, kebun, showstatusblok, ev);
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}

		post_response_text(tujuan, param, respog);
	} else if (showstatusblok == 3) {
		periodeawal = document.getElementById('periodeawal3');
		periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
		periodeakhir = document.getElementById('periodeakhir3');
		periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
		kegiatan = document.getElementById('detailkegiatan3');
		kegiatan = kegiatan.options[kegiatan.selectedIndex].value;
		noakun = document.getElementById('noakun3');
		noakun = noakun.options[noakun.selectedIndex].value;
		detaillaporan = document.getElementById('detaillaporan3').value;

		param = 'type=detail&periodeawal=' + periodeawal + '&periodeakhir=' + periodeakhir + '&kebun=' + kebun + '&kegiatan=' + kegiatan + '&noakun=' + noakun + '&detaillaporan=' + detaillaporan + '&idsvg=' + idsvg;
		tujuan = 'bi_map_siklus.php';
		
		function respog() {
			if (con.readyState==4) {
				if (con.status == 200) {
					busy_off();

					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						showdetailsvg(idsvg,tipesvg,con.responseText,kodept,kebun,showstatusblok,ev);
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}

		post_response_text(tujuan, param, respog);
	} else {
		showdetailsvg(idsvg, tipesvg, '', kodept, kebun, showstatusblok, ev);
	}
}

function showdetailsvg(idsvg, tipesvg, detail, kodept, kebun, showstatusblok, ev) {
	param = 'method=showinfosvg&idsvg=' + idsvg + '&tipesvg=' + tipesvg + '&detailreport=' + detail + '&kodept=' + kodept + '&kebun=' + kebun + '&showstatusblok=' + showstatusblok;
	tujuan = 'bi_slave_map.php';

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();

				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('informasi').innerHTML = con.responseText;
				}

				if (showstatusblok == 1 || showstatusblok == 3) {
					tabActionBI(document.getElementById('tabFRM2'), 2, 'FRM', 2, 'skyblue');
				}

				resizePopUp3('pane3', ev);
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}

	post_response_text(tujuan, param, respog);
}

function clearTemporary(){
	idsvg = document.getElementById('tempId').value;
	width = document.getElementById('tempWidth').value;
	color = document.getElementById('tempColor').value;
	
	if(idsvg!==''){
		document.getElementById('tempId').value = '';
		document.getElementById('tempWidth').value = '';
		document.getElementById('tempColor').value = '';
		document.getElementById(idsvg).style.strokeWidth = width;
		document.getElementById(idsvg).style.stroke = color;
	}
}

function resizePopUp3(id,ev){
	heightdivInformasi = document.getElementById('tblInformasi').clientHeight;
	widthdivInformasi = document.getElementById('tblInformasi').clientWidth;
	pane = document.getElementById(id);
	pane.style.width = (10 + widthdivInformasi)+'px';
	pane.style.minWidth = (10 + widthdivInformasi)+'px';
	pane.style.height = (40 + heightdivInformasi)+'px';
	pane.style.minHeight = (40 + heightdivInformasi)+'px';
	
	pos = new Array();
	pos = getMouseP(ev);
	
	// if((pos[1] + (40 + heightdivInformasi)) >= 620){
		// pane.style.top = (pos[1]-(40 + heightdivInformasi)) + 'px';
	// }else{
		pane.style.top = pos[1] + 'px';
	// }
	if((pos[0] - (10 + widthdivInformasi)) < 0){
		pane.style.left = (pos[0]) +'px';
	}else{
		pane.style.left = (pos[0] - (10 + widthdivInformasi)) +'px';
	}
}

function resizePopUp31(){
	heightdivInformasi = document.getElementById('tblInformasi').clientHeight;
	widthdivInformasi = document.getElementById('tblInformasi').clientWidth;
	pane = document.getElementById('pane3');
	pane.style.width = (10 + widthdivInformasi)+'px';
	pane.style.minWidth = (10 + widthdivInformasi)+'px';
	pane.style.height = (40 + heightdivInformasi)+'px';
	pane.style.minHeight = (40 + heightdivInformasi)+'px';
}

// function showdetailsvg(idsvg,tipesvg,detail,ev){
	// param = 'method=showinfosvg&idsvg='+idsvg+'&tipesvg='+tipesvg+'&detailreport='+detail;
	
    // showDialogBi('Informasi',"<iframe frameborder=0 style='width:520px;height:200px'"+
    // "src='bi_slave_map.php?"+param+"'></iframe>",'525','200',ev);	
    // var dialog = document.getElementById('dynamic1');
	
	// pos = new Array();
	// pos = getMouseP(ev);
	// if((pos[1] + 200) >= 600){
		// dialog.style.top = (pos[1]-210) + 'px';
	// }else{
		// dialog.style.top = pos[1] + 'px';
	// }
	// if((pos[0] - 500) < 0){
		// dialog.style.left = (pos[0]) +'px';
	// }else{
		// dialog.style.left = (pos[0] - 500) +'px';
	// }
	// dialog.style.display='';
// }

function preview(){
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	periodeawal=document.getElementById('periodeawal');
    periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir=document.getElementById('periodeakhir');
    periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
	detailtipedokumen=document.getElementById('detailtipedokumen');
    detailtipedokumen=detailtipedokumen.options[detailtipedokumen.selectedIndex].value;
	detailkegiatan=document.getElementById('detailkegiatan');
    detailkegiatan=detailkegiatan.options[detailkegiatan.selectedIndex].value;
	
	param = 'method=preview&kebun='+kebun+'&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&detailkegiatan='+detailkegiatan+'&detailtipedokumen='+detailtipedokumen;
	tujuan = 'bi_slave_map.php';
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('svgDetail').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function getChkDetail() {
	let chkdetail = document.getElementById('chkdetail');
	chkdetail = chkdetail.options[chkdetail.selectedIndex].value;

	clearChkDetail();
	// if(chkdetail==''){
	// }else{
		// getdetailkebun2();
	// }
}

function checkChkTipe(){
	chkKegiatan = document.getElementById('chkKegiatan');
	chkLaporan = document.getElementById('chkLaporan');
	
	if(chkKegiatan.checked){
		document.getElementById('divChkKegiatan').style.display = '';
		document.getElementById('divChkLaporan').style.display = 'none';
	}else{
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = '';
	}
	document.getElementById('svgDetail').innerHTML = '';
}

function preview2() {
	kebun = document.getElementById('kebun');
	kebun = kebun.options[kebun.selectedIndex].value;
	periodeawal = document.getElementById('periodeawal2');
	periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir = document.getElementById('periodeakhir2');
	periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
	detaillaporan2 = document.getElementById('detaillaporan2');
	detaillaporan2 = detaillaporan2.options[detaillaporan2.selectedIndex].value;
	namafile = document.getElementById('namafile2').value;

	if (namafile == '') {
		alert("Laporan harus dipilih.");
		return;
	}

	param = 'type=preview&periodeawal=' + periodeawal + '&periodeakhir=' + periodeakhir + '&kebun=' + kebun + '&detaillaporan=' + detaillaporan2;
	tujuan = namafile;

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();

				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					splitArr = con.responseText.split("####");
					var list = splitArr[0];
					var arrlist = new Array();
					arrlist = JSON.parse(list);
					for (var key in arrlist) {
						if (arrlist.hasOwnProperty(key)) {
							document.getElementById(arrlist[key]['idsvg']).style.fill = arrlist[key]['warna'];
							var object = document.getElementById(arrlist[key]['idsvg']);
							// object.setAttribute("onmousedown", 'isClicked=false');
							// object.setAttribute("onmousemove", 'isClicked=true');
							// object.onmouseup("onmousemove", "showinfosvg('"+arrlist[key]['idsvg']+"',1,'event')");
							object.setAttribute("fill-opacity", 1);
						}
					}
				}

				document.getElementById('divLegend').style.display = '';
				document.getElementById('divLegend').innerHTML = splitArr[1];
				document.getElementById('showstatusblok').value = 1;
				if (detaillaporan2 == 'LAP0000007') document.getElementById('showstatusblok').value = 0;

				heightdivLegend = document.getElementById('divLegend').clientHeight;
				heightdivChkLaporan = document.getElementById('divChkLaporan').clientHeight;

				document.getElementById('pane').style.height = (75 + heightdivChkLaporan + heightdivLegend) + 'px';
				document.getElementById('pane').style.minHeight = (75 + heightdivChkLaporan + heightdivLegend) + 'px';
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}

	post_response_text(tujuan, param, respog);
}

function resetactivity(){
	document.getElementById('showstatusblok').value = 0;
	document.getElementById('periodeawal').selectedIndex = '0';
	document.getElementById('periodeakhir').selectedIndex = '0';
	document.getElementById('detailtipedokumen').selectedIndex = '0';
	document.getElementById('periodeawal2').selectedIndex = '0';
	document.getElementById('periodeakhir2').selectedIndex = '0';
	document.getElementById('detaillaporan2').selectedIndex = '0';
	document.getElementById('periodeawal3').selectedIndex = '0';
	document.getElementById('periodeakhir3').selectedIndex = '0';
	document.getElementById('detailkegiatan3').selectedIndex = '0';
	getdetailkegiatan();
}

function resetestate(){
	clearPopUpDetail();
	closeDialogPopUpSvg('addons2');
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	detailpt = document.getElementById('detailpt');
	
	if(kebun==''){
		document.getElementById('trdetail').style.display = 'none';
		detailpt.style.display = 'none';
		document.getElementById('svgPt').innerHTML = '';
		document.getElementById('addons').style.display = 'none';
		detailpt.innerHTML="";
		document.getElementById('svgDetail').innerHTML='';
	}else{
		detailpt.style.display = '';		
		param = 'method=getdetailkebun&kodept='+kodept+'&kebun='+kebun;
		tujuan = 'bi_slave_map.php';
		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						document.getElementById('svgPt').innerHTML=vSplit[0];
						detailpt.innerHTML=vSplit[1];
						
						//bisst
						document.getElementById('divLegend').style.display = 'none';
						document.getElementById('divLegend').innerHTML = '';
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}
		post_response_text(tujuan, param, respog);    
	}
}

function isifile(namafile,ev){
    param = 'method=isifile&namafile='+namafile;
    title="Data Detail";
	showDialogBi2(title,"<iframe frameborder=0 style='width:795px;height:395px'"+
    " src='bi_slave_map.php?"+param+"'></iframe>",'800','400',ev);	
    var dialog = document.getElementById('dynamic4');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

// function preview3(){
// 	getdetailkebun2();
// 	// getdetailkebun2(function(){
// 		kebun=document.getElementById('kebun');
// 		kebun=kebun.options[kebun.selectedIndex].value;
// 		periodeawal=document.getElementById('periodeawal3');
// 		periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
// 		periodeakhir=document.getElementById('periodeakhir3');
// 		periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;

// 		detaillaporan3=document.getElementById('detaillaporan3').value;
// 		noakun=document.getElementById('noakun3');
// 		noakun=noakun.options[noakun.selectedIndex].value;
// 		kegiatan=document.getElementById('detailkegiatan3');
// 		kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
// 		namafile = document.getElementById('namafile3').value;
// 		param = 'type=preview&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&detaillaporan='+detaillaporan3+'&kegiatan='+kegiatan+'&noakun='+noakun;
// 		tujuan = namafile;
		
// 		function clearColorDetail(mapid,callback){
// 			var map = document.getElementById(mapid);
// 			path = map.getElementsByTagName("path");
// 			for(i=0; i<path.length; i++){
// 				path[i].style.fill = null;
// 				path[i].setAttribute("fill-opacity", 0.4);
// 			}
// 			(function(){
// 				callback();
// 			})();
// 		}
// 		function respog(){
// 			if(con.readyState==4){
// 				if(con.status == 200){
// 					busy_off();
// 					if(!isSaveResponse(con.responseText)){
// 						alert('ERROR TRANSACTION,\n' + con.responseText);
// 					}else{
// 						splitArr = con.responseText.split("####");
// 						var list = splitArr[0];
// 						var titpePeta = splitArr[2];
// 						var arrlist = new Array();
// 						arrlist = JSON.parse(list);
// 						clearColorDetail(titpePeta,function(){
// 							if(arrlist.length > 0){
// 								for(var key in arrlist){
// 									if (arrlist.hasOwnProperty(key)){
// 										document.getElementById(arrlist[key]['idsvg']).style.fill = arrlist[key]['warna'];
// 										var object = document.getElementById(arrlist[key]['idsvg']);
// 										object.setAttribute("fill-opacity", 1);
// 									}
// 								}
// 							}
// 						});
						
						
// 						document.getElementById('divLegend').style.display = '';
// 						document.getElementById('divLegend').innerHTML = splitArr[1];
// 						document.getElementById('showstatusblok').value = 3;
// 						heightdivLegend = document.getElementById('divLegend').clientHeight;
// 						heightdivChkSiklus = document.getElementById('divChkSiklus').clientHeight;
// 						document.getElementById('pane').style.height = (75 + heightdivChkSiklus + heightdivLegend)+'px';
// 						document.getElementById('pane').style.minHeight = (75 + heightdivChkSiklus + heightdivLegend)+'px';
// 					}
// 				}else{
// 					busy_off();
// 					error_catch(con.status);
// 				}
// 			}	
// 		}
// 		post_response_text(tujuan, param, respog);
// 	// });
// }

function preview3() {
	kebun = document.getElementById('kebun');
	kebun = kebun.options[kebun.selectedIndex].value;
	periodeawal = document.getElementById('periodeawal3');
	periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir = document.getElementById('periodeakhir3');
	periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
	detaillaporan3 = document.getElementById('detaillaporan3').value;
	noakun = document.getElementById('noakun3');
	noakun = noakun.options[noakun.selectedIndex].value;
	kegiatan = document.getElementById('detailkegiatan3');
	kegiatan = kegiatan.options[kegiatan.selectedIndex].value;
	namafile = document.getElementById('namafile3').value;

	param = 'type=preview&periodeawal=' + periodeawal + '&periodeakhir=' + periodeakhir + '&kebun=' + kebun + '&detaillaporan=' + detaillaporan3 + '&kegiatan=' + kegiatan + '&noakun=' + noakun;
	tujuan = namafile;

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();

				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					splitArr = con.responseText.split("####");
					var list = splitArr[0];
					var arrlist = new Array();
					arrlist = JSON.parse(list);
					for (var key in arrlist) {
						if (arrlist.hasOwnProperty(key)) {
							document.getElementById(arrlist[key]['idsvg']).style.fill = arrlist[key]['warna'];
							var object = document.getElementById(arrlist[key]['idsvg']);
							// object.setAttribute("onmousedown", 'isClicked=false');
							// object.setAttribute("onmousemove", 'isClicked=true');
							// object.onmouseup("onmousemove", "showinfosvg('"+arrlist[key]['idsvg']+"',1,'event')");
							object.setAttribute("fill-opacity", 1);
						}
					}
				}

				document.getElementById('divLegend').style.display = '';
				document.getElementById('divLegend').innerHTML = splitArr[1];
				document.getElementById('showstatusblok').value = 3;

				heightdivLegend = document.getElementById('divLegend').clientHeight;
				heightdivChkSiklus = document.getElementById('divChkSiklus').clientHeight;

				document.getElementById('pane').style.height = (75 + heightdivChkSiklus + heightdivLegend) + 'px';
				document.getElementById('pane').style.minHeight = (75 + heightdivChkSiklus + heightdivLegend) + 'px';
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}

	post_response_text(tujuan, param, respog);
}

function CreateSVGIndonesia(typeload,url,callback){
		if(typeload == "indonesia"){
			url = "images/indonesia.svg";
		}
		var ajax = new XMLHttpRequest();
		ajax.open("GET", url, true);
		ajax.send();
		ajax.onload = function(e) {
			if(typeload == "svg" || typeload == "indonesia"){
				Node = new DOMParser().parseFromString(ajax.responseText, "image/svg+xml");
				mapCache = Node.getElementsByTagName("svg")[0];
				console.log(mapCache);
				if(typeload == "indonesia"){
					var bodyCanvas = document.getElementById("home_map");
					bodyCanvas.appendChild(mapCache);
					var svgEle = document.getElementById("owl-map");
					
					//window.onload = function() {
					window.panZoom = window.panZoom = svgPanZoom(svgEle, {
						zoomEnabled: true,
						controlIconsEnabled: true,
						fit: 1,
						center: 1
					});
					//};
					
					window.onresize = function(){
						window.panZoom.resize();
						window.panZoom.fit();
						window.panZoom.center();
					};
					/*
					window.panZoom = svgPanZoom(svgEle, {
						zoomEnabled: true,
						controlIconsEnabled: true,
						fit: 1,
						center: 1,
						onPan: function(){
							isPaused = true;
							stabiliZationPin(this.getPan(),this.getSizes(),this.getZoom());
						}
					});*/
				}
				
			}else if(typeload == "base64"){
				var div = document.createElement("div");
				div.innerHTML = ajax.responseText;
				svgEle = div.childNodes[0];
				var xml = new XMLSerializer().serializeToString(svgEle);
				var svg64 = btoa(xml);
				var b64Start = 'data:image/svg+xml;base64,';
				var image64 = b64Start + svg64;
				mapCache = image64;
			}
			if(callback && typeof callback == "function"){
				eval(callback(mapCache));
			}			
		}
	
}

function preview4(){
	kebun=document.getElementById('kebun');
	kebun=kebun.options[kebun.selectedIndex].value;
	filterblok=document.getElementById('filterblok');
	filterblok=filterblok.options[filterblok.selectedIndex].value;
	
	param = 'type=preview&kebun='+kebun+'&filterblok='+filterblok;
	tujuan = 'bi_map_informasiblok.php';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					splitArr = con.responseText.split("####");
					var list = splitArr[0];
					var arrlist = new Array();
					arrlist = JSON.parse(list);
					for(var key in arrlist){
						if (arrlist.hasOwnProperty(key)) {
							document.getElementById(arrlist[key]['idsvg']).style.fill = arrlist[key]['warna'];
							var object = document.getElementById(arrlist[key]['idsvg']);
							object.setAttribute("fill-opacity", 1);
						}
					}
					
					document.getElementById('divLegend').style.display = '';
					document.getElementById('divLegend').innerHTML = splitArr[1];
					// document.getElementById('showstatusblok').value = 3;
					heightdivLegend = document.getElementById('divLegend').clientHeight;
					heightdivChkInformasiBlok = document.getElementById('divChkInformasiBlok').clientHeight;
					document.getElementById('pane').style.height = (75 + heightdivChkInformasiBlok + heightdivLegend)+'px';
					document.getElementById('pane').style.minHeight = (75 + heightdivChkInformasiBlok + heightdivLegend)+'px';
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function closeDialogPopUpSvg(id){
	id = document.getElementById(id);
	id.style.display = 'none';
	if(document.getElementById('tempId').value!=='undefined'){
		clearTemporary();
	}
}

function detaillaporangraph(id, tipe, ev) {
	drResize('pane2');

	addons = document.getElementById('addons2');
	pane = document.getElementById('pane2');

	pane.style.width = '680px';
	pane.style.minWidth = '680px';
	pane.style.height = '370px';
	pane.style.minHeight = '370px';
	
	panewidth = pane.style.width;
	
	pos = new Array();
	pos = getMouseP(ev);
	
	pane.style.top = pos[1] + 'px';
	pane.style.left = (pos[0] - (panewidth)) + 'px';

	addons.style.display = '';
	
	kebun = document.getElementById('kebun');
	kebun = kebun.options[kebun.selectedIndex].value;
	
	if (tipe == 1) {
		periodeawal = document.getElementById('periodeawal3');
		periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
		periodeakhir = document.getElementById('periodeakhir3');
		periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
		namafile = document.getElementById('namafile3').value;
		detaillaporan2 = document.getElementById('detaillaporan3').value;
		noakun = document.getElementById('noakun3');
		noakun = noakun.options[noakun.selectedIndex].value;
		kegiatan = document.getElementById('detailkegiatan3');
		kegiatan = kegiatan.options[kegiatan.selectedIndex].value;
	} else {
		periodeawal = document.getElementById('periodeawal2');
		periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
		periodeakhir = document.getElementById('periodeakhir2');
		periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
		namafile = document.getElementById('namafile2').value;
		detaillaporan2 = document.getElementById('detaillaporan2');
		detaillaporan2 = detaillaporan2.options[detaillaporan2.selectedIndex].value;
		noakun = '';
		kegiatan = '';
	}

	document.getElementById('detailreport').innerHTML = '';

	param = 'type=globalreport&periodeawal=' + periodeawal + '&periodeakhir=' + periodeakhir + '&kebun=' + kebun + '&detaillaporan=' + detaillaporan2 + '&noakun=' + noakun + '&kegiatan=' + kegiatan;
	tujuan = namafile;

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();

				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('detailreport').innerHTML = con.responseText;	
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}

	post_response_text(tujuan, param, respog);
}

function detailinformasiblokgraph(filterblok,ev){
	drResize('pane2');
	addons = document.getElementById('addons2');
	pane = document.getElementById('pane2');
	pane.style.width = '680px';
	pane.style.minWidth = '680px';
	pane.style.height = '370px';
	pane.style.minHeight = '370px';
	panewidth = pane.style.width;
	
	pos = new Array();
	pos = getMouseP(ev);
	
	pane.style.top = pos[1]+'px';
	pane.style.left = (pos[0]-(panewidth))+'px';
	
	
	addons.style.display = '';
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	document.getElementById('detailreport').innerHTML = '';	
	
	param = 'type=globalreport&kebun='+kebun+'&filterblok='+filterblok;
	tujuan = 'bi_map_informasiblok.php';

	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('detailreport').innerHTML = con.responseText;	
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function showDataTracking(){
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	karyawanid=document.getElementById('karyawanid4');
    karyawanid=karyawanid.options[karyawanid.selectedIndex].value;
	
	tanggalhistory=document.getElementById('tanggalhistory').value;
	chkRealTime = document.getElementById('realtime').checked;
	
	if(karyawanid=='0'){
		alert("Nama Karyawan harus dipilih.");
		return false;
	}
	
	if(chkRealTime == true){
		tipetracking = 'realtime';
		method = 'showDataTrackingRealtime';
		timeOutTracking = setTimeout(showDataTracking, 10000);
		
		param = 'method='+method+'&kodept='+kodept+'&kebun='+kebun+'&karyawanid='+karyawanid+'&tanggalhistory='+tanggalhistory+'&tipetracking='+tipetracking;
		tujuan = 'bi_slave_map.php';
		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						vSplit = con.responseText.split("####");
						
						if(vSplit[0]!==''){
							if(vSplit[0]==vSplit[2]){
								vSplit[2] = parseFloat(vSplit[2])+0.00001;
							}
							vSplit[0] = parseFloat(vSplit[0]);
							vSplit[1] = parseFloat(vSplit[1]);
							vSplit[3] = parseFloat(vSplit[3]);
							var xmlns = "http://www.w3.org/2000/svg";
							
							newRow = document.createElementNS(xmlns,"filter");
							newRow.setAttributeNS(null,"id","location_image");
							newRow.setAttributeNS(null,"x","-50%");
							newRow.setAttributeNS(null,"y","-150%");
							newRow.setAttributeNS(null,"width","200%");
							newRow.setAttributeNS(null,"height","200%");
							newRow.innerHTML += "<feImage xlink:href='images/location.png' />";

							// vSplit[1]=112.06400000000;
							// vSplit[2]=0.357529;
							
							newRow2 = document.createElementNS(xmlns,"circle");
							newRow2.setAttributeNS(null,"cx",vSplit[0]);
							newRow2.setAttributeNS(null,"cy",vSplit[1]);
							newRow2.setAttributeNS(null,"r","0.00003");
							newRow2.setAttributeNS(null,"fill","red");
							newRow2.setAttributeNS(null,"filter","url(#location_image)");
							
							newRow3 = document.createElementNS(xmlns,"circle");
							newRow3.setAttributeNS(null,"cx",vSplit[2]);
							newRow3.setAttributeNS(null,"cy",vSplit[3]);
							newRow3.setAttributeNS(null,"r","0.00003");
							newRow3.setAttributeNS(null,"fill","red");
							newRow3.setAttributeNS(null,"filter","url(#location_image)");
							
							pointLine = vSplit[0]+','+vSplit[1]+' '+vSplit[2]+','+vSplit[3];
							newRow4 = document.createElementNS(xmlns,"polyline");
							// newRow4.setAttributeNS(null,"points","112.06400000000,0.357529 112.065,0.357549");
							newRow4.setAttributeNS(null,"points",pointLine);
							newRow4.setAttributeNS(null,"stroke","green");
							newRow4.setAttributeNS(null,"stroke-width","0.00001");
							newRow4.setAttributeNS(null,"stroke-linecap","butt");
							newRow4.setAttributeNS(null,"fill","none");
							newRow4.setAttributeNS(null,"stroke-linejoin","miter");
							
							
							tabBody = document.getElementById('svgTracking');
							tabBody.appendChild(newRow);
							tabBody.appendChild(newRow2);
							tabBody.appendChild(newRow3);
							tabBody.appendChild(newRow4);
							
							var realZoom= panZoom.getSizes().realZoom;
							xx = vSplit[0];
							yy = vSplit[1];
							panZoom.pan({
								x:-(xx * realZoom) + (panZoom.getSizes().width/2),
								y:-(yy * realZoom) + (panZoom.getSizes().height/2)
							});
							panZoom.zoom((3772),false);
						}
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}
		post_response_text(tujuan, param, respog);
	}else{
		tipetracking = 'history';
		method = 'showDataTracking';
		
		param = 'method='+method+'&kodept='+kodept+'&kebun='+kebun+'&karyawanid='+karyawanid+'&tanggalhistory='+tanggalhistory+'&tipetracking='+tipetracking;
		tujuan = 'bi_slave_map.php';
		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						vSplit = con.responseText.split("####");
						document.getElementById('svgTracking').innerHTML=vSplit[0];
						
						var realZoom= panZoom.getSizes().realZoom;
						xx = (vSplit[1]);
						yy = (vSplit[2]);
						panZoom.pan({
							x:-(xx * realZoom) + (panZoom.getSizes().width/2),
							y:-(yy * realZoom) + (panZoom.getSizes().height/2)
						});
						panZoom.zoom((3772),false);
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}
		post_response_text(tujuan, param, respog);
	}
}

/*###################### 
###	BEGIN CLEAR AREA ###
######################*/
function clearKodePt(){
	document.getElementById('trkebun').style.display = 'none';
	clearEstate();
}

function clearEstate(){
	document.getElementById('trdetail').style.display = 'none';
	document.getElementById('addons').style.display = 'none';
	document.getElementById('detailpt').style.display = 'none';
	document.getElementById('svgPt').innerHTML = '';
	document.getElementById('detailpt').innerHTML="";
	document.getElementById('svgDetail').innerHTML='';
}

function clearDivLegend(){
	document.getElementById('divLegend').style.display = 'none';
}

function clearColorSvgBlok(){
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	
	param = 'method=clearColorSvgBlok&kodept='+kodept+'&kebun='+kebun;
	tujuan = 'bi_slave_map.php';
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					splitArr = con.responseText.split("####");
					var list = splitArr[0];
					var arrlist = new Array();
					arrlist = JSON.parse(list);
					for(var key in arrlist){
						if (arrlist.hasOwnProperty(key)) {
							document.getElementById(arrlist[key]['idsvg']).style.fill = splitArr[1];
							var object = document.getElementById(arrlist[key]['idsvg']);
							var object = document.getElementById(arrlist[key]['idsvg']);
							object.setAttribute("fill-opacity", 0.4);
						}
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function clearChkDetail() {
	clearPopUpDetail();
	clearInterval(timeOutTracking);
	closeDialogPopUpSvg('addons2');	
	clearTracking();

	document.getElementById('svgDetail').innerHTML = '';
	document.getElementById('showstatusblok').value = 0;
	
	getResizePane();
	clearColorSvgBlok();
}

function clearTracking(){
	clearPopUpDetail();
	document.getElementById('svgTracking').innerHTML = '';	
}

function getResizePane() {
	let chkdetail = document.getElementById('chkdetail');
	chkdetail = chkdetail.options[chkdetail.selectedIndex].value;

	clearDivLegend();
	
	if (chkdetail == '') {
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = 'none';
		document.getElementById('divChkTracking').style.display = 'none';
		document.getElementById('divChkInformasiBlok').style.display = 'none';

		document.getElementById('pane').style.height = '75px';
		document.getElementById('pane').style.minHeight = '75px';
	} else if (chkdetail == 'activitymonitoring') {
		document.getElementById('divChkKegiatan').style.display = '';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = 'none';
		document.getElementById('divChkTracking').style.display = 'none';
		document.getElementById('divChkInformasiBlok').style.display = 'none';
		
		heightDivChkKegiatan = document.getElementById('divChkKegiatan').clientHeight;

		document.getElementById('pane').style.height = (75 + heightDivChkKegiatan) + 'px';
		document.getElementById('pane').style.minHeight = (75 + heightDivChkKegiatan) + 'px';
	} else if (chkdetail == 'performance') {
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = '';
		document.getElementById('divChkSiklus').style.display = 'none';
		document.getElementById('divChkTracking').style.display = 'none';
		document.getElementById('divChkInformasiBlok').style.display = 'none';

		heightDivChkLaporan = document.getElementById('divChkLaporan').clientHeight;

		document.getElementById('pane').style.height = (75 + heightDivChkLaporan) + 'px';
		document.getElementById('pane').style.minHeight = (75 + heightDivChkLaporan) + 'px';
	} else if (chkdetail == 'siklus') {
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = '';
		document.getElementById('divChkTracking').style.display = 'none';
		document.getElementById('divChkInformasiBlok').style.display = 'none';

		heightDivChkSiklus = document.getElementById('divChkSiklus').clientHeight;

		document.getElementById('pane').style.height = (75 + heightDivChkSiklus) + 'px';
		document.getElementById('pane').style.minHeight = (75 + heightDivChkSiklus) + 'px';
	} else if (chkdetail == 'tracking') {
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = 'none';
		document.getElementById('divChkTracking').style.display = '';
		document.getElementById('divChkInformasiBlok').style.display = 'none';

		heightDivChkSiklus = document.getElementById('divChkTracking').clientHeight;

		document.getElementById('pane').style.height = (75 + heightDivChkSiklus) + 'px';
		document.getElementById('pane').style.minHeight = (75 + heightDivChkSiklus) + 'px';
	} else if (chkdetail == 'informasiblok') {
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = 'none';
		document.getElementById('divChkTracking').style.display = 'none';
		document.getElementById('divChkInformasiBlok').style.display = '';

		heightDivChkInformasiBlok = document.getElementById('divChkInformasiBlok').clientHeight;

		document.getElementById('pane').style.height = (75 + heightDivChkInformasiBlok) + 'px';
		document.getElementById('pane').style.minHeight = (75 + heightDivChkInformasiBlok) + 'px';
	}
}

function clearPopUpDetail() {
	closeDialogPopUpSvg('addons3');
	if(document.getElementById('dynamic2') != undefined){
		closeDialogBi2();
	}
}
/*###################### 
#### END CLEAR AREA ####
######################*/

/*#################################
#### BEGIN ACTIVITY MONITORING ####
#################################*/
function clearAMSvgDetail(){
	clearPopUpDetail();
	document.getElementById('svgDetail').innerHTML='';
}

//Get kegiatan di Activity Monitoring
function getdetailkegiatan() {
	kebun = document.getElementById('kebun');
	kebun = kebun.options[kebun.selectedIndex].value;
	periodeawal = document.getElementById('periodeawal');
	periodeawal = periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir = document.getElementById('periodeakhir');
	periodeakhir = periodeakhir.options[periodeakhir.selectedIndex].value;
	detailtipedokumen = document.getElementById('detailtipedokumen');
	detailtipedokumen = detailtipedokumen.options[detailtipedokumen.selectedIndex].value;
	
	if (detailtipedokumen != '') {
		param = 'method=getdetailkegiatan&kebun=' + kebun + '&periodeawal=' + periodeawal + '&periodeakhir=' + periodeakhir + '&detailtipedokumen=' + detailtipedokumen;
		tujuan = 'bi_slave_map.php';

		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();

					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						document.getElementById('detailkegiatan').innerHTML = con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}

		post_response_text(tujuan, param, respog);
	}

	clearAMSvgDetail();
}
/*#################################
##### END ACTIVITY MONITORING #####
#################################*/

/*==========================================================================================================*/

/*##########################
##### BEGIN PEFORMANCE #####
##########################*/
function clearPFLaporan(){
	closeDialogPopUpSvg('addons2');
	getResizePane();
	clearPopUpDetail();
	clearColorSvgBlok();
}

function clearPFLaporan2(){
	closeDialogPopUpSvg('addons2');
	getResizePane();
	clearPopUpDetail();
}

function getnamafile() {
	detaillaporan2 = document.getElementById('detaillaporan2');
	detaillaporan2 = detaillaporan2.options[detaillaporan2.selectedIndex].value;

	if (detaillaporan2 != '') {
		param = 'method=getnamafile&detaillaporan2=' + detaillaporan2;
		tujuan = 'bi_slave_map.php';
		
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();

					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
					} else {
						document.getElementById('namafile2').value = con.responseText;
					
						clearColorSvgBlok();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}	
		}

		post_response_text(tujuan, param, respog);
	} else {
		document.getElementById('namafile2').value = '';
		clearColorSvgBlok();

	}

	clearPFLaporan2();
}
/*##########################
###### END PEFORMANCE ######
##########################*/

/*==========================================================================================================*/

/*######################
##### BEGIN SIKLUS #####
######################*/
function getidsiklus(){
	noakun=document.getElementById('noakun3');
    noakun=noakun.options[noakun.selectedIndex].value;
	clearPFLaporan();
	if(noakun==''){
		document.getElementById('detaillaporan3').value = '';
	}else{
		detailkegiatan=document.getElementById('detailkegiatan3');
		detailkegiatan=detailkegiatan.options[detailkegiatan.selectedIndex].value;
		
		param = 'noakun='+noakun+'&detailkegiatan='+detailkegiatan+'&method=getidsiklus';
		tujuan = 'bi_slave_map.php';

		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						document.getElementById('detaillaporan3').value = con.responseText;

						clearColorSvgBlok();
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}
		post_response_text(tujuan, param, respog);
	}
}

function getkegiatan(){
	noakun=document.getElementById('noakun3');
    noakun=noakun.options[noakun.selectedIndex].value;
	
	param = 'noakun='+noakun+'&method=getkegiatan';
	tujuan = 'bi_slave_map.php';

	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('detailkegiatan3').innerHTML = con.responseText;
					getidsiklus();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}
/*######################
###### END SIKLUS ######
######################*/


/*########################
##### BEGIN TRACKING #####
########################*/
function showinfogps(type,data,ev){
	// alert("test");
	drResize('pane3');
	document.getElementById('addons3').style.display = '';	
	switch(type){
		case 'fingerprint':
			method = 'showDataTrackingDetail';
			let dataArr = data.split(',');
			let tanggalhistory=document.getElementById('tanggalhistory').value;
			param = 'method='+method+'&sn='+dataArr[0]+'&lat='+dataArr[1]+'&long='+dataArr[2]+'&maxrd='+dataArr[3]+'&tanggalhistory='+tanggalhistory;
			tujuan = 'bi_slave_map.php';
			function respog(){
				if(con.readyState==4){
					if(con.status == 200){
						busy_off();
						if(!isSaveResponse(con.responseText)){
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							// document.getElementById('informasifp').innerHTML = con.responseText;
							document.getElementById('informasi').innerHTML = con.responseText;
							resizePopUp31();
						}
					}else{
						busy_off();
						error_catch(con.status);
					}
				}	
			}
			post_response_text(tujuan, param, respog);
		break;
	}
}

function changeTipeTracking(){
	chkRealTime = document.getElementById('realtime').checked;
	
	if(chkRealTime == true){
		document.getElementById('tanggaltracking').style.display = 'none';
	}else{
		clearInterval(timeOutTracking);
		document.getElementById('tanggaltracking').style.display = '';
	}
	heightDivChkSiklus = document.getElementById('divChkTracking').clientHeight;
	document.getElementById('pane').style.height = (75+heightDivChkSiklus)+'px';
	document.getElementById('pane').style.minHeight = (75+heightDivChkSiklus)+'px';
	clearTracking();
}
/*######################
##### END TRACKING #####
######################*/

activeTabBI='tab0';

function tabActionBI(cur,numactive,tabID,max,theme)
{
	if(theme=='skyblue'){
		img='images/tab3.png';
	}else if(theme=='red'){
		img='images/tab3Red.png';
	}else{
		img='images/tab3Gray.png';
	}

	activeTabBI = 'tab'+tabID  + numactive;
	try {
		for (x = 0; x <= max; x++) {
			if(numactive!==x){
			document.getElementById('tab'+tabID + x).style.backgroundImage = 'url('+img+')';
			document.getElementById('tab'+tabID  + x).style.color = '#FFFFFF';
			document.getElementById('tab'+tabID  + x).style.fontWeight = 'normal';
			document.getElementById('content'+tabID  + x).style.display = 'none';
			}
		}
		cur.style.backgroundImage = 'url(images/tab1.png)';
		cur.style.color = '#CC3366';
		cur.style.fontWeight = 'bold';

		document.getElementById('content'+tabID  + numactive).style.display = '';
	}
	catch(e)
	{
		alert(e.toString()+"\nMaybe Tab's component not loaded correctly");
		
	}
	resizePopUp31();
}

function chgBackgroundImgBI(obj,img,color){
	if (obj.id != activeTabBI) {
		obj.style.backgroundImage = 'url(' + img + ')';
		obj.style.color=color;
	}
}

function printmap(){
	// var doc = new jsPDF();
	// var specialElementHandlers = {
		// '#editor': function (element, renderer) {
			// return true;
		// }
	// };
	
	// doc.fromHTML(document.getElementById('mapall').innerHTML, 15, 15, {
        // 'width': 170,
            // 'elementHandlers': specialElementHandlers
	// doc.save('PrintMap.pdf');
    // });
	
	var data = document.getElementById('mapall').innerHTML;
	var mywindow = window.open('', 'Print Map', ',type=fullWindow,fullscreen,scrollbars=yes');
	mywindow.document.write('<html><head><title>Print Map</title>');
	/*optional stylesheet*/ //mywindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
	mywindow.document.write('</head><body >');
	mywindow.document.write(data);
	mywindow.document.write('</body></html>');

	mywindow.print();
	mywindow.close();

	return true;
	
	
	
	
	
	
	
	
	
	
	
	// kodept=document.getElementById('kodept');
    // kodept=kodept.options[kodept.selectedIndex].value;
	// kebun=document.getElementById('kebun');
    // kebun=kebun.options[kebun.selectedIndex].value;
	
	// chkdetail=document.getElementById('chkdetail');
    // chkdetail=chkdetail.options[chkdetail.selectedIndex].value;
	// filterblok=document.getElementById('filterblok');
    // filterblok=filterblok.options[filterblok.selectedIndex].value;
	
	// var printContents = document.getElementById('mapall').innerHTML;
	// var originalContents = document.body.innerHTML;
	// document.body.innerHTML = printContents;
	// window.print();
	
	// document.body.innerHTML = originalContents;
	
	// k=document.getElementById('kodept');
	// for(a=0;a<k.length;a++){
        // if(k.options[a].value==kodept){
            // k.options[a].selected=true;
        // }
    // }
	
	// k=document.getElementById('kebun');
	// for(a=0;a<k.length;a++){
        // if(k.options[a].value==kebun){
            // k.options[a].selected=true;
        // }
    // }
	
	// k=document.getElementById('chkdetail');
	// for(a=0;a<k.length;a++){
        // if(k.options[a].value==chkdetail){
            // k.options[a].selected=true;
        // }
    // }
	// k=document.getElementById('filterblok');
	// for(a=0;a<k.length;a++){
        // if(k.options[a].value==filterblok){
            // k.options[a].selected=true;
        // }
    // }
}
