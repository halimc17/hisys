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

function getkebun(){
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	
	param = 'method=getkebun&kodept='+kodept;
	tujuan = 'bi_slave_map.php';
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					if(kodept==''){
						document.getElementById('trkebun').style.display = 'none';
					}else{
						document.getElementById('trkebun').style.display = '';
					}
					document.getElementById('kebun').innerHTML=con.responseText;
					getdetailkebun();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function getdetailkebun(){
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
						document.getElementById('trdetail').style.display = '';
						vSplit = con.responseText.split("####");
						document.getElementById('svgPt').innerHTML=vSplit[0];
						detailpt.innerHTML=vSplit[1];
						document.getElementById('detailmap').innerHTML=vSplit[2];
						document.getElementById('addons').style.display = '';
						document.getElementById('svgDetail').innerHTML='';
						
						var realZoom= panZoom.getSizes().realZoom;
						xx = (vSplit[3]);
						yy = (vSplit[4]);
						panZoom.pan({
							x:-(xx * realZoom) + (panZoom.getSizes().width/2),
							y:-(yy * realZoom) + (panZoom.getSizes().height/2)
						});
						panZoom.zoom((251),false);
						// document.getElementById('showscale').innerHTML="Zoom : 31 x ";
	
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

function getdetailkebun2(){
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	detailpt = document.getElementById('detailpt');
	
	if(kebun==''){
		//Hidden detail PT
		document.getElementById('trdetail').style.display = 'none';
		detailpt.style.display = 'none';
		detailpt.innerHTML="";
		
		//Hidden SVG PT
		document.getElementById('svgPt').innerHTML = '';
		
		//Hidden Pop Up Kegiatan
		document.getElementById('addons').style.display = 'none';
		
		//Hidden SVG Detail Kegiatan
		document.getElementById('svgDetail').innerHTML='';
	}else{
		//Show detail PT
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
						resetactivity();
						
						// var realZoom= panZoom.getSizes().realZoom;
						// xx = (vSplit[3]);
						// yy = (vSplit[4]);
						// panZoom.pan({
							// x:-(xx * realZoom) + (panZoom.getSizes().width/2),
							// y:-(yy * realZoom) + (panZoom.getSizes().height/2)
						// });
						// panZoom.zoom((250),false);
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

function checkMarkListPt(id){
	kodept=document.getElementById('kodept');
    kodept=kodept.options[kodept.selectedIndex].value;
	if(id.checked){
		document.getElementById(id.value).style.display = '';
	}else{
		document.getElementById(id.value).style.display = 'none';
	}
}

function showinfosvg(idsvg,tipesvg,ev,siklus){
	if(isClicked){
		return;
	}
	// var realZoom= panZoom.getSizes().realZoom;
	// document.getElementById('showscale').innerHTML=realZoom;
	showstatusblok = document.getElementById('showstatusblok').value;
	detInfo = "";
	
	if(showstatusblok == 1){
		kebun=document.getElementById('kebun');
		kebun=kebun.options[kebun.selectedIndex].value;
		periodeawal=document.getElementById('periodeawal2');
		periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
		periodeakhir=document.getElementById('periodeakhir2');
		periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
		detaillaporan2=document.getElementById('detaillaporan2');
		detaillaporan2=detaillaporan2.options[detaillaporan2.selectedIndex].value;
		namafile = document.getElementById('namafile2').value;
		
		param = 'type=detail&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&detaillaporan='+detaillaporan2+'&idsvg='+idsvg;
		tujuan = namafile;
		
		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						showdetailsvg(idsvg,tipesvg,con.responseText,ev);
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}
		post_response_text(tujuan, param, respog);
	}else if(showstatusblok == 3){
		kebun=document.getElementById('kebun');
		kebun=kebun.options[kebun.selectedIndex].value;
		periodeawal=document.getElementById('periodeawal3');
		periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
		periodeakhir=document.getElementById('periodeakhir3');
		periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
		kegiatan=document.getElementById('detailkegiatan3');
		kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
		
		param = 'type=detail&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&kegiatan='+kegiatan+'&idsvg='+idsvg;
		tujuan = 'bi_map_siklus.php';
		
		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert('ERROR TRANSACTION,\n' + con.responseText);
					}else{
						showdetailsvg(idsvg,tipesvg,con.responseText,ev);
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}	
		}
		post_response_text(tujuan, param, respog);
	}else{
		showdetailsvg(idsvg,tipesvg,'',ev);
	}
}

function showdetailsvg(idsvg,tipesvg,detail,ev){
	param = 'method=showinfosvg&idsvg='+idsvg+'&tipesvg='+tipesvg+'&detailreport='+detail;
	
    showDialogBi('Informasi',"<iframe frameborder=0 style='width:520px;height:200px'"+
    "src='bi_slave_map.php?"+param+"'></iframe>",'525','200',ev);	
    var dialog = document.getElementById('dynamic1');
	
	pos = new Array();
	pos = getMouseP(e);
	
	if((pos[1] + 200) >= 600){
		dialog.style.top = (pos[1]-210) + 'px';
	}else{
		dialog.style.top = pos[1] + 'px';
	}
	if((pos[0] - 500) < 0){
		dialog.style.left = (pos[0]) +'px';
	}else{
		dialog.style.left = (pos[0] - 500) +'px';
	}
	dialog.style.display='';
}

function showdetailreport(idsvg){
	
}

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

function getdetailkegiatan(){
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	periodeawal=document.getElementById('periodeawal');
    periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir=document.getElementById('periodeakhir');
    periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
	detailtipedokumen=document.getElementById('detailtipedokumen');
    detailtipedokumen=detailtipedokumen.options[detailtipedokumen.selectedIndex].value;
	
	param = 'method=getdetailkegiatan&kebun='+kebun+'&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&detailtipedokumen='+detailtipedokumen;
	tujuan = 'bi_slave_map.php';
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('detailkegiatan').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function getChkDetail(){
	if(document.getElementById('dynamic1') != undefined){
		closeDialogBi();
	}
		
	chkdetail=document.getElementById('chkdetail');
    chkdetail=chkdetail.options[chkdetail.selectedIndex].value;
	
	if(chkdetail == 'activitymonitoring'){
		document.getElementById('divChkKegiatan').style.display = '';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = 'none';
	}else if(chkdetail == 'performance'){
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = '';
		document.getElementById('divChkSiklus').style.display = 'none';
	}else{
		document.getElementById('divChkKegiatan').style.display = 'none';
		document.getElementById('divChkLaporan').style.display = 'none';
		document.getElementById('divChkSiklus').style.display = '';
	}
	document.getElementById('svgDetail').innerHTML = '';
	document.getElementById('pane').style.height = '150px';
	document.getElementById('divLegend').innerHTML = '';
	document.getElementById('divLegend').style.display = 'none';
	getdetailkebun2();
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

function getnamafile(){
	detaillaporan2=document.getElementById('detaillaporan2');
    detaillaporan2=detaillaporan2.options[detaillaporan2.selectedIndex].value;
	
	param = 'method=getnamafile&detaillaporan2='+detaillaporan2;
	tujuan = 'bi_slave_map.php';
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('namafile2').value=con.responseText;
					resetestate();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function preview2(){
	kebun=document.getElementById('kebun');
    kebun=kebun.options[kebun.selectedIndex].value;
	periodeawal=document.getElementById('periodeawal2');
    periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir=document.getElementById('periodeakhir2');
    periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;
	detaillaporan2=document.getElementById('detaillaporan2');
    detaillaporan2=detaillaporan2.options[detaillaporan2.selectedIndex].value;
	namafile = document.getElementById('namafile2').value;
	
	if(namafile == ''){
		alert("Laporan harus dipilih.");
		return;
	}
	
	param = 'type=preview&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&detaillaporan='+detaillaporan2;
	tujuan = namafile;
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
							object.setAttribute("onmousedown", 'isClicked=false');
							object.setAttribute("onmousemove", 'isClicked=true');
							object.onmouseup("onmousemove", "showinfosvg('"+arrlist[key]['idsvg']+"',1,'event')");
							object.setAttribute("fill-opacity", 1);
						}
					}
				}
				document.getElementById('pane').style.height = ((splitArr[2] * 15) + 20 + 160)+'px';
				document.getElementById('divLegend').style.display = '';
				document.getElementById('divLegend').innerHTML = splitArr[1];
				document.getElementById('showstatusblok').value = 1;
			}else{
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
	document.getElementById('namafile3').value = '';
	getdetailkegiatan();
}

function resetestate(){
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
						document.getElementById('divLegend').style.display = 'none';
						document.getElementById('divLegend').innerHTML = '';
						document.getElementById('pane').style.height = '150px';
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

function preview3(){
	getdetailkebun2();
	kebun=document.getElementById('kebun');
	kebun=kebun.options[kebun.selectedIndex].value;
	periodeawal=document.getElementById('periodeawal3');
	periodeawal=periodeawal.options[periodeawal.selectedIndex].value;
	periodeakhir=document.getElementById('periodeakhir3');
	periodeakhir=periodeakhir.options[periodeakhir.selectedIndex].value;

	detaillaporan2='LAP0000001';
	kegiatan=document.getElementById('detailkegiatan3');
	kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
	namafile = document.getElementById('namafile3').value;
	param = 'type=preview&periodeawal='+periodeawal+'&periodeakhir='+periodeakhir+'&kebun='+kebun+'&detaillaporan='+detaillaporan2+'&kegiatan='+kegiatan;
	tujuan = 'bi_map_siklus.php';

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
				}
				document.getElementById('pane').style.height = ((splitArr[2] * 15) + 20 + 150)+'px';
				document.getElementById('divLegend').style.display = '';
				document.getElementById('divLegend').innerHTML = splitArr[1];
				document.getElementById('showstatusblok').value = 3;
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}