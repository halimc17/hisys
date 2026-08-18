function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
	}	
}
function adddata(){
	i = document.getElementsByName("nama[]");
	e = document.getElementsByName("check[]");
	data=dtnm=""; jlh=0;
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){
			data+=i[n].innerHTML+",";
			jlh=jlh+1;
		}
	}
	document.getElementById('sumber').value = data.substr(0,data.length-1);
	// closeDialog();
	alertify.popup().destroy();
}

function clickall(){
	e = document.getElementsByName("check[]");
	h = document.getElementById('checkall');
	for(i=0;i<e.length;i++){
		if(e[i].disabled==false){			
			if(h.checked==true){
				e[i].checked=true;
			}else{
				e[i].checked=false;
			}
		}
	}
}

function popupsumber(){
	sumber = document.getElementById('sumber').value;
	param  = '';
	param += '&sumber=' + sumber;
	param += '&proses=popupsumber';
	tujuan = 'sdm_slave_2fpdanba.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// width    = '';
					// height   = '';
					// title    = "Sumber";
					// content  = "<fieldset ><legend>"+title+"</legend>";
					// content += "<div id=containerjurnal>";
					// content += "</div>";
					// content += "</fieldset>";

					// ev = 'event';
					// showDialog1(title, content, width, height, ev);
					
					// document.getElementById('containerjurnal').innerHTML = con.responseText;
					//alertify.popup("Kepada",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('400px','500px'); 
					alertify.popup("Detail",con.responseText).set({
						'resizable':true,
						'maximizable':true,
							onclose:function(){
								adddata()
							}
					}).resizeTo('400px','500px');

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisitipe() {
    unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    param = 'unit=' + unit + '&proses=getdivisitipe';
    tujuan = 'sdm_slave_2fpdanba.php';
    testvar = "test";
    post_response_text(tujuan, param, respog);
    function respog() {
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

}

function filPeriode() {
    unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    param = 'unit=' + unit + '&proses=filPeriode';
    tujuan = 'sdm_slave_2fpdanba.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //document.getElementById('periode').innerHTML = con.responseText;
                    arr = con.responseText.split("##");
                    document.getElementById('periode').innerHTML = arr[0];
                    document.getElementById('tipekar').innerHTML = arr[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function filKaryawan() {
    unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
    divisi = document.getElementById('divisi').options[document.getElementById('divisi').selectedIndex].value;
    periode = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
    tipekar = document.getElementById('tipekar').options[document.getElementById('tipekar').selectedIndex].value;

    param = 'unit=' + unit + '&divisi=' + divisi + '&periode=' + periode + '&tipekar=' + tipekar + '&proses=filKaryawan';
    tujuan = 'sdm_slave_2fpdanba.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('karyawan').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function filTipeKaryawan(val) {

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
    method = 'filTipeKaryawan';
    param += '&proses=' + method;
    //alert(param);

    tujuan = "sdm_slave_2fpdanba.php";

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('tipekar').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function preview(val, ev) {

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
    method = 'preview';
    param += '&proses=' + method + '&event=' + ev;
    tujuan = "sdm_slave_2fpdanba.php";

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('printContainer').innerHTML = con.responseText;
					leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function printexcel1(ev, val) {
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
    param += "&proses=preview&event=excel";

    // kdpbr=document.getElementById('kdpbr').value;
    // tgl1=document.getElementById('tgl1').value;

    // param='kdpbr='+kdpbr+'&tgl1='+tgl1+'&proses=preview1&type=excel';

    tujuan = 'sdm_slave_2fpdanba.php';
    judul = 'Report Ms.Excel';
    printFile(param, tujuan, judul, ev)
}

function pdf(val, ev) {
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
    param += "&proses=preview&event=pdf";
    
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_2fpdanba.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}

function printFile(param, tujuan, title, ev) {
    tujuan = tujuan + "?" + param;
    width = '';
    height = '';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog1(title, content, width, height, ev);
}

function lihatDetail(karyawanid, tanggal) {
	unit = document.getElementById('unit').value;
    divisi = document.getElementById('divisi').value;
    periode = document.getElementById('periode').value;
    tipekar = document.getElementById('tipekar').value;
    json = document.getElementById('json').value;
	
    param = 'proses=html&karyawanid=' + karyawanid + '&tanggal=' + tanggal;
    param += '&unit=' + unit;
    param += '&divisi=' + divisi;
    param += '&periode=' + periode;
    param += '&tipekar=' + tipekar;
    param += '&json=' + json;
	tujuan = 'sdm_slave_2fpdanba.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

var bankdataDetail = new Array();

// function lihatdetailpass(){
// 	var tab = document.getElementById("datadetail").textContent;
// }
