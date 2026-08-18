function loadData()
{ 
        kdKebun=document.getElementById('kdKebun').value; 
        thnProd=document.getElementById('thnProd').value;
        kdBlok=document.getElementById('kdBlok').value;
	param='proses=loadData'+'&thnProd='+thnProd+'&kdKebun='+kdKebun+'&kdBlok='+kdBlok;
	tujuan='kebun_slave_5bjr';
       post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //var res = document.getElementById(idCont);
//                    res.innerHTML = con.responseText;
                      document.getElementById('container').innerHTML=con.responseText;
                      document.getElementById('kdKebun').disabled=true;
                      document.getElementById('kdBlok').disabled=false;
                      document.getElementById('kelaspohon').disabled=false;
                      document.getElementById('jmBjr').disabled=false;
                      document.getElementById('listThnProduksi').style.display='none';
                      document.getElementById('listDataBjr').style.display='block';
                                          
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function loadBlok()
{ 
        kdKebun=document.getElementById('kdKebun').value;
	param='proses=loadBlok&kdKebun='+kdKebun;
//        alert(param);
	tujuan='kebun_slave_5bjr';
       post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //var res = document.getElementById(idCont);
//                    res.innerHTML = con.responseText;
//alert(con.responseText);
                      document.getElementById('kdBlok').innerHTML=con.responseText;
//                      document.getElementById('kdBlok').disabled=false;
//                      document.getElementById('jmBjr').disabled=false;
//                      document.getElementById('listThnProduksi').style.display='none';
//                      document.getElementById('listDataBjr').style.display='block';
                                          
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function getPrd()
{ 
	bln=document.getElementById('bln').value;
	tahun=document.getElementById('tahun').value;
	param='proses=getPrd&bln='+bln+'&tahun='+tahun;
	tujuan='kebun_slave_5bjr';
	post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                } else {
					hasil=con.responseText.split("###");
					document.getElementById('periode1').innerHTML=hasil[0];
					document.getElementById('periode2').innerHTML=hasil[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function getBln()
{ 
	param='proses=getBln';
	tujuan='kebun_slave_5bjr';
	post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                } else {
					document.getElementById('bln').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}
function getThn()
{ 
	param='proses=getThn';
	tujuan='kebun_slave_5bjr';
	post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                } else {
					document.getElementById('tahun').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}

function cancelIsi()
{
    document.getElementById('kdKebun').disabled=false;
    document.getElementById('kdBlok').disabled=true;
    document.getElementById('kelaspohon').disabled=true;
    document.getElementById('jmBjr').disabled=true; 
    document.getElementById('container').innerHTML=isidata;
    document.getElementById('thnProd').value='';
    document.getElementById('listThnProduksi').style.display='block';
    document.getElementById('listDataBjr').style.display='none';
}
// JavaScript Document
function saveFranco(fileTarget,passParam) {

    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	//alert(param);
  //alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                        loadData();
                        document.getElementById('jmBjr').value='';
                        document.getElementById('kdBlok').disabled=false;
                        document.getElementById('kelaspohon').disabled=false;
                        document.getElementById('kdBlok').selectedIndex=0;
                        document.getElementById('kelaspohon').selectedIndex=0;
                        //cancelIsi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php', param, respon);

}

function cariBast2(num)
{
        kdKebun=document.getElementById('kdKebun').value; 
        thnProd=document.getElementById('thnProd').value;
        kdBlok=document.getElementById('kdBlok').value;
//		thnProd=document.getElementById('thnProd').value;
                param='proses=loadData'+'&thnProd='+thnProd+'&kdKebun='+kdKebun+'&kdBlok='+kdBlok;
		param+='&page='+num;
		tujuan = 'kebun_slave_5bjr.php';
		post_response_text(tujuan, param, respog);			
		function respog(){
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
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
function fillField(kdorg,kelaspohon,jmbjr)
{
    lLokasi=document.getElementById('kdBlok');
    for(ard=0;ard<lLokasi.length;ard++)
    {
        if(lLokasi.options[ard].value==kdorg)
            {
                lLokasi.options[ard].selected=true;
            }
    }
	
	lKlsPhn=document.getElementById('kelaspohon');
	for(ard2=0;ard2<lKlsPhn.length;ard2++)
    {
        if(lKlsPhn.options[ard2].value==kelaspohon)
            {
                lKlsPhn.options[ard2].selected=true;
            }
    }
	
    document.getElementById('jmBjr').value=jmbjr;
    document.getElementById('kdBlok').disabled=true;
    document.getElementById('proses').value="update";
					
}
function delData(thnproduksi,kdorg)
{
	param='proses=delData'+'&thnProd='+thnproduksi;
        param+='&kdBlok='+kdorg;
	tujuan='kebun_slave_5bjr';
	if(confirm("Anda yakin ingin menghapus"))
       {
		post_response_text(tujuan+'.php', param, respon);
	}
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    //var res = document.getElementById(idCont);
//                    res.innerHTML = con.responseText;
					  loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function enabletahun()
{
	document.getElementById('tahun').disabled=false;
}

maxf = 0
sekarang = 1;
function saveall(maxRow) 
{
	if(maxRow =='' || maxRow ==0)
    {
        alert('Data tidak ditemukan, proses dibatalkan !');
        return;
    }
	if(confirm("Proses ini akan me-replace data yg sudah ada, lanjutkan ?")){
		maxf = maxRow;
		loopsave(1, maxRow);
	}

}
function loopsave(currRow, maxRow)
{
    blok = trim(document.getElementById('blok'+ currRow).innerHTML);
    //jjg = trim(document.getElementById('jjg' + currRow).innerHTML);
    //kg = trim(document.getElementById('kg' + currRow).innerHTML);
    bjr = trim(document.getElementById('bjr' + currRow).value);
    prd = trim(document.getElementById('prd' + currRow).innerHTML);
    $bjr=remove_comma_var(bjr);
	
    param = 'blok=' + blok + '&bjr=' + bjr + '&prd=' + prd;
    param += "&proses=savedata";
	
	
    tujuan = 'kebun_slave_5bjrproses.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row' + currRow).style.backgroundColor = 'cyan';
    //lockScreen('wait');

    function respog() {
        if (con.readyState == 4) {

            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('row' + currRow).style.backgroundColor = 'red';
                    unlockScreen();
                }
                else {
                    document.getElementById('row' + currRow).style.display = 'none';
                    currRow += 1;
                    sekarang = currRow;
                    if (currRow > maxRow)
                    {
                        alert('Done');
                    }
                    else
                    {
                        loopsave(currRow, maxRow);
                    }
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}