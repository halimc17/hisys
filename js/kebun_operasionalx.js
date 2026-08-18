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

function detailData(notransaksi,numRow,ev,tipe,jenis){
    param = "proses=html&tipe="+tipe+"&notransaksi="+notransaksi+"&jenis="+jenis;
        title="Data Detail";
        showDialog1(title,"<iframe frameborder=0 style='width:995px;min-height:400px'"+
        " src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>",'1000','400',ev);	
        var dialog = document.getElementById('dynamic1');
        dialog.style.top = '50px';
        dialog.style.left = '15%';
}

function detailPDF(notransaksi,numRow,ev,tipe) {
    param = "proses=pdf&tipe="+tipe+"&notransaksi="+notransaksi;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:995px;height:400px'"+
        " src='kebun_slave_operasional_print_detailx.php?"+param+"'></iframe>",'1000','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function postingData(notransaksi,numRow) {
    var param = "notransaksi="+notransaksi;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    x=document.getElementById('tr_'+numRow);
                    x.cells[13].innerHTML='';
                    x.cells[14].innerHTML='';
                    x.cells[15].innerHTML="<img class=\"zImgOffBtn\" title=\"Posted\" src=\"images/skyblue/posted.png\">";
 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	//alert("Yang ini belum jadi postingnya, perlu hitung ulang"); return;
    if(confirm('Akan dilakukan posting untuk transaksi '+notransaksi+
        '\nData tidak dapat diubah setelah ini. Anda yakin?')) {
        post_response_text('kebun_slave_operasional_postingx.php', param, respon);
    }
}


function edit(notransaksi,tgl,kodeorg,nobkm,mandor,mandor1,asst,kerani){
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('tgl').value=tgl;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('nobkm').value=nobkm;
    document.getElementById('mandor').value=mandor;
    document.getElementById('mandor1').value=mandor1;
    document.getElementById('kerani').value=kerani;
    document.getElementById('asst').value=asst;
    document.getElementById('mode').value='edit';
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
    //document.getElementById('detail').style.display='block';
	simpanheader();
	//addHeader(notransaksi);
}

function deletedetail(notransaksi,karyawanid,blok,kegiatan,numrow){
    param='method=deletedetail'+'&notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan;
 
    tujuan='kebun_slave_operasionalx.php';
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
				   loaddatadetail(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}


function editdetail(notransaksi,karyawanid,kegiatan,blok,luas,satuan,prestasi,jhk,upah,premi,numrow){
	row=document.getElementById('jlhbrs').value;
	if(row!='' || row!=0){
		alert('Silahkan uncheck Per Mandor untuk melakukan Edit !\n\nJika nama karyawan tidak muncul silahkan pilih Filter Divisi = Seluruhnya'); return;
	}
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('karyawanid').value=karyawanid;
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
	cekPremiAktif(kegiatan);
	
}

function cekPremiAktif(kegiatan){
    param='method=getDataDetail'+'&kegiatan='+kegiatan; 
    tujuan='kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
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
	document.getElementById('karyawanid').value='';
	document.getElementById('karyawanid').disabled=false;
	document.getElementById('kegiatan').disabled=false;
	document.getElementById('kegiatan').value='';
	document.getElementById('blok').disabled=false;
	document.getElementById('blok').value='';
	document.getElementById('luas').value='';
	document.getElementById('satuan').value='';
	document.getElementById('prestasi').value='';
	document.getElementById('jhk').value='';
	document.getElementById('upah').value='';
	document.getElementById('premi').value='';
}

function cleardetail(baris){
	row=document.getElementById('jlhbrs').value;
	document.getElementById('method').value='insert';
	if(row==0){
		document.getElementById('karyawanid').value='';
		document.getElementById('karyawanid').disabled=false;
		document.getElementById('kegiatan').disabled=false;
		document.getElementById('blok').disabled=false;
		document.getElementById('luas').value='';
		document.getElementById('satuan').value='';
		document.getElementById('upah').value='';
		document.getElementById('premi').value='';
		document.getElementById('basis').value='';
		document.getElementById('rpsat').value='';
	} else {
		document.getElementById('kegiatan'+baris).disabled=false;
		document.getElementById('kegiatan'+baris).value='';
		document.getElementById('blok'+baris).disabled=false;
		document.getElementById('blok'+baris).value='';
		document.getElementById('luas'+baris).value='';
		document.getElementById('satuan'+baris).value='';
		document.getElementById('upah'+baris).value='';
		document.getElementById('premi'+baris).value='';
		document.getElementById('basis'+baris).value='';
		document.getElementById('rpsat'+baris).value='';
		document.getElementById('prestasi'+baris).value='';
		document.getElementById('jhk'+baris).value='';
	}
}

function checkval(word,value){
	if(value.value > 1){
		alert("Value "+word+" maximal adalah 1");
		value.value='';
		value.focus();
	}
}

maxf=0
sekarang=1;
function saveAll(maxRow){  
	if(maxRow =='' || maxRow ==0){
        alert('Data tidak ditemukan, proses dibatalkan !');
        return;
    }
	if(confirm("Info : Hanya Kegiatan, Blok, Prestasi, HK atau Premi yang berisi\nyg akan di simpan.\n\nSimpan semua ???")){
		maxf=maxRow;
		savedetail(1,maxRow);
	}
}

function savedetail(currRow,maxRow){
	row=document.getElementById('jlhbrs').value;
	notransaksi=document.getElementById('notransaksi').value;
	stsawal=document.getElementById('stsawal').value;
	method=document.getElementById('method').value;
	if(row==0){
		karyawanid=document.getElementById('karyawanid').value;
		kegiatan=document.getElementById('kegiatan').value;
		blok=document.getElementById('blok').value;
		prestasi=document.getElementById('prestasi').value;
		jhk=document.getElementById('jhk').value;
		upah=document.getElementById('upah').value;
		premi=document.getElementById('premi').value;
		
		if(karyawanid==''){alert("Nama Karyawan Wajib diisi !!!"); document.getElementById('karyawanid').focus(); return;}
		if(kegiatan==''){alert("Kegiatan Wajib diisi !!!");document.getElementById('kegiatan').focus(); return;}
		if(blok==''){alert("Blok Wajib diisi !!!"); document.getElementById('blok').focus(); return;}
		if(prestasi==''){alert("Hasil Kerja Wajib diisi !!!"); document.getElementById('prestasi').focus(); return;}
		if((parseFloat(upah)=='' || parseFloat(upah)==0) && (parseFloat(premi)==''|| parseFloat(premi)==0)){alert("Upah atau Premi salah satu wajib diisi !!!"); document.getElementById('jhk').focus(); return;}
		
	} else {
		karyawanid=document.getElementById('karyawanid'+currRow).value;
		kegiatan=document.getElementById('kegiatan'+currRow).value;
		blok=document.getElementById('blok'+currRow).value;
		prestasi=document.getElementById('prestasi'+currRow).value;
		jhk=document.getElementById('jhk'+currRow).value;
		upah=document.getElementById('upah'+currRow).value;
		premi=document.getElementById('premi'+currRow).value;
	}

	param = "";
	param += "notransaksi="+notransaksi;
	param += "&karyawanid="+karyawanid;
	param += "&kegiatan="+kegiatan;
	param += "&blok="+blok;
	param += "&prestasi="+prestasi;
	param += "&jhk="+jhk;
	param += "&upah="+upah;
	param += "&premi="+premi;
	param += "&stsawal="+stsawal;
	param +='&method='+method;
	
	
	tujuan='kebun_slave_operasionalx.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('row' + currRow).style.backgroundColor='cyan';
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                } else {
					cleardetail(currRow);
					loaddatadetail();
					if(currRow != undefined){
						document.getElementById('row' + currRow).style.backgroundColor='';
					}
					currRow+=1;
                    sekarang=currRow;
                    if((currRow>maxRow) || (maxRow == undefined)){
						//alert('Done');
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
function getDataDetailAllAll(baris){
	maxRow=document.getElementById('jlhbrs').value;
	maxf=maxRow;

	copykeg=document.getElementById('copykeg');
	copyblk=document.getElementById('copyblok');
	copyprs=document.getElementById('copypres');
	
	if(copykeg.checked==true){
		getDataDetailAll(baris,maxRow);
	} else if(copyblk.checked==true){
		getDataDetailAll(baris,maxRow);
	} else if(copyprs.checked==true){
		getDataDetailAll(baris,maxRow);
	} else{
		getDataDetail(baris);
	}
}

// Fungsi ini sama dengan bawah, jangan tanya kenapa di buat dua biji !!!
function getDataDetailAll(baris,maxRow){
	row=document.getElementById('jlhbrs').value;
	kodeorg=document.getElementById('kodeorg').value;
    filterdivisi=document.getElementById('filterdivisi').value; 
	tgl=document.getElementById('tgl').value;
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
    param='method=getDataDetail'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan+'&kodeorg='+kodeorg+'&prestasi='+prestasi; 
    tujuan='kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
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
							document.getElementById('premi').value = numberFormat(totalpremi,2);
						} else {
							document.getElementById('premi'+baris).disabled = true;
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						}
					} else {
						if(row==0){	
							document.getElementById('premi').disabled = false;
							document.getElementById('premi').value = numberFormat(totalpremi,2);
						} else {
							document.getElementById('premi'+baris).disabled = false;
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						}
					}
						
					if(row==0){						
						document.getElementById('luas').value = numberFormat(luasblok,2);
						document.getElementById('satuan').value = satkegiatan;
						document.getElementById('basis').value = numberFormat(basis);
						document.getElementById('rpsat').value = numberFormat(rpsat,2);
					} else {
						document.getElementById('luas'+baris).value = numberFormat(luasblok,2);
						document.getElementById('satuan'+baris).value = satkegiatan;
						document.getElementById('basis'+baris).value = numberFormat(basis);
						document.getElementById('rpsat'+baris).value = numberFormat(rpsat,2);
					}

					baris+=1;
                    sekarang=baris;
                    if((baris>maxRow) || (maxRow == undefined)){
						//alert('Done');
					} else {
						getDataDetailAll(baris,maxRow);
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
function getDataDetail(baris){
	row=document.getElementById('jlhbrs').value;
	kodeorg=document.getElementById('kodeorg').value;
    filterdivisi=document.getElementById('filterdivisi').value; 
	tgl=document.getElementById('tgl').value;
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
    param='method=getDataDetail'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&karyawanid='+karyawanid+'&blok='+blok+'&kegiatan='+kegiatan+'&kodeorg='+kodeorg+'&prestasi='+prestasi; 
    tujuan='kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
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
							document.getElementById('premi').value = numberFormat(totalpremi,2);
						} else {
							document.getElementById('premi'+baris).disabled = true;
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						}
					} else {
						if(row==0){	
							document.getElementById('premi').disabled = false;
							document.getElementById('premi').value = numberFormat(totalpremi,2);
						} else {
							document.getElementById('premi'+baris).disabled = false;
							document.getElementById('premi'+baris).value = numberFormat(totalpremi,2);
						}
					}
						
					if(row==0){						
						document.getElementById('luas').value = numberFormat(luasblok,2);
						document.getElementById('satuan').value = satkegiatan;
						document.getElementById('basis').value = numberFormat(basis);
						document.getElementById('rpsat').value = numberFormat(rpsat,2);
					} else {
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

function getumr(baris){
	row=document.getElementById('jlhbrs').value;
	tgl=document.getElementById('tgl').value;
	if(row==0){
		karyawanid=document.getElementById('karyawanid').value;
		jhk=document.getElementById('jhk').value;
	} else {		
		karyawanid=document.getElementById('karyawanid'+baris).value;
		jhk=document.getElementById('jhk'+baris).value;
	}
	if(jhk>1){
		alert('Jumlah HK maksimal dalam sehari = 1 HK'); 
		if(row==0){
			document.getElementById('jhk').value='';
			document.getElementById('upah').value='';
		} else {		
			document.getElementById('jhk'+baris).value='';
			document.getElementById('upah'+baris).value='';
		}
		return;
	}
	
    param='method=getumr'+'&karyawanid='+karyawanid+'&tgl='+tgl;
    tujuan='kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
					umr = trim(con.responseText);
					jlhrp = parseFloat(trim(umr))*parseFloat(jhk);
					if(isNaN(jlhrp)==true){
						jlhrp=0;
					}
					
					if(umr==0){
						alert('Gaji Pokok Karyawan belum ada.'); 
						if(row==0){	
							document.getElementById('upah').value='';
							document.getElementById('jhk').value='';
						} else {
							document.getElementById('upah'+baris).value='';
							document.getElementById('jhk'+baris).value='';
						}
						return;
					} else{
						if(row==0){	
							document.getElementById('upah').value=numberFormat(jlhrp,2);
						} else {
							document.getElementById('upah'+baris).value=numberFormat(jlhrp,2);
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
    filterdivisi=document.getElementById('filterdivisi').value; 
    mandor=document.getElementById('mandor').value; 
    kodeorg=document.getElementById('kodeorg').value; 
	tgl=document.getElementById('tgl').value;
	showpermandor = document.getElementById('showpermandor');   
	if(showpermandor.checked==true){
		method='getdatamandor';
		document.getElementById('copy').style.display = '';
	}else{
		method='inputdetail';
		document.getElementById('copy').style.display = 'none';
	}
	
    param='method='+method+'&filterdivisi='+filterdivisi+'&mandor='+mandor+'&tgl='+tgl+'&kodeorg='+kodeorg;
    tujuan='kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
					isdtmdr = con.responseText.split("######");
                    document.getElementById('inputdetail').innerHTML = isdtmdr[0];
					row = trim(isdtmdr[1]);
					getdata(row);
					
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }  	
}


function getdata(row){
	row=document.getElementById('jlhbrs').value;
    filterdivisi=document.getElementById('filterdivisi').value; 
	tgl=document.getElementById('tgl').value;
	stsawal=document.getElementById('stsawal').value;
	kodeorg=document.getElementById('kodeorg').value;
	
    param='method=getdata'+'&filterdivisi='+filterdivisi+'&tgl='+tgl+'&stsawal='+stsawal+'&kodeorg='+kodeorg;
    tujuan='kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
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

function getnotransaksi(){
	kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tgl=document.getElementById('tgl').value;
	document.getElementById('notransaksi').value='';
	param='tgl='+tgl+'&kodeorg='+kodeorg+'&method=getnotransaksi';
	
	tujuan='kebun_slave_operasionalx.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
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
    kodeorg= document.getElementById('kodeorg').value;
    mandor= document.getElementById('mandor').value;
    mandor1= document.getElementById('mandor1').value;
    asst= document.getElementById('asst').value;
    kerani= document.getElementById('kerani').value;
    nobkm=document.getElementById('nobkm').value;
    tgl=document.getElementById('tgl').value;
    stsawal=document.getElementById('stsawal').value;
    mode=document.getElementById('mode').value;
    
	if(tgl==''||kodeorg==''){
        alert('Tanggal dan atau Kode Organisasi harus di isi !');
        return;
    }
	if(mode=='baru'){
		document.getElementById('tomboldetail').disabled = true;
	}else{
		document.getElementById('tomboldetail').disabled = false;
	}
    param = 'method=simpanheader';
    param += '&tgl=' + tgl+'&kodeorg=' + kodeorg+'&nobkm=' + nobkm+'&mandor=' + mandor+'&mandor1=' + mandor1+'&asst=' + asst+'&kerani=' + kerani+'&stsawal='+stsawal+'&mode='+mode+'&notransaksi='+notransaksi;
    tujuan = 'kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
					if(mode=='baru'){
						document.getElementById('notransaksi').value = trim(con.responseText);
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
    kodeorg= document.getElementById('kodeorg').value;
    mandor= document.getElementById('mandor').value;
    mandor1= document.getElementById('mandor1').value;
    asst= document.getElementById('asst').value;
    kerani= document.getElementById('kerani').value;
    nobkm=document.getElementById('nobkm').value;
    tgl=document.getElementById('tgl').value;
    notransaksi=document.getElementById('notransaksi').value;
    stsawal=document.getElementById('stsawal').value;
    mode=document.getElementById('mode').value;
    
	if(tgl==''||kodeorg==''){
        alert('Tanggal dan atau Kode Organisasi harus di isi !');
        return;
    }
						
    param = 'method=detail';
    param += '&tgl=' + tgl+'&kodeorg=' + kodeorg+'&nobkm=' + nobkm+'&mandor=' + mandor+'&mandor1=' + mandor1+'&asst=' + asst+'&kerani=' + kerani+'&notransaksi='+notransaksi+'&stsawal='+stsawal+'&mode='+mode;
    tujuan = 'kebun_slave_operasionalx.php';
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
    kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	filterdivisi= document.getElementById('filterdivisi').options[document.getElementById('filterdivisi').selectedIndex].value;
	showpermandor = document.getElementById('showpermandor');   
	if(showpermandor.checked==true){
		showpermandor=1;
	}else{
		showpermandor=0;
	}
	tgl=document.getElementById('tgl').value;
    notransaksi=document.getElementById('notransaksi').value;
    
  
    param = 'method=inputdetail';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi+'&filterdivisi=' + filterdivisi+'&showpermandor=' + showpermandor;
    tujuan = 'kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('inputdetail').innerHTML = con.responseText;
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
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=inputdetailmaterial';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
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
	notransaksi=document.getElementById('notransaksi').value;
	
	kegiatan=document.getElementById('kegiatanmat'+currRow).innerHTML;
	blok=document.getElementById('blokmat'+currRow).innerHTML;
	kodegudang=document.getElementById('kodegudang'+currRow).innerHTML;
	kodebarang=document.getElementById('kodemat'+currRow).value;
	qtymat=document.getElementById('qtymat'+currRow).value;
	prestasi=document.getElementById('pres'+currRow).innerHTML;

	param = 'method=insertmaterial';
	param += '&notransaksi='+notransaksi;
	param += '&kegiatan='+kegiatan;
	param += '&blok='+blok;
	param += '&kodebarang='+kodebarang;
	param += '&qtymat='+qtymat;
	param += '&kodegudang='+kodegudang;
	param += '&prestasi='+prestasi;
	
	tujuan='kebun_slave_operasionalx.php';
	post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
					document.getElementById('rowmat_' + currRow).style.backgroundColor = 'red';
                } else {
					document.getElementById('rowmat_' + currRow).style.backgroundColor='cyan';
					loaddatadetailmaterial(notransaksi);
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
}

function delmaterial(notransaksi,kegiatan,blok,kodebarang){

	param = 'method=delmaterial';
	param += '&notransaksi='+notransaksi;
	param += '&kegiatan='+kegiatan;
	param += '&blok='+blok;
	param += '&kodebarang='+kodebarang;
	
	tujuan='kebun_slave_operasionalx.php';
	post_response_text(tujuan, param, respog);
    
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert('Delete');
					loaddatadetailmaterial(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadetailmaterial(notransaksi){
	tgl=document.getElementById('tgl').value;
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    param = 'method=loaddatadetailmaterial';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('loaddatadetailmaterial').innerHTML = con.responseText;
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
	if(kdgdg==''){alert("Kode Gudang belum ada, silahkan tambah melalui menu Kebun - Setup - Gudang Divisi !!!"); return;}
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
	param = 'txtcari='+txtcari+'&method=caribarang&kodegudang='+kodegudang+'&kegiatan='+kegiatan;
	tujuan = 'kebun_slave_operasionalx.php';
	post_response_text(tujuan, param, respog);
			
	function respog(){
		if (con.readyState == 4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
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


function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel();  
}

function del(notransaksi,numrow){
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
	
    param='method=delete'+'&notransaksi='+notransaksi;
    tujuan='kebun_slave_operasionalx.php';
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
    document.getElementById('divsch').value='';
    document.getElementById('postingsrc').value='';
    document.getElementById('periodesch').value='';
    document.getElementById('mandorsrc').value='';
    document.getElementById('nobkmsch').value='';
    document.getElementById('mode').value='baru';
	
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
	
	document.getElementById('header_trans').style.display='block';
    document.getElementById('judul_header').style.display='block';
    //document.getElementById('hidebtn').style.display='block';
    //document.getElementById('unhidebtn').style.display='none';
    loaddata(0);
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page){
    notransaksisch=document.getElementById('notransaksisch').value;
    tglmulai=document.getElementById('tglmulai').value;
    tglselesai=document.getElementById('tglselesai').value;
    divsch=document.getElementById('divsch').value;
    postingsrc=document.getElementById('postingsrc').value;
    periodesch=document.getElementById('periodesch').value;
    nobkmsch=document.getElementById('nobkmsch').value;
    mandorsrc=document.getElementById('mandorsrc').value;
	stsawal=document.getElementById('stsawal').value;
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
	
 
    tujuan = 'kebun_slave_operasionalx.php';
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
    document.getElementById('notransaksi').value='';
    document.getElementById('nobkm').value='';
    document.getElementById('mandor').value='';
    document.getElementById('mandor1').value='';
    document.getElementById('kerani').value='';
    document.getElementById('asst').value='';
    document.getElementById('mode').value='baru';
}

function loaddatadetail(notransaksi){
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('tgl').disabled=true;
    tgl=document.getElementById('tgl').value;
    kodeorg=document.getElementById('kodeorg').value;
    notransaksi=document.getElementById('notransaksi').value;
    
    param = 'method=loaddatadetail';
    param += '&kodeorg=' + kodeorg+'&tgl=' + tgl+'&notransaksi=' + notransaksi;
    tujuan = 'kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
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
    tujuan = 'kebun_slave_operasionalx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
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
		alert('Lengkapi unit dan periode.');
		return;
	}
    judul='Report Ms.Excel';	
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);	
}
