// JavaScript Document
/* Function getKebun
 * Fungsi untuk mengambil data kebun sesuai PTnya
 */
function getKebun() {
    var param = "pt="+getValue('pt')+'&proses=getKbn',
		tujuan = "kebun_slave_2panen";
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
					document.getElementById('kebun').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text(tujuan+'.php', param, respon);
}


/**
 * level1
 * Show Level 1 Container
 */
function level1() {
	var cont1 = getById('report-level-1'),
		cont2 = getById('report-level-2'),
		cont3 = getById('report-level-3'),
		nav1 = getById('navLevel1'),
		nav2 = getById('navLevel2'),
		nav3 = getById('navLevel3');		
		showLuas = getById('showLuas');
	cont1.style.display = '';
	cont2.style.display = 'none';
	cont3.style.display = 'none';
	showLuas.style.display = 'none';
	
	nav1.style['font-weight'] = 'bold';
	nav1.style['color'] = 'black';
	nav1.style['cursor'] = 'auto';
	nav1.removeAttribute('onclick');
	nav2.style['font-weight'] = 'normal';
	nav2.style['color'] = 'blue';
	nav2.style['cursor'] = 'pointer';
	nav2.setAttribute('onclick',"level2()");
	nav3.style.display = 'none';
}


/**
 * level2
 * Generate Level 2 Report
 */
function level2() {
	var param = "pt="+getValue('ptRep')+"&kebun="+getValue('kebunRep')+"&tipe="+
			getValue('tipeRep')+"&tanggal="+getValue('tanggalRep')+'&tipekebun='+getValue('tipekebun'),
		tujuan = "kebun_slave_2accreport";
    
	post_response_text(tujuan+'.php?mode=preview&level=2', param, respon);
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					var cont1 = getById('report-level-1'),
						cont2 = getById('report-level-2'),
						cont3 = getById('report-level-3'),
						nav1 = getById('navLevel1'),
						nav2 = getById('navLevel2'),
						nav3 = getById('navLevel3');
						showLuas = getById('showLuas');
					
					// Hide / Show Container
					cont1.style.display = 'none';
					cont2.style.display = '';
					cont3.style.display = 'none';
					showLuas.style.display = 'none';
					cont2.innerHTML = con.responseText;
					
					nav1.style['font-weight'] = 'normal';
					nav1.style['color'] = 'blue';
					nav1.style['cursor'] = 'pointer';
					nav1.setAttribute('onclick',"level1()");
					
					nav2.style['font-weight'] = 'bold';
					nav2.style['color'] = 'black';
					nav2.style['cursor'] = 'auto';
					nav2.removeAttribute('onclick');
					
					if (getValue('tipeRep')=='BIBIT') {
						nav3.style.display = 'none';
					} else {
						nav3.style.display = '';
					}
					nav3.style['font-weight'] = 'normal';
					//nav3.style['color'] = 'blue';
					//nav3.style['cursor'] = 'pointer';
					//nav3.setAttribute('onclick',"level3()");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/**
 * level3
 * Generate Level 3 Report
 */
function level3(status, tahuntanam,luasha) {
	var param = "pt="+getValue('ptRep')+"&kebun="+getValue('kebunRep')+"&tipe="+
			getValue('tipeRep')+"&tanggal="+getValue('tanggalRep')+"&statustanam="+
			status+"&tahuntanam="+tahuntanam+'&tipekebun='+getValue('tipekebun'),
		tujuan = "kebun_slave_2accreport";
    
	post_response_text(tujuan+'.php?mode=preview&level=3', param, respon);
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					var cont1 = getById('report-level-1'),
						cont2 = getById('report-level-2'),
						cont3 = getById('report-level-3'),
						nav1 = getById('navLevel1'),
						nav2 = getById('navLevel2'),
						nav3 = getById('navLevel3');
						showLuas = getById('showLuas');
					
					// Hide / Show Container
					cont1.style.display = 'none';
					cont2.style.display = 'none';
					cont3.style.display = '';
					showLuas.style.display = '';
					labelluas.innerHTML = luasha+" HA";
					cont3.innerHTML = con.responseText;
					
					nav2.style['font-weight'] = 'normal';
					nav2.style['color'] = 'blue';
					nav2.style['cursor'] = 'pointer';
					nav2.setAttribute('onclick',"level2()");
					
					nav3.style['font-weight'] = 'bold';
					nav3.style['color'] = 'black';
					nav3.style['cursor'] = 'auto';
					nav3.removeAttribute('onclick');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

/**
 * level4
 * Generate Level 4 Report (Detail Level 3)
 */
// function level4(tipeReport, akun, kodebarang) {
	// var param = "pt="+getValue('ptRep')+"&kebun="+getValue('kebunRep')+"&tipe="+
			// getValue('tipeRep')+"&tanggal="+getValue('tanggalRep')+"&statustanam="+
			// status+"&tahuntanam="+tahuntanam,
	// tujuan = "kebun_slave_2accreportdetail";
    
	// param += "&tipe="+tipeReport+"$noakun="+akun;
	// if (typeof kodebarang != 'undefined') {
		// param += "&kodebarang="+kodebarang;
	// }
	// post_response_text(tujuan+'.php?mode=preview&level=4', param, respon);
	
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
					// // Result
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

String.prototype.ucfirst = function()
{
    return this.charAt(0).toUpperCase() + this.substr(1);
}

function level4(event, tipeReport, akun, namakegiatan,kodebarang) {
	document.getElementById('title').value = tipeReport;
	document.getElementById('noakun').value = akun;
	document.getElementById('namakegiatan').value = namakegiatan;
	document.getElementById('kodebarang').value = kodebarang;
	var param = "pt="+getValue('ptRep')+"&kebun="+getValue('kebunRep')+"&tipe="+
			getValue('tipeRep')+"&tanggal="+getValue('tanggalRep')+"&statustanam="+
			getValue('statustanam')+"&tahuntanam="+getValue('tahuntanam')+"&mode=preview&level=4&title="+tipeReport+"&noakun="+akun+"&namakegiatan="+namakegiatan+'&tipekebun='+getValue('tipekebun');
	if (typeof kodebarang != 'undefined') {
		param += "&kodebarang="+kodebarang;
	}
	tujuan = 'kebun_slave_2accreport.php'+"?"+param;
	width='800';
	height='400';

	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1('View Details '+tipeReport.ucfirst(),content,width,height,event); 
}