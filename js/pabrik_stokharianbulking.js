// JavaScript Document
function displayFormInput(){
        cancel();
		document.getElementById('formInput').style.display='block';
		document.getElementById('listData').style.display='none';
}


function cancel()
{
	document.getElementById('notransaksi').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('kodept').value='';
	document.getElementById('kodeunit').value='';
	document.getElementById('kodetangki').value='';
	document.getElementById('kuantitas').value='';
	document.getElementById('ffa').value='';
	document.getElementById('moisture').value='';
	document.getElementById('dirt').value='';
	document.getElementById('keterangan').value='';
	
}

function cancelsch()
{
	document.getElementById('notransaksisch').value='';
	document.getElementById('tanggalsch').value='';
	document.getElementById('kodeptsch').value='';
	document.getElementById('kodetangkisch').value='';
}

function loaddata(pg){
	notransaksisch=document.getElementById('notransaksisch').value;
	tanggalsch=document.getElementById('tanggalsch').value;
	kodeptsch=document.getElementById('kodeptsch').value;
	kodetangkisch=document.getElementById('kodetangkisch').value;
	
	param='proses=LoadData&page='+pg+'&notransaksisch='+notransaksisch+'&tanggalsch='+tanggalsch+'&kodeptsch='+kodeptsch+'&kodetangkisch='+kodetangkisch;
	tujuan='pabrik_slave_stokharianbulking.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('formInput').style.display='none';
                    document.getElementById('listData').style.display='block';
					document.getElementById('container').innerHTML=con.responseText;
					
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}



function save()
{
	notransaksi=document.getElementById('notransaksi').value;
	tanggal=document.getElementById('tanggal').value;
	kodept=document.getElementById('kodept').value;
	kodeunit=document.getElementById('kodeunit').value;
	kodetangki=document.getElementById('kodetangki').value;
	kuantitas=document.getElementById('kuantitas').value;
	ffa=document.getElementById('ffa').value;
	moisture=document.getElementById('moisture').value;
	dirt=document.getElementById('dirt').value;
	keterangan=document.getElementById('keterangan').value;
	pros=document.getElementById('proses').value;
	
	kuantitas=remove_comma_var(kuantitas);
	
	param='notransaksi='+notransaksi+'&tanggal='+tanggal+'&kodept='+kodept+'&kodeunit='+kodeunit+'&kodetangki='+kodetangki+'&kuantitas='+kuantitas+'&ffa='+ffa+'&moisture='+moisture+'&dirt='+dirt+'&keterangan='+keterangan+'&proses='+pros;
	//alert(param);
	tujuan='pabrik_slave_stokharianbulking.php';
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
							loaddata();
							cancel();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
}
function fillField(notransaksi)
{
	
	document.getElementById('proses').value='update';
	param='notransaksi='+notransaksi+'&proses=showData';
	//alert(param);
	tujuan='pabrik_slave_stokharianbulking.php';
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
							
	
							ar=con.responseText.split("###");
							//alert(ar[0]+ar[1]+ar[2]);
							document.getElementById('notransaksi').value=ar[0];
							document.getElementById('tanggal').value=ar[1];
							document.getElementById('kodept').value=ar[2];							
							document.getElementById('kodeunit').value=ar[3];
							document.getElementById('kodetangki').value=ar[4];
							document.getElementById('kuantitas').value=ar[5];
							document.getElementById('ffa').value=ar[6];
							document.getElementById('moisture').value=ar[7];
							document.getElementById('dirt').value=ar[8];
							document.getElementById('keterangan').value=ar[9];

							getunit(ar[3]),ar[4];


							document.getElementById('formInput').style.display='block';
                   			document.getElementById('listData').style.display='none';
							//loadData(paged);

							
							
						}
						
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	
}
function deldata(notransaksi)
{

	param='notransaksi='+notransaksi+'&proses=delData';
	//alert(param);
	tujuan='pabrik_slave_stokharianbulking.php';
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
						//	alert(con.responseText);
							loaddata();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	 if(confirm("Are You Sure Want Delete This Data"))
	 	post_response_text(tujuan, param, respog);
}

function cari(){
	kontrakcari=document.getElementById('kontrakcari').value;
	daTtgl=document.getElementById('tgl_cari').value;
	param='kontrakcari='+kontrakcari+'&daTtgl='+daTtgl+'&proses=cariData';
	//alert(param);
	tujuan='log_slave_kontrakpayung.php';
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
						//	alert(con.responseText);
							document.getElementById('contain').innerHTML=con.responseText;


						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	
}

function getunit(unit,tangki)
{
       kodept=document.getElementById('kodept').value;
       param='kodept='+kodept+'&kodeunit='+unit;
      
       tujuan='pabrik_slave_stokharianbulking.php';
        post_response_text(tujuan+'?proses=getunit', param, respog);
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

                        document.getElementById('kodeunit').innerHTML=con.responseText;
                       gettangki(tangki);
                       
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
          } 
     }  
}

function gettangki(tangki)
{
       kodeunit=document.getElementById('kodeunit').value;
       param='kodeunit='+kodeunit+'&kodetangki='+tangki;
      
       tujuan='pabrik_slave_stokharianbulking.php';
        post_response_text(tujuan+'?proses=gettangki', param, respog);
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

                        document.getElementById('kodetangki').innerHTML=con.responseText;
                       
                       
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
          } 
     }  
}

function submitfile() {
	var kontrak = document.getElementById("kontrak").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("kontrak", kontrak);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_kontrakpayung.php?proses=submitfile", true);
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
					loadfiles(kontrak);
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loadfiles(kontrak) {
	param = 'proses=loadfiles&kontrak='+kontrak;
	tujuan = 'log_slave_kontrakpayung.php';
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

function deletefile(kontrak, namafile) {
	param = 'proses=deletefile&kontrak=' + kontrak + '&namafile=' + namafile;
	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(kontrak);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function selesai() {
	displayList();
}

function postingData(kontrak,pt) {
    param = 'proses=postingData&kontrak=' + kontrak + '&pt=' + pt;
   	tujuan = 'log_slave_kontrakpayung.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}