// JavaScript Document

function simpan()
{
    tanggal=trim(document.getElementById('tanggal').value);
    jam=document.getElementById('jam').options[document.getElementById('jam').selectedIndex].value;
    mnt=document.getElementById('mnt').options[document.getElementById('mnt').selectedIndex].value;
    kodetraksi=document.getElementById('kodetraksi').options[document.getElementById('kodetraksi').selectedIndex].value;
    kodealat=document.getElementById('kodealat').options[document.getElementById('kodealat').selectedIndex].value;
    operator=document.getElementById('operator').options[document.getElementById('operator').selectedIndex].value;
    posisihm=document.getElementById('posisihm').value;
    namapelapor=trim(document.getElementById('namapelapor').value);
    indikasikerusakan=trim(document.getElementById('indikasikerusakan').value);
    penyebabrusak=document.getElementById('penyebabrusak').options[document.getElementById('penyebabrusak').selectedIndex].value;
    noberitaacara=document.getElementById('noberitaacara').options[document.getElementById('noberitaacara').selectedIndex].value;
    hedept=document.getElementById('hedept').options[document.getElementById('hedept').selectedIndex].value;
    divmanager=document.getElementById('divmanager').options[document.getElementById('divmanager').selectedIndex].value;
    workshop=document.getElementById('workshop').options[document.getElementById('workshop').selectedIndex].value;
    method=document.getElementById('method').value;	
    notransaksi=document.getElementById('notransaksi').value;
    
    if(tanggal=='') { alert('Please fill TANGGAL'); return; }
	if(operator=='') { alert('Please fill OPERATOR'); return; }
    
    param='tanggal='+tanggal+'&jam='+jam+'&mnt='+mnt;
    param+='&kodetraksi='+kodetraksi+'&kodealat='+kodealat+'&operator='+operator+'&posisihm='+posisihm+'&namapelapor='+namapelapor;
    param+='&indikasikerusakan='+indikasikerusakan+'&penyebabrusak='+penyebabrusak+'&noberitaacara='+noberitaacara+'&hedept='+hedept;
    param+='&divmanager='+divmanager+'&workshop='+workshop+'&notransaksi='+notransaksi;
    param+='&method='+method;
    tujuan = 'vhc_slave_wo.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    alert('Done.');
                    loadData();
                    batal();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }	
}

function batal()
{
    var d = new Date();
    var curr_date = d.getDate();
    var curr_month = d.getMonth() + 1; //Months are zero based
    var curr_year = d.getFullYear();
    d1=curr_date + "-" + curr_month + "-" + curr_year;

    document.getElementById('tanggal').value='';
    document.getElementById('jam').selectedIndex=0;
    document.getElementById('mnt').selectedIndex=0;
    document.getElementById('kodetraksi').selectedIndex=0;
    document.getElementById('kodealat').options.length = 0;
    document.getElementById('operator').options.length = 0;
    document.getElementById('posisihm').value='0';
    document.getElementById('namapelapor').value='';
    document.getElementById('indikasikerusakan').value='';
    document.getElementById('penyebabrusak').selectedIndex=0;
    document.getElementById('noberitaacara').selectedIndex=0;
    document.getElementById('hedept').selectedIndex=0;
    document.getElementById('divmanager').selectedIndex=0;
    document.getElementById('workshop').selectedIndex=0;
    document.getElementById('method').value="insert";
}

function fillField(notransaksi,kodetraksi,tanggal,jam,mnt,kodealat,operator,posisihm,namapelapor,indikasikerusakan,
    penyebabrusak,noberitaacara,hedept,divmanager,workshop)
{
    document.getElementById('notransaksi').value=notransaksi;
    setValue('tanggal',tanggal);
    setValue('jam',jam);
    setValue('mnt',mnt);
    setValue('kodetraksi',kodetraksi);
    //document.getElementById('kodealat').value=kodealat;
    //document.getElementById('operator').value=operator;
    document.getElementById('posisihm').value=posisihm;
    document.getElementById('namapelapor').value=namapelapor;
    document.getElementById('indikasikerusakan').value=indikasikerusakan;
    setValue('penyebabrusak',penyebabrusak);
    setValue('noberitaacara',noberitaacara);
    setValue('hedept',hedept);
    setValue('divmanager',divmanager);
    setValue('workshop',workshop);
    document.getElementById('method').value="update";
	getAlat(kodealat,operator,noberitaacara);
	//cekBA(noberitaacara);
}

function del(notransaksi)
{
    document.getElementById('method').value='hapus';
    param='notransaksi='+notransaksi+'&method=delete';
    if(confirm('Delete/Hapus '+notransaksi+'?'))
    {
        tujuan='vhc_slave_wo.php';
        post_response_text(tujuan, param, respog);			
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    alert('Done.');
                    loadData();
                    batal();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }		
}

function loadData()
{
    perSch=document.getElementById('perSch').value;
    param='method=loadData'+'&perSch='+perSch;
    tujuan='vhc_slave_wo.php';
    post_response_text(tujuan, param, respog);	
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }		
}

function printPdf(notransaksi,event) {
    param = "method=printPdf&notransaksi="+notransaksi;
    
    showDialog1('Print PDF',"<iframe frameborder=0 width='100%' height='100%'"+
        " src='vhc_slave_wo.php?"+param+"'></iframe>",'800','400',event);
}
function getAlat(value,operator,noberitaacara)
{
    kodetraksi=document.getElementById('kodetraksi').options[document.getElementById('kodetraksi').selectedIndex].value;
    param='kodetraksi='+kodetraksi+'&method=getAlat';
    tujuan='vhc_slave_wo.php';
	getById('operator').options.length=1;
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('kodealat').innerHTML = con.responseText;
					if(typeof value!='undefined') {
						var kodealat = document.getElementById('kodealat'),
							index=-1;
						for(i in kodealat.options) {
							if(kodealat.options[i].value==value) {
								index = i;
							}
						}
						kodealat.selectedIndex = index;
						if(typeof noberitaacara=='undefined') noberitaacara='';
						getOperator(operator,noberitaacara);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getOperator(value,noberitaacara)
{
    kodealat=document.getElementById('kodealat').options[document.getElementById('kodealat').selectedIndex].value;
    param='kodealat='+kodealat+'&method=getOperator';
    tujuan='vhc_slave_wo.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('operator').innerHTML = con.responseText;
					if(typeof value!='undefined') {
						var operator = document.getElementById('operator'),
							index=-1;
						for(i in operator.options) {
							if(operator.options[i].value==value) {
								index = i;
							}
						}
						operator.selectedIndex = index;
						cekBalaka(noberitaacara);
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cekBA(value) {
	var sebab = document.getElementById('penyebabrusak');
	if(sebab.options[sebab.selectedIndex].value=='KECELAKAAN') {
		if(typeof value!='undefined') {
			getBA(value);
		} else {
			getBA();
		}
	} else {
		document.getElementById('noberitaacara').innerHTML = "<option value=''></option>";
	}
}

/**
 * Cek Dokumen Berita Acara Kecelakaan
 */
function cekBalaka(val) {
	var operator = getById('operator'),
		tanggal = getValue('tanggal');
	if(operator.options.length==0) return;
	if(typeof val=='undefined') val='';
	
	var kodetraksi = getValue('kodetraksi'),
		kodealat = getValue('kodealat'),
		operator = getValue('operator'),
		balaka = getById('noberitaacara'),
		param='tanggal='+tanggal+'&kodetraksi='+kodetraksi+'&kodealat='+kodealat+
			'&operator='+operator+'&balaka='+val+'&method=getBA',
		tujuan='vhc_slave_wo.php';
	// Empty Dokumen BA Laka
	balaka.options.length = 0;
	balaka.options[0] = new Option('','');
	
	if(tanggal!='' && operator!='') { // Cek Server jika ada tanggal dan operator
		post_response_text(tujuan, param, respon);
	}
	
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    document.getElementById('noberitaacara').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setSebab() {
	var balaka = getValue('noberitaacara');
	if(balaka=='') {
		setValue('penyebabrusak','UMUM');
	} else {
		setValue('penyebabrusak','KECELAKAAN');
	}
}