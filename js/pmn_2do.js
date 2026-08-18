// JavaScript Document
function zPdfDetail(fileTarget,passParam,idCont) {
    var cont = document.getElementById(idCont);
    var passP = passParam.split('##');
	
    var param = "proses=detailpdf";
	//alert(param);
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        param += "&"+passP[i]+"="+getValue(passP[i]);
    }
	//alert(param);
	document.getElementById('cetakdHtml').style.display='none';
	document.getElementById('cetakdPdf').style.display='block';
    cont.innerHTML = "<iframe frameborder=0 style='width:100%;height:350;' src='"+fileTarget+".php?"+param+"'></iframe>";
}


function printFileData(param,tujuan,title,ev)
{
  //  alert(param);
   tujuan=tujuan+"?"+param;  
   width='200';
   height='150';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev); 	
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='800';
   height='550';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function zBack()
{
	document.getElementById('cetakdHtml').style.display='block';
	document.getElementById('cetakdPdf').style.display='none';
}
function detailExcel(param,tujuan,title,ev)
{
    dataParam='notrans='+param+'&proses=getExcel';
    //alert(dataParam);
    printFileData(dataParam,tujuan,title,ev);
}
 
function zPreview2(fileTarget,passParam,idCont) {
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
  // alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var res = document.getElementById(idCont);
                    res.innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php?proses=preview2', param, respon);

}
function zExcel2(ev,tujuan,passParam)
{
	judul='Report Excel';
	//alert(param);
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
	param+='&proses=excel2';
	//alert(param);
	printFile(param,tujuan,judul,ev)
} 
function zPreview3(fileTarget,passParam,idCont) {
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
  // alert(param);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    var res = document.getElementById(idCont);
                    res.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    //
  //  alert(fileTarget+'.php?proses=preview', param, respon);
    post_response_text(fileTarget+'.php?proses=preview3', param, respon);

}
function zExcel3(ev,tujuan,passParam)
{
	judul='Report Excel';
	//alert(param);
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
	param+='&proses=excel3';
	//alert(param);
	printFile(param,tujuan,judul,ev)
}

function pdf(nodo) {

    tujuan = 'pmn_slave_suratperintahpengiriman.php';
    param = 'proses=pdf' + '&nodo=' + nodo;
    tujuan = tujuan + '?' + param;
    // alert(param);
    // alert(tujuan);
    //content = document.getElementById('test');
    content = "<iframe frameborder=0 style='width:100%;height:99%' src='" + tujuan + "'></iframe>";
    width = '820';
    height = '500';
    title = "";
    // showDialog5(title, content, width, height, 'event');
    alertify.popuppdf("PDF", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_slave_suratperintahpengiriman.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('80%', '70%');
}


function pdf1(nokontrak) {
	param = "proses=pdf1&notrans=" + nokontrak;
	alertify.popuppdf("title", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_slave_2do.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('90%', '80%');
}

function pdf2(nokontrak,no) {
	param = "proses=pdf2&notrans=" + nokontrak + "&ceklist="+document.getElementById('ceklist'+no).checked;
	alertify.popuppdf("title", "<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_slave_2do.php?" + param + "'></iframe>").set({ 'resizable': true, 'overflow': false }).resizeTo('90%', '80%');
}