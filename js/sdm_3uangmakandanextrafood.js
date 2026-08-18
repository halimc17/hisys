function setharikary(karyawanid,periode,row){
	hari=document.getElementById('hari'+row).value;
	
    param='method=setharikary'+'&karyawanid='+karyawanid;
	param += '&periode=' + periode; 
	param += '&hari=' + hari; 
    tujuan='sdm_slave_3uangmakandanextrafood.php';
	post_response_text(tujuan, param, respog);	
    
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					simpanheader();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function detailData(kodeorg,periode,tipekar,jenisid,jenis){
    param = "method=preview&kodeorg="+kodeorg+"&jenis="+jenis;
	param += '&periode=' + periode; 
	param += '&tipekar=' + tipekar; 
	param += '&jenisid=' + jenisid; 
	title="Data Detail";
	ev = 'event';
	// showDialog1(title,"<fieldset style='height:385px'><legend>Preview</legend><iframe frameborder=0 style='width:100%;height:370px'"+
	// " src='sdm_slave_3uangmakandanextrafood.php?"+param+"'></iframe></fieldset>",'900','400',ev);	
	// var dialog = document.getElementById('dynamic1');
	// dialog.style.top = '50px';
	// dialog.style.left = '15%';
	
	alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='sdm_slave_3uangmakandanextrafood.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	leftFixedTable();
}

function postingData(kodeorg,periode,tipekar,jenis){
    param='method=posting'+'&kodeorg='+kodeorg;
	param += '&periode=' + periode; 
	param += '&tipekar=' + tipekar; 
	param += '&jenis=' + jenis; 
    tujuan='sdm_slave_3uangmakandanextrafood.php';
    if(confirm('Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
				  getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function unposting(kodeorg,periode,tipekar,jenis){
    param='method=unposting'+'&kodeorg='+kodeorg;
	param += '&periode=' + periode; 
	param += '&tipekar=' + tipekar; 
	param += '&jenis=' + jenis; 
    tujuan='sdm_slave_3uangmakandanextrafood.php';
    if(confirm('Anda yakin ???\nJika proses gaji sudah dilakukan, maka setelah diposting lakukan proses gaji ulang.')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
				  getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function del(kodeorg,periode,tipekar,jenis){
    param='method=delete'+'&kodeorg='+kodeorg;
	param += '&periode=' + periode;
	param += '&tipekar=' + tipekar;
	param += '&jenis=' + jenis;
    tujuan='sdm_slave_3uangmakandanextrafood.php';
    if(confirm('Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
				  getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function displayList(){
    document.getElementById('kodeorgsch').value='';
    document.getElementById('periodesch').value='';
    document.getElementById('tipesch').value='';
    
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
	
	document.getElementById('header_trans').style.display='block';
    document.getElementById('judul_header').style.display='block';
    loaddata(0);
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
	
	kodeorg=document.getElementById('kodeorgsch').value;
	periode=document.getElementById('periodesch').value;
	tipekar=document.getElementById('tipesch').value;
	param = 'method=loaddata&page=' + page;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&tipekar=' + tipekar;
 
    tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
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
    document.getElementById('kodeorg').value='';
    document.getElementById('tipekar').value='';
    document.getElementById('periode').value='';
    document.getElementById('jenis').value='';
}

function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel();  
}

function edit(kodeorg,periode,tipekar,jenis){
    document.getElementById('tipekar').value=tipekar;
    document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('periode').value=periode;
	document.getElementById('jenis').value=jenis;
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
	simpanheader();
}

function simpanheader(){
	kodeorg= document.getElementById('kodeorg').value;
	tipekar = document.getElementById('tipekar').value;
	periode= document.getElementById('periode').value;
	jenis= document.getElementById('jenis').value;

    
	if(periode==''||kodeorg==''){
        alert('Kode organisasi dan periode harus di isi.');
        return;
    }
	if(jenis==''){
        alert('Jenis harus di isi.');
        return;
    }
	
    param = 'method=simpanheader';
    param += '&kodeorg=' + kodeorg+'&tipekar=' + tipekar+'&periode=' + periode+'&jenis=' + jenis;
    tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
					document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    leftFixedTable();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanall(maxRow){  
	if(maxRow =='' || maxRow ==0){
        alert('Data tidak ditemukan, proses dibatalkan.');
        return;
    }
	if(confirm("Simpan semua ???")){
		simpan(1,maxRow,1);
	}
}

function simpan(currRow,maxRow,currcol){
	currcol   = parseFloat(currcol);
	kodeorg   = document.getElementById('kodeorg').value;
	periode   = document.getElementById('periode').value;
	jumlahtgl = document.getElementById('jumlahtgl').value;
	idkomponen= document.getElementById('idkomponen').value;
	tipekar   = document.getElementById('tipekar').value;
	
	karyawanid= document.getElementById('karyawanid'+currRow).innerHTML;
	rupiah    = document.getElementById('rupiah_'+currRow+'_'+currcol).innerHTML;
	tanggal   = document.getElementById('tanggal_'+currRow+'_'+currcol).innerHTML;
	namahari  = document.getElementById('namahari_'+currRow+'_'+currcol).innerHTML;
	jamkerja  = document.getElementById('jamkerja_'+currRow+'_'+currcol).innerHTML;
	absen     = document.getElementById('absen_'+currRow+'_'+currcol).innerHTML;
	rupiah    = remove_comma_var(rupiah);

	param = "";
	param += "kodeorg="+kodeorg;
	param += "&periode="+periode;
	param += "&idkomponen="+idkomponen;
	param += "&tipekar="+tipekar;
	param += "&karyawanid="+karyawanid;
	param += "&rupiah="+rupiah;
	param += "&tanggal="+tanggal;
	param += "&jumlahtgl="+jumlahtgl;
	param += "&namahari="+namahari;
	param += "&jamkerja="+jamkerja;
	param += "&absen="+absen;
	param += "&method=insert";
	
	tujuan='sdm_slave_3uangmakandanextrafood.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    unlockScreen();
                } else {
					if(currcol!=undefined){
						if(document.getElementById('rupiah_'+currRow+'_'+currcol).style.backgroundColor!='grey'){
							if(document.getElementById('rupiah_'+currRow+'_'+currcol).style.backgroundColor=='cyan'){
								document.getElementById('rupiah_'+currRow+'_'+currcol).style.backgroundColor='';
							}else{								
								document.getElementById('rupiah_'+currRow+'_'+currcol).style.backgroundColor='cyan';
							}
						}
					}
					currcol+=1;
					if(currcol>jumlahtgl){						
						document.getElementById('baris'+currRow).style.display='none';
						document.getElementById('barisa'+currRow).style.display='none';
						document.getElementById('barisb'+currRow).style.display='none';
						document.getElementById('barisc'+currRow).style.display='none';
						currRow+=1;
						if((currRow>maxRow) || (maxRow == undefined)){
							alert("Done");
						} else {
							simpan(currRow,maxRow,1);
						}
					}else{
						simpan(currRow,maxRow,currcol);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}


















// function detailPDF(notransaksi,numRow,ev,tipe) {
    // param = "proses=pdf&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis=pdf";
    
    // showDialog1('Print PDF',"<iframe frameborder=0 style='width:885px;height:400px'"+
        // " src='kebun_slave_operasional_print_detailx_pdf.php?"+param+"'></iframe>",'900','400',ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
// }





// function deletedetail(notransaksi,karyawanid,blok,kegiatan,numrow){
    // param='method=deletedetail'+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan;
 
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
	// if(confirm('Anda yakin ???')){
		// post_response_text(tujuan, param, respog);	
	// }
    // function respog(){
		// if(con.readyState==4){
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
				// } else {
				   // loaddatadetail(notransaksi);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }	
    // }
// }


// function editdetail(notransaksi,karyawanid,kegiatan,blok,luas,satuan,prestasi,jhk,upah,premi,numrow){
	// row=document.getElementById('jlhbrs').value;
	// if(row!='' || row!=0){
		// alert('Silahkan uncheck Per Mandor untuk melakukan Edit !\n\nJika nama karyawan tidak muncul silahkan pilih Filter Divisi = Seluruhnya'); return;
	// }
	// document.getElementById('notransaksi').value=notransaksi;
	// document.getElementById('karyawanid').value=karyawanid;
	// document.getElementById('karyawanid').disabled=true;
	// document.getElementById('blok').value=blok;
	// document.getElementById('blok').disabled=true;
	// document.getElementById('kegiatan').value=kegiatan;
	// document.getElementById('kegiatan').disabled=true;
	// document.getElementById('luas').value=luas;
	// document.getElementById('satuan').value=satuan;
	// document.getElementById('prestasi').value=prestasi;
	// document.getElementById('jhk').value=jhk;
	// document.getElementById('upah').value=upah;
	// document.getElementById('premi').value=premi;
	// document.getElementById('method').value='update';
	// cekPremiAktif(kegiatan);
	
// }

// function cekPremiAktif(kegiatan){
    // param='method=getDataDetail'+'&kegiatan='+kegiatan; 
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// isdt = con.responseText.split("######"); 
					// if(isdt[0]==1){
						// document.getElementById('premi').disabled = true;
					// }else{
						// document.getElementById('premi').disabled = false;
					// }
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function unhidedendadt(){
	// row=document.getElementById('jlhbrsdt').value;
	// document.getElementById('pheaddt').style.display = '';
	// //document.getElementById('tabledt').style.width = '100%';
	// for(i=1;i<=10;i++){
		// document.getElementById('pdt'+i).style.display = '';
		// document.getElementById('tpddt'+i).style.display = '';
	// }
	// for(i=1;i<=10;i++){
		// for(brs=1;brs<=row;brs++){
			// document.getElementById('pddt'+i+brs).style.display = '';
		// }
	// }
// }

// function cleardetailall(){
	// document.getElementById('method').value='insert';
	// document.getElementById('karyawanid').value='';
	// document.getElementById('karyawanid').disabled=false;
	// document.getElementById('kegiatan').disabled=false;
	// document.getElementById('kegiatan').value='';
	// document.getElementById('blok').disabled=false;
	// document.getElementById('blok').value='';
	// document.getElementById('luas').value='';
	// document.getElementById('satuan').value='';
	// document.getElementById('prestasi').value='';
	// document.getElementById('jhk').value='';
	// document.getElementById('upah').value='';
	// document.getElementById('premi').value='';
// }

// function cleardetail(baris){
	// row=document.getElementById('jlhbrs').value;
	// document.getElementById('method').value='insert';
	// if(row==0){
		// document.getElementById('karyawanid').value='';
		// document.getElementById('karyawanid').disabled=false;
		// document.getElementById('kegiatan').disabled=false;
		// document.getElementById('blok').disabled=false;
		// document.getElementById('luas').value='';
		// document.getElementById('satuan').value='';
		// document.getElementById('upah').value='';
		// document.getElementById('premi').value='';
		// document.getElementById('basis').value='';
		// document.getElementById('rpsat').value='';
		// //document.getElementById('jhk').value='';
	// } else {
		// document.getElementById('kegiatan'+baris).disabled=false;
		// document.getElementById('kegiatan'+baris).value='';
		// document.getElementById('blok'+baris).disabled=false;
		// document.getElementById('blok'+baris).value='';
		// document.getElementById('luas'+baris).value='';
		// document.getElementById('satuan'+baris).value='';
		// document.getElementById('upah'+baris).value='';
		// document.getElementById('premi'+baris).value='';
		// document.getElementById('basis'+baris).value='';
		// document.getElementById('rpsat'+baris).value='';
		// document.getElementById('prestasi'+baris).value='';
		// document.getElementById('jhk'+baris).value='';
	// }
// }

// function checkval(word,value){
	// if(value.value > 1){
		// alert("Value "+word+" maximal adalah 1");
		// value.value='';
		// value.focus();
	// }
// }

// maxf=0
// sekarang=1;
// function saveAll(maxRow){  
	// if(maxRow =='' || maxRow ==0){
        // alert('Data tidak ditemukan, proses dibatalkan !');
        // return;
    // }
	// if(confirm("Info : Hanya Kegiatan, Blok, Prestasi, HK atau Premi yang berisi\nyg akan di simpan.\n\nSimpan semua ???")){
		// maxf=maxRow;
		// savedetail(1,maxRow);
	// }
// }

// function savedetail(currRow,maxRow){
	// row=document.getElementById('jlhbrs').value;
	// notransaksi=document.getElementById('notransaksi').value;
	// nobkm=document.getElementById('nobkm').value;
	// stsawal=document.getElementById('stsawal').value;
    // kodeorg= document.getElementById('kodeorg').value;
    // mandor= document.getElementById('mandor').value;
    // mandor1= document.getElementById('mandor1').value;
    // asst= document.getElementById('asst').value;
    // kerani= document.getElementById('kerani').value;
    // tgl=document.getElementById('tgl').value;
    // mode=document.getElementById('mode').value;
	// method=document.getElementById('method').value;
	// if(row==0){
		// karyawanid=document.getElementById('karyawanid').value;
		// kegiatan=document.getElementById('kegiatan').value;
		// blok=document.getElementById('blok').value;
		// prestasi=document.getElementById('prestasi').value;
		// jhk=document.getElementById('jhk').value;
		// upah=document.getElementById('upah').value;
		// premi=document.getElementById('premi').value;
		
		// if(karyawanid==''){alert("Nama Karyawan Wajib diisi !!!"); document.getElementById('karyawanid').focus(); return;}
		// if(kegiatan==''){alert("Kegiatan Wajib diisi !!!");document.getElementById('kegiatan').focus(); return;}
		// if(blok==''){alert("Blok Wajib diisi !!!"); document.getElementById('blok').focus(); return;}
		// if(prestasi==''){alert("Hasil Kerja Wajib diisi !!!"); document.getElementById('prestasi').focus(); return;}
		// if((parseFloat(upah)=='' || parseFloat(upah)==0) && (parseFloat(premi)==''|| parseFloat(premi)==0)){alert("Upah atau Premi salah satu wajib diisi !!!"); document.getElementById('jhk').focus(); return;}
		
	// } else {
		// karyawanid=document.getElementById('karyawanid'+currRow).value;
		// kegiatan=document.getElementById('kegiatan'+currRow).value;
		// blok=document.getElementById('blok'+currRow).value;
		// prestasi=document.getElementById('prestasi'+currRow).value;
		// jhk=document.getElementById('jhk'+currRow).value;
		// upah=document.getElementById('upah'+currRow).value;
		// premi=document.getElementById('premi'+currRow).value;
	// }

	// param = "";
	// param += "notransaksi="+notransaksi;
	// param += "&karyawanid="+karyawanid;
	// param += "&kegiatan="+kegiatan;
	// param += "&blok="+blok;
	// param += "&prestasi="+prestasi;
	// param += "&jhk="+jhk;
	// param += "&upah="+upah;
	// param += "&premi="+premi;
	// param += "&stsawal="+stsawal;
	// param += "&nobkm="+nobkm;
	// param +='&method='+method;
	// param +='&tgl='+tgl;
	// param +='&kodeorg='+kodeorg;
	// param +='&mandor='+mandor;
	// param +='&mandor1='+mandor1;
	// param +='&asst='+asst;
	// param +='&kerani='+kerani;
	// param +='&mode='+mode;
	
	// tujuan='sdm_slave_3uangmakandanextrafood.php';
	// post_response_text(tujuan, param, respog); if(currRow!=undefined){		
		// document.getElementById('row' + currRow).style.backgroundColor='cyan';
	// }
    // function respog(){
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
					// document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    // unlockScreen();
                // } else {
					// if(trim(con.responseText)!=''){
						// document.getElementById('notransaksi').value = trim(con.responseText);
					// }
					// cleardetail(currRow);
					// loaddatadetail();
					// if(currRow != undefined){
						// document.getElementById('row' + currRow).style.backgroundColor='';
					// }
					// currRow+=1;
                    // sekarang=currRow;
                    // if((currRow>maxRow) || (maxRow == undefined)){
						// loaddatadetail();
					// } else {
						// savedetail(currRow,maxRow);
                    // }
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }		
// }


// function copykegiatan(baris){
	// row=document.getElementById('jlhbrs').value;
	// copykeg=document.getElementById('copykeg');
	// if(copykeg.checked==true){
		// kegiatan=document.getElementById('kegiatan'+baris).value;
		// if(row>0){
			// for(i=0;i<row;i++){
				// document.getElementById('kegiatan'+(baris+i)).value=kegiatan;
			// }
		// }
	// } 
// }

// function copyblok(baris){
	// row=document.getElementById('jlhbrs').value;
	// copyblk=document.getElementById('copyblok');
	// if(copyblk.checked==true){
		// blok=document.getElementById('blok'+baris).value;
		// if(row>0){
			// for(i=0;i<row;i++){
				// if(document.getElementById('blok'+(baris+i))!=null){
					// document.getElementById('blok'+(baris+i)).value=blok;
				// }
			// }
		// }
	// }
// }

// function copypres(baris){
	// row=document.getElementById('jlhbrs').value;
	// copyprs=document.getElementById('copypres');
	// if(copyprs.checked==true){
		// prestasi=document.getElementById('prestasi'+baris).value;
		// if(row>0){
			// for(i=0;i<row;i++){
				// document.getElementById('prestasi'+(baris+i)).value=prestasi;
			// }
		// }
	// } 
// }

// maxf=0
// sekarang=1;
// function getDataDetailAllAll(baris,id){
	// maxRow=document.getElementById('jlhbrs').value;
	// maxf=maxRow;

	// copykeg=document.getElementById('copykeg');
	// copyblk=document.getElementById('copyblok');
	// copyprs=document.getElementById('copypres');
	
	// if(copykeg.checked==true){
		// getDataDetailAll(baris,maxRow,id);
	// } else if(copyblk.checked==true){
		// getDataDetailAll(baris,maxRow,id);
	// } else if(copyprs.checked==true){
		// getDataDetailAll(baris,maxRow,id);
	// } else{
		// getDataDetail(baris,id);
	// }
// }

// // Fungsi ini sama dengan bawah, jangan tanya kenapa di buat dua biji !!!
// function getDataDetailAll(baris,maxRow,id){
	// row=document.getElementById('jlhbrs').value;
	// kodeorg=document.getElementById('kodeorg').value;
    // filterdivisi=document.getElementById('filterdivisi').value; 
	// tgl=document.getElementById('tgl').value;
	// if(row==0){
		// karyawanid=document.getElementById('karyawanid').value;
		// blok=document.getElementById('blok').value;
		// kegiatan=document.getElementById('kegiatan').value;
		// prestasi=document.getElementById('prestasi').value;
	// } else {		
		// karyawanid=document.getElementById('karyawanid'+baris).value;
		// blok=document.getElementById('blok'+baris).value;
		// kegiatan=document.getElementById('kegiatan'+baris).value;
		// prestasi=document.getElementById('prestasi'+baris).value;
	// }
    // param='method=getDataDetail'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan+'&kodeorg='+kodeorg+'&prestasi='+prestasi; 
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// isdt = con.responseText.split("######"); 
					// stspremi = parseFloat(trim(isdt[0]));
					// basis = parseFloat(trim(isdt[1]));
					// premibasis = parseFloat(trim(isdt[2]));
					// premilebihbasis = parseFloat(trim(isdt[3]));
					// tipeKary = parseFloat(trim(isdt[4]));
					// luasblok = parseFloat(trim(isdt[5]));
					// satkegiatan = trim(isdt[6]);
					// rpsat = parseFloat(trim(isdt[7]));
					// kdkeg = trim(isdt[8]);
					
					// if(isNaN(luasblok)==true){
						// luasblok=0;
					// }
					// if(isNaN(basis)==true){
						// basis=0;
					// }
					// if(isNaN(rpsat)==true){
						// rpsat=0;
					// }
					// totalpremi=premibasis+premilebihbasis;
					// if(isNaN(totalpremi)==true){
						// totalpremi=0;
					// }
					// if(trim(isdt[0])==1){
						// if(row==0){	
							// document.getElementById('premi').disabled = true;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
						// } else {
							// document.getElementById('premi'+baris).disabled = true;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						// }
					// } else {
						// if(row==0){	
							// document.getElementById('premi').disabled = false;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
						// } else {
							// document.getElementById('premi'+baris).disabled = false;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						// }
					// }
					
					// if(row==0){
						// alert(id);
						// if(id=='changekeg'){							
							// document.getElementById('kegiatan').innerHTML = kdkeg;
						// }
						// document.getElementById('luas').value = numberFormat(luasblok,2);
						// document.getElementById('satuan').value = satkegiatan;
						// document.getElementById('basis').value = numberFormat(basis);
						// document.getElementById('rpsat').value = numberFormat(rpsat,2);
					// } else {
						// if(id=='changekeg'){
							// document.getElementById('kegiatan'+baris).innerHTML = kdkeg;
						// }
						// document.getElementById('luas'+baris).value = numberFormat(luasblok,2);
						// document.getElementById('satuan'+baris).value = satkegiatan;
						// document.getElementById('basis'+baris).value = numberFormat(basis);
						// document.getElementById('rpsat'+baris).value = numberFormat(rpsat,2);
					// }

					// baris+=1;
                    // sekarang=baris;
                    // if((baris>maxRow) || (maxRow == undefined)){
						// //alert('Done');
					// } else {
						// getDataDetailAll(baris,maxRow,id);
                    // }
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// // Fungsi ini sama dengan atas, jangan tanya kenapa di buat dua biji !!!
// function getDataDetail(baris,id){
	// row=document.getElementById('jlhbrs').value;
	// kodeorg=document.getElementById('kodeorg').value;
    // filterdivisi=document.getElementById('filterdivisi').value; 
	// tgl=document.getElementById('tgl').value;
	// if(row==0){
		// karyawanid=document.getElementById('karyawanid').value;
		// blok=document.getElementById('blok').value;
		// kegiatan=document.getElementById('kegiatan').value;
		// prestasi=document.getElementById('prestasi').value;
	// } else {		
		// karyawanid=document.getElementById('karyawanid'+baris).value;
		// blok=document.getElementById('blok'+baris).value;
		// kegiatan=document.getElementById('kegiatan'+baris).value;
		// prestasi=document.getElementById('prestasi'+baris).value;
	// }
    // param='method=getDataDetail'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan+'&kodeorg='+kodeorg+'&prestasi='+prestasi; 
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// isdt = con.responseText.split("######"); 
					// stspremi = parseFloat(trim(isdt[0]));
					// basis = parseFloat(trim(isdt[1]));
					// premibasis = parseFloat(trim(isdt[2]));
					// premilebihbasis = parseFloat(trim(isdt[3]));
					// tipeKary = parseFloat(trim(isdt[4]));
					// luasblok = parseFloat(trim(isdt[5]));
					// satkegiatan = trim(isdt[6]);
					// rpsat = parseFloat(trim(isdt[7]));
					// kdkeg = trim(isdt[8]);
					
					// if(isNaN(luasblok)==true){
						// luasblok=0;
					// }
					// if(isNaN(basis)==true){
						// basis=0;
					// }
					// if(isNaN(rpsat)==true){
						// rpsat=0;
					// }
					// totalpremi=premibasis+premilebihbasis;
					// if(isNaN(totalpremi)==true){
						// totalpremi=0;
					// }
					// if(trim(isdt[0])==1){
						// if(row==0){	
							// document.getElementById('premi').disabled = true;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
						// } else {
							// document.getElementById('premi'+baris).disabled = true;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						// }
					// } else {
						// if(row==0){	
							// document.getElementById('premi').disabled = false;
							// document.getElementById('premi').value = numberFormat(totalpremi,2);
						// } else {
							// document.getElementById('premi'+baris).disabled = false;
							// document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						// }
					// }
						
					// if(row==0){
						// if(id=='changekeg'){						
							// document.getElementById('kegiatan').innerHTML = kdkeg;
						// }
						// document.getElementById('luas').value = numberFormat(luasblok,2);
						// document.getElementById('satuan').value = satkegiatan;
						// document.getElementById('basis').value = numberFormat(basis);
						// document.getElementById('rpsat').value = numberFormat(rpsat,2);
					// } else {
						// if(id=='changekeg'){
							// document.getElementById('kegiatan'+baris).innerHTML = kdkeg;
						// }
						// document.getElementById('luas'+baris).value = numberFormat(luasblok,2);
						// document.getElementById('satuan'+baris).value = satkegiatan;
						// document.getElementById('basis'+baris).value = numberFormat(basis);
						// document.getElementById('rpsat'+baris).value = numberFormat(rpsat,2);
					// }
					// getumr(baris);
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function getumr(baris,dclick){
	// row=document.getElementById('jlhbrs').value;
	// tgl=document.getElementById('tgl').value;
	// kodeorg=document.getElementById('kodeorg').value;
	
	// //dclick isinya : d => didapat dari perintah dible click, i => sumber isian
	// if(dclick=='d'){
		// if(row==0){
			// document.getElementById('jhk').value=1;			
		// }else{
			// document.getElementById('jhk'+baris).value=1;			
		// }
	// }
	
	// if(row==0){
		// karyawanid=document.getElementById('karyawanid').value;
		// jhk=document.getElementById('jhk').value;
	// } else {		
		// karyawanid=document.getElementById('karyawanid'+baris).value;
		// jhk=document.getElementById('jhk'+baris).value;
	// }
	// if(jhk>1){
		// alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
		// if(row==0){
			// document.getElementById('jhk').value='';
			// document.getElementById('upah').value='';
		// } else {		
			// document.getElementById('jhk'+baris).value='';
			// document.getElementById('upah'+baris).value='';
		// }
		// return false;
	// }
	
    // param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// umr = trim(con.responseText);
					// jlhrp = parseFloat(trim(umr))*parseFloat(jhk);
					// if(isNaN(jlhrp)==true){
						// jlhrp=0;
					// }
					
					// if(umr==0){
						// if(row==0){	
							// document.getElementById('upah').value='';
							// document.getElementById('jhk').value='';
						// } else {
							// document.getElementById('upah'+baris).value='';
							// document.getElementById('jhk'+baris).value='';
						// }
						// alert('Gaji Pokok Karyawan belum ada.'); 
						// return false;
					// } else{
						// if(row==0){	
							// document.getElementById('upah').value=numberFormat(jlhrp,2);
						// } else {
							// document.getElementById('upah'+baris).value=numberFormat(jlhrp,2);
						// }
					// }
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function getdatamandor(){
    // filterdivisi=document.getElementById('filterdivisi').value; 
    // mandor=document.getElementById('mandor').value; 
    // kodeorg=document.getElementById('kodeorg').value; 
	// tgl=document.getElementById('tgl').value;
	// showpermandor = document.getElementById('showpermandor');   
	// if(showpermandor.checked==true){
		// method='getdatamandor';
		// document.getElementById('copy').style.display = '';
	// }else{
		// method='inputdetail';
		// document.getElementById('copy').style.display = 'none';
	// }
	
    // param='method='+method+'&filterdivisi='+filterdivisi+'&mandor='+mandor+'&tgl='+tgl+'&kodeorg='+kodeorg;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// isdtmdr = con.responseText.split("######");
                    // document.getElementById('inputdetail').innerHTML = isdtmdr[0];
					// row = trim(isdtmdr[1]);
					// getdata(row);
					
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }


// function getdata(row){
	// row=document.getElementById('jlhbrs').value;
    // filterdivisi=document.getElementById('filterdivisi').value; 
	// tgl=document.getElementById('tgl').value;
	// stsawal=document.getElementById('stsawal').value;
	// kodeorg=document.getElementById('kodeorg').value;
	
    // param='method=getdata'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&stsawal='+stsawal+'&kodeorg='+kodeorg;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// if(row==0){
						// isdata = con.responseText.split("######");
						// document.getElementById('karyawanid').innerHTML = isdata[0];
						// document.getElementById('blok').innerHTML = isdata[1];
					// } else {
						// for(i=1;i<=row;i++){
						// isdata = con.responseText.split("######");
							// document.getElementById('blok'+i).innerHTML=isdata[1];	
						// }						
					// }
					
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function getnotransaksi(){
	// kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	// tgl=document.getElementById('tgl').value;
	// document.getElementById('notransaksi').value='';
	// param='tgl='+tgl+'&kodeorg='+kodeorg+'&method=getnotransaksi';
	
	// tujuan='sdm_slave_3uangmakandanextrafood.php';  
	// post_response_text(tujuan, param, respog);
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('notransaksi').value=trim(con.responseText);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }	
// }



// function addHeader(){
    // kodeorg= document.getElementById('kodeorg').value;
    // mandor= document.getElementById('mandor').value;
    // mandor1= document.getElementById('mandor1').value;
    // asst= document.getElementById('asst').value;
    // kerani= document.getElementById('kerani').value;
    // nobkm=document.getElementById('nobkm').value;
    // tgl=document.getElementById('tgl').value;
    // notransaksi=document.getElementById('notransaksi').value;
    // stsawal=document.getElementById('stsawal').value;
    // mode=document.getElementById('mode').value;
    
	// if(tgl==''||kodeorg==''){
        // alert('Tanggal dan atau Kode Organisasi harus di isi !');
        // return;
    // }
						
    // param = 'method=detail';
    // param += '&tgl=' + tgl+'&kodeorg=' + kodeorg+'&nobkm=' + nobkm+'&mandor=' + mandor+'&mandor1=' + mandor1+'&asst=' + asst+'&kerani=' + kerani+'&notransaksi='+notransaksi+'&stsawal='+stsawal+'&mode='+mode;
    // tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)){
                    // alert(con.responseText);
                // }else {
                    // document.getElementById('detail').style.display = 'block';
                    // document.getElementById('detail').innerHTML = con.responseText;
                    // inputdetail(notransaksi);
					
                // }
            // }else{
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

// function inputdetail(notransaksi){
    // kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	// filterdivisi= document.getElementById('filterdivisi').options[document.getElementById('filterdivisi').selectedIndex].value;
	// showpermandor = document.getElementById('showpermandor');   
	// if(showpermandor.checked==true){
		// showpermandor=1;
	// }else{
		// showpermandor=0;
	// }
	// tgl=document.getElementById('tgl').value;
    // notransaksi=document.getElementById('notransaksi').value;
    
  
    // param = 'method=inputdetail';
    // param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi+'&filterdivisi=' + filterdivisi+'&showpermandor=' + showpermandor;
    // tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)){
                    // alert(con.responseText);
                // } else {
                    // document.getElementById('inputdetail').innerHTML = con.responseText;
					// loaddatadetail(notransaksi);
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

// function inputdetailmaterial(notransaksi){
	// tgl=document.getElementById('tgl').value;
    // notransaksi=document.getElementById('nobkm').value;
    // kodeorg=document.getElementById('kodeorg').value;
    
    // param = 'method=inputdetailmaterial';
    // param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    // tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)){
                    // alert(con.responseText);
                // } else {
                    // document.getElementById('inputdetailmaterial').innerHTML = con.responseText;
					// loaddatadetailmaterial(notransaksi);
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }


// function savematerial(currRow){
	// notransaksi=document.getElementById('notran'+currRow).innerHTML;
	// kegiatan=document.getElementById('kegiatanmat'+currRow).innerHTML;
	// blok=document.getElementById('blokmat'+currRow).innerHTML;
	// kodegudang=document.getElementById('kodegudang'+currRow).innerHTML;
	// kodebarang=document.getElementById('kodemat'+currRow).value;
	// qtymat=document.getElementById('qtymat'+currRow).value;
	// prestasi=document.getElementById('pres'+currRow).innerHTML;
	// tgl = trim(document.getElementById('tgl').value);
	
	// if(kodebarang=='' || kodebarang=='0'){
		// notif('kodemat'+currRow+'#namamat'+currRow,'','Kode atau nama barang tidak boleh kosong.'); return;
	// }
	// if(qtymat=='' || qtymat=='0'){
		// notif('qtymat'+currRow,'','Jumlah tidak boleh kosong.'); return;
	// }
	
	// param = 'method=insertmaterial';
	// param += '&notransaksi='+notransaksi;
	// param += '&kegiatan='+kegiatan;
	// param += '&blok='+blok;
	// param += '&kodebarang='+kodebarang;
	// param += '&qtymat='+qtymat;
	// param += '&kodegudang='+kodegudang;
	// param += '&prestasi='+prestasi;
	// param += '&tgl='+tgl;
	
	// tujuan='sdm_slave_3uangmakandanextrafood.php';
	// post_response_text(tujuan, param, respog);
    
    // function respog(){
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
					// //document.getElementById('rowmat_' + currRow).style.backgroundColor = 'red';
                // } else {
					// document.getElementById('rowmat_' + currRow).style.color='';
					// loaddatadetail(notransaksi);
					// clearmaterial(currRow);
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }		
// }

// function clearmaterial(currRow){
	// document.getElementById('kodemat'+currRow).value='';
	// document.getElementById('namamat'+currRow).value='';
	// document.getElementById('satmat'+currRow).value='';
	// document.getElementById('qtymat'+currRow).value='';
	// hapuswarna('kodemat'+currRow+'#namamat'+currRow+'#qtymat'+currRow);
// }

// function delmaterial(notransaksi,kegiatan,blok,kodebarang){

	// param = 'method=delmaterial';
	// param += '&notransaksi='+notransaksi;
	// param += '&kegiatan='+kegiatan;
	// param += '&blok='+blok;
	// param += '&kodebarang='+kodebarang;
	
	// tujuan='sdm_slave_3uangmakandanextrafood.php';
	// if(confirm('Anda yakin ???')){
		// post_response_text(tujuan, param, respog);
	// }
    
    // function respog(){
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
					// loaddatadetail(notransaksi);
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

// function loaddatadetailmaterial(notransaksi){
	// tgl=document.getElementById('tgl').value;
    // notransaksi=document.getElementById('nobkm').value;
    // kodeorg=document.getElementById('kodeorg').value;
    
    // param = 'method=loaddatadetailmaterial';
    // param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    // tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)){
                    // alert(con.responseText);
                // } else {
                    // document.getElementById('loaddatadetailmaterial').innerHTML = con.responseText;
					// loaddataabsensi();
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

// function searchmat(baris,title,ev){
	// kdgdg = document.getElementById('kodegudang'+baris).innerHTML;
	// kgtn = document.getElementById('kegiatanmat'+baris).innerHTML;
	// if(kdgdg==''){alert("Kode Gudang belum ada, silahkan tambah melalui menu Kebun - Setup - Gudang Divisi !!!"); return;}
	// content= "<div style='width:100%;'>";
	// content+="<fieldset style=width:95%>Search : <input type=text id=txtnamabarang onkeypress='key=getKey(event);if(key==13){goCariBarang()}' class=myinputtext size=25 maxlength=35><button class=mybutton onclick=goCariBarang()>Search</button> </div></fieldset>";
	// content+="<input id=kodegudang value="+kdgdg+" style=display:none>";
	// content+="<input id=kegiatansch value="+kgtn+" style=display:none>";
	// content+="<input id=baris value="+baris+" style=display:none>";
	// content+="<fieldset><legend><i>Result</i></legend><div id=containercari style='overflow:auto;max-height:317px;'></div></fieldset>";
    // width='auto';
	// height='auto';
	// showDialog2(title,content,width,height,ev);
	
	// var dialog = document.getElementById('dynamic2');
	// clientWidth = document.getElementById("dynamic2").clientWidth;
	// clientHeight = document.getElementById("dynamic2").clientHeight;
	// pos = new Array();
	// pos = getMouseP(ev);

	// dialog.style.top = pos[1]+'px';
	// dialog.style.left = (pos[0]-clientWidth)+'px';
	// goCariBarang();
// }


// function goCariBarang(){
	// kodegudang = trim(document.getElementById('kodegudang').value);
	// kegiatan = trim(document.getElementById('kegiatansch').value);
	// txtcari = trim(document.getElementById('txtnamabarang').value);
	// tgl = trim(document.getElementById('tgl').value);
	// param = 'txtcari='+txtcari+'&method=caribarang&kodegudang='+kodegudang+'&kegiatan='+kegiatan+'&tgl='+tgl;
	// tujuan = 'sdm_slave_3uangmakandanextrafood.php';
	// post_response_text(tujuan, param, respog);
			
	// function respog(){
		// if (con.readyState == 4){
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) 
				// {
					// alert(con.responseText);
				// }else {
					
					// document.getElementById('containercari').innerHTML=con.responseText;
				// }
			// }else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function loadField(kode,nama,sat){
	// baris = document.getElementById('baris').value;
	// document.getElementById('kodemat'+baris).value=kode;
	// document.getElementById('namamat'+baris).value=nama;
	// document.getElementById('satmat'+baris).value=sat;
	// closeDialog2();
// }








// function cariby(val,sumber){
	// if(sumber=='notran'){
		// if(getValue('notrandetsch')==''){
			// document.getElementById('notrandetsch').value=val;
		// }else{
			// document.getElementById('notrandetsch').value='';
		// }
	// }
	// if(sumber=='namakary'){
		// if(getValue('namakarydetsch')==''){
			// document.getElementById('namakarydetsch').value=val;			
		// }else{
			// document.getElementById('namakarydetsch').value='';			
		// }
	// }
	// if(sumber=='blok'){
		// if(getValue('blokdetsch')==''){
			// document.getElementById('blokdetsch').value=val;			
		// }else{
			// document.getElementById('blokdetsch').value='';
		// }
	// }
	// if(sumber=='kegiatan'){
		// if(getValue('kegdetsch')==''){
			// document.getElementById('kegdetsch').value=val;			
		// }else{
			// document.getElementById('kegdetsch').value='';			
		// }
	// }
	// loaddatadetail();
// }

// function cancelcari(){
	// document.getElementById('notrandetsch').value='';
	// document.getElementById('namakarydetsch').value='';
	// document.getElementById('blokdetsch').value='';
	// document.getElementById('kegdetsch').value='';
	// loaddatadetail();
// }



// function loaddatadetail(notransaksi){
    // document.getElementById('kodeorg').disabled=true;
    // document.getElementById('tgl').disabled=true;
	// tgl         =document.getElementById('tgl').value;
	// kodeorg     =document.getElementById('kodeorg').value;
	// notransaksi =document.getElementById('notransaksi').value;
	// nobkm       =document.getElementById('nobkm').value;
	// notrandetsch=document.getElementById('notrandetsch').value;
	// namakary    =document.getElementById('namakarydetsch').value;
	// blok        =document.getElementById('blokdetsch').value;
	// kegiatan    =document.getElementById('kegdetsch').value;
	
	
    // param = 'method=loaddatadetail';
    // param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    // param += '&nobkm=' + nobkm;
	// param += '&notrandetsch=' + notrandetsch;
    // param += '&namakary=' + namakary;
    // param += '&blok=' + blok;
    // param += '&kegiatan=' + kegiatan;
    // tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)){
                    // alert(con.responseText);
                // } else {
                    
                    // document.getElementById('loaddatadetail').innerHTML = con.responseText;
					// inputdetailmaterial(notransaksi);
					
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }

// function numberFormat(number,digit) {
      // number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      // //Seperates the components of the number
      // var components = (parseFloat(number).toFixed(digit)).split(".");
      // //Comma-fies the first part
      // components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      // //Combines the two sections
      // return components.join(".");
// }


// function form(){
    // width = '720';
    // height = '';
    // //nopp=document.getElementById('nopp_'+id).value;
    // content = "<fieldset><div id=containerd align=center style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // title = "Detail HTML";
    // showDialog5(title, content, width, height, ev); 
// }

// function html(notransaksi,kodeorg, tgl){
    // form();
    // param = 'method=html' + '&kodeorg=' + kodeorg + '&tgl=' + tgl+ '&notransaksi=' + notransaksi;
    // tujuan = 'sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText))
                // {
                    // alert(con.responseText);
                // }else{
                    // document.getElementById('containerd').innerHTML = con.responseText;
                // }
            // }else{
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }



// function pindahtab(id,no){ 
	// tabAction(document.getElementById(id),no,'FRM',0);
// }


// function getjurnal(pt,notransaksi,tgl1,tgl2){
	// width    = '900';
	// height   = '400';
	// title    = "Detail Jurnal";
	// content  = "<fieldset ><legend>"+title+"</legend>";
	// content += "<div style=height:370px;width:880px;overflow:auto;><pre>";
	// content += "<table class='sortable' cellspacing='1' border='0'>";
	// content += "<thead><tr>";
	// content += "<th align=center >No</th>";
	// content += "<th align=center >No Jurnal</th>";
	// content += "<th align=center >Kode Jurnal</th>";
	// content += "<th align=center >Nama Jurnal</th>";
	// content += "<th align=center >Tipe</th>";
	// content += "<th align=center >No Voucher</th>";
	// content += "<th align=center >Tanggal</th>";
	// content += "<th align=center >Unit</th>";
	// content += "<th align=center >No Akun</th>";
	// content += "<th align=center >Nama Akun</th>";
	// content += "<th align=center >Tipe Pembayaran</th>";
	// content += "<th align=center >Keterangan</th>";
	// content += "<th align=center >Debet</th>";
	// content += "<th align=center >Kredit</th>";
	// content += "<th align=center >No Referensi</th>";
	// content += "<th align=center >Blok</th>";
	// content += "<th align=center >TT</th>";
	// content += "<th align=center >Kode Kegiatan</th>";
	// content += "<th align=center >Nama Kegiatan</th>";
	// content += "<th align=center >Revisi</th>";
	// content += "</tr></thead><tbody id=containerjurnal></table>";
	// content += "</div>";
	// content += "</fieldset>";
	
    // ev = 'event';
    // showDialog1(title, content, width, height, ev); 
	
	// param = 'pt=' + pt;
	// param += '&ref=' + notransaksi;
	// param += '&periode=' + tgl1;
	// param += '&periode1=' + tgl2;
	// param += '&tipelaporan=html';
	// tujuan = 'keu_laporanJurnal.php';
	// post_response_text(tujuan, param, respog);

	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert('ERROR TRANSACTION,\n' + con.responseText);
				// } else {
					// document.getElementById('containerjurnal').innerHTML = con.responseText;
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }	
// }

// function saveabsensi(){
	// tgl        =document.getElementById('tgl').value;
	// nobkm      =document.getElementById('nobkm').value;
	// notransaksi=document.getElementById('notransaksi').value;
	// karyawanid =document.getElementById('karyawanidabsensi').value;
	// jhk        =document.getElementById('jhkabsen').value;
	// kodeabsen  =document.getElementById('kodeabsen').value;
	// upah       =document.getElementById('upahabsen').value;
	// premi      =document.getElementById('premiabsen').value;
	// keterangan =document.getElementById('keteranganabsen').value;
	// stsawal    =document.getElementById('stsawal').value;
	// method     =document.getElementById('methodabsensi').value;
	// kodeorg     =document.getElementById('kodeorg').value;
	// kodeorgabsensi     =document.getElementById('kodeorgabsensi').value;
	
	// if(karyawanid==''){
		// notif('karyawanidabsensi','notifcontainer','Nama Karyawan Wajib diisi.');return;
	// }
	// if(kodeabsen==''){
		// notif('kodeabsen','notifcontainer','Kode Absensi tidak boleh kosong.');return;
	// }
	// if(upah==''){upah=0;}
	// if(premi==''){premi=0;}
	// if(upah==0 && premi==0){
		// notif('upahabsen#premiabsen','notifcontainer','Upah atau Premi wajib diisi.');return;
	// }
	
    // param ='';
    // param +='&notransaksi='+notransaksi;
    // param +='&kodeorg='+kodeorg;
    // param +='&stsawal='+stsawal;
    // param +='&nobkm='+nobkm;
    // param +='&tgl='+tgl;
    // param +='&method='+method;
    // param +='&karyawanid='+karyawanid;
	// param += '&jhk=' + jhk;
	// param += '&kodeabsen=' + kodeabsen;
	// param += '&upah=' + upah;
	// param += '&premi=' + premi;
	// param += '&keterangan=' + keterangan;
	// param += '&kodeorgabsensi=' + kodeorgabsensi;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
                // } else {
                    // clearabsensi();
					// loaddataabsensi();
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function clearabsensi(){
	// document.getElementById('karyawanidabsensi').value='';
	// document.getElementById('karyawanidabsensi').disabled=false;
	// document.getElementById('kodeabsen').value='H';
	// document.getElementById('jhkabsen').value='1';
	// document.getElementById('upahabsen').value='';
	// document.getElementById('premiabsen').value='';
	// document.getElementById('keteranganabsen').value='';
	// document.getElementById('kodeorgabsensi').value='';
	// document.getElementById('methodabsensi').value='insertabsensi';
	// hapuswarna('kodeabsen#jhkabsen#upahabsen#karyawanidabsensi#premiabsen');
// }

// function loaddataabsensi(){
	// tgl        =document.getElementById('tgl').value;
	// notransaksi=document.getElementById('notransaksi').value;
	// nobkm      =document.getElementById('nobkm').value;
	
    // param ='';
    // param +='&method=loaddataabsensi';
    // param +='&notransaksi='+notransaksi;
    // param +='&tgl='+tgl;
    // param +='&nobkm='+nobkm;
    
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
                // } else {
                    // document.getElementById('loaddataabsensi').innerHTML = con.responseText;
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function editabsensi(karyawanid,absensi,nilaihk,umr,premi,penjelasan,kodeorgabsensi){
	// document.getElementById('karyawanidabsensi').value=karyawanid;
	// document.getElementById('kodeabsen').value=absensi;
	// document.getElementById('jhkabsen').value=nilaihk;
	// document.getElementById('upahabsen').value=umr;
	// document.getElementById('premiabsen').value=premi;
	// document.getElementById('keteranganabsen').value=penjelasan;
	// document.getElementById('kodeorgabsensi').value=kodeorgabsensi;
	// document.getElementById('methodabsensi').value='updateabsensi';
	// document.getElementById('karyawanidabsensi').disabled=true;
// }

// function delabsen(notransaksi,tgl,kodeorg,karyawanid){
	// param ='';
    // param +='&method=delabsen';
    // param +='&notransaksi='+notransaksi;
    // param +='&tgl='+tgl;
    // param +='&kodeorg='+kodeorg;
    // param +='&karyawanid='+karyawanid;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';

    // if(confirm('Anda yakin ???')){
		// post_response_text(tujuan, param, respog);	
	// }
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
                // } else {
                    // loaddataabsensi();
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }


// function notif(idkolom,idpesan,isipesan){
	// if(idpesan!=''){		
		// //document.getElementById(idpesan).innerHTML=isipesan;
	// }
	// col = idkolom.split("#");
	// n = col.length;
	// for(i=0;i<n;i++){
		// kolom=document.getElementById(col[i]);
		// kolom.focus();
		// kolom.style.borderColor='red';		
		// kolom.style.backgroundColor='#F2F94D';
		// kolom.style.fontWeight='bold';
	// }
	// alert(isipesan);
// }
// function hapuswarna(id){
	// col = id.split("#");
	// n = col.length;
	// for(i=0;i<n;i++){
		// kolom=document.getElementById(col[i]);
		// kolom.style.borderColor='';		
		// kolom.style.backgroundColor='';
		// kolom.style.fontWeight='';
	// }
// }

// function getumrabsensi(){
	// tgl=document.getElementById('tgl').value;
	// karyawanid=document.getElementById('karyawanidabsensi').value;
	// jhk=document.getElementById('jhkabsen').value;
	// kodeorg=document.getElementById('kodeorg').value;
	// kodeabsen=document.getElementById('kodeabsen').value;
	// if(kodeabsen=='H'){
		// document.getElementById('jhkabsen').disabled=false;
		// document.getElementById('upahabsen').disabled=false;
	// }else{
		// document.getElementById('jhkabsen').disabled=true;
		// document.getElementById('upahabsen').disabled=true;
	// }
	
	
	// if(jhk>1){
		// alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
		// document.getElementById('jhkabsen').value='';
		// document.getElementById('upahabsen').value='';
		// return false;
	// }
	
	
    // param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl+'&kodeorg='+kodeorg+'&jhk='+jhk;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// umr = con.responseText; 
					// jlhrp = parseFloat(trim(umr))*parseFloat(jhk);
					// if(isNaN(jlhrp)==true){
						// jlhrp=0;
					// }
					
					// /* if(jenishari=='LIBUR' && jhk>0 ){
						// document.getElementById('upahabsen').value='';
						// document.getElementById('jhkabsen').value='';
						// notif('premiabsen','','Untuk hari libur Upah = Nol, Absensi = HM / HB dan rupiah biaya langsung masuk ke Premi.');						
						// return false;
					// } */
					
                    // document.getElementById('upahabsen').value = jlhrp;
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function getnilaihk(){
	// kodeabsen=document.getElementById('kodeabsen').value;
    // param='method=getnilaihk'+'&kodeabsen='+kodeabsen;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
                    // document.getElementById('jhkabsen').value = trim(con.responseText);
					// getumrabsensi();
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // }  	
// }

// function gethk(idsumber,idhasil,idkary,baris){
	// row=document.getElementById('jlhbrs').value;
	// tgl=document.getElementById('tgl').value;
	// kodeorg=document.getElementById('kodeorg').value;
	// rpupah=document.getElementById(idsumber).value;
	// karyawanid=document.getElementById(idkary).value;
	
	// rpupah=remove_comma_var(rpupah);
	// if(karyawanid==''){
		// alert('Pilih nama karyawan terlebih dahulu.'); return;
	// }
	
	// param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl;
    // tujuan='sdm_slave_3uangmakandanextrafood.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if(con.readyState==4){
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                        // alert(con.responseText);
                // } else {
					// umr = trim(con.responseText);
					// if(isNaN(parseFloat(rpupah))==true){rpupah=0;}
					// if(isNaN(parseFloat(umr))==true){umr=0;}
					
					// if(rpupah>0){
						// jhk=parseFloat(rpupah)/parseFloat(umr);
					// }					
					
					// if(parseFloat(rpupah)=='0' || parseFloat(rpupah)==''){jhk=0;}
					// if(isNaN(jhk)==true){jhk=0;}
					// if(parseFloat(umr)=='0'){
						// document.getElementById(idhasil).value='';
						// document.getElementById(idsumber).value='';
						// alert('Gaji pokok karyawan belum ada.'); return;
					// }
					// if(parseFloat(rpupah)>parseFloat(umr)){
						// alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
						// document.getElementById(idhasil).value='';
						// document.getElementById(idsumber).value='';
					// }else{						
						// document.getElementById(idhasil).value=jhk;
					// }
                // }
            // }else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }	
    // } 
// }
