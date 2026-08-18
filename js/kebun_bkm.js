function changelabel(e){
	if(e.checked){
		document.getElementById('labelbahasa').innerHTML = 'EN';
	}else{
		document.getElementById('labelbahasa').innerHTML = 'ID';
	}
}

function loaddataexcel() {
	document.getElementById('listData').style.display = 'block';
    document.getElementById('judul_header').style.display='block';
	if(document.getElementById('header')!=undefined){
		document.getElementById('header').style.display = 'none';
	}
	if(document.getElementById('detail')!=undefined){
		document.getElementById('detail').style.display = 'none';
	}
	
	notransaksisch=document.getElementById('notransaksisch').value;
	tglmulai      =document.getElementById('tglmulai').value;
	tglselesai    =document.getElementById('tglselesai').value;
	divsch        =document.getElementById('divsch').value;
	postingsrc    =document.getElementById('postingsrc').value;
	periodesch    =document.getElementById('periodesch').value;
	nobkmsch      =document.getElementById('nobkmsch').value;
	mandorsrc     =document.getElementById('mandorsrc').value;
	stsawal       =document.getElementById('stsawal').value;
	
	param = 'method=loaddataexcel&stsawal='+stsawal;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
    if (notransaksisch != '') {
        param += '&notransaksisch=' + notransaksisch;
    }
	if (tglmulai != '') {
        param += '&tglmulai=' + tglmulai;
    }
	if (tglselesai != '') {
        param += '&tglselesai=' + tglselesai;
    }
	if (postingsrc != '') {
        param += '&postingsrc=' + postingsrc;
    }
	if (periodesch != '') {
        param += '&periodesch=' + periodesch;
    }
	if (nobkmsch != '') {
        param += '&nobkmsch=' + nobkmsch;
    }
	if (mandorsrc != '') {
        param += '&mandorsrc=' + mandorsrc;
    }
	
	// alert(param);
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_bkm.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');		
}





function getunit(){
    filterpt=document.getElementById('filterpt').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=getunit';
    param += '&filterpt=' + filterpt;
    param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + getValue('divisi');
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert('Info',con.responseText);
                } else {
					data = con.responseText.split("####");
                    document.getElementById('filterunit').innerHTML = trim(data[0]);
                    document.getElementById('filterdivisi').innerHTML = trim(data[1]);
					getdatadivisi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdivisi(){
    filterunit=document.getElementById('filterunit').value;
    
    param = 'method=getdivisi';
    param += '&filterunit=' + filterunit;
	param += '&divisi=' + getValue('divisi');
	param += '&tgl=' + getValue('tgl');
	param += '&kodeorg=' + getValue('kodeorg');
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('filterdivisi').innerHTML = con.responseText;
					getdatadivisi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getdivmdr(sumber){
	kodeorg=document.getElementById('kodeorg').value;
	divisi =document.getElementById('divisi').value;
	tgl    =document.getElementById('tgl').value;
	
	
    param='method=getdivmdr&tgl='+tgl+'&kodeorg='+kodeorg;
	param += "&divisi="+divisi;
	param += "&sumber="+sumber;
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert('Info',con.responseText);
                } else {
					data = con.responseText.split("####");
					if(sumber=='kebun'){						
						document.getElementById('divisi').innerHTML = data[0];
					}
					document.getElementById('mandor').innerHTML = data[1];
					document.getElementById('mandor1').innerHTML = data[2];
					document.getElementById('kerani').innerHTML = data[3];
					document.getElementById('asst').innerHTML = data[4];
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function unhideheader(){
    document.getElementById('header_trans').style.display='block';
    document.getElementById('judul_header').style.display='block';
    document.getElementById('hidebtn').style.display='block';
    document.getElementById('unhidebtn').style.display='none';
}

function hideheader(){
    document.getElementById('header_trans').style.display='none';
    document.getElementById('judul_header').style.display='none';
	document.getElementById('hidebtn').style.display='none';
	document.getElementById('unhidebtn').style.display='';
}

function detailDataExcel(notransaksi,numRow,ev,tipe,jenis){
    param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
	title="Data Detail";
		
	//alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	printnopopup("kebun_slave_operasional_print_detailx.php?"+param);
	
        // showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+
        // " src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>",'900','400',ev);	
        // var dialog = document.getElementById('dynamic1');
        // dialog.style.top = '50px';
        // dialog.style.left = '15%';
}
function detailData(notransaksi,numRow,ev,tipe,jenis){
	param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
	
	tujuan = 'kebun_slave_operasional_print_detailx.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
                    alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function detailPDF(notransaksi,numRow,ev,tipe) {
    param = "proses=pdf&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis=pdf";
    
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_operasional_print_detailx_pdf.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
    // showDialog1('Print PDF',"<iframe frameborder=0 style='width:885px;height:400px'"+
        // " src='kebun_slave_operasional_print_detailx_pdf.php?"+param+"'></iframe>",'900','400',ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
}

function postingData(notransaksi,numRow,abs) {
    var param = "notransaksi="+notransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
					alertify.popup().destroy();
					loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	alertify.confirm("Info","Akan dilakukan posting untuk transaksi nomor "+notransaksi+"<br><br>Data tidak dapat diubah setelah ini. Anda yakin ?",
		function(){
			if(abs=='absensi'){
				param += "&method=postingabsensi";
				post_response_text('kebun_slave_bkm.php', param, respon);
			} else if (abs=='project') {
				post_response_text('kebun_slave_operasional_postingx.php', param, respon);
			}
		},
		function(){
			return;
		}
	);
}

function postingDataDetail(notransaksi) {
    var param = "notransaksi="+notransaksi;

	post_response_text('kebun_slave_operasional_postingx_new.php', param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    //=== Success Response
					alertify.popup().destroy();
					getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewPostingData(notransaksi,numRow,ev,tipe) {
	param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi;
	
	tujuan = 'kebun_slave_detailview_postingbkm.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
                    alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function postingDataBkm(notransaksi) {
	param = "notransaksi=" + notransaksi;
	tujuan = "kebun_slave_insertdetail_bkm.php";

	alertify.confirm("Info","Akan dilakukan posting untuk transaksi nomor "+notransaksi+"<br><br>Data tidak dapat diubah setelah ini. Anda yakin ?",
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
                    alertify.alert(con.responseText);
                } else {
                    postingDataDetail(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function postingDataBkmOld(notransaksi) {
//     rows_pres = document.getElementById('rows_pres').value;
// 	rows_matr = document.getElementById('rows_matr').value;

// 	param = "method=insertDetails";

// 	param += "&notransaksi=" + notransaksi;

// 	for (let i = 1; i <= rows_pres; i++) {
// 		notrans_pres = document.getElementById('notransx_'+i).innerHTML;
// 		nobkm_pres = document.getElementById('nobkmx_'+i).innerHTML;
// 		nourut_pres = document.getElementById('nourutx_'+i).innerHTML;
// 		divisi_pres = document.getElementById('divisix_'+i).innerHTML;
// 		nik_pres = document.getElementById('nikx_'+i).innerHTML;
// 		keg_pres = document.getElementById('kegx_'+i).innerHTML;
// 		indukblok_pres = document.getElementById('indukblokx_'+i).innerHTML;
// 		blokkecil_pres = document.getElementById('blokkecilx_'+i).innerHTML;
// 		hasilkerja_pres = document.getElementById('hasilkerjax_'+i).innerHTML;
// 		hk_pres = document.getElementById('hkx_'+i).innerHTML;
// 		umr_pres = document.getElementById('umrx_'+i).innerHTML;
// 		premi_pres = document.getElementById('premix_'+i).innerHTML;

// 		param 	+= "&notrans_pres["+i+"]="+notrans_pres;
// 		param 	+= "&nobkm_pres["+i+"]="+nobkm_pres;
// 		param 	+= "&nourut_pres["+i+"]="+nourut_pres;
// 		param 	+= "&divisi_pres["+i+"]="+divisi_pres;
// 		param 	+= "&nik_pres["+i+"]="+nik_pres;
// 		param 	+= "&keg_pres["+i+"]="+keg_pres;
// 		param 	+= "&indukblok_pres["+i+"]="+indukblok_pres;
// 		param 	+= "&blokkecil_pres["+i+"]="+blokkecil_pres;
// 		param 	+= "&hasilkerja_pres["+i+"]="+hasilkerja_pres;
// 		param 	+= "&hk_pres["+i+"]="+hk_pres;
// 		param 	+= "&umr_pres["+i+"]="+umr_pres;
// 		param 	+= "&premi_pres["+i+"]="+premi_pres;
// 	}

// 	for (let i = 1; i <= rows_matr; i++) {
// 		notrans_matr = document.getElementById('notransm_'+i).innerHTML;
// 		keg_matr = document.getElementById('kegiatanm_'+i).innerHTML;
// 		indukblok_matr = document.getElementById('indukblokm_'+i).innerHTML;
// 		kodegudang_matr = document.getElementById('kdgdgm_'+i).innerHTML;
// 		blokkecil_matr = document.getElementById('blkclm_'+i).innerHTML;
// 		kodebarang_matr = document.getElementById('kdbrgm_'+i).innerHTML;
// 		jmlbrg_matr = document.getElementById('jmlbrgm_'+i).innerHTML;
// 		jmlha_matr = document.getElementById('jmlham_'+i).innerHTML;

// 		param 	+= "&notrans_matr["+i+"]="+notrans_matr;
// 		param 	+= "&indukblok_matr["+i+"]="+indukblok_matr;
// 		param 	+= "&keg_matr["+i+"]="+keg_matr;
// 		param 	+= "&blokkecil_matr["+i+"]="+blokkecil_matr;
// 		param 	+= "&kodebarang_matr["+i+"]="+kodebarang_matr;
// 		param 	+= "&jmlbrg_matr["+i+"]="+jmlbrg_matr;
// 		param 	+= "&jmlha_matr["+i+"]="+jmlha_matr;
// 		param 	+= "&kodegudang_matr["+i+"]="+kodegudang_matr;
// 	}

// 	tujuan = "kebun_slave_bkm.php";
// 	// console.log(param);
// 	// alertify.alert("Testing!!!!");

// 	alertify.confirm("Info","Akan dilakukan posting untuk transaksi nomor "+notransaksi+"<br><br>Data tidak dapat diubah setelah ini. Anda yakin ?",
// 		function(){		
// 			post_response_text(tujuan, param, respon);
// 		},
// 		function(){
// 			return;
// 		}
// 	);
    
//     function respon() {
//         if (con.readyState == 4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alertify.alert(con.responseText);
//                 } else {
//                     // postingDataDetail(notransaksi);
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }

function validasiVerifikasi(notransaksi) {
	param = 'method=validasiVerifikasi';
	param += '&notransaksi='+notransaksi;
	// alertify.popuppdf("Detail Verifikasi","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_bkm.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	tujuan='kebun_slave_bkm.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
					detailVerifikasi(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function detailVerifikasi(notransaksi) {
	param = 'method=detailVerifikasi';
	param += '&notransaksi='+notransaksi;
	// alertify.popuppdf("Detail Verifikasi","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='kebun_slave_bkm.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	tujuan='kebun_slave_bkm.php';
	
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
					alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function insertVerifikasi(notransaksi) {
	getRowDt = document.getElementById('getRowDt').value;
	
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;

	param = 'method=insertVerifikasi';
	param += '&notransaksi='+notransaksi;
	param += '&row='+getRowDt;
	for (let getRow = 1; getRow <= getRowDt; getRow++) {
		kegiatandt = document.getElementById('kegiatandt_'+getRow).innerHTML;
		hasilkerja = document.getElementById('hasilkerja_'+getRow).value;
		stts	   = document.getElementById('optsukses'+getRow).options[document.getElementById('optsukses'+getRow).selectedIndex].value;
		// console.log(kegiatandt);
		// console.log(hasilkerja);
		// console.log(stts);
		
		param += '&kodekegiatan_'+getRow+'='+kegiatandt;
		param += '&hasilkerja_'+getRow+'='+hasilkerja;
		param += '&status_'+getRow+'='+stts;
	}

	tujuan= 'kebun_slave_bkm.php';
	
	alertify.confirm("Info","Akan dilakukan verifikasi untuk transaksi nomor "+notransaksi+"<br>Apakah Anda yakin ?",
		function(){		
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
					alertify.popup().destroy();
					alertify.alert('Verifiying Success...');
					loaddata(paged);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
	}
}

function getSuksesDt(row) {
	stts = document.getElementById('optsukses'+row).value;

	if (stts == 1) {
		oldHasilKerja = document.getElementById('oldHasilKerja_'+row).value;
		document.getElementById('hasilkerja_'+row).disabled = true;
		document.getElementById('hasilkerja_'+row).value = oldHasilKerja;
	} else {
		document.getElementById('hasilkerja_'+row).disabled = false;
	}
}


function edit(notransaksi,kontanan) {
	param = 'method=edithead' + '&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info',con.responseText);
				} else {
					data = con.responseText.split("####");
					// alert(data[12]);
					// alert(data[13]);
					// alert(data[14]);
					// alert(data[15]);
					if (kontanan == "KONTAN") {
						document.getElementById('kontanan').checked = true;
					} else {
						document.getElementById('kontanan').checked = false;
					}

					document.getElementById('notransaksi').value     = data[0];
					document.getElementById('tgl').value             = data[1];
					document.getElementById('kodeorg').value         = data[2];
					document.getElementById('kodeorg').innerHTML="<option value='"+ data[2] +"'>"+ data[10] +"</option>";
					document.getElementById('nobkm').value           = data[3];
					
					// document.getElementById('kerani').value          = data[7];
					//document.getElementById('jenis').value         = data[9];
					document.getElementById('divisi').value          = data[8];
					document.getElementById('divisi').innerHTML="<option value='"+ data[8] +"'>"+ data[11] +"</option>";
					
					
					document.getElementById('mandor').innerHTML      = data[13];
					document.getElementById('mandor1').innerHTML     = data[14];
					document.getElementById('kerani').innerHTML      = data[15];
					document.getElementById('asst').innerHTML        = data[16];
					
					// document.getElementById('mandor').value          = data[4];
					// document.getElementById('mandor1').value         = data[5];
					// document.getElementById('asst').value            = data[6];
					
					document.getElementById('mode').value            ='edit';
					document.getElementById('listData').style.display='none';
					document.getElementById('header').style.display  ='block';
					// simpanheader();

					// setValue2('divisi',data[8]);
					setValue2('mandor',data[4]);
					setValue2('mandor1',data[5]);
					setValue2('asst',data[6]);
					setValue2('kerani',data[7]);
					// setValue2('mandor1',mandor1);
					// setValue2('kerani',kerani);
					// setValue2('asst',asst);
					
					addHeader();
					// simpanheader();
					//addHeader(notransaksi);
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedetail(notransaksi,karyawanid,blok,kegiatan,numrow){
    param='method=deletedetail'+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan;
 
    tujuan='kebun_slave_bkm.php';
	alertify.confirm("Delete","Anda yakin ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
				} else {
				   loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}


function editdetail(notransaksi,karyawanid,kegiatan,blok,luas,satuan,prestasi,jhk,upah,premi,numrow, namakary,namablok){
	row=document.getElementById('jlhbrs').value;
	if(row!='' || row!=0){
		alertify.alert('Silahkan uncheck Per Mandor untuk melakukan Edit !\n\nJika nama karyawan tidak muncul silahkan pilih Filter Divisi = Seluruhnya'); return;
	}
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('karyawanid').disabled=true;
	document.getElementById('blok').value=blok;
	document.getElementById('blok').disabled=true;
	document.getElementById('kegiatan').value=kegiatan;
	document.getElementById('kegiatan').disabled=true;
	document.getElementById('luas').value=luas;
	document.getElementById('satuan').value=satuan;
	document.getElementById('prestasi').value=prestasi;
	document.getElementById('jhk').value=jhk;
	document.getElementById('upah').value=upah;
	document.getElementById('premi').value=premi;
	document.getElementById('method').value='update';
	
	document.getElementById('karyawanid').innerHTML="<option value='"+ karyawanid +"'>"+ namakary +"</option>"
	// setValue2('karyawanid',karyawanid);
	//setValue2('blok',blok);
	document.getElementById('blok').innerHTML="<option value='"+ blok +"'>"+ namablok +"</option>"
	getDataDetail('','changekeg')
	setTimeout(() => {
		setValue2('kegiatan',kegiatan);		
		numberFormat2('premi',0);

		document.getElementById('premi').value=premi;
		//window.scroll(100,0);
		window.scroll({
			top: 100,
			left: 0,
			behavior: 'smooth'
		});
	}, 700);
}

function cekPremiAktif(kegiatan){
    param='method=getDataDetail'+'&kegiatan='+kegiatan; 
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					isdt = con.responseText.split("######"); 
					if(isdt[0]==1){
						document.getElementById('premi').disabled = true;
					}else{
						document.getElementById('premi').disabled = false;
					}
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function unhidedendadt(){
	row=document.getElementById('jlhbrsdt').value;
	document.getElementById('pheaddt').style.display = '';
	//document.getElementById('tabledt').style.width = '100%';
	for(i=1;i<=10;i++){
		document.getElementById('pdt'+i).style.display = '';
		document.getElementById('tpddt'+i).style.display = '';
	}
	for(i=1;i<=10;i++){
		for(brs=1;brs<=row;brs++){
			document.getElementById('pddt'+i+brs).style.display = '';
		}
	}
}

function cleardetailall(){
	document.getElementById('method').value='insert';
	//document.getElementById('karyawanid').value='';
	document.getElementById('karyawanid').disabled=false;
	document.getElementById('kegiatan').disabled=false;
	//document.getElementById('kegiatan').value='';
	document.getElementById('blok').disabled=false;
	//document.getElementById('blok').value='';
	document.getElementById('luas').value='';
	document.getElementById('satuan').value='';
	document.getElementById('prestasi').value='';
	document.getElementById('jhk').disabled=false;
	document.getElementById('upah').disabled=false;
	document.getElementById('jhk').value='';
	document.getElementById('upah').value='';
	document.getElementById('premi').value='';
	
	setValue2('karyawanid',null);
	setValue2('kegiatan',null);
	setValue2('blok',null);
}

function cleardetail(baris){
	row=document.getElementById('jlhbrs').value;
	document.getElementById('method').value='insert';
	if(row==0){
		// document.getElementById('karyawanid').value='';
		document.getElementById('karyawanid').disabled=false;
		document.getElementById('kegiatan').disabled=false;
		document.getElementById('blok').disabled=false;
		document.getElementById('luas').value='';
		document.getElementById('satuan').value='';
		document.getElementById('jhk').disabled=false;
		document.getElementById('upah').disabled=false;
		document.getElementById('jhk').value='';
		document.getElementById('upah').value='';
		document.getElementById('premi').value='';
		document.getElementById('basis').value='';
		document.getElementById('rpsat').value='';
		document.getElementById('prestasi').value='';
		
		setValue2('kegiatan',null);
		setValue2('karyawanid',null);
	} else {
		document.getElementById('kegiatan'+baris).disabled=false;
		document.getElementById('kegiatan'+baris).value='';
		document.getElementById('blok'+baris).disabled=false;
		document.getElementById('blok'+baris).value='';
		document.getElementById('luas'+baris).value='';
		document.getElementById('satuan'+baris).value='';
		document.getElementById('jhk'+baris).disabled=false;
		document.getElementById('upah'+baris).disabled=false;
		document.getElementById('jhk'+baris).value='';
		document.getElementById('upah'+baris).value='';
		document.getElementById('premi'+baris).value='';
		document.getElementById('basis'+baris).value='';
		document.getElementById('rpsat'+baris).value='';
		document.getElementById('prestasi'+baris).value='';
	}
	if(baris=='x'){
		getdatadivisi(undefined,'x');
	}
}

function checkval(word,value){
	if(value.value > 1){
		alertify.alert("Value "+word+" maximal adalah 1");
		value.value='';
		value.focus();
	}
}

maxf=0
sekarang=1;
function saveAll(maxRow){  
	if(maxRow =='' || maxRow ==0){
        alertify.alert('Data tidak ditemukan, proses dibatalkan !');
        return;
    }
	alertify.confirm("Info","Hanya Kegiatan, Blok, Prestasi, HK atau Premi yang berisi<br>yg akan di simpan.<br><br>Simpan semua ???",
		function(){
			maxf=maxRow;
			savedetail(1,maxRow);
		},
		function(){
			return;
		}
	);
}

function savedetail(currRow,maxRow){
	row         = document.getElementById('jlhbrs').value;
	notransaksi = document.getElementById('notransaksi').value;
	nobkm       = document.getElementById('nobkm').value;
	stsawal     = document.getElementById('stsawal').value;
	kodeorg     = document.getElementById('kodeorg').value;
	mandor      = document.getElementById('mandor').value;
	mandor1     = document.getElementById('mandor1').value;
	asst        = document.getElementById('asst').value;
	kerani      = document.getElementById('kerani').value;
	tgl         = document.getElementById('tgl').value;
	if(document.getElementById('kontanan').checked == true){
		kontanan = "KONTAN";
	}else{
		kontanan = "";
	}
	mode        = document.getElementById('mode').value;
	divisi      = document.getElementById('divisi').value;
	filterdivisi= document.getElementById('filterdivisi').value;
	method      = document.getElementById('method').value;
	if(row==0){
		karyawanid=document.getElementById('karyawanid').value;
		kegiatan  =document.getElementById('kegiatan').value;
		blok      =document.getElementById('blok').value;
		prestasi  =document.getElementById('prestasi').value;
		jhk       =document.getElementById('jhk').value;
		upah      =document.getElementById('upah').value;
		premi     =document.getElementById('premi').value;
		
		if(karyawanid==''){alertify.alert("Nama Karyawan Wajib diisi !!!"); document.getElementById('karyawanid').focus(); return;}
		if(kegiatan==''){alertify.alert("Kegiatan Wajib diisi !!!");document.getElementById('kegiatan').focus(); return;}
		if(blok==''){alertify.alert("Blok Wajib diisi !!!"); document.getElementById('blok').focus(); return;}
		if(prestasi=='0'){alertify.alert("Hasil Kerja Wajib diisi !!!"); document.getElementById('prestasi').focus(); return;}
		if(prestasi==''){alertify.alert("Hasil Kerja Wajib diisi !!!"); document.getElementById('prestasi').focus(); return;}
		if((parseFloat(upah)=='' || parseFloat(upah)==0) && (parseFloat(premi)==''|| parseFloat(premi)==0)){alertify.alert("Upah atau Premi salah satu wajib diisi !"); document.getElementById('jhk').focus(); return;}
		if ((jhk == '' || jhk == 0) && (premi != '' || premi != 0)) {
			isHkHead = 0;
		} else {
			isHkHead = 1;
		}
	} else {
		karyawanid=document.getElementById('karyawanid'+currRow).value;
		kegiatan  =document.getElementById('kegiatan'+currRow).value;
		blok      =document.getElementById('blok'+currRow).value;
		prestasi  =document.getElementById('prestasi'+currRow).value;
		jhk       =document.getElementById('jhk'+currRow).value;
		upah      =document.getElementById('upah'+currRow).value;
		premi     =document.getElementById('premi'+currRow).value;
		if ((jhk == '' || jhk == 0) && (premi != '' || premi != 0)) {
			isHkHead = 0;
		} else {
			isHkHead = 1;
		}
	}

	param = "";
	param += "&filterdivisi="+filterdivisi;
	param += "&divisi="+divisi;
	param += "&notransaksi="+notransaksi;
	param += "&karyawanid="+karyawanid;
	param += "&kegiatan="+kegiatan;
	param += "&blok="+blok;
	param += "&prestasi="+prestasi;
	param += "&jhk="+jhk;
	param += "&upah="+upah;
	param += "&premi="+premi;
	param += "&stsawal="+stsawal;
	param += "&nobkm="+nobkm;
	param +='&method='+method;
	param +='&tgl='+tgl;
	param +='&kodeorg='+kodeorg;
	param +='&mandor='+mandor;
	param +='&mandor1='+mandor1;
	param +='&asst='+asst;
	param +='&kerani='+kerani;
	param +='&kontanan='+kontanan;
	param +='&isHkHead='+isHkHead;
	param +='&mode='+mode;
	
	tujuan='kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog); if(currRow!=undefined){		
		document.getElementById('row' + currRow).style.backgroundColor='cyan';
	}
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                } else {
					if(trim(con.responseText)!=''){
						document.getElementById('notransaksi').value = trim(con.responseText);
					}
					cleardetail(currRow);
					loaddatadetail();
					if(currRow != undefined){
						document.getElementById('row' + currRow).style.backgroundColor='';
					}
					currRow+=1;
                    sekarang=currRow;
                    if((currRow>maxRow) || (maxRow == undefined)){
						loaddatadetail();
					} else {
						savedetail(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}


function copykegiatan(baris){
	row=document.getElementById('jlhbrs').value;
	copykeg=document.getElementById('copykeg');
	if(copykeg.checked==true){
		kegiatan=document.getElementById('kegiatan'+baris).value;
		if(row>0){
			for(i=0;i<row;i++){
				document.getElementById('kegiatan'+(baris+i)).value=kegiatan;
			}
		}
	} 
}

function copyblok(baris){
	row=document.getElementById('jlhbrs').value;
	copyblk=document.getElementById('copyblok');
	if(copyblk.checked==true){
		blok=document.getElementById('blok'+baris).value;
		if(row>0){
			for(i=0;i<row;i++){
				if(document.getElementById('blok'+(baris+i))!=null){
					document.getElementById('blok'+(baris+i)).value=blok;
				}
			}
		}
	}
}

function copypres(baris){
	row=document.getElementById('jlhbrs').value;
	copyprs=document.getElementById('copypres');
	if(copyprs.checked==true){
		prestasi=document.getElementById('prestasi'+baris).value;
		if(row>0){
			for(i=0;i<row;i++){
				document.getElementById('prestasi'+(baris+i)).value=prestasi;
			}
		}
	} 
}

maxf=0
sekarang=1;
function getDataDetailAllAll(baris,id){
	maxRow=document.getElementById('jlhbrs').value;
	maxf=maxRow;

	copykeg=document.getElementById('copykeg');
	copyblk=document.getElementById('copyblok');
	copyprs=document.getElementById('copypres');
	
	if(copykeg.checked==true){
		getDataDetailAll(baris,maxRow,id);
	} else if(copyblk.checked==true){
		getDataDetailAll(baris,maxRow,id);
	} else if(copyprs.checked==true){
		getDataDetailAll(baris,maxRow,id);
	} else{
		getDataDetail(baris,id);
	}
}

// Fungsi ini sama dengan bawah, jangan tanya kenapa di buat dua biji !!!
function getDataDetailAll(baris,maxRow,id){
	row=document.getElementById('jlhbrs').value;
	kodeorg=document.getElementById('kodeorg').value;
    filterdivisi=document.getElementById('filterdivisi').value; 
	tgl=document.getElementById('tgl').value;
	bahasa=document.getElementById('bahasa').value;
	if(row==0){
		karyawanid=document.getElementById('karyawanid').value;
		blok=document.getElementById('blok').value;
		kegiatan=document.getElementById('kegiatan').value;
		prestasi=document.getElementById('prestasi').value;
	} else {		
		karyawanid=document.getElementById('karyawanid'+baris).value;
		blok=document.getElementById('blok'+baris).value;
		kegiatan=document.getElementById('kegiatan'+baris).value;
		prestasi=document.getElementById('prestasi'+baris).value;
	}
    param='method=getDataDetail'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan+'&kodeorg='+kodeorg+'&prestasi='+prestasi+'&bahasa='+bahasa; 
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					isdt = con.responseText.split("######"); 
					stspremi = parseFloat(trim(isdt[0]));
					basis = parseFloat(trim(isdt[1]));
					premibasis = parseFloat(trim(isdt[2]));
					premilebihbasis = parseFloat(trim(isdt[3]));
					tipeKary = parseFloat(trim(isdt[4]));
					luasblok = parseFloat(trim(isdt[5]));
					satkegiatan = trim(isdt[6]);
					rpsat = parseFloat(trim(isdt[7]));
					kdkeg = trim(isdt[8]);
					
					if(isNaN(luasblok)==true){
						luasblok=0;
					}
					if(isNaN(basis)==true){
						basis=0;
					}
					if(isNaN(rpsat)==true){
						rpsat=0;
					}
					totalpremi=premibasis+premilebihbasis;
					if(isNaN(totalpremi)==true){
						totalpremi=0;
					}
					if(trim(isdt[0])==1){
						if(row==0){	
							document.getElementById('premi').disabled = true;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
							document.getElementById('premi').value = numberFormat(totalpremi,0);
						} else {
							document.getElementById('premi'+baris).disabled = true;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,0);
						}
					} else {
						if(row==0){	
							document.getElementById('premi').disabled = false;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
							document.getElementById('premi').value = numberFormat(totalpremi,0);
						} else {
							document.getElementById('premi'+baris).disabled = false;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,0);
						}
					}
					
					if(row==0){
						alertify.alert(id);
						if(id=='changekeg'){							
							document.getElementById('kegiatan').innerHTML = kdkeg;
						}
						document.getElementById('luas').value = numberFormat(luasblok,2);
						document.getElementById('satuan').value = satkegiatan;
						document.getElementById('basis').value = numberFormat(basis);
						document.getElementById('rpsat').value = numberFormat(rpsat,2);
					} else {
						if(id=='changekeg'){
							document.getElementById('kegiatan'+baris).innerHTML = kdkeg;
						}
						document.getElementById('luas'+baris).value = numberFormat(luasblok,2);
						document.getElementById('satuan'+baris).value = satkegiatan;
						document.getElementById('basis'+baris).value = numberFormat(basis);
						document.getElementById('rpsat'+baris).value = numberFormat(rpsat,2);
					}

					baris+=1;
                    sekarang=baris;
                    if((baris>maxRow) || (maxRow == undefined)){
						//alertify.alert('Done');
					} else {
						getDataDetailAll(baris,maxRow,id);
                    }
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

// Fungsi ini sama dengan atas, jangan tanya kenapa di buat dua biji !!!
function getDataDetail(baris,id){
	row=document.getElementById('jlhbrs').value;
	kodeorg=document.getElementById('kodeorg').value;
    filterdivisi=document.getElementById('filterdivisi').value; 
	tgl=document.getElementById('tgl').value;
	bahasa=document.getElementById('bahasa').value;
	if(row==0){
		karyawanid=document.getElementById('karyawanid').value;
		blok=document.getElementById('blok').value;
		kegiatan=document.getElementById('kegiatan').value;
		prestasi=document.getElementById('prestasi').value;
	} else {		
		karyawanid=document.getElementById('karyawanid'+baris).value;
		blok=document.getElementById('blok'+baris).value;
		kegiatan=document.getElementById('kegiatan'+baris).value;
		prestasi=document.getElementById('prestasi'+baris).value;
	}
    param='method=getDataDetail'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan+'&kodeorg='+kodeorg+'&prestasi='+prestasi+'&bahasa='+bahasa; 
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					isdt = con.responseText.split("######"); 
					stspremi = parseFloat(trim(isdt[0]));
					basis = parseFloat(trim(isdt[1]));
					premibasis = parseFloat(trim(isdt[2]));
					premilebihbasis = parseFloat(trim(isdt[3]));
					tipeKary = parseFloat(trim(isdt[4]));
					luasblok = parseFloat(trim(isdt[5]));
					satkegiatan = trim(isdt[6]);
					rpsat = parseFloat(trim(isdt[7]));
					kdkeg = trim(isdt[8]);
					isHkHead = trim(isdt[9]);
					
					if(isNaN(luasblok)==true){
						luasblok=0;
					}
					if(isNaN(basis)==true){
						basis=0;
					}
					if(isNaN(rpsat)==true){
						rpsat=0;
					}
					totalpremi=premibasis+premilebihbasis;
					if(isNaN(totalpremi)==true){
						totalpremi=0;
					}
					if(trim(isdt[0])==1){
						if(row==0){	
							document.getElementById('premi').disabled = true;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
							document.getElementById('premi').value = numberFormat(totalpremi,0);
						} else {
							document.getElementById('premi'+baris).disabled = true;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,0);
						}
					} else {
						if(row==0){	
							document.getElementById('premi').disabled = false;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
							// document.getElementById('premi').value = numberFormat(totalpremi,0);
						} else {
							document.getElementById('premi'+baris).disabled = false;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,0);
						}
					}
						
					if(row==0){
						if(id=='changekeg'){						
							document.getElementById('kegiatan').innerHTML = kdkeg;
						}
						if (isHkHead == 1) {
							document.getElementById('jhk').disabled = true;
							document.getElementById('upah').disabled = true;
						} else {
							document.getElementById('jhk').disabled = false;
							document.getElementById('upah').disabled = false;
						}
						document.getElementById('luas').value = numberFormat(luasblok,2);
						document.getElementById('satuan').value = satkegiatan;
						document.getElementById('basis').value = numberFormat(basis);
						document.getElementById('rpsat').value = numberFormat(rpsat,2);
					} else {
						if(id=='changekeg'){
							document.getElementById('kegiatan'+baris).innerHTML = kdkeg;
						}
						if (isHkHead == 1) {
							document.getElementById('jhk'+baris).disabled = true;
							document.getElementById('upah'+baris).disabled = true;
						} else {
							document.getElementById('jhk'+baris).disabled = false;
							document.getElementById('upah'+baris).disabled = false;
						}
						document.getElementById('luas'+baris).value = numberFormat(luasblok,2);
						document.getElementById('satuan'+baris).value = satkegiatan;
						document.getElementById('basis'+baris).value = numberFormat(basis);
						document.getElementById('rpsat'+baris).value = numberFormat(rpsat,2);
					}
					getumr(baris);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function getumr(baris,dclick){
	row=document.getElementById('jlhbrs').value;
	tgl=document.getElementById('tgl').value;
	kodeorg=document.getElementById('kodeorg').value;
	
	//dclick isinya : d => didapat dari perintah dible click, i => sumber isian
	if(dclick=='d'){
		if(row==0){
			document.getElementById('jhk').value=1;			
		}else{
			document.getElementById('jhk'+baris).value=1;			
		}
	}
	
	if(row==0){
		karyawanid=document.getElementById('karyawanid').value;
		jhk=document.getElementById('jhk').value;
	} else {		
		karyawanid=document.getElementById('karyawanid'+baris).value;
		jhk=document.getElementById('jhk'+baris).value;
	}
	if(jhk>1){
		alertify.alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
		if(row==0){
			document.getElementById('jhk').value='';
			document.getElementById('upah').value='';
		} else {		
			document.getElementById('jhk'+baris).value='';
			document.getElementById('upah'+baris).value='';
		}
		return false;
	}
	
    param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl+'&jhk='+jhk;
    tujuan='kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					umr = trim(con.responseText);
					jlhrp = parseFloat(trim(umr))*parseFloat(jhk);
					if(isNaN(jlhrp)==true){
						jlhrp=0;
					}
					
					if(umr==0){
						if(row==0){	
							document.getElementById('upah').value='';
							document.getElementById('jhk').value='';
						} else {
							document.getElementById('upah'+baris).value='';
							document.getElementById('jhk'+baris).value='';
						}
						if(karyawanid!=''){		
							alertify.alert('Gaji Pokok Karyawan belum ada.'); 
							return false;
						}
					} else{
						if(row==0){
							document.getElementById('upah').value=numberFormat(jlhrp,0);
							// document.getElementById('upah').value=numberFormat(jlhrp,2);
						} else {
							document.getElementById('upah'+baris).value=numberFormat(jlhrp,0);
							// document.getElementById('upah'+baris).value=numberFormat(jlhrp,2);
						}
					}
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function getdatamandor(){
	filterdivisi = document.getElementById('filterdivisi').value; 
	mandor       = document.getElementById('mandor').value; 
	kodeorg      = document.getElementById('kodeorg').value; 
	tgl          = document.getElementById('tgl').value;
	showpermandor= document.getElementById('showpermandor');   
	if(showpermandor.checked==true){
		method='getdatamandor';
		document.getElementById('copy').style.display = '';
	}else{
		method='inputdetail';
		document.getElementById('copy').style.display = 'none';
	}
	
    param='method='+method+'&filterdivisi='+filterdivisi+'&mandor='+mandor+'&tgl='+tgl+'&kodeorg='+kodeorg;
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					isdtmdr = con.responseText.split("######");
                    document.getElementById('inputdetail').innerHTML = isdtmdr[0];
					
					if(isdtmdr[1]!=undefined){						
						row = trim(isdtmdr[1]);
					}
					
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					getdatadivisi(row);
					
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}


function getdatadivisi(filterdivisi,clear){
	if(clear!='x'){		
		cleardetail();
	}
	if(filterdivisi==undefined){		
		filterdivisi=document.getElementById('filterdivisi').value; 
	}
	row         =document.getElementById('jlhbrs').value;
	filterdivisi=document.getElementById('filterdivisi').value; 
	tgl         =document.getElementById('tgl').value;
	kodeorgkary= document.getElementById('filterunit').value;
	stsawal     =document.getElementById('stsawal').value;
	kodeorg     =document.getElementById('kodeorg').value;
	
    param='method=getdata'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&stsawal='+stsawal+'&kodeorg='+kodeorg;
	param += '&divisi=' + getValue('divisi');
	param += '&kodeorgkary=' + getValue('filterunit');
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					if(row==0){
						isdata = con.responseText.split("######");
						document.getElementById('karyawanid').innerHTML = isdata[0];
						document.getElementById('blok').innerHTML = isdata[1];
					} else {
						for(i=1;i<=row;i++){
						isdata = con.responseText.split("######");
							document.getElementById('blok'+i).innerHTML=isdata[1];	
						}						
					}
					
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}
function getdataabs(filterdivisi,clear){
	if(clear!='x'){		
		cleardetail();
	}
	if(filterdivisi==undefined){		
		filterdivisi=document.getElementById('filterdivisi').value; 
	}
	filterdivisi=document.getElementById('filterdivisiabsensi').value; 
	tgl         =document.getElementById('tgl').value;
	stsawal     =document.getElementById('stsawal').value;
	kodeorg     =document.getElementById('kodeorg').value;
	
    param='method=getdata'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&stsawal='+stsawal+'&kodeorg='+kodeorg;
	param += '&divisi=' + getValue('divisi');
	param += '&kodeorgkary=' + getValue('kodeorg');
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					isdata = con.responseText.split("######");
					document.getElementById('karyawanidabsensi').innerHTML = isdata[0];
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function getnotransaksi(){
	kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tgl=document.getElementById('tgl').value;
	document.getElementById('notransaksi').value='';
	param='tgl='+tgl+'&kodeorg='+kodeorg+'&method=getnotransaksi';
	
	tujuan='kebun_slave_bkm.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('notransaksi').value=trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function simpanheader(){
	notransaksi= document.getElementById('notransaksi').value;
	kodeorg    = document.getElementById('kodeorg').value;
	mandor     = document.getElementById('mandor').value;
	mandor1    = document.getElementById('mandor1').value;
	asst       = document.getElementById('asst').value;
	kerani     = document.getElementById('kerani').value;
	nobkm      = document.getElementById('nobkm').value;
	tgl        = document.getElementById('tgl').value;
	stsawal    = document.getElementById('stsawal').value;
	if(document.getElementById('kontanan').checked == true){
		kontanan = "KONTAN";
	}else{
		kontanan = "";
	}
	mode       = document.getElementById('mode').value;
    
	if(tgl==''||kodeorg==''){
        alertify.alert('Tanggal dan atau Kode Organisasi harus di isi !');
        return;
    }
	if(mode=='baru'){
		document.getElementById('tomboldetail').disabled = true;
	}else{
		document.getElementById('tomboldetail').disabled = false;
	}
    param = 'method=simpanheader';
    param += '&tgl=' + tgl+'&kodeorg=' + kodeorg+'&nobkm=' + nobkm+'&mandor=' + mandor+'&mandor1=' + mandor1+'&asst=' + asst+'&kerani=' + kerani+'&stsawal='+stsawal+'&mode='+mode+'&notransaksi='+notransaksi;
	divisi = document.getElementById('divisi').value;
	param += '&divisi=' + divisi;
	param += '&kontanan=' + kontanan;
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else {
					if(mode=='baru'){
						document.getElementById('notransaksi').value = trim(con.responseText);
						document.getElementById('nobkm').value = trim(con.responseText);
					}
                    addHeader();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function addHeader(){
	kodeorg    = document.getElementById('kodeorg').value;
	mandor     = document.getElementById('mandor').value;
	mandor1    = document.getElementById('mandor1').value;
	asst       = document.getElementById('asst').value;
	kerani     = document.getElementById('kerani').value;
	nobkm      = document.getElementById('nobkm').value;
	tgl        = document.getElementById('tgl').value;
	notransaksi= document.getElementById('notransaksi').value;
	stsawal    = document.getElementById('stsawal').value;
	mode       = document.getElementById('mode').value;
	divisi     = document.getElementById('divisi').value;
	if(document.getElementById('kontanan').checked == true){
		kontanan = "KONTAN";
	}else{
		kontanan = "";
	}
    
	validate([
        ["kodeorg","Kebun tidak boleh kosong."],
        ["tgl","Tanggal tidak boleh kosong."],
        ["divisi","Divisi pekerjaan tidak boleh kosong."]
    ]);
	
	document.getElementById('kodeorg').disabled = true;
	document.getElementById('divisi').disabled = true;
	document.getElementById('tgl').disabled = true;
	
    param = 'method=detail';
    param += '&tgl=' + tgl+'&kodeorg=' + kodeorg+'&nobkm=' + nobkm+'&mandor=' + mandor+'&mandor1=' + mandor1+'&asst=' + asst+'&kerani=' + kerani+'&notransaksi='+notransaksi+'&stsawal='+stsawal+'&mode='+mode;
	param += '&divisi=' + divisi;
	param += '&kontanan=' + kontanan;
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    inputdetail(notransaksi);
					
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function inputdetail(notransaksi){
	kodeorg      = document.getElementById('kodeorg').value;
	filterdivisi = document.getElementById('filterdivisi').value;
	showpermandor= document.getElementById('showpermandor');   
	if(showpermandor.checked==true){
		showpermandor=1;
	}else{
		showpermandor=0;
	}
	tgl        = document.getElementById('tgl').value;
	notransaksi= document.getElementById('notransaksi').value;
	
  
    param = 'method=inputdetail';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi+'&filterdivisi=' + filterdivisi+'&showpermandor=' + showpermandor;
	param += '&filterunit=' + getValue('filterunit');
	param += '&divisi=' + getValue('divisi');
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('inputdetail').innerHTML = con.responseText;
					
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
						});
					});
					
					$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
						$(this).closest(".select2-container").siblings('select:enabled').select2('open');
					});
					
					loaddatadetail(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function inputdetailmaterial(notransaksi){
	tgl=document.getElementById('tgl').value;
    notransaksi=document.getElementById('nobkm').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=inputdetailmaterial';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('inputdetailmaterial').innerHTML = con.responseText;
					loaddatadetailmaterial(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function savematerial(currRow){
	notransaksi= document.getElementById('notran'+currRow).innerHTML;
	kegiatan   = document.getElementById('kegiatanmat'+currRow).innerHTML;
	blok       = document.getElementById('blokmat'+currRow).innerHTML;
	kodegudang = document.getElementById('kodegudang'+currRow).innerHTML;
	kodebarang = document.getElementById('kodemat'+currRow).value;
	qtymat     = document.getElementById('qtymat'+currRow).value;
	prestasi   = document.getElementById('pres'+currRow).innerHTML;
	tgl        = trim(document.getElementById('tgl').value);
	
	if(kodebarang=='' || kodebarang=='0'){
		notif('kodemat'+currRow+'#namamat'+currRow,'','Kode atau nama barang tidak boleh kosong.'); return;
	}
	if(qtymat=='' || qtymat=='0'){
		notif('qtymat'+currRow,'','Jumlah tidak boleh kosong.'); return;
	}
	
	param = 'method=insertmaterial';
	param += '&notransaksi='+notransaksi;
	param += '&kegiatan='+kegiatan;
	param += '&blok='+blok;
	param += '&kodebarang='+kodebarang;
	param += '&qtymat='+qtymat;
	param += '&kodegudang='+kodegudang;
	param += '&prestasi='+prestasi;
	param += '&tgl='+tgl;
	
	tujuan='kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
					//document.getElementById('rowmat_' + currRow).style.backgroundColor = 'red';
                } else {
					document.getElementById('rowmat_' + currRow).style.color='';
					loaddatadetail(notransaksi);
					clearmaterial(currRow);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function clearmaterial(currRow){
	document.getElementById('kodemat'+currRow).value='';
	document.getElementById('namamat'+currRow).value='';
	document.getElementById('satmat'+currRow).value='';
	document.getElementById('qtymat'+currRow).value='';
	hapuswarna('kodemat'+currRow+'#namamat'+currRow+'#qtymat'+currRow);
}

function delmaterial(notransaksi,kegiatan,blok,kodebarang){

	param = 'method=delmaterial';
	param += '&notransaksi='+notransaksi;
	param += '&kegiatan='+kegiatan;
	param += '&blok='+blok;
	param += '&kodebarang='+kodebarang;
	
	tujuan='kebun_slave_bkm.php';
	alertify.confirm("Delete","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					loaddatadetail(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadetailmaterial(notransaksi){
	tgl        =document.getElementById('tgl').value;
	notransaksi=document.getElementById('nobkm').value;
	kodeorg    =document.getElementById('kodeorg').value;
    
    param = 'method=loaddatadetailmaterial';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailmaterial').innerHTML = con.responseText;
					loaddataabsensi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function searchmat(baris,title,ev){
	kdgdg = document.getElementById('kodegudang'+baris).innerHTML;
	kgtn = document.getElementById('kegiatanmat'+baris).innerHTML;
	if(kdgdg==''){alertify.alert("Kode Gudang belum ada, silahkan tambah melalui menu Kebun - Setup - Gudang Divisi !!!"); return;}
	content= "<div style='width:100%;'>";
	content+="<fieldset style=width:95%>Search : <input type=text id=txtnamabarang onkeypress='key=getKey(event);if(key==13){goCariBarang()}' class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariBarang()>Search</button> </div></fieldset>";
	content+="<input id=kodegudang value="+kdgdg+" style=display:none>";
	content+="<input id=kegiatansch value="+kgtn+" style=display:none>";
	content+="<input id=baris value="+baris+" style=display:none>";
	content+="<fieldset><legend><i>Result</i></legend><div id=containercari style='overflow:auto;max-height:317px;'></div></fieldset>";
    width='auto';
	height='auto';
	showDialog2(title,content,width,height,ev);
	
	var dialog = document.getElementById('dynamic2');
	clientWidth = document.getElementById("dynamic2").clientWidth;
	clientHeight = document.getElementById("dynamic2").clientHeight;
	pos = new Array();
	pos = getMouseP(ev);

	dialog.style.top = pos[1]+'px';
	dialog.style.left = (pos[0]-clientWidth)+'px';
	goCariBarang();
}


function goCariBarang(){
	kodegudang = trim(document.getElementById('kodegudang').value);
	kegiatan = trim(document.getElementById('kegiatansch').value);
	txtcari = trim(document.getElementById('txtnamabarang').value);
	tgl = trim(document.getElementById('tgl').value);
	param = 'txtcari='+txtcari+'&method=caribarang&kodegudang='+kodegudang+'&kegiatan='+kegiatan+'&tgl='+tgl;
	tujuan = 'kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
			
	function respog(){
		if (con.readyState == 4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alertify.alert(con.responseText);
				}else {
					
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadField(kode,nama,sat){
	baris = document.getElementById('baris').value;
	document.getElementById('kodemat'+baris).value=kode;
	document.getElementById('namamat'+baris).value=nama;
	document.getElementById('satmat'+baris).value=sat;
	closeDialog2();
}


function add_new_data(sumber){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
	getdivmdr(sumber);
    cancel();  
}

function del(notransaksi,numrow){
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
	
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='kebun_slave_bkm.php';
	alertify.confirm("Delete","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alertify.alert(con.responseText);
				} else {
				  loaddata(paged);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}


function displayList(){
    document.getElementById('notransaksisch').value='';
    document.getElementById('tglmulai').value='';
    document.getElementById('tglselesai').value='';
    //document.getElementById('divsch').value='';
    document.getElementById('postingsrc').value='';
    //document.getElementById('periodesch').value='';
    document.getElementById('mandorsrc').value='';
    document.getElementById('nobkmsch').value='';
    document.getElementById('mode').value='baru';
	
	setValue2('postingsrc',null);
	setValue2('mandorsrc',null);
	
	
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
	
	document.getElementById('header_trans').style.display='block';
    document.getElementById('judul_header').style.display='block';
    //document.getElementById('hidebtn').style.display='block';
    //document.getElementById('unhidebtn').style.display='none';
	
	loaddata(0);
 
	/* param  = 'method=getprdcari';
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
                    //document.getElementById('periodesch').innerHTML = con.responseText;
					loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } */
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page){
	document.getElementById('listData').style.display = 'block';
    document.getElementById('judul_header').style.display='block';
	if(document.getElementById('header')!=undefined){
		document.getElementById('header').style.display = 'none';
	}
	if(document.getElementById('detail')!=undefined){
		document.getElementById('detail').style.display = 'none';
	}
	
	
	notransaksisch=document.getElementById('notransaksisch').value;
	tglmulai      =document.getElementById('tglmulai').value;
	tglselesai    =document.getElementById('tglselesai').value;
	divsch        =document.getElementById('divsch').value;
	postingsrc    =document.getElementById('postingsrc').value;
	periodesch    =document.getElementById('periodesch').value;
	nobkmsch      =document.getElementById('nobkmsch').value;
	mandorsrc     =document.getElementById('mandorsrc').value;
	stsawal       =document.getElementById('stsawal').value;
	verifikasisch       =document.getElementById('verifikasisch').value;
	param = 'method=loaddata&page=' + page+'&stsawal='+stsawal;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
    if (notransaksisch != '') {
        param += '&notransaksisch=' + notransaksisch;
    }
	if (tglmulai != '') {
        param += '&tglmulai=' + tglmulai;
    }
	if (tglselesai != '') {
        param += '&tglselesai=' + tglselesai;
    }
	if (postingsrc != '') {
        param += '&postingsrc=' + postingsrc;
    }
	if (periodesch != '') {
        param += '&periodesch=' + periodesch;
    }
	if (nobkmsch != '') {
        param += '&nobkmsch=' + nobkmsch;
    }
	if (mandorsrc != '') {
        param += '&mandorsrc=' + mandorsrc;
    }
	if (verifikasisch != '') {
        param += '&verifikasisch=' + verifikasisch;
    }
	
 
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
					leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function cancel(){
    document.getElementById('detail').style.display = 'none';
    document.getElementById('tomboldetail').disabled=false;
    document.getElementById('tgl').disabled=false;
    document.getElementById('tgl').value='';
	document.getElementById('kodeorg').disabled=false;
    document.getElementById('kodeorg').value='';
	document.getElementById('divisi').disabled=false;
    document.getElementById('divisi').value='';
    document.getElementById('notransaksi').value='';
    document.getElementById('nobkm').value='';
    document.getElementById('mandor').value='';
    document.getElementById('mandor1').value='';
    document.getElementById('kerani').value='';
    document.getElementById('asst').value='';
	document.getElementById('kontanan').checked = false;
    document.getElementById('mode').value='baru';
	
	setValue2('kodeorg',null);
	setValue2('mandor',null);
	setValue2('mandor1',null);
	setValue2('kerani',null);
	setValue2('asst',null);
}
function cariby(val,sumber){
	if(sumber=='notran'){
		if(getValue('notrandetsch')==''){
			document.getElementById('notrandetsch').value=val;
		}else{
			document.getElementById('notrandetsch').value='';
		}
	}
	if(sumber=='namakary'){
		if(getValue('namakarydetsch')==''){
			document.getElementById('namakarydetsch').value=val;			
		}else{
			document.getElementById('namakarydetsch').value='';			
		}
	}
	if(sumber=='blok'){
		if(getValue('blokdetsch')==''){
			document.getElementById('blokdetsch').value=val;			
		}else{
			document.getElementById('blokdetsch').value='';
		}
	}
	if(sumber=='kegiatan'){
		if(getValue('kegdetsch')==''){
			document.getElementById('kegdetsch').value=val;			
		}else{
			document.getElementById('kegdetsch').value='';			
		}
	}
	loaddatadetail();
}

function cancelcari(){
	document.getElementById('notrandetsch').value='';
	document.getElementById('namakarydetsch').value='';
	document.getElementById('blokdetsch').value='';
	document.getElementById('kegdetsch').value='';
	loaddatadetail();
}

function loaddatadetailxls(notransaksi,jenis){
    ev = 'event';
	tipe = 'excel';
	
	param = "method=loaddatadetail&tipe="+tipe;
	if(jenis=='dt'){
		nobkm =document.getElementById('nobkm').value;
		param += '&nobkm=' + nobkm;
	}else{		
		param += '&notrandetsch=' + notransaksi;
	}
	
	title="Excel";
	showDialog1(title,"<iframe frameborder=0 style='width:100%;min-height:400px'"+" src='kebun_slave_bkm.php?"+param+"'></iframe>",'900','400',ev);	
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}

function loaddatadetail(notransaksi){
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('tgl').disabled=true;
	tgl         =document.getElementById('tgl').value;
	kodeorg     =document.getElementById('kodeorg').value;
	notransaksi =document.getElementById('notransaksi').value;
	nobkm       =document.getElementById('nobkm').value;
	notrandetsch=document.getElementById('notrandetsch').value;
	namakary    =document.getElementById('namakarydetsch').value;
	blok        =document.getElementById('blokdetsch').value;
	kegiatan    =document.getElementById('kegdetsch').value;
	
	
    param = 'method=loaddatadetail';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    param += '&nobkm=' + nobkm;
	param += '&notrandetsch=' + notrandetsch;
    param += '&namakary=' + namakary;
    param += '&blok=' + blok;
    param += '&kegiatan=' + kegiatan;
	param += '&filterunit=' + getValue('filterunit');
	param += '&divisi=' + getValue('divisi');
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                } else {
                    
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
					inputdetailmaterial(notransaksi);
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

function numberFormat2(iniid,digit){
	// sama kek di atas tapi ga pake ribu2
	isinya=document.getElementById(iniid).value;
    number = parseFloat(isinya.toString().match(/^-?\d+\.?\d{0,2}/));
    var components = (parseFloat(number).toFixed(digit)).split(".");
    components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, "");
    var jadinya=components.join(".");
	if(isNaN(jadinya)){jadinya=0;}
	
	document.getElementById(iniid).value=jadinya;
}

function form(){
    width = '720';
    height = '';
    //nopp=document.getElementById('nopp_'+id).value;
    content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog5(title, content, width, height, ev); 
}

function html(notransaksi,kodeorg, tgl){
    form();
    param = 'method=html' + '&kodeorg=' + kodeorg + '&tgl=' + tgl+ '&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alertify.alert(con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function excel(ev,tujuan){
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
	if(unitexp==''||perexp==''){
		alertify.alert('Lengkapi unit dan periode.');
		return;
	}
    judul='Report Ms.Excel';	
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);	
}

function pindahtab(id,no){ 
	tabAction(document.getElementById(id),no,'FRM',0);
}


function getjurnal(pt,notransaksi,tgl1,tgl2){
	// width    = '900';
	// height   = '400';
	// title    = "Detail Jurnal";
	// content = "<fieldset><div id=containerjurnal align=center style=\"width:880px;height:385px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // showDialog1(title, content, width, height, ev); 
	
	param = 'pt=' + pt;
	param += '&ref=' + notransaksi;
	param += '&periode=' + tgl1;
	param += '&periode1=' + tgl2;
	param += '&tipelaporan=html';
	tujuan = 'keu_laporanJurnal.php';

	alertify.popuppdf("Jurnal","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='keu_laporanJurnal.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');

	/* post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('containerjurnal').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	} */	
}

function saveabsensi(){
	tgl           =document.getElementById('tgl').value;
	nobkm         =document.getElementById('nobkm').value;
	notransaksi   =document.getElementById('notransaksi').value;
	karyawanid    =document.getElementById('karyawanidabsensi').value;
	jhk           =document.getElementById('jhkabsen').value;
	nilaihk    	  =document.getElementById('nilaihkabs').value;
	kodeabsen     =document.getElementById('kodeabsen').value;
	upah          =document.getElementById('upahabsen').value;
	premi         =document.getElementById('premiabsen').value;
	keterangan    =document.getElementById('keteranganabsen').value;
	stsawal       =document.getElementById('stsawal').value;
	method        =document.getElementById('methodabsensi').value;
	kodeorg       =document.getElementById('kodeorg').value;
	kodeorgabsensi=document.getElementById('kodeorgabsensi').value;
	noakun        =document.getElementById('noakunabsensi').value;

	if(karyawanid==''){
		notif('karyawanidabsensi','notifcontainer','Nama Karyawan Wajib diisi.');return;
	}
	if(kodeabsen==''){
		notif('kodeabsen','notifcontainer','Kode Absensi tidak boleh kosong.');return;
	}
	if(noakun==''){
		notif('noakunabsensi','notifcontainer','Nomor Akun tidak boleh kosong.');return;
	}
	if(keterangan==''){
		notif('keteranganabsen','notifcontainer','Keterangan tidak boleh kosong.');return;
	}
	if(nilaihk > 0) {
		if(upah==''){upah=0;}
		if(premi==''){premi=0;}
		if(upah==0 && premi==0){
			notif('upahabsen#premiabsen','notifcontainer','Upah atau Premi wajib diisi.');return;
		}
	}
	
    param ='';
    param +='&notransaksi='+notransaksi;
    param +='&kodeorg='+kodeorg;
    param +='&stsawal='+stsawal;
    param +='&nobkm='+nobkm;
    param +='&tgl='+tgl;
    param +='&method='+method;
    param +='&karyawanid='+karyawanid;
	param += '&jhk=' + jhk;
	param += '&kodeabsen=' + kodeabsen;
	param += '&upah=' + upah;
	param += '&premi=' + premi;
	param += '&keterangan=' + keterangan;
	param += '&kodeorgabsensi=' + kodeorgabsensi;
	param += '&noakun=' + noakun;
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
                } else {
                    clearabsensi();
					loaddataabsensi();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function clearabsensi(){
	document.getElementById('karyawanidabsensi').value='';
	document.getElementById('karyawanidabsensi').disabled=false;
	document.getElementById('kodeabsen').value='H';
	setValue2('karyawanidabsensi',null);
	setValue2('kodeabsen','H');
	
	document.getElementById('jhkabsen').value='1';
	document.getElementById('nilaihkabs').value='1';
	document.getElementById('upahabsen').value='';
	document.getElementById('premiabsen').value='';
	document.getElementById('keteranganabsen').value='';
	document.getElementById('kodeorgabsensi').value='';
	document.getElementById('methodabsensi').value='insertabsensi';
	hapuswarna('kodeabsen#jhkabsen#upahabsen#karyawanidabsensi#premiabsen');
}

function loaddataabsensi(){
	tgl        =document.getElementById('tgl').value;
	notransaksi=document.getElementById('notransaksi').value;
	nobkm      =document.getElementById('nobkm').value;
	
    param ='';
    param +='&method=loaddataabsensi';
    param +='&notransaksi='+notransaksi;
    param +='&tgl='+tgl;
    param +='&nobkm='+nobkm;
    
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
                } else {
                    document.getElementById('loaddataabsensi').innerHTML = con.responseText;
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function editabsensi(karyawanid,absensi,nilaihk,umr,premi,penjelasan,kodeorgabsensi,noakun){
	document.getElementById('karyawanidabsensi').value=karyawanid;
	
	setValue2('karyawanidabsensi',karyawanid);
	setValue2('noakunabsensi',noakun);
	setValue2('kodeabsen',absensi);
	// document.getElementById('kodeabsen').value=absensi;
	document.getElementById('jhkabsen').value=nilaihk;
	document.getElementById('upahabsen').value=umr;
	document.getElementById('premiabsen').value=premi;
	document.getElementById('keteranganabsen').value=penjelasan;
	document.getElementById('kodeorgabsensi').value=kodeorgabsensi;
	document.getElementById('methodabsensi').value='updateabsensi';
	document.getElementById('karyawanidabsensi').disabled=true;
}

function delabsen(notransaksi,tgl,kodeorg,karyawanid){
	param ='';
    param +='&method=delabsen';
    param +='&notransaksi='+notransaksi;
    param +='&tgl='+tgl;
    param +='&kodeorg='+kodeorg;
    param +='&karyawanid='+karyawanid;
    tujuan='kebun_slave_bkm.php';

    alertify.confirm("Delete","Anda yakin ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
                } else {
                    loaddataabsensi();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}


function notif(idkolom,idpesan,isipesan){
	if(idpesan!=''){		
		//document.getElementById(idpesan).innerHTML=isipesan;
	}
	col = idkolom.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.focus();
		kolom.style.borderColor='red';		
		kolom.style.backgroundColor='#F2F94D';
		kolom.style.fontWeight='bold';
	}
	alertify.alert(isipesan);
}
function hapuswarna(id){
	col = id.split("#");
	n = col.length;
	for(i=0;i<n;i++){
		kolom=document.getElementById(col[i]);
		kolom.style.borderColor='';		
		kolom.style.backgroundColor='';
		kolom.style.fontWeight='';
	}
}

function getumrabsensi(){
	tgl       =document.getElementById('tgl').value;
	karyawanid=document.getElementById('karyawanidabsensi').value;
	jhk       =document.getElementById('jhkabsen').value;
	kodeorg   =document.getElementById('kodeorg').value;
	kodeabsen =document.getElementById('kodeabsen').value;
	if(kodeabsen=='H'){
		document.getElementById('jhkabsen').disabled=false;
		document.getElementById('upahabsen').disabled=false;
	}else{
		document.getElementById('jhkabsen').disabled=true;
		document.getElementById('upahabsen').disabled=true;
	}
	
	
	if(jhk>1){
		alertify.alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
		document.getElementById('jhkabsen').value='';
		document.getElementById('upahabsen').value='';
		return false;
	}
	
	
    param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl+'&kodeorg='+kodeorg+'&jhk='+jhk;
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					data = con.responseText.split("####");
					
					umr = data[0]; 
					jlhrp = parseFloat(trim(umr))*parseFloat(jhk);
					if(isNaN(jlhrp)==true){
						jlhrp=0;
					}
					// if(trim(data[1])!='4'){
					// 	document.getElementById('upahabsen').style.display='none';
					// }else{
					// 	document.getElementById('upahabsen').style.display='block';
					// }
					document.getElementById('upahabsen').style.display='block';
					
					/* if(jenishari=='LIBUR' && jhk>0 ){
						document.getElementById('upahabsen').value='';
						document.getElementById('jhkabsen').value='';
						notif('premiabsen','','Untuk hari libur Upah = Nol, Absensi = HM / HB dan rupiah biaya langsung masuk ke Premi.');						
						return false;
					} */
					
                    document.getElementById('upahabsen').value = numberFormat(jlhrp,0);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function getnilaihk(){
	kodeabsen=document.getElementById('kodeabsen').value;
    param='method=getnilaihk'+'&kodeabsen='+kodeabsen;
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
                    document.getElementById('jhkabsen').value = trim(con.responseText);
                    document.getElementById('nilaihkabs').value = trim(con.responseText);
					getumrabsensi();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}

function gethk(idsumber,idhasil,idkary,baris){
	// numberFormat2('upah',0);
	// numberFormat2('upahabsen',0);

	row       = document.getElementById('jlhbrs').value;
	tgl       = document.getElementById('tgl').value;
	kodeorg   = document.getElementById('kodeorg').value;
	rpupah    = document.getElementById(idsumber).value;
	karyawanid= document.getElementById(idkary).value;
	
	rpupah=remove_comma_var(rpupah);
	if(karyawanid==''){
		alertify.alert('Pilih nama karyawan terlebih dahulu.'); return;
	}
	
	param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl;
    tujuan='kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alertify.alert(con.responseText);
                } else {
					umr = trim(con.responseText);
					if(isNaN(parseFloat(rpupah))==true){rpupah=0;}
					if(isNaN(parseFloat(umr))==true){umr=0;}
					
					if(rpupah>0){
						jhk=parseFloat(rpupah)/parseFloat(umr);
					}					
					if(isNaN(jhk)==true){jhk=0;}
					
					if(parseFloat(rpupah)=='0' || parseFloat(rpupah)==''){jhk=0;}
					if(parseFloat(umr)=='0'){
						document.getElementById(idhasil).value='0';
						document.getElementById(idsumber).value='0';
						alertify.alert('Gaji pokok karyawan belum ada.'); return;
					}
					if(parseFloat(rpupah)>parseFloat(umr)){
						alertify.alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
						document.getElementById(idhasil).value='0';
						document.getElementById(idsumber).value='0';
					}else{						
						document.getElementById(idhasil).value=jhk;
					}
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    } 
}

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0]) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(notransaksi){
	ev = 'event';
	//showformupload(ev);
	param='method=showupload&notransaksi='+notransaksi;
	
	tujuan='kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    //document.getElementById('contUpload').innerHTML=con.responseText;
					alertify.popup().destroy();
					alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('400px','400px');
					
					loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function previewData(notransaksi,tipe) {
	param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi;
	
	tujuan = 'kebun_slave_preview_mobile_databkm.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup().destroy();
                    alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function postingMobileERP(noreferensi,proses) {
	param = "notransaksi=" + noreferensi;
	param += "&proses=" + proses;
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					loaddata(0);
					alertify.alert("Data Successfully Downloaded !");
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
	alertify.confirm("Info","Akan dilakukan download data untuk transaksi "+noreferensi+"<br>Apakah Anda yakin ?",
		function(){
			post_response_text('kebun_slave_download_from_mobile.php', param, respon);
		},
		function(){
			return;
		}
	)
}

// fungsi untuk progress bar
function progressHandler(event) {
	document.getElementById("progressBar").style.display="block";
	document.getElementById("loaded_n_total").innerHTML = "Uploaded " + numberFormat(Math.round(event.loaded/1024)) + " KB of " + numberFormat(Math.round(event.total/1024))+" KB";
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}
function completeHandler(event) {
	document.getElementById("progressBar").style.display="none";
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 0; //wil clear progress bar after successful upload
}
function errorHandler(event) {
  document.getElementById("status").innerHTML = "Upload Failed";
}
function abortHandler(event) {
  document.getElementById("status").innerHTML = "Upload Aborted";
}

function submitfile(notransaksi) {
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	if (getValue('upload') == "") {
		alertify.alert("Upload file has been empty.");
		return false;
	}
	if(notransaksi==''){
		alertify.alert("Nomor transaksi tidak ditemukan.");
		return false;
	}

	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').style.display="none";
	//tambahan progress bar
	con.upload.addEventListener("progress", progressHandler, false);
	con.addEventListener("load", completeHandler, false);
	con.addEventListener("error", errorHandler, false);
	con.addEventListener("abort", abortHandler, false);
	//tambahan progress bar -end-
	con.open("POST", "kebun_slave_bkm.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//=== Success Response
					alertify.alert('Uploaded Success.');
					document.getElementById('btnsubmit').style.display="";
					document.getElementById("upload").value = "";
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(notransaksi) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	tujuan = 'kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	tujuan = 'kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function formupload() {
	width = '';
	height = '';
	content = "<fieldset style=\"width:97%;\"><div id=contviewupload style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "View";
	showDialog5(title, content, width, height, ev);
}
function viewfile(idfile,sumber) {
	//formupload();
	param = 'method=viewfile&idfile=' + idfile;
	tujuan = 'kebun_slave_bkm.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contviewupload').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}