function getnotiket() {
	kodeorg = document.getElementById('kodeorg').value;
	tanggal = document.getElementById('tanggal').value;
	param = "proses=getnotiket&kodeorg=" + kodeorg+'&tanggal=' + tanggal;
	tujuan = "pabrik_slave_gradingtbs.php";
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById("notiket").innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function displayList(){
    document.getElementById('formnew').style.display ='none';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('listcari').style.display = 'block';
    document.getElementById('listData').style.display ='block';
	loaddata(0);
}

function formbaru(){
    document.getElementById('listData').style.display ='none';
    document.getElementById('formnew').style.display ='block';
    document.getElementById('detail').style.display = 'none';
    document.getElementById('listcari').style.display = 'none';
    document.getElementById('proses').value ='insert_header';
	clear_form();
    setTimeout(() => {
        unlock_header_form();
    }, 400);
	// bersih_form_pekerjaan();
}

function unlock_header_form() {
	document.getElementById('kodeorg').disabled = false;
	document.getElementById('notiket').disabled = false;
	document.getElementById('tanggal').disabled = false;
	document.getElementById('save_kepala').disabled = false;
	document.getElementById('save_kepala').style.visibility = 'visible';
}

function lock_header_form() {
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('notiket').disabled = true;
	document.getElementById('tanggal').disabled = true;
	document.getElementById('save_kepala').disabled = true;
	document.getElementById('save_kepala').style.visibility = 'hidden';
}

function clear_form() {
	document.getElementById('notiket').innerHTML = "<option value=''>Pilih Data</options>";
	document.getElementById('tanggal').value = '';
	document.getElementById('save_kepala').value = '';
	let select = document.getElementById("blok");
	for (let i = select.options.length - 1; i >= 0; i--) {
	if (select.options[i].selected) {
		select.remove(i);
	}
	}
}

function batalcariDataTransaksi(){
	document.getElementById('tgl_cari').value='';
	document.getElementById('tgl_carisd').value='';
	document.getElementById('txtCari').value='';
	loaddata(0);
}

function enter(e) {
	key = getKey(e);
	if (key == 13) {
		loaddata(0);
		return true;
	} else {
		return tanpa_kutip_dan_sepasi(e);
	}
}

function cancel_kepala_form() {
	document.getElementById('save_kepala').disabled = true;
	unlock_header_form();
	setTimeout(function() {
		clear_form();		
	}, 100);
}

function hitungtotalgrading(no) {
    let ntotal = 0;
    let ptotal = 0;
    let persen = 0;
    const ttljlh = document.getElementsByName('jumlah');

    for (let i = 0; i < ttljlh.length; i++) {
        let jlh = parseFloat(ttljlh[i].value) || 0; // Ambil nilai langsung dari elemen
        ntotal += parseFloat(jlh); 
    }

    for (let i = 1; i <= ttljlh.length; i++) {
        persen   =(parseFloat(getValue('jumlah'+i) || 0) * 100)/ntotal;
        document.getElementById('persen'+i).innerHTML = numberFormat(persen,2)+' %'
        ptotal  += parseFloat(persen)
    }
    document.getElementById('ttlsampel').innerHTML  =numberFormat(ntotal,2) 
    document.getElementById('ttlpersen').innerHTML  =numberFormat(ptotal,2)+' %'
}

function fillField(noTrans, Thn) {
    document.getElementById('formnew').style.display ='block';
    document.getElementById('detail').style.display = 'block';
    document.getElementById('listcari').style.display = 'none';
    document.getElementById('listData').style.display = 'none';
	notrn = noTrans;
	param = 'notiket=' + notrn + '&proses=getData';
	tujuan = 'pabrik_slave_gradingtbs.php';
	post_response_text(tujuan, param, respog);
	async function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('proses').value = 'update_head';
					ar = JSON.parse(con.responseText);
                    setValue2('kodeorg',ar.pabrik);
					data = ar.blok.split(",");
					$('#blok').val(data).trigger("change");
                    const originalDate = ar.tanggal; // Format: YYYY-MM-DD
                    const [year, month, day] = originalDate.split('-');
                    const tanggalnormal = `${day}-${month}-${year}`;
                    setValue2('tanggal',tanggalnormal);			
                    setTimeout(() => {
						getnotiket()
						setTimeout(() => {
							setValue2('notiket',ar.notiket);
							loaddetail();
						}, 400);
                    }, 100);
					document.getElementById('tanggal').disabled = true;
					document.getElementById('kodeorg').disabled = true;
					document.getElementById('notiket').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function save_header() {
	kdOrg       = getValue('kodeorg');
	tgl_kerja   = getValue('tanggal');
	notiket     = getValue('notiket');
	pro         = getValue('proses');

    if(kdOrg == ''){
        alertify.alert('Informasi ','Kode organisasi harus dipilih.');
        return false;
    }else if(tgl_kerja == ''){
        alertify.alert('Informasi ','Tanggal tidak boleh kosong.');
        return false;
    }else if(getValue('blok') == ''){
        // alertify.alert('Informasi ','Blok tidak boleh kosong.');
        // return false;
    }

	param	= 'tanggal=' + tgl_kerja + '&kodeorg=' + kdOrg  + '&proses=' + pro + '&notiket=' + notiket;
	tujuan 	= 'pabrik_slave_gradingtbs.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isidt = con.responseText.split("####");
                    document.getElementById('detail').style.display = 'block';
					lock_header_form();
					loaddetail();
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

function loaddata(num) {
	txtTgl      = document.getElementById('tgl_cari').value;
	tgl_carisd  = document.getElementById('tgl_carisd').value;
	txtCari     = document.getElementById('txtCari').value;
	param 		= 'proses=loaddata&page=' + num;
	param 		+="&tgl_cari=" + txtTgl + "&txtCari=" + txtCari + '&tgl_carisd=' +tgl_carisd;
	tujuan 		= 'pabrik_slave_gradingtbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					document.getElementById('contain').innerHTML=data[0];
					document.getElementById('containfoot').innerHTML=data[1];
					leftFixedTable();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddetail() {
    notiket= getValue('notiket');
    param 	= 'notiket=' + notiket;
    param 	+= '&proses=loaddetail';
    tujuan 	= 'pabrik_slave_gradingtbs.php';

    async function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    dt = con.responseText.split('##');
                    document.getElementById('containdetail').innerHTML = dt[0];
                    hitungtotalgrading(1)
                    document.getElementById('containdetail2').innerHTML = dt[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respog);
}

function save_detail() {
	pro         = getValue('proses_pekerjaan');
	notrans     = getValue('notiket');
	pabrik      = getValue('kodeorg');
	tanggal     = getValue('tanggal');
    param       = 'notiket=' + notrans + '&pabrik=' + pabrik + '&tanggal=' + tanggal + '&proses=' + pro ;
	param 		+= '&blok=' + $('#blok').val();

    ttljumlah   = document.getElementsByName('jumlah');
    ttljlh      = document.getElementsByName('jlh');
    
    // Loop jumlah
    for (let i = 0; i < ttljumlah.length; i++) {
        let jumlah = parseFloat(ttljumlah[i].value) || 0;
        let kode = document.getElementById('kode'+ (i + 1)).innerText;
        
        param += '&jumlah' + (i + 1) + '=' + jumlah; // index +1 agar mulai dari jumlah1
        param += '&kode' + (i + 1) + '=' + encodeURIComponent(kode); // index +1 agar mulai dari jumlah1
    }

    // Loop jlh
    for (let ii = 0; ii < ttljlh.length; ii++) {
        let jlh = parseFloat(ttljlh[ii].value) || 0;
        let kode2 = document.getElementById('kode2'+(ii + 1)).textContent;
        
        param += '&jlh' + (ii + 1) + '=' + jlh; // index +1 agar mulai dari jlh1
        param += '&kode2' + (ii + 1) + '=' + kode2; // index +1 agar mulai dari jlh1
    }
    param += '&ttljumlah='+ttljumlah.length+'&ttljlh='+ttljlh.length;
	
	tujuan = 'pabrik_slave_gradingtbs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
                    document.getElementById('detail').style.display = 'none';
					unlock_header_form();
					setValue2('notiket',null);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function delHead(noTran,page) {
	notrans = noTran;
	param = 'notiket=' + notrans + '&proses=deleteHead';
	tujuan = 'pabrik_slave_gradingtbs.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	if (confirm("Data Graiding dengan notiket = "+notrans+" akan dihapus, apakah anda yakin ?")) {
		post_response_text(tujuan, param, respog);
	} else {
		return;
	}
}

function postingdata(notrans,kdvhc,tgl,page) {
    param = 'proses=postingdata';
    param += '&notiket='+notrans+'&kdVhc='+kdvhc+'&tgl='+tgl;
    tujuan = 'pabrik_slave_gradingtbs.php';
    if (confirm("Apakah anda yakin ingin memposting data dengan notransaksi = "+notrans+"?")) {
        post_response_text(tujuan, param, respog);
    } else {
        return;
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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

function pdflaporan(notiket,novhc,tgl)
{
	param 	='notiket='+notiket+'&nokendaraan='+novhc+'&tanggal='+tgl+'&proses=pdflaporan';
	tujuan	='pabrik_slave_gradingtbs.php';
    
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+'?'+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}