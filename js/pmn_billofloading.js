// JavaScript Document





// function posting(notransaksi,page){
	// content= "<div id=formpost  style=\"height:100%;width:800px;\"></div>";
	// title='Posting';
	// formposting(notransaksi,page);
// } 


function pdf(nobast) {
    param = "proses=pdf&nobast="+nobast;
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='pmn_slave_billofloading.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','80%');
}


function posting(nobast,page){
	proses = 'posting';
	param = '';
	param += '&proses=' + proses+'&nobast=' + nobast + '&page=' + page;
	tujuan = 'pmn_slave_billofloading.php';
	post_response_text(tujuan, param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Informasi',con.responseText);
                } else {
				   alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','50%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
} 
 
function saveposting(page) {
	
	nobast=document.getElementById('nobast').value;	
	tipe=document.getElementById('tipe').value;	
	param='';
	proses = 'saveposting';
	param += '&nobast=' + nobast + '&tipe=' + tipe+ '&page=' + page;
	param += '&proses=' + proses;
	tujuan = 'pmn_slave_billofloading.php';
	alertify.confirm("Informasi","Posting transaksi : "+nobast+" ???",
		function(){
			post_response_text(tujuan, param, respon);
		},
		function(){
			return;
		}
	);
	
	
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi',con.responseText);
				} else {
					alertify.popup().destroy();
					loadData(page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}  
} 
 
 



function viewlistfile(ev,bil) {
    param       = 'proses=viewlistfile&bil='+trim(bil);
	// alert(param);
	tujuan      = 'pmn_slave_billofloading.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					
					if (document.getElementById('listfiles') !== null) {
						// document.getElementById('listfiles').innerHTML = con.responseText;
						alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','80%'); 
					}
				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function add_new_data(){
	document.getElementById('headher').style.display='block';
	document.getElementById('listData').style.display='none';
	bersih();
}

function bersih()
{
	document.getElementById('pt').value='';
	document.getElementById('kontrak').value='';
	document.getElementById('tgl').value='';
	document.getElementById('tglbast').value='';
	document.getElementById('bil').value='';
	document.getElementById('kg').value='0';
	document.getElementById('cust').value='';
	document.getElementById('kota').value='';
	
	document.getElementById('ffa').value='0';
	document.getElementById('moisture').value='0';
	document.getElementById('dirt').value='0';
	document.getElementById('dobi').value='0';
	document.getElementById('broken').value='0';
	document.getElementById('impurities').value='0';
	document.getElementById('mdani').value='0';
	document.getElementById('rpkgclaim').value='0';
	
}

function loadData(pg){
	kontrakcari=document.getElementById('kontrakcari').value;
	bilcari=document.getElementById('bilcari').value;
	tglcari=document.getElementById('tglcari').value;
	tglbastcari=document.getElementById('tglbastcari').value;
	param='proses=LoadData&page='+pg+'&kontrakcari='+kontrakcari+'&tglcari='+tglcari+'&tglbastcari='+tglbastcari+'&bilcari='+bilcari;
	tujuan='pmn_slave_billofloading.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contain').innerHTML=con.responseText;
					document.getElementById('headher').style.display='none';
					document.getElementById('listData').style.display='block';
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
	loadData(paged);
}

function displayList(){
	document.getElementById('headher').style.display='none';
	document.getElementById('listData').style.display='block';
	document.getElementById('kontrakcari').value='';
	document.getElementById('tglcari').value='';
	document.getElementById('tglbastcari').value='';
	loadData(0);
}

function saveData()
{
	pt=document.getElementById('pt').value;
	kontrak=document.getElementById('kontrak').value;
	tgl=document.getElementById('tgl').value;
	cust=document.getElementById('cust').value;
	bil=document.getElementById('bil').value;
	kg=document.getElementById('kg').value;
	
	kg=remove_comma_var(kg);
	
	tglbast=document.getElementById('tglbast').value;
	kota=document.getElementById('kota').value;
	ffa=document.getElementById('ffa').value;
	moisture=document.getElementById('moisture').value;
	dirt=document.getElementById('dirt').value;
	dobi=document.getElementById('dobi').value;
	broken=document.getElementById('broken').value;
	impurities=document.getElementById('impurities').value;
	mdani=document.getElementById('mdani').value;
	rpkgclaim=document.getElementById('rpkgclaim').value;
	
	pros=document.getElementById('proses').value;
	
	param='pt='+pt+'&kontrak='+kontrak+'&tgl='+tgl+'&cust='+cust+'&bil='+bil+'&kg='+kg+'&proses='+pros;
	param+='&tglbast='+tglbast+'&kota='+kota;
	param+='&ffa='+ffa+'&moisture='+moisture+'&dirt='+dirt+'&dobi='+dobi+'&broken='+broken+'&impurities='+impurities+'&mdani='+mdani+'&rpkgclaim='+rpkgclaim;
	
	tujuan='pmn_slave_billofloading.php';
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
							document.getElementById('bil').value=bil;
							alert('Data Tersimpan');
							//alert(con.responseText);
							// displayList();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
}
function fillField(kontrak,bil,cust,pt)
{
	
	document.getElementById('proses').value='update';
	param='kontrak='+kontrak+'&bil='+bil+'&proses=showData';
	//alert(param);
	tujuan='pmn_slave_billofloading.php';
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
							document.getElementById('headher').style.display='block';
							document.getElementById('listData').style.display='none';
							ar=con.responseText.split("###");
							//alert(ar);
							//alert(ar[0]+ar[1]+ar[2]);
							document.getElementById('bil').value=bil;
							document.getElementById('bil').disabled=true;
							document.getElementById('kontrak').value=kontrak;
							document.getElementById('cust').value=cust;
							document.getElementById('tgl').value=ar[1];							
							document.getElementById('kg').value=ar[2];
							document.getElementById('pt').value=pt;
							document.getElementById('tglbast').value=ar[4];
							document.getElementById('kota').value=ar[5];
							document.getElementById('ffa').value=ar[6];
							document.getElementById('moisture').value=ar[7];
							document.getElementById('dirt').value=ar[8];
							document.getElementById('dobi').value=ar[9];
							document.getElementById('broken').value=ar[10];
							document.getElementById('impurities').value=ar[11];
							document.getElementById('mdani').value=ar[12];
							document.getElementById('rpkgclaim').value=ar[13];
							
							loadfiles(bil);
							
						}
						
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  
	
}
function deldata(kontrak,bil)
{
	

	param='kontrak='+kontrak+'&bil='+bil+'&proses=delData';
	//alert(param);
	tujuan='pmn_slave_billofloading.php';
	alertify.confirm("Informasi","Anda yakin ingin menghapus data ini "+ bil+" ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
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
							displayList();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }  

}

function cari(){
	kontrakcari=document.getElementById('kontrakcari').value;
	tglcari=document.getElementById('tglcari').value;
	tglbastcari=document.getElementById('tglbastcari').value;
	param='kontrakcari='+kontrakcari+'&tglcari='+tglcari+'&tglbastcari='+tglbastcari+'&proses=cariData';
	//alert(param);
	tujuan='pmn_slave_billofloading.php';
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

function getpt()
{
       kontrak=document.getElementById('kontrak').value;
       param='kontrak='+kontrak;
       tujuan='pmn_slave_billofloading.php';
        post_response_text(tujuan+'?proses=getpt', param, respog);
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
                	isdtmdr = con.responseText.split("##");
               
                	document.getElementById('pt').innerHTML = isdtmdr[0];
                	document.getElementById('cust').innerHTML = isdtmdr[1];
               

                    
                       
                       
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
          } 
     }  
}

function getnobl()
{
       kontrak=document.getElementById('kontrak').value;
       pt=document.getElementById('pt').value;
       tglbast=document.getElementById('tglbast').value;
       bil=document.getElementById('bil').value;
       param='kontrak='+kontrak+'&pt='+pt+'&tglbast='+tglbast+'&bil='+bil;
       tujuan='pmn_slave_billofloading.php';
        post_response_text(tujuan+'?proses=getnobl', param, respog);
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
             
               
                	document.getElementById('bil').value = con.responseText;
                	/*document.getElementById('cust').innerHTML = isdtmdr[1];
                	document.getElementById('bil').value = isdtmdr[2];*/

                    
                       
                       
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
	var bil = document.getElementById("bil").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("bil", bil);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alertify.alert("Informasi","warning : Upload file has been empty.");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "pmn_slave_billofloading.php?proses=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					alertify.alert("Informasi",'Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(bil);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



function deletefile(bil, namafile) {
	param = 'proses=deletefile&bil=' + bil + '&namafile=' + namafile;
	tujuan = 'pmn_slave_billofloading.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					loadfiles(bil);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadfiles() {
    bil = document.getElementById('bil').value;
    param       = 'proses=loadfiles&bil='+trim(bil);
	tujuan      = 'pmn_slave_billofloading.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					// loaddatadetail();
					// document.getElementById('listfiles').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}



// function submitfile() {
	// var kontrak = document.getElementById("kontrak").value;
	// var bil = document.getElementById("bil").value;
	// var kriteriaefil = document.getElementById("kriteriaefil").value;
	// var file = document.getElementById("upload").files[0];
	// var formdata = new FormData();
	// formdata.append("file", file);
	// formdata.append("fileupload", getValue('upload'));
	// formdata.append("kontrak", kontrak);
	// formdata.append("bil", bil);
	// formdata.append("kriteriaefil", kriteriaefil);
	// if (getValue('upload') == "") {
		// alert("warning : Upload file has been empty.");
		// return false;
	// }
	// document.getElementsByClassName("mybutton").disabled=true;
	// busy_on();
	// var con = createXMLHttpRequest();
	// con.open("POST", "pmn_slave_billofloading.php?proses=submitfile", true);
	// con.onreadystatechange = eval(respon);
	// con.send(formdata);
	// function respon() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// //=== Success Response
					// document.getElementsByClassName("mybutton").disabled=false;
					// alert('Uploaded Success.');
					// document.getElementById("upload").value = "";
					// loadfiles(bil);
					
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function loadfiles(bil) {
	// param = 'proses=loadfiles&bil='+bil;
	// tujuan = 'pmn_slave_billofloading.php';

	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// if (document.getElementById('listfilestop') !== null) {
						// document.getElementById('listfilestop').innerHTML = con.responseText;
					// }
					// if (document.getElementById('listfiles') !== null) {
						// document.getElementById('listfiles').innerHTML = con.responseText;
					// }
					// if (document.getElementById('listfilesview') !== null) {
						// document.getElementById('listfilesview').innerHTML = con.responseText;
					// }
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function deletefile(kontrak, namafile,bil) {
	// param = 'proses=deletefile&kontrak=' + kontrak + '&namafile=' + namafile+ '&bil=' + bil;
	// tujuan = 'pmn_slave_billofloading.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// loadfiles(kontrak);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

function selesai() {
	displayList();
}

/*
function postingData(kontrak,bil) {
    param = 'proses=postingData&kontrak=' + kontrak + '&bil=' + bil;
   	tujuan = 'pmn_slave_billofloading.php';
	alertify.confirm("Informasi","Anda yakin ingin memposting data ini "+ bil+" ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	// post_response_text(tujuan, param, respog);
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
*/