// JavaScript Document

function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) + 'px';
	document.getElementById('dynamic2').style.display = '';
}



function showupload(ev,no) {
	showformupload(ev);
	nopp = document.getElementById('detail_kode'+no).innerHTML;
	param = 'proses=showupload&rnopp=' + nopp;
	tujuan = 'pmn_slave_hargapasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//alert(con.responseText);
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfiles(nopp) {
	param = 'proses=loadfiles&rnopp=' + nopp;
	tujuan = 'pmn_slave_hargapasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfilestop') !== null) {
						document.getElementById('listfilestop').innerHTML = con.responseText;
					}
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('listfilesview') !== null) {
						document.getElementById('listfilesview').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var nopp = document.getElementById("noppupload").innerHTML;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("rnopp", nopp);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_slave_hargapasar.php?proses=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function deletefile(nopp, namafile) {
	param = 'proses=deletefile&rnopp=' + nopp + '&namafile=' + namafile;
	tujuan = 'pmn_slave_hargapasar.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(nopp);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function downloadfile(path, filename) {
	param = 'path=' + path + '&filename=' + filename;
	tujuan = 'download.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}




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
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                        loadData();
                        cancelIsi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);

}
function getpasar(komoditi){	
        param='komoditi='+komoditi+'&proses=getPasar';
		
        tujuan='pmn_slave_hargapasar.php';
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
                                                        document.getElementById('idPasar').innerHTML=con.responseText;
                                                        
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
        param='proses=loadData';
        tujuan='pmn_slave_hargapasar';
        post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
	   document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}
function cariBast(num)
{
        param='proses=loadData';
        param+='&page='+num;
         tujuan='pmn_slave_hargapasar.php';
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
function cariTransaksi()
{
        tgl=document.getElementById('tglCri').value;
        kdbrg=document.getElementById('kdBrgCari').options[document.getElementById('kdBrgCari').selectedIndex].value;
        ipsd=document.getElementById('idPsrCari').options[document.getElementById('idPsrCari').selectedIndex].value;
        param='proses=cariData'+'&idPasar='+ipsd+'&kdBrgCari='+kdbrg+'&tglHarga='+tgl;
        tujuan='pmn_slave_hargapasar';
        post_response_text(tujuan+'.php', param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
	  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
}
function cariTrans(num)
{
            tgl=document.getElementById('tglCri').value;
            kdbrg=document.getElementById('kdBrgCari').options[document.getElementById('kdBrgCari').selectedIndex].value;
            ipsd=document.getElementById('idPsrCari').options[document.getElementById('idPsrCari').selectedIndex].value;
            param='proses=cariData'+'&idPasar='+ipsd+'&kdBrgCari='+kdbrg+'&tglHarga='+tgl;
            param+='&page='+num;
             tujuan='pmn_slave_hargapasar.php';
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
function fillField(tgl,kdbrg,sat,psdr,idmtuang,hrga,status,ffa,mni,keterangan)
{
	document.getElementById('tglHarga').value=tgl;
        l=document.getElementById('kdBarang');

        for(a=0;a<l.length;a++)
        {
        if(l.options[a].value==kdbrg)
            {
                l.options[a].selected=true;
            }
        }
	//document.getElementById('kdBarang').value='';
	document.getElementById('satuan').value=sat;
	document.getElementById('idPasar').value='';
        
        
        dl=document.getElementById('idPasar');
        for(a=0;a<dl.length;a++)
        {
        if(dl.options[a].value==psdr)
            {
                dl.options[a].selected=true;
            }
        }
        
	document.getElementById('idMatauang').value=idmtuang;
        document.getElementById('hrgPasar').value=hrga;
        
        q=document.getElementById('status');
        for(a=0;a<q.length;a++)
        {
        if(q.options[a].value==status)
            {
                q.options[a].selected=true;
            }
        }
        
         document.getElementById('ffa').value=ffa;
         document.getElementById('mni').value=mni;
        
        document.getElementById('proses').value="update";
	document.getElementById('tglHarga').disabled=true;
	document.getElementById('kdBarang').disabled=true;
        document.getElementById('idPasar').disabled=true;
        document.getElementById('keterangan').value=keterangan;
	
}
function cancelIsi()
{
    //$arr="##tglHarga##kdBarang##satuan##idPasar##idMatauang##hrgPasar##proses";
	document.getElementById('tglHarga').value='';
	document.getElementById('kdBarang').value='';
	document.getElementById('satuan').value='';
	document.getElementById('idPasar').value='';
	document.getElementById('idMatauang').value='';
        document.getElementById('keterangan').value='';
        document.getElementById('hrgPasar').value='';
         document.getElementById('ffa').value='';
         document.getElementById('mni').value='';
        document.getElementById('status').value='Best Bidder';
        document.getElementById('proses').value="insert";
	document.getElementById('tglHarga').disabled=false;
	document.getElementById('kdBarang').disabled=false;
        document.getElementById('idPasar').disabled=false;
}
function delData(tgl,kdbrg,psdr)
{
	param='proses=delData'+'&kdBarang='+kdbrg+'&tglHarga='+tgl+'&idPasar='+psdr;
	tujuan='pmn_slave_hargapasar';
	if(confirm("Delete, are you sure?"))
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
                            loadData();
                            cancelIsi();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getSatuan()
{
    kdBar=document.getElementById('kdBarang').options[document.getElementById('kdBarang').selectedIndex].value;
    param='proses=getSatuan'+'&kdBarang='+kdBar;
    tujuan='pmn_slave_hargapasar';
    post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var res = document.getElementById('satuan');
                    res.value = con.responseText;
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}