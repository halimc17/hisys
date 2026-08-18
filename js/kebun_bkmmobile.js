//1. tarik data dari tabel mobile
function prosestransferdata(notransMobile){
    param='method=tarikdata&notransaksi='+notransaksi;
	
	tujuan='kebun_slave_bkmmobile.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				}else {
                    try{
                        //all data header and detail yang akan di kirim 
                        data = JSON.parse(con.responseText);
                        //dan seterusnya
                        simpanheader(data.header,data.prestasi,data.mutu);



                    }catch(e){

                    }

                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function simpanheader(dataHeader,dataPrestasi){
	notransaksi= dataHeader.notransaksi;
    //dan seterusnya
	kodeorg    = document.getElementById('kodeorg').value;
	mandor     = document.getElementById('mandor').value;
	mandor1    = document.getElementById('mandor1').value;
	asst       = document.getElementById('asst').value;
	kerani     = document.getElementById('kerani').value;
	nobkm      = document.getElementById('nobkm').value;
	tgl        = document.getElementById('tgl').value;
	stsawal    = document.getElementById('stsawal').value;
	mode       = document.getElementById('mode').value;
    divisi = document.getElementById('divisi').value;
	
    param = 'method=simpanheader';
    param += '&tgl=' + tgl+'&kodeorg=' + kodeorg+'&nobkm=' + nobkm+'&mandor=' + mandor+'&mandor1=' + mandor1+'&asst=' + asst+'&kerani=' + kerani+'&stsawal='+stsawal+'&mode='+mode+'&notransaksi='+notransaksi;
	param += '&divisi=' + divisi;
    tujuan = 'kebun_slave_bkm.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else {
					savedetail(dataPrestasi);
                    

                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }

    function savedetail(dataPrestasi){
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
        mode        = document.getElementById('mode').value;
        divisi      = document.getElementById('divisi').value;
        filterdivisi= document.getElementById('filterdivisi').value;
        method      = "insert";
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
            
        } else {
            karyawanid=document.getElementById('karyawanid'+currRow).value;
            kegiatan  =document.getElementById('kegiatan'+currRow).value;
            blok      =document.getElementById('blok'+currRow).value;
            prestasi  =document.getElementById('prestasi'+currRow).value;
            jhk       =document.getElementById('jhk'+currRow).value;
            upah      =document.getElementById('upah'+currRow).value;
            premi     =document.getElementById('premi'+currRow).value;
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
}