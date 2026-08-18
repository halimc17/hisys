var imported = document.createElement('script');
imported.src = 'ckeditor/ckeditor.js';
document.head.appendChild(imported);


function hidetomboldeleteeditpopup(){	
	e = document.getElementsByName('labeleditdeletepopup[]');
	n = document.getElementsByName('timerhelppopup[]');
	for(i = 0; i < e.length; i++){
		e[i].style.display='none';
		n[i].style.display='none';
	}
}

function loadTimer(){
	var fiveMinutes = 60 * 1;
	var e = document.getElementsByName('timerhelppopup[]');
	for(i = 0; i < e.length; i++){
		startTimer(fiveMinutes, e[i]);
	}
	
	setInterval('hidetomboldeleteeditpopup()',60600);
}


function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}

function showposthelppoup(e){
	if(e.value=='5'){
		// unposting
		document.getElementById('bariskraniinput1').style.display='';
		document.getElementById('bariskraniinput2').style.display='';
	}else{
		document.getElementById('bariskraniinput1').style.display='none';
		document.getElementById('bariskraniinput2').style.display='none';
	}
}


function getdetailuser(username){
    param = 'method=getdetailuser&username=' + username;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.set('notifier','position', 'top-right');
					alertify.success(con.responseText,5);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setMapUserMenuDet(uname) {
    //pos = getMouseP('event');
    param = 'username=' + uname;
    param += '&proses=detailrole';
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('600px','400px'); 
                    // //alert(con.responseText);
                    // document.getElementById('contentmenu').innerHTML = con.responseText;
                    // document.getElementById('ctrmenu').style.display = '';
                    // document.getElementById('ctrmenu').style.top = pos[1] + 'px';
                    // document.getElementById('ctrmenu').style.left = pos[0] + 'px';
                    //rowobj.style.backgroundColor = '#E8F2FE'; //class standardrow color
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text('sdm_slave_2userowlpopup.php', param, respog);
}

function delhelppopup(idhelp,id){
    param = 'method=delhelppopup&idhelp=' + idhelp;
	param += '&idmenu=' + id;
    alertify.confirm("Hapus, Anda Yakin ???",
		function(){
			tujuan = 'help_slave_show.php';
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					showhelppopup(id);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function delfilereporthelppopup(idmenu,id,filename){
    param = 'method=delfilereporthelppopup&id=' + id;
	param += '&id=' + id;
	param += '&filename=' + filename;
    alertify.confirm("Hapus, Anda Yakin ???",
		function(){
			tujuan = 'help_slave_show.php';
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					tambahreporthelppopup(idmenu,id,'edit');
					// alertify.popup3().destroy();
					// setTimeout(function(){
					// }, 500);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function delreporthelppopup(idmenu,idhelp){
    param = 'method=delreporthelppopup';
	param += '&idmenu=' + idmenu;
	param += '&idhelp=' + idhelp;
    alertify.confirm("Hapus, Anda Yakin ???",
		function(){
			tujuan = 'help_slave_show.php';
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup3().destroy();
					reporthelppopup(idmenu);
					if(document.getElementById('outputticketsupport999999999')!=undefined){
						loaddataticketsupport999999999(idhelp);
						alertify.closeAll();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deldtpopup(idht,iddt){
    param = 'method=deldtpopup';
	param += '&idht=' + idht;
	param += '&iddt=' + iddt;
    alertify.confirm("Hapus, Anda Yakin ???",
		function(){
			tujuan = 'help_slave_show.php';
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup5().destroy();
					setTimeout(function(){
						openConvhelppopup(idht);
					}, 500);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function gethelppopup(id){
    param = 'method=gethelppopup&idmenu=' + id;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data = con.responseText.split("####");
					alertify.popup2().set({onshow:function(){showhelppopup(id)}}); 
					alertify.popup2(data[1],data[0]).set({
						'resizable':true,
						'maximizable':true,
							onclose:function(){
								readhelppopup(id,'help')
							}
					}).resizeTo('70%','70%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function readhelppopup(id,sumber){
    param = 'method=readhelppopup&idmenu=' + id;
    param += '&sumber=' + sumber;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancelhelppopup(id){
	document.getElementById('idcarihelppopup').value='';
	document.getElementById('tentangcarihelppopup').value='';
	document.getElementById('penjelasancarihelppopup').value='';
	showhelppopup(id);
}
function showhelppopup(id){
	idhelp    = document.getElementById('idcarihelppopup').value;
	tentang   = document.getElementById('tentangcarihelppopup').value;
	penjelasan= document.getElementById('penjelasancarihelppopup').value;
	//idmodul   = document.getElementById('idmodulhelppopup').value;
	
    param  = 'method=showhelppopup&idmenu=' + id;
    param += '&idhelp=' + idhelp;
    param += '&tentang=' + tentang;
    param += '&penjelasan=' + penjelasan;
    //param += '&idmodul=' + idmodul;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('containerhelppopup').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showhelppopup2(id){
	// idhelp    = document.getElementById('idcarihelppopup').value;
	// tentang   = document.getElementById('tentangcarihelppopup').value;
	// penjelasan= document.getElementById('penjelasancarihelppopup').value;
	//idmodul   = document.getElementById('idmodulhelppopup').value;
	
    param  = 'method=showhelppopup&idmenu=' + id;
    param += '&jenis=x';
    // param += '&idhelp=' + idhelp;
    // param += '&tentang=' + tentang;
    // param += '&penjelasan=' + penjelasan;
    //param += '&idmodul=' + idmodul;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup2("Help",con.responseText).set({
						'resizable':true,
						'maximizable':true,
							onclose:function(){
								readhelppopup(id,'help')
							}
					}).resizeTo('70%','70%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function openConvhelppopup(id){
    param = 'method=openConvhelppopup&idhelp=' + id;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup5().destroy();
					alertify.popup5().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText,'title':'Report No #'+id,onclose:function(){readhelppopup(id,'tiket')}}).resizeTo('70%','70%').show();
					CKEDITOR.replace('penjelasanhelppopup2',{
						width: '100%',
						height: '25vh',
						removeButtons: 'About'
					});
					loadTimer();
					
					$(document).ready(function() {
						$('.select2.help').select2({
							dropdownAutoWidth:true
						});
					});
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function tambahhelppopup(id,idhelp,action){
    param = 'method=tambahhelppopup&idmenu=' + id;
	param += '&action=' + action;
	param += '&idhelp=' + idhelp;
	if(action=='edit'){
		judul="Edit Help";
	}else{
		judul="Add Help";
	}
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					//alertify.popuphist(judul,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','70%'); 
					alertify.popuphist().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					CKEDITOR.replace('penjelasanhelppopup',{
						width: '100%',
						height: '50vh',
						removeButtons: 'About'
					});
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function tambahreporthelppopup(id,idhelp,action){
    param = 'method=tambahreporthelppopup&idmenu=' + id;
	param += '&action=' + action;
	param += '&idhelp=' + idhelp;
	if(action=='edit'){
		judul="Edit";
	}else{
		judul="Add";
	}
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup4().destroy();
					alertify.popup3().destroy();
					alertify.popup3().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText,'title':judul+' ('+id+')'}).resizeTo('70%','70%').show();
					CKEDITOR.replace('penjelasanhelppopup',{
						width: '100%',
						height: '50vh',
						removeButtons: 'About'
					});
					setTimeout(function(){
						alertify.popup2().destroy();
						adaselect2 = document.getElementById('adaselect2').value;
						if(adaselect2=='y'){
							$(document).ready(function() {
								$('.select2.help').select2({
									dropdownAutoWidth:false
								});
								$('.select2-selection--single').height(30).css({
									cursor: "auto"
								});
								$('.select2-selection__arrow b').css({
									top: "70%"
								});
								$('.select2-selection__rendered').css({
									'line-height': '31px'
								});
							});
						}
					}, 150);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getpathmenu(id){
    param = 'method=getpathmenu&idmenu=' + id;
	
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('idmenutambahhelppopup').value=id;
					document.getElementById('pathmenuhelppopup').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function reporthelppopup(id){
    param = 'method=reporthelppopup&idmenu=' + id;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup4().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText,'title':'Report'}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function jumpHelp(idhelp){
	param = 'method=jumpHelp';
	param += '&idhelp=' + idhelp;
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getinfohelppopup(){
	param = 'method=getinfohelppopup';
	
    tujuan = 'help_slave_show.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanaddhelppopup(action){
	var id        = document.getElementById('idmenutambahhelppopup').value;
	var idhelp    = document.getElementById('idhelptambahhelppopup').value;
	var tentang   = document.getElementById('tentanghelppopup').value;
	// var penjelasan= document.getElementById('penjelasanhelppopup').value;
	var penjelasan=  CKEDITOR.instances.penjelasanhelppopup.getData();
	var linkurl   = document.getElementById('linkhelppopup').value;
	validate([
		["tentanghelppopup","Tentang tidak boleh kosong."]
	]);
	if(penjelasan==''){
		alertify.alert("Penjelasan tidak boleh kosong."); return;
	}
	
	if(linkurl!='' && getValue('fileshelppopup')!=''){
		alertify.alert("File dan Link (url) tidak boleh terisi dua - duanya."); return;
	}
	
	var formdata = new FormData();
	var file = document.getElementById("fileshelppopup").files[0];
	formdata.append("file", file);
	formdata.append("fileupload", getValue('fileshelppopup'));
	formdata.append("idmenu", id);	
	formdata.append("idhelp", idhelp);	
	formdata.append("tentang", tentang);	
	formdata.append("penjelasan", penjelasan);	
	formdata.append("action", action);	
	formdata.append("linkurl", linkurl);	
	
	
	
	if (!isSaveResponse(tentang)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}
	if (!isSaveResponse(penjelasan)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}	
	
	busy_on;
	var con = createXMLHttpRequest();
	con.open("POST", "help_slave_show.php?method=simpanaddhelppopup", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.alert("Data sudah disimpan.");
					alertify.popuphist().destroy();
					showhelppopup(id);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanreporthelppopup(action){
	var radiovalue = "";
	var radio = document.getElementsByName("radio[]");
	for (var i = 0; i < radio.length; i++) {
		if(radio[i].checked==true){
			radiovalue = radio[i].value;
		}
	}
	var id           = document.getElementById('idmenutambahhelppopup').value;
	var idhelp       = document.getElementById('idhelptambahhelppopup').value;
	var tentang      = document.getElementById('tentanghelppopup').value;
	var namafilemenu = document.getElementById('idmenutambahhelppopupaction').value;
	var idmenuawal   = document.getElementById('idmenutambahhelppopupawal').value;
	var userinput    = document.getElementById('namauserinputtransaksi').value;
	var kodeorg      = document.getElementById('namaorginputtransaksi').value;
	var penjelasan   =  CKEDITOR.instances.penjelasanhelppopup.getData();
	
	if(namafilemenu=='user_tiketreport' && idmenuawal==id){
		alertify.alert("Menu tidak boleh kosong."); return;
	}
	
	validate([
		["tentanghelppopup","Subject tidak boleh kosong."]
	]);
	if(penjelasan==''){
		alertify.alert("Penjelasan tidak boleh kosong."); return;
	}
	if(radiovalue==''){
		alertify.alert("Jenis harus dipilih."); return;
	}
		
	var formdata = new FormData();
	var totalfiles = document.getElementById('fileshelppopup').files.length;
	if(totalfiles>5){
		alertify.alert("File terlalu banyak, maksimal hanya 5 file."); return;
	}
	for (var i = 0; i < totalfiles; i++) {
		formdata.append("file[]", document.getElementById('fileshelppopup').files[i]);
	}
	
	formdata.append("fileupload", getValue('fileshelppopup'));
	formdata.append("idmenu", id);	
	formdata.append("idhelp", idhelp);	
	formdata.append("tentang", tentang);	
	formdata.append("penjelasan", penjelasan);	
	formdata.append("action", action);	
	formdata.append("userinput", userinput);	
	formdata.append("kodeorg", kodeorg);	
	formdata.append("jenis", radiovalue);	
	
	if (!isSaveResponse(tentang)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}
	if (!isSaveResponse(penjelasan)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}	
	
	busy_on;
	var con = createXMLHttpRequest();
	con.open("POST", "help_slave_show.php?method=simpanreporthelppopup", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup3().destroy();
					reporthelppopup(id);

					setTimeout(function(){
						if(document.getElementById('outputticketsupport999999999')!=undefined){
							loaddataticketsupport999999999(id);
							alertify.closeAll();
						}
					}, 500);
					// alertify.alert("Data sudah disimpan.");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpanreporthelppopup2(idhelp){
	// var penjelasan= document.getElementById('penjelasanhelppopup2').value;
	// validate([
		// ["penjelasanhelppopup2","Penjelasan tidak boleh kosong."]
	// ]);
	
	var penjelasan =  CKEDITOR.instances.penjelasanhelppopup2.getData();
	if(penjelasan==''){
		alertify.alert("Penjelasan tidak boleh kosong."); return;
	}
		
	var formdata = new FormData();
	var totalfiles = document.getElementById('fileshelppopup2').files.length;
	if(totalfiles>5){
		alertify.alert("File terlalu banyak, maksimal hanya 5 file."); return;
	}
	for (var i = 0; i < totalfiles; i++) {
		formdata.append("file[]", document.getElementById('fileshelppopup2').files[i]);
	}
	
	formdata.append("fileupload", getValue('fileshelppopup2'));
	formdata.append("tindaklanjut", getValue('tindaklanjut'));
	formdata.append("idhelp", idhelp);	
	formdata.append("penjelasan", penjelasan);	
	
	if (!isSaveResponse(penjelasan)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}	
	
	busy_on;
	var con = createXMLHttpRequest();
	con.open("POST", "help_slave_show.php?method=simpanreporthelppopup2", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// alertify.alert("Data sudah disimpan.");
					alertify.popup5().destroy();
					setTimeout(function(){
						openConvhelppopup(idhelp);
					}, 500);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletetindaklanjut(){
	document.getElementById('tindaklanjut').value='';
	$('#tindaklanjut').val(value).trigger('change');
}

function getticketsupportclose(idhelp,idmenu){
	param = 'method=getticketsupportclose';
	param += '&idhelp=' + idhelp;
    tujuan = 'help_slave_show.php';
	alertify.confirm("Close","Anda yakin untuk menutup ticket ini ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	).set('resizable',false).resizeTo(100,250);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.closeAll();
					if(document.getElementById('outputticketsupport999999999')!=undefined){
						loaddataticketsupport999999999(idmenu);
					}
					setTimeout(function(){	
						readhelppopup(idhelp,'tiket');
					}, 1000);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getticketsupporttolak(idhelp){
	var penjelasan =  CKEDITOR.instances.penjelasanhelppopup2.getData();
	if(penjelasan==''){
		alertify.alert("Penjelasan tidak boleh kosong."); return;
	}
		
	var formdata = new FormData();
	var totalfiles = document.getElementById('fileshelppopup2').files.length;
	if(totalfiles>5){
		alertify.alert("File terlalu banyak, maksimal hanya 5 file."); return;
	}
	for (var i = 0; i < totalfiles; i++) {
		formdata.append("file[]", document.getElementById('fileshelppopup2').files[i]);
	}
	
	formdata.append("fileupload", getValue('fileshelppopup2'));
	formdata.append("tindaklanjut", getValue('tindaklanjut'));
	formdata.append("idhelp", idhelp);	
	formdata.append("penjelasan", penjelasan);	
	
	if (!isSaveResponse(penjelasan)) {
		alertify.alert("errorcode : Hindari penggunaan kata : ERROR, WARNING dan GAGAL");
		throw Error('Stop!');
	}	
	
	busy_on;
	var con = createXMLHttpRequest();
	con.open("POST", "help_slave_show.php?method=getticketsupporttolak", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// alertify.alert("Data sudah disimpan.");
					alertify.popup5().destroy();
					setTimeout(function(){
						openConvhelppopup(idhelp);
					}, 500);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getticketsupportajukan(idhelp,idmenu){
	param = 'method=getticketsupportajukan';
	param += '&idhelp=' + idhelp;
	param += '&idmenu=' + idmenu;
    tujuan = 'help_slave_show.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup5().destroy();
					if(con.responseText!=''){						
						alertify.popup5().set({'resizable':true,'maximizable':false,'startMaximized':false,'message':con.responseText,'title':'Ajukan ?'}).resizeTo('450px','200px').show();
					}else{
						if(document.getElementById('outputticketsupport999999999')!=undefined){
							loaddataticketsupport999999999(idmenu);
						}else{
							alertify.closeAll();
						}
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getticketsupportajukansimpan(idhelp,idmenu,jumlahapproval){
	param = 'method=getticketsupportajukansimpan';
	param += '&idhelp=' + idhelp;
	param += '&idmenu=' + idmenu;
	param += '&jumlahapproval=' + jumlahapproval;
	
	for (i = 1; i <= jumlahapproval; i++) {
		param +="&persetujuan[" +i +"]=" + trim(document.getElementById("approvaltucketsupport_" + i).value);
	}
	
    tujuan = 'help_slave_show.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.closeAll();
					if(document.getElementById('outputticketsupport999999999')!=undefined){
						loaddataticketsupport999999999(idmenu);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function gantijenishelppopup(idhelp,idmenu,jenisbaru){
	param = 'method=gantijenishelppopup';
	param += '&idhelp=' + idhelp;
	param += '&jenisbaru=' + jenisbaru;
	
    tujuan = 'help_slave_show.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.closeAll();
					if(document.getElementById('outputticketsupport999999999')!=undefined){
						loaddataticketsupport999999999(idmenu);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
