function getsubunit() {
	kodeorg = document.getElementById('kodeorg').value;
  
    param = "method=getsubunit&kodeorg=" + kodeorg;
    tujuan = "vhc_slave_rkh.php";
    post_response_text(tujuan, param, respog);
  
    function respog() {
      if (con.readyState == 4) {
        if (con.status == 200) {
          busy_off();
          if (!isSaveResponse(con.responseText)) {
            alert(con.responseText);
          } else {
            document.getElementById("div").innerHTML = con.responseText;
          }
        } else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  }


function excel(ev, tujuan) {
	unitexp = document.getElementById('unitexp').value;
	perexp = document.getElementById('perexp').value;
	if (unitexp == '' || perexp == '') {
		alert('Lengkapi unit dan periode.');
		return;
	}
	judul = 'Report Ms.Excel';
	param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
	printnopopup(tujuan+'?'+param);
}
function add_new_data(){
	document.getElementById('header').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	cancel();
}
function form() {
	width = '720';
	height = '';
	//nopp=document.getElementById('nopp_'+id).value;
	content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:300px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}
function html(div, tgl) {
	// form();
	param = 'method=html' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
                    alertify.popup("Detail Rencana Kerja Harian ",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','40%');
					// document.getElementById('containerd').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function displayList() {
	setValue2('divsch',null);
	document.getElementById('tglsch').value = '';
	document.getElementById('listData').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	loaddata(0);
}
function edit(kodeorg, div, tgl) {
	setValue2('kodeorg',kodeorg);
	setValue2('div',div);
	setValue2('tgl',tgl);
	document.getElementById('listData').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	//document.getElementById('detail').style.display='block';
	detail(div, tgl);
}
function editdetail(div, kodekend, tgl, driver, pekerjaan, keterangan, satuan, fisik) {
	document.getElementById('kodekend').value = kodekend;
	document.getElementById('kodekend').disabled = true;
	document.getElementById('driver').value = driver;
	document.getElementById('driver').disabled = true;
	document.getElementById('pekerjaan').value = pekerjaan;
	document.getElementById('pekerjaan').disabled = true;
	document.getElementById('keterangan').value = keterangan;
	document.getElementById('satuan').value = satuan;
	document.getElementById('fisik').value = fisik;
	document.getElementById('method').value = 'update';
}
function deletedetail(div, tgl, driver, pekerjaan) {
	param = 'method=deletedetail' + '&div=' + div + '&tgl=' + tgl + '&driver=' + driver + '&pekerjaan=' + pekerjaan;
	tujuan = 'vhc_slave_rkh.php';
	//if(confirm(' Anda yakin ingin menghapus nomor transaksi'))
	// {
	post_response_text(tujuan, param, respog);
	//}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail(div, tgl);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function del(div, tgl) {
	param = 'method=delete' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'vhc_slave_rkh.php';
	if (confirm(' Anda yakin ingin menghapus nomor transaksi')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML = con.responseText;
					//loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function posting(div, tgl, numrow) {
	param = 'method=posting' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'vhc_slave_rkh.php';
	if (confirm('Anda yakin ingin memposting transaksi unit ' + div + ' pada tanggal ' + tgl + ' ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[5].innerHTML = '';
					x.cells[6].innerHTML = '';
					x.cells[7].innerHTML = '';
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function unposting(div, tgl, numrow) {
	param = 'method=unposting' + '&div=' + div + '&tgl=' + tgl;
	tujuan = 'vhc_slave_rkh.php';
	if (confirm('Anda yakin ingin unposting transaksi unit ' + div + ' pada tanggal ' + tgl + ' ??')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contain').innerHTML=con.responseText;
					x = document.getElementById('tr_' + numrow);
					x.cells[5].innerHTML = '';
					x.cells[6].innerHTML = '';
					x.cells[7].innerHTML = '';
					loaddata(0);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function detail() {
	kodeorg = document.getElementById('kodeorg').value;
	div = document.getElementById('div').value;
	tgl = document.getElementById('tgl').value;
	if (div == '' || tgl == '' || kodeorg == '') {
		alert('Lengkapi Pengisian');
		return;
	}
	param = 'method=detail';
	param += '&div=' + div + '&tgl=' + tgl + '&kodeorg=' + kodeorg;
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('detail').style.display = 'block';
					document.getElementById('detail').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					loaddatadetail(div, tgl);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getdivisi(kdkbn, kdblok,jns_pekerjan,kodedept) {

	if (document.getElementById('pekerjaan').value == '' && document.getElementById('kodekend').value != '') {
		alert("Jenis Pekerjaan harus diisi terlebih dahulu!");
		document.getElementById('lokasi_kerja').selectedIndex = 0;
		return false;
	}

	if ((kdkbn == '') && (kdblok == '')) {
		lokasi = document.getElementById('lokasi_kerja').value;
		pekerjaan = document.getElementById('pekerjaan').value;
		param = 'lokasi=' + lokasi + '&pekerjaan=' + pekerjaan + '&method=getdivisi';
	} else {
		lokasi = kdkbn;
		Blok = kdblok;
		pekerjaan = document.getElementById('pekerjaan').value;
		param = 'lokasi=' + lokasi + '&pekerjaan=' + pekerjaan + '&Blok=' + Blok + '&method=getdivisi';
	}
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('divisi').innerHTML = con.responseText;
					$('#divisi').select2();
					getsatuan();
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
	divsch = document.getElementById('divsch').value;
	tglsch = document.getElementById('tglsch').value;
	param = 'method=loaddata&page=' + page;
	if (divsch != '') {
		param += '&divsch=' + divsch;
	}
	if (tglsch != '') {
		param += '&tglsch=' + tglsch;
	}
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
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
function cancel() {
	document.getElementById('detail').style.display = 'none';
	document.getElementById('tomboldetail').disabled = false;
	document.getElementById('div').disabled = false;
	document.getElementById('tgl').disabled = false;
	document.getElementById('kodeorg').disabled = false;
	setValue2('div',null);
	setValue2('tgl',null);
	setValue2('kodeorg',null);
}
function loaddatadetail(div, tgl) {
	document.getElementById('tomboldetail').disabled = true;
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('div').disabled = true;
	div = document.getElementById('div').value;
	document.getElementById('tgl').disabled = true;
	tgl = document.getElementById('tgl').value;
	param = 'method=loaddatadetail';
	param += '&div=' + div + '&tgl=' + tgl;
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('loaddatadetail').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdata() {
	kodekend = document.getElementById('kodekend').value;
	param = 'method=getdata' + '&kodekend=' + kodekend;
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(con.responseText.trim() == ''){
						document.getElementById('driver').innerHTML = '';
					}else{
						document.getElementById('driver').innerHTML = con.responseText;
					}
					//getkg();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getsatuan() {
	pekerjaan = document.getElementById('pekerjaan').options[document.getElementById('pekerjaan').selectedIndex].value;
	param = 'method=getsatuan' + '&pekerjaan=' + pekerjaan;
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('satuan').value = con.responseText;
					//getkg();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function savedetail() {
	kodeorg = document.getElementById('kodeorg').value;
	div = document.getElementById('div').value;
	tgl = document.getElementById('tgl').value;
	kodekend = document.getElementById('kodekend').value;
	driver = document.getElementById('driver').value;
	pekerjaan = document.getElementById('pekerjaan').value;
	blok = document.getElementById('blok').value;
	keterangan = document.getElementById('keterangan').value;
	satuan = document.getElementById('satuan').value;
	fisik = document.getElementById('fisik').value;
	method = document.getElementById('method').value;
	if ((kodekend == '' || driver == '' || pekerjaan == '' || fisik == '' || keterangan == '')) {
		alert('Lengkapi Pengisian.');
		return;
	}
	param = 'kodeorg=' + kodeorg + '&kodekend=' + kodekend + '&driver=' + driver;
	param += '&pekerjaan=' + pekerjaan + '&keterangan=' + keterangan + '&satuan=' + satuan + '&fisik=' + fisik;
	param += '&div=' + div + '&tgl=' + tgl;
	param += '&blok=' + $('#blok').val();
	param += '&method=' + method;
	tujuan = 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					cleardetail();
					setTimeout(() => {
						loaddatadetail(kodeorg, div, tgl);
					}, 300);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function cleardetail() {
	setValue2('kodekend',null);
	document.getElementById('kodekend').disabled = false;
	setValue2('driver',null);
	document.getElementById('driver').disabled = false;
	setValue2('pekerjaan',null);
	document.getElementById('pekerjaan').disabled = false;
	document.getElementById('keterangan').value = '';
	document.getElementById('satuan').value = '';
	document.getElementById('fisik').value = '';
}
// function getnotransaksi()
// {
// kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
// tgl=document.getElementById('tgl').value;
// param='tgl='+tgl+'&kodeorg='+kodeorg+'&method=getnotransaksi';
// tujuan='vhc_slave_rkh.php';
// post_response_text(tujuan, param, respog);
// function respog(){
// if (con.readyState == 4) {
// if (con.status == 200) {
// busy_off();
// if (!isSaveResponse(con.responseText)) {
// alert(con.responseText);
// }
// else {
// document.getElementById('notransaksi').value=trim(con.responseText);
// }
// }
// else {
// busy_off();
// error_catch(con.status);
// }
// }
// }
// }

function getblok() {
	divisi 		= document.getElementById('divisi').value;
	pekerjaan 	= document.getElementById('pekerjaan').value;
	lokasi_kerja= document.getElementById('lokasi_kerja').value;
	param 		= 'method=getblok' + '&divisi=' + divisi + '&jns_kerja='+pekerjaan + '&lokasi_kerja='+ lokasi_kerja;
	tujuan 		= 'vhc_slave_rkh.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function printpdf(unitexp,perexp){
	param = 'method=excel&tipe=pdf&unitexp='+unitexp+'&perexp='+perexp;
	tujuan = tujuan+'?' + param;
	alertify.popuppdf().destroy();
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_rkh.php?"+param+"'></iframe>").set({'resizable':true,'maximizable':true,'startMaximized':true,'overflow':false}).resizeTo('80%','70%');
}