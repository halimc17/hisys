

function edit(notransaksix)
{
    
    var container = document.getElementById('container');
    var listtable = document.getElementById('listtable');
    var tables = document.getElementById('tables');

    var param='notransaksi='+notransaksix;
    
    //alert(param);
    post_response_text('lgl_slave_progressSpk.php?proses=checkdata',param,respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if(con.responseText=='0')
                    {
                        viewListData(notransaksix);
                    }
                    else
                    {
                        viewEditListData(notransaksix);
                    }
                    container.style.display='none';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
   
}

function deletehd(num)
{
    var notransaksi = document.getElementById('notransaksi_'+num).innerHTML;
    var unit = document.getElementById('unit_'+num).innerHTML;

    var param='notransaksi='+notransaksi+'&unit='+unit;
   
    post_response_text('lgl_slave_progressSpk.php?proses=deletehd',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function addplusform(num)
{
    
    var container = document.getElementById('container');
    var forms = document.getElementById('forms');
    var listtable = document.getElementById('listtable');
    var tables = document.getElementById('tables');

    var notransaksi = document.getElementById('notransaksi_'+num).innerHTML;
    var unit = document.getElementById('unit_'+num).innerHTML;

    var param='notransaksi='+notransaksi+'&unit='+unit;
   
    post_response_text('lgl_slave_progressSpk.php?proses=addplusform',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    forms.style.display="none";
                    listtable.style.display="block";
                    tables.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function html(notransaksi) {
    width = '';
    height = '';
    content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
    param = 'notransaksi=' + notransaksi;
    tujuan = 'lgl_slave_progressSpk.php?proses=html';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contviewx').innerHTML = con.responseText;
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadData()
{
    
    var container = document.getElementById('container');
    var param;
   
    post_response_text('lgl_slave_progressSpk.php?proses=loadData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    if(document.getElementById('listtable'))
                        {
                            document.getElementById('listtable').style.display='none';
                        }
                    container.style.display="block";
                    container.innerHTML=con.responseText;
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
    var param ='page='+num;
    //alert(param);
    post_response_text('lgl_slave_progressSpk.php?proses=loadData',param,respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('container').innerHTML=con.responseText;
                    document.getElementById('pages').value=num;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }
    }   
}

function caridata()
{
   
    var container = document.getElementById('container');

    var unit = document.getElementById('unitcr').value;
    var tanggal = document.getElementById('tanggalcr').value;

    var param = 'unit='+unit+'&tanggal='+tanggal;
   
    post_response_text('lgl_slave_progressSpk.php?proses=loadCariData',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    container.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadDataDetail()
{
    var tables = document.getElementById('tables');
    var param;
   
    post_response_text('lgl_slave_progressSpk.php?proses=loadDataDetail',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {


                    tables.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewListData(notransaksi)
{
    var listtable = document.getElementById('listtable');
    var tables = document.getElementById('tables');

    var param='notransaksi='+notransaksi;
    post_response_text('lgl_slave_progressSpk.php?proses=loadData2',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    listtable.style.display='';
                    tables.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}

function viewEditListData(notransaksi)
{
    var listtable = document.getElementById('listtable');
    var tables = document.getElementById('tables');

    var param='notransaksi='+notransaksi;
    post_response_text('lgl_slave_progressSpk.php?proses=loadData3',param,respon);

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    listtable.style.display='';
                    tables.innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}
function saveDataDetail()
{
    var listtable = document.getElementById('listtable');

    var notransaksi = document.getElementById('notransaksi_0').innerHTML;
    var vpo = document.getElementById('vpo').value;
    var PMC = document.getElementById('PMC').value;
    var acc = document.getElementById('acc').value;
    var fin = document.getElementById('fin').value;
    var dri = document.getElementById('dri').value;
    var tpengiriman = document.getElementById('tpengiriman').value;
    var tpenerimaan = document.getElementById('tpenerimaan').value;
    var tglTTDSPK = document.getElementById('tglTTDSPK').value;
    var pengirimanSPK = document.getElementById('pengirimanSPK').value;
    var keterangan = document.getElementById('keterangan').value;


    
    var param='notransaksi='+notransaksi;
    param+='&tpVPO='+vpo+'&tpPOMILLCIVIL='+PMC+'&tpAccounting='+acc+'&tpFinance='+fin+'&tpDireksi='+dri;
    param+='&tPengiriman='+tpengiriman+'&tPenerimaan='+tpenerimaan+'&tglTTDSPK='+tglTTDSPK+'&pengirimanSPK='+pengirimanSPK+'&keterangan='+keterangan;
    //alert(param);
    post_response_text('lgl_slave_progressSpk.php?proses=savedata',param,respon);
    

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    listtable.style.display='none';
                    //alert(con.responseText);
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}

function updateData()
{
    var listtable = document.getElementById('listtable');

    var notransaksi = document.getElementById('notransaksi_0').innerHTML;
    var vpo = document.getElementById('vpo').value;
    var PMC = document.getElementById('PMC').value;
    var acc = document.getElementById('acc').value;
    var fin = document.getElementById('fin').value;
    var dri = document.getElementById('dri').value;
    var tpengiriman = document.getElementById('tpengiriman').value;
    var tpenerimaan = document.getElementById('tpenerimaan').value;
    var tglTTDSPK = document.getElementById('tglTTDSPK').value;
    var pengirimanSPK = document.getElementById('pengirimanSPK').value;
    var keterangan = document.getElementById('keterangan').value;


    
    var param='notransaksi='+notransaksi;
    param+='&tpVPO='+vpo+'&tpPOMILLCIVIL='+PMC+'&tpAccounting='+acc+'&tpFinance='+fin+'&tpDireksi='+dri;
    param+='&tPengiriman='+tpengiriman+'&tPenerimaan='+tpenerimaan+'&tglTTDSPK='+tglTTDSPK+'&pengirimanSPK='+pengirimanSPK+'&keterangan='+keterangan;
    //alert(param);
    post_response_text('lgl_slave_progressSpk.php?proses=updateData',param,respon);
    

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {

                    listtable.style.display='none';
                    //alert(con.responseText);
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }


}

function cancel()
{
    
    loadData();

}

function Batal()
{
    var unit = document.getElementById('unit');
    var tanggal = document.getElementById('tanggal');

    unit.value='';
    tanggal.value='';
}

function dataKeExcel(ev,tujuan,notransaksi){
    judul='Report Ms.Excel';    
    param='notransaksi='+notransaksi+'&proses=excel';
    printFile(param,tujuan,judul,ev)    
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>";
   showDialog1(title,content,width,height,ev);  
}