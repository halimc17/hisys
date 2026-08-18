function cekalldata() {
	totalbaris=document.getElementById('totalbaris').value;
    cekall = document.getElementById('cekall').checked;
    if(cekall==true){
        cekall='1';
    }else{
        cekall='0';
    }
	for(i=1;i<=totalbaris;i++) {
		if(cekall==1){
			document.getElementById('cek_'+i).checked=true;
		}else{
			document.getElementById('cek_'+i).checked=false;
		}
    }
}


function newdata(){
    // reset();
    document.getElementById('entry').style.display='block';
    document.getElementById('loadpreview').style.display='none';
}

function displaylist() {
    reset();
    document.getElementById('entry').style.display = 'none';
    document.getElementById('loadpreview').style.display='block';
    loaddata();
}

function proses() {
    tujuan = 'pmn_slave_feetbs.php';
    let notrans = document.getElementById('notrans').value;
    let unit = document.getElementById('unit').value;
    let tipetbs = document.getElementById('tipetbs').value;
    let supplier = document.getElementById('supplier').value;
    let tgl = document.getElementById('tgl').value;
    let tgl1 = document.getElementById('tgl1').value;
    let tgl2 = document.getElementById('tgl2').value;
    let jenis = document.getElementById('jenis').value;

    if(unit == '' && tipetbs == '' && supplier == '' && tgl == '' && tgl1 == '' && tgl2 == ''){
        alertify.alert("Mohon lengkapi data dengan lengkap.");
    }

    param = 'method=proses';
    param += '&notrans='+notrans+'&unit='+unit+'&tipetbs='+tipetbs+'&supplier='+supplier+'&tgl='+tgl;
    param += '&tgl1='+tgl1+'&tgl2='+tgl2+'&jenis='+jenis;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById('listdata').innerHTML = ar[0];
                    document.getElementById('loadpreview').style.display = 'block';
                    document.getElementById('notrans').value = ar[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function loaddata(page) {
    tujuan = 'pmn_slave_feetbs.php';
    let notrans = document.getElementById('notranshr').value;
    let unit = document.getElementById('unithr').value;
    let tgl = document.getElementById('tglhr').value;
    let posting = document.getElementById('postinghr').value;

    param = 'method=loaddata';
    param += '&page=' + page;
    param += '&notrans='+notrans+'&unit='+unit+'&tgl='+tgl+'&posting='+posting;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdata').innerHTML = con.responseText;
                    document.getElementById('loadpreview').style.display = 'block';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function getTipe() {
    unit = document.getElementById("unit").value;
    param = "method=getTipe&unit="+unit;
    tujuan = "pmn_slave_feetbs.php";

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    // alert(con.responseText);
                    document.getElementById("tipetbs").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getSup() {
    unit = document.getElementById("unit").value;
    tipetbs = document.getElementById("tipetbs").value;
    param = "method=getSup&unit="+unit+"&tipetbs="+tipetbs;
    tujuan = "pmn_slave_feetbs.php";

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi", con.responseText);
                } else {
                    // alert(con.responseText);
                    document.getElementById("supplier").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

sekarang = 1;
function saveall(maxRow){
	dicek=0;
	for(i=1;i<=maxRow;i++){
		cek = document.getElementById('cek_'+i).checked;
		if(cek==true){
			dicek++;
		}
	}
	if(dicek==0){
		alert("Silahkan checked salah satu."); return;
	}else{		
		loopsave(1, maxRow);
	}
}

function loopsave(currRow,maxRow){
	notrans = document.getElementById('notrans').value;
	unit    = document.getElementById('unit').value;
	tipetbs = document.getElementById('tipetbs').value;
	supplier= document.getElementById('supplier').value;
	tgl     = document.getElementById('tgl').value;
	tgl1    = document.getElementById('tgl1').value;
	tgl2    = document.getElementById('tgl2').value;
	jenis    = document.getElementById('jenis').value;
	nospb   = trim(document.getElementById('nospb'+currRow).innerHTML);
	alokasi = trim(document.getElementById('alokasi'+currRow).innerHTML);
	rekening= trim(document.getElementById('rekening'+currRow).innerHTML);
	debet   = trim(document.getElementById('debet'+currRow).innerHTML);
	kredit  = trim(document.getElementById('kredit'+currRow).innerHTML);
	bruto   = trim(document.getElementById('bruto'+currRow).innerHTML);
	potongan= trim(document.getElementById('potongan'+currRow).innerHTML);
	netto   = trim(document.getElementById('netto'+currRow).innerHTML);
	rpkg    = trim(document.getElementById('rpkg'+currRow).innerHTML);
	total   = trim(document.getElementById('total'+currRow).innerHTML);
	persenppn   = trim(document.getElementById('persenppn'+currRow).innerHTML);
	rpppn   = trim(document.getElementById('rpppn'+currRow).innerHTML);
    document.getElementById('row'+currRow).style.backgroundColor = 'cyan';
    cek = document.getElementById('cek_'+currRow).checked;
    if(cek==true){
        dicek='1';
    }else{
        dicek='0';
    }
    
    param = 'method=savedata';
    param += '&notrans='+notrans+'&unit='+unit+'&tipetbs='+tipetbs;
    param += '&supplier='+supplier+'&tgl='+tgl+'&tgl1='+tgl1+'&tgl2='+tgl2;
    param += '&rekening='+rekening+'&bruto='+bruto+'&potongan='+potongan;
    param += '&netto='+netto+'&rpkg='+rpkg+'&total='+total+'&persenppn='+persenppn+'&rpppn='+rpppn;
    param += '&alokasi='+alokasi+'&debet='+debet+'&kredit='+kredit+'&nospb='+nospb;
    param += '&jenis='+jenis;
    tujuan = 'pmn_slave_feetbs.php';

    if(cek==true){
        // alert(currRow);
        post_response_text(tujuan, param, respog);
    }else{
        currRow+=1;
        sekarang = currRow;
        if(currRow > maxRow) {
            alertify.alert('Done');
            displaylist();
        } else {
            loopsave(currRow,maxRow);
        }
    }

    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                    document.getElementById('row'+currRow).style.backgroundColor='red';
                    unlockScreen();
                } else {
                    document.getElementById('row'+currRow).style.display = 'none';
                    currRow+=1;
                    sekarang = currRow;
                    if(currRow > maxRow) {
                        alertify.alert('Done');
                        displaylist();
                    } else {
                        loopsave(currRow,maxRow);
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function detail(notrans){
    param = 'method=detail';
    param += '&notrans=' + notrans;
    tujuan = 'pmn_slave_feetbs.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {                    
                    title = 'Data Detail : ' + notrans;
                    tujuan = tujuan + "?" + param;
					//alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function deletedata(notrans) {
    param = 'method=hapus';
    param += '&notrans=' + notrans;
    tujuan = 'pmn_slave_feetbs.php';
    alertify.confirm("Informasi","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function posting(notrans) {
    param = 'method=posting';
    param += '&notrans=' + notrans;
    tujuan = 'pmn_slave_feetbs.php';
    alertify.confirm("Informasi","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function reset(){
    document.getElementById('notrans').value = '';
    document.getElementById('unit').value = '';
    document.getElementById('tipetbs').value = '';
    document.getElementById('supplier').value = '';
    document.getElementById('tgl').value = '';
    document.getElementById('tgl1').value = '';
    document.getElementById('tgl2').value = '';
    document.getElementById('notranshr').value = '';
    document.getElementById('unithr').value = '';
    document.getElementById('tglhr').value = '';
    document.getElementById('postinghr').value = '';
    loaddata();
}

function formajukan(unit,notrans){
  param = 'unit='+unit+'&notrans='+notrans+'&method=formajukan';
  tujuan = 'pmn_slave_feetbs.php';

  content = "<div id=formajukan style=\"height:100%;width:100%;\"></div>";
  title = 'Posting';
  height = '';
  width = 300;
  //showDialog4(title, content, width, height, 'event');

  post_response_text(tujuan, param, respon);
  function respon() {
      if (con.readyState == 4) {
          if (con.status == 200) {
              busy_off();
              if (!isSaveResponse(con.responseText)) {
                  alertify.alert("Informasi",con.responseText);
              } else {
                  // document.getElementById('formajukan').innerHTML = con.responseText;
				  alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('20%','50%');
              }
          } else {
              busy_off();
              error_catch(con.status);
          }
      }
  }
} 

function posting(notrans,maxaproval) {
  param = '';
  method = 'posting';
  tanggalpengajuan = document.getElementById('tanggalpengajuan').value; 

  if(maxaproval=='0'){
    alert('Belum ada setup persetujuan.');
    return;
  }
  if(tanggalpengajuan==''){
    alert('Silakan isi tanggal.');
    return;
  }

  strper = '';
  for(i=1;i<=maxaproval;i++){
   strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value);
   if(trim(document.getElementById('persetujuan'+i).value)==''){
    alert('Silakan isi persetujuan');
    return;
   }
  }
  param += 'notrans=' + notrans + '&tanggalpengajuan=' + tanggalpengajuan;
  param += '&maxaproval=' + maxaproval;
  param += '&method=' + method;
  param += strper;  
  tujuan = 'pmn_slave_feetbs.php';

  alertify.confirm("Informasi","Yakin ingin memposting ???",
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
          //closeDialog4();
		  alertify.popup().destroy();
          loaddata();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }  
} 

function detailpost(notrans){
  param = 'notrans='+notrans+'&method=detailpost';
  tujuan = 'pmn_slave_feetbs.php';

  content = "<div id=detailpost style=\"height:100%;width:100%;\"></div>";
  title = 'Posting';
  height = '';
  width = 500;
  //showDialog4(title, content, width, height, 'event');

  post_response_text(tujuan, param, respon);
  function respon() {
      if (con.readyState == 4) {
          if (con.status == 200) {
              busy_off();
              if (!isSaveResponse(con.responseText)) {
                  alertify.alert("Informasi",con.responseText);
              } else {
                  // document.getElementById('detailpost').innerHTML = con.responseText;
				  alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('30%','50%');
              }
          } else {
              busy_off();
              error_catch(con.status);
          }
      }
  }
} 

function pdf(notrans){
    param = 'method=pdf';
    param += '&notrans=' + notrans;
    tujuan = 'pmn_slave_feetbs.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {                    
                    title = 'Data Detail : ' + notrans;
                    tujuan = tujuan + "?" + param;
                    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}