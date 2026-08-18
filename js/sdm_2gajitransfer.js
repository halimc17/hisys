var fileTarget;
fileTarget='sdm_slave_2gajitransfer.php';

function getunit(){
    pt=document.getElementById('pt').value;
    param='pt='+pt;

    function respon(){
        if(con.readyState == 4){
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                      document.getElementById('unit').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'?method=getunit', param, respon);
}

function getkar(){
    pt=document.getElementById('pt').value;
    tipekar=document.getElementById('tipekar').value;
    param='pt='+pt+'&tipekar='+tipekar;

    function respon(){
        if(con.readyState == 4){
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    data = con.responseText.split("##");
                      document.getElementById('karyawan').innerHTML=data[0];
                      document.getElementById('sumberrek').innerHTML=data[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'?method=getkar', param, respon);
}
var groupdataRemove = new Array();
function loadLaporan(){
    
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    karyawan=document.getElementById('karyawan').value;
    tanggal=document.getElementById('tanggal').value;
    sumberrek=document.getElementById('sumberrek').value;
    tipekar=document.getElementById('tipekar').value;
  
    param = 'method=loadLaporan';
    param += '&pt=' + pt+'&unit=' + unit+'&periode=' + periode+'&karyawan=' + karyawan+'&tanggal=' + tanggal+'&sumberrek=' + sumberrek+'&tipekar=' + tipekar;
    tujuan = 'sdm_slave_2gajitransfer.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    document.getElementById('containerlist1').innerHTML = con.responseText;
					var content =  document.getElementById('containerlist1');
					var datarekening = content.getElementsByClassName('datarekening');
					//console.log(datarekening.length);
					if(datarekening.length > 0){
						for(i=0; i<datarekening.length; i++){
							//console.log(typeof datarekening[i]);
							//console.log(i);
							datarekening[i].onclick = function(event){
								if(event.shiftKey == true){
									if(groupdataRemove.length == 0){
										groupdataRemove.push(this.nextElementSibling.rowIndex);
									}else{
										groupdataRemove[1] = this.previousElementSibling.rowIndex;
										parentTable = this.parentNode.parentNode;
										theadEle = parentTable.getElementsByTagName("thead");
										trH = 0;
										if(theadEle.length > 0){
											thead = theadEle[0];
											trH = thead.getElementsByTagName("tr");
										}
										
										parentBody = this.parentNode;
										tr = parentBody.getElementsByTagName("tr");
										firstRow = (groupdataRemove[0]-trH.length);
										lastRow = (groupdataRemove[1]-trH.length);
										for(x=firstRow; x<=lastRow; x++){
											tr[x].classList.add("todelete");
										}
										groupdataRemove = new Array();
									}
									
								}else{
									groupdataRemove[0] = this.nextElementSibling.rowIndex;
								}
								
								this.classList.toggle("todelete");
								var removelistbtn =  document.getElementById('removelist');
								var exporttolistbtn =  document.getElementById('exporttolist');
								var exportCsvlistbtn =  document.getElementById('exportcsvlistbtn');
								removelistbtn.style.display ="inline-block";
								exporttolistbtn.style.display ="none";
								exportCsvlistbtn.style.display ="none";
							}
						}
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function exportCsvlist(id){
	var content =  document.getElementById(id);
	var todelete = content.getElementsByClassName('todelete');
	if(todelete.length > 0){
		for(i=0; i<=todelete.length; i++){
			todelete[i].classList.remove("todelete");
		}
		export_table_to_csv('laporan_Gaji_transfer',id);
	}else{
		export_table_to_csv('laporan_Gaji_transfer',id);
	}
}
function exportlist(id){
	var content =  document.getElementById(id);
	var todelete = content.getElementsByClassName('todelete');
	if(todelete.length > 0){
		for(i=0; i<=todelete.length; i++){
			todelete[i].classList.remove("todelete");
		}
		exportTabletoExcel('laporan_Gaji_transfer',id);
	}else{
		exportTabletoExcel('laporan_Gaji_transfer',id);
	}
}
function removelist(){
	var content =  document.getElementById('containerlist1');
	var todelete = content.getElementsByClassName('todelete');
	if(todelete.length > 0){
		todelete[0].remove();
		removelist();
	}else{
		var removelistbtn =  document.getElementById('removelist');
		var exporttolistbtn =  document.getElementById('exporttolist');
		var exportCsvlistbtn =  document.getElementById('exportcsvlistbtn');
		removelistbtn.style.display ="none";
		exporttolistbtn.style.display ="inline-block";
		exportCsvlistbtn.style.display ="inline-block";
	}
}
function excel(ev){
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
    periode=document.getElementById('periode').value;
    karyawan=document.getElementById('karyawan').value;
    tanggal=document.getElementById('tanggal').value;
    sumberrek=document.getElementById('sumberrek').value;
    param += '&pt=' + pt+'&unit=' + unit+'&periode=' + periode+'&karyawan=' + karyawan+'&tanggal=' + tanggal+'&sumberrek=' + sumberrek+'&tipe=excel';
    tujuan = 'sdm_slave_2gajitransfer.php';
    judul='Report Ms.Excel';        
    printFile(param,tujuan,judul,ev)    
}


