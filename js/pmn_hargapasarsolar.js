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
    tujuan = 'pmn_slave_hargapasarsolar.php';
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
    tujuan = 'pmn_slave_hargapasarsolar.php';
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
    con.open("POST", "pmn_slave_hargapasarsolar.php?proses=submitfile", true);
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
    tujuan = 'pmn_slave_hargapasarsolar.php';
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

/*function getpasar(komoditi){    
    param='komoditi='+komoditi+'&proses=getPasar';
    
    tujuan='pmn_slave_hargapasarsolar.php';
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
                    document.getElementById('supplier').innerHTML=con.responseText;  
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }    
}*/

function loadData()
{
    param='proses=loadData';
    tujuan='pmn_slave_hargapasarsolar';
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
     tujuan='pmn_slave_hargapasarsolar.php';
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
    unit=document.getElementById('unitCari').value;
    tgl=document.getElementById('tglCri').value;
    kdbrg=document.getElementById('kdBrgCari').options[document.getElementById('kdBrgCari').selectedIndex].value;
    ipsd=document.getElementById('supplierCari').options[document.getElementById('supplierCari').selectedIndex].value;
    param='proses=cariData'+'&supplier='+ipsd+'&kdBrgCari='+kdbrg+'&tglHarga='+tgl+'&unit='+unit;
    tujuan='pmn_slave_hargapasarsolar';
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
    unit=document.getElementById('unitCari').value;
    kdbrg=document.getElementById('kdBrgCari').options[document.getElementById('kdBrgCari').selectedIndex].value;
    ipsd=document.getElementById('supplierCari').options[document.getElementById('supplierCari').selectedIndex].value;
    param='proses=cariData'+'&supplier='+ipsd+'&kdBrgCari='+kdbrg+'&tglHarga='+tgl+'&unit='+unit;
    param+='&page='+num;
     tujuan='pmn_slave_hargapasarsolar.php';
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

function fillField(unit,tgl,kdbrg,sat,psdr,idmtuang,hrga,status)
{
    document.getElementById('unit').value=unit;
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
    document.getElementById('supplier').value='';

    dl=document.getElementById('supplier');
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
        
    document.getElementById('proses').value="update";
    document.getElementById('unit').disabled=true;
    document.getElementById('tglHarga').disabled=true;
    document.getElementById('kdBarang').disabled=true;
    document.getElementById('supplier').disabled=true; 
}

function cancelIsi()
{
    //$arr="##tglHarga##kdBarang##satuan##supplier##idMatauang##hrgPasar##proses";
    document.getElementById('unit').value='';
    document.getElementById('tglHarga').value='';
    document.getElementById('kdBarang').value='';
    document.getElementById('satuan').value='';
    document.getElementById('supplier').value='';
    document.getElementById('idMatauang').value='';
    document.getElementById('hrgPasar').value='';
    document.getElementById('status').value='Best Bidder';
    document.getElementById('proses').value="insert";
    document.getElementById('unit').disabled=false;
    document.getElementById('tglHarga').disabled=false;
    document.getElementById('kdBarang').disabled=false;
    document.getElementById('supplier').disabled=false;
}

function delData(unit,tgl,kdbrg,psdr)
{
    param='proses=delData'+'&unit='+unit+'&kdBarang='+kdbrg+'&tglHarga='+tgl+'&supplier='+psdr;
    tujuan='pmn_slave_hargapasarsolar';
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
    tujuan='pmn_slave_hargapasarsolar';
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