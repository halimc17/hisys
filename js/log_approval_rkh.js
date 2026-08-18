function prpopup(noPP, title, ev) {
	width = '';
	height = '';
	content = "<fieldset><legend>" + noPP + "</legend><div id=prcontainer></div></fieldset><input type=hidden id=datPP name=datPP value=" + noPP + " />";
	showDialog1(title, content, width, height, ev);
}

function viewFile(response,ev){
	title='RENCANA KERJA HARIAN';	
	width='';
	height='400';
	content= response;
	showDialog1(title,content,width,height,ev); 	
}

function viewdata(ev,tujuan,nomortransaksi){
	param='view&notransaksi='+nomortransaksi+'&for=viewdetail';
	getSlave(param,'',ev,viewFile);
}

function getSlave(switchcase,ele,valuefor,funct) {
    var param = "";
	var prosees = ""
	var workwarp = document.getElementById('workwarp');
	var datadetail = document.getElementById('datadetail');
	var tanggal = document.getElementById('tanggal');
	var vr = "";
	if(typeof valuefor !== 'undefined'){
		vr = valuefor;
	}
	if(typeof switchcase !== 'undefined'){
		prosees = "?proses="+switchcase;
		if(switchcase == 'findblok' || switchcase == 'findblokinfo' || switchcase == 'findbarang' || switchcase == 'findsatuanmaterial'){
			if(typeof ele !== 'undefined'){
				param += "value="+ele.options[ele.selectedIndex].value;
				if(ele.id == "kegiatan"){
					var khususpemanen = document.getElementById('khususpemanen');
					if(ele.options[ele.selectedIndex].value == "611010101"){
						khususpemanen.style.display = "inline block";
					}else{
						khususpemanen.style.display = "none";
					}
					if(vr==""){
						var matrialbox = document.getElementById('matrialbox');
						var tr = matrialbox.getElementsByTagName('tr');
						for(i=0; i<tr.length; i++){
							tr[i].remove();
						}
					}else{
						for(i=0; i<vr.length; i++){
							param += "&kodebarang[]="+vr[i].kodebarang;
						}
					}
				}else if(ele.id == "blok"){
					janjangtbs.value = 0;
					var tbskg = document.getElementById('tbskg');
					tbskg.innerHTML = 0	;
				}else if(ele.id == "divisi"){
					var statusblok = document.getElementById('statusblok');
					statusblok.value = "";
				}
			}
		}else if(switchcase == 'findbjr'){
			if(typeof ele !== 'undefined'){
				param += "value="+ele.value;
				var blok = document.getElementById('blok');
				if(tanggal.value !== ""){
					param += "&tanggal="+tanggal.value;
				}
				if(typeof blok.options[blok.selectedIndex] !== "undefined"){
					param += "&blok="+blok.options[blok.selectedIndex].value;
				}
			}
		}else if(switchcase == 'listprestasi'){
			
			var asisten = document.getElementById('asisten');
			if(typeof asisten.options[asisten.selectedIndex] !== "undefined"){
				asisten	= asisten.options[asisten.selectedIndex].value;
			}else{
				asisten = "";
			}
			var divisi = document.getElementById('divisi');
			if(typeof divisi.options[divisi.selectedIndex] !== "undefined"){
				divisi	= divisi.options[divisi.selectedIndex].value;
			}else{
				divisi = "";
			}
			
			if(tanggal.value !=="" && asisten !=="" && divisi !==""){
				param = "tanggal="+tanggal.value+"&asisten="+asisten+"&divisi="+divisi;
				//POST 
				post_response_text('kebun_slave_rkh.php'+prosees, param, respon);
			}
		}else if(switchcase == 'showadd'){
			if(typeof ele !== "undefined" && ele !== ""){
				dataPar = ele.split(',');
				param = "nomortransaksi="+dataPar[0];
				param += "&nomorurut="+dataPar[1];
			}
		}else if(switchcase == 'ajukanrkh'){
			if(typeof valuefor !== "undefined"){
				param = "notransaksi="+valuefor;
			}
		}
	if(switchcase !== 'listprestasi'){
		//POST 
		post_response_text('kebun_slave_rkh.php'+prosees, param, respon);
	}
}else{
	var carinorhk 	= document.getElementById('carinorhk').value;
	var cariDivisi 	= document.getElementById('cariDivisi').value;
	var cariTanggal = document.getElementById('cariTanggal').value;
	param ="default=true";
	if(cariTanggal.trim() !==""){
		param += "&tanggal="+cariTanggal;
	}
	if(cariDivisi.trim() !==""){
		param += "&divisi="+cariDivisi;
	}
	if(carinorhk.trim() !==""){
		param += "&nomortransaksi="+carinorhk;
	}
	post_response_text('kebun_slave_rkh.php', param, respon);
}
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					if(funct){
						eval(funct(con.responseText,valuefor));
					}else{
						if(typeof switchcase !== 'undefined'){
							if(switchcase == 'findblok'){
								var blockid = document.getElementById('blok');
								blockid.innerHTML = con.responseText;
								getSlave('listprestasi');
							}else if(switchcase == 'findblokinfo'){
								findblokinfo(con.responseText);
								if(vr !== ""){
									setValue(vr.prestasi[0].kodekegiatan,'kegiatan','input');
									getSlave('findbarang',document.getElementById('kegiatan'),vr.material);								
								}
							}else if(switchcase == 'findbarang'){
								onchangeKegiatan(con.responseText);
								if(vr !== ""){
									if(vr.length > 0){
										createMaterial(vr);
									}
								}
							}else if(switchcase == 'findsatuanmaterial'){
								var satuanmaterial = document.getElementById('satuanmaterial');
								satuanmaterial.innerHTML = con.responseText;
							}else if(switchcase == 'findbjr'){
								var tbskg = document.getElementById('tbskg');
								tbskg.innerHTML = con.responseText;
							}else if(switchcase == 'showadd'){
								workwarp.innerHTML = con.responseText;
							}
							else if(switchcase == 'listprestasi'){
								datadetail.innerHTML = con.responseText;
							}
							else if(switchcase == 'ajukanrkh'){
								setelahajukan(con.responseText,ele);
							}else{
								workwarp.innerHTML = con.responseText;
							}
							
						}else{
							workwarp.innerHTML = con.responseText;
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


function wew(notransaksi){
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_rkh_approval.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
						
						
					}
						// loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfilesx(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_rkh_approval.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
						
						
					}
						// loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdatarkh(id, kolom) {
	prpopup(id, 'Approval Form', 'event');
	notransaksi = id;
	met = 'get_form_approval';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'kebun_slave_rkh_approval.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (con.responseText == '') {
						document.getElementById('prcontainer').innerHTML = 'You are not registred in the list';
					} else {
						document.getElementById('prcontainer').innerHTML = "<input type=hidden id=kolom value=" + kolom + ">" + con.responseText;
						return con.responseText;
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

function nextapprovalrkh(tipe) {
	kolom = document.getElementById('kolom').value;
	comment = document.getElementById('comment_fr').value;
	notransaksi = document.getElementById('notransaksi').value;
	if(tipe!='approved'){
	userid = trim(document.getElementById('user_id').options[document.getElementById('user_id').selectedIndex].value);
		if (comment == '' || userid == '') {
			alert('Please compleate the form !');
			return;
		}		
	}else{
		if (comment == '') {
			alert('Please compleate the form !');
			return;
		}
	}
	document.getElementById('Ajukan').disabled = true;
	met = met.value = 'insert_nextapproval';
	param = 'comment=' + comment + '&method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	if(tipe!='approved'){
		param += '&userid=' + userid;
	}
	tujuan = 'kebun_slave_rkh_approval.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					closeDialog();
					getdetail('RKH');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function tolakrkh(id, kolom) {
	prpopup(id, 'Rejection Form', 'event');
	notransaksi = id;
	met = 'tolak';
	param = 'method=' + met + '&notransaksi=' + notransaksi + '&kolom=' + kolom;
	tujuan = 'kebun_slave_rkh_approval.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('prcontainer').innerHTML = "<input type=hidden id=kolom value=" + kolom + ">" + con.responseText;
					return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function inserttolakrkh(klm) {
	notransaksi = trim(document.getElementById('notransaksi').value);
	met = 'inserttolak';
	kolom = klm;
	comment = trim(document.getElementById('cmnt_tolak').value);
	if (comment == '') {
		alert('Please leave a note');
	} else {
		param = 'notransaksi=' + notransaksi + '&method=' + met + '&comment=' + comment + '&kolom=' + kolom;
		tujuan = 'kebun_slave_rkh_approval.php';
		function respog() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						closeDialog();
						getdetail('RKH');
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