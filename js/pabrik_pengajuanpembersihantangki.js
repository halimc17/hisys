
function gettangki(kodetangki,kodebarang) {
    kodeorg=document.getElementById('kodeorg').value; 
    param='method=gettangki'+'&kodeorg='+kodeorg+'&kodetangki='+kodetangki;
    tujuan='pabrik_pengajuanpembersihantangki_slave.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('kodetangki').innerHTML=con.responseText;
					getbarang(kodebarang);	 
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}


function getbarang(kodebarang){
    kodetangki=document.getElementById('kodetangki').value;
    kodeorg=document.getElementById('kodeorg').value; 
    param='method=getbarang'+'&kodeorg='+kodeorg+'&kodetangki='+kodetangki+'&kodebarang='+kodebarang;
    tujuan='pabrik_pengajuanpembersihantangki_slave.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('kodebarang').innerHTML=con.responseText;
					loadfiles();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}



function editht(notransaksi) {
	param = 'method=geteditht' + '&notransaksi=' + notransaksi;
	tujuan = 'pabrik_pengajuanpembersihantangki_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					ar = con.responseText.split("###");
					document.getElementById('notransaksi').value = ar[0];
					document.getElementById('kodeorg').value = ar[1];
					document.getElementById('kodeorg').disabled = true;
					document.getElementById('kodetangki').value = ar[2];
					document.getElementById('kodebarang').value=ar[3];
					document.getElementById('tanggal').value=ar[4];
					document.getElementById('sawal').value=ar[5];
					document.getElementById('keterangan').value=ar[6];
					document.getElementById('tanggalcuci').value=ar[7];
					document.getElementById('listdata').style.display='none';
					document.getElementById('header').style.display='block';
					document.getElementById('detail').style.display='block';
					gettangki(ar[2],ar[3]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}



function displaylist() {
	cancelht();
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('notransaksisch').value='';
    document.getElementById('tanggalselesaisch').value='';
    document.getElementById('tanggalmulaisch').value='';
    document.getElementById('kodeorgsch').value='';
	loaddata(0);
}

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}


function loaddata(num) {
	
	if (document.getElementById('listdata') !== null) {
		document.getElementById('listdata').style.display = 'block';
	}
	if (document.getElementById('header') !== null) {
		document.getElementById('header').style.display = 'none';
	}
	if (document.getElementById('detail') !== null) {
		document.getElementById('detail').style.display = 'none';
	}
	
	notransaksi=document.getElementById('notransaksisch').value;
    tanggalmulai=document.getElementById('tanggalmulaisch').value;
    tanggalselesai=document.getElementById('tanggalselesaisch').value;
    kodeorg=document.getElementById('kodeorgsch').value;
    
	param = 'method=loaddata&page=' + num;
	param += '&notransaksi=' + notransaksi+'&tanggalmulai=' + tanggalmulai+'&tanggalselesai=' + tanggalselesai;
	param += '&kodeorg=' + kodeorg;
	// alert(param);
	tujuan = 'pabrik_pengajuanpembersihantangki_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					leftFixedTable();
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	cancelht();
}


function cancelht(){
	document.getElementById('detail').style.display = 'none';
	document.getElementById('notransaksi').value='';
	document.getElementById('kodebarang').value='';
	document.getElementById('keterangan').value='';
	document.getElementById('kodeorg').value='';
	document.getElementById('kodetangki').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('sawal').value='0';
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('kodetangki').disabled=false;

	// document.getElementById('kodeunit').disabled=false;
}


function saveht(parameter) {
	method='saveht';
	tujuan='pabrik_pengajuanpembersihantangki_slave.php';
    var passP = parameter.split('###');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
		param += "&"+passP[i]+"="+getValue(passP[i]);
    }
	param += '&method=' + method;
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    // alertify.alert('Informasi',con.responseText);
					alertify.alert('Informasi',con.responseText);
                } else {
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('detail').style.display='block';
					loadfiles();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}



/*
#===================================================================================================================
#================= detail FILE =====================================================================================
#===================================================================================================================
*/



function submitfile() {
	var notransaksi = document.getElementById("notransaksi").value;
	var kriteriaefil= document.getElementById("kriteriaefil").value;
	var file        = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
    formdata.append("notransaksi", trim(notransaksi));
    formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
    }
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "pabrik_pengajuanpembersihantangki_slave.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles();
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles() {
    notransaksi = document.getElementById('notransaksi').value;
    param       = 'method=loadfiles&notransaksi='+trim(notransaksi);
	tujuan      = 'pabrik_pengajuanpembersihantangki_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					document.getElementById('listfiles').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi,namafile) {
	param = 'method=deletefile&notransaksi=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'pabrik_pengajuanpembersihantangki_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function formajukan(notransaksi,page){
	method = 'formajukan';
	param='';
	param += '&notransaksi=' + notransaksi + '&page=' + page;
	param += '&method=' + method;
	post_response_text(tujuan, param, respon);
	tujuan = 'pabrik_pengajuanpembersihantangki_slave.php';
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi',con.responseText);
                } else {
				   alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','60%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
} 
 
 
function deleteht(notransaksi,page){
	param = 'method=deleteht';
	param+='&notransaksi='+notransaksi;
	tujuan = 'pabrik_pengajuanpembersihantangki_slave.php';
	alertify.confirm("Informasi","Hapus transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	// post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


 
 
// #= diubah menjadi persetujuan
function saveajukan(notransaksi,page,maxaproval) {
	param='';
	method = 'saveajukan';
	strper='';
	for(i=1;i<=maxaproval;i++){
	 strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value)
	}
	param += '&notransaksi=' + notransaksi;
	param += '&maxaproval=' + maxaproval;
	param += '&method=' + method;
	param+=strper;	
	tujuan = 'pabrik_pengajuanpembersihantangki_slave.php';
	alertify.confirm("Informasi","Ajukan transaksi : "+notransaksi+" ???",
		function(){
			post_response_text(tujuan, param, respon);
		},
		function(){
			return;
		}
	);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					alertify.popup().destroy();
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}  
} 
 
