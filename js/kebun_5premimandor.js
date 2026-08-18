function getunit(){
	kodept = document.getElementById('kodept').value;

	param = 'method=getunit'; 
	param += '&kodept=' + kodept;
	tujuan = 'kebun_slave_5premimandor.php';
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off(); 
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
					document.getElementById('kodeunit').innerHTML=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function simpandatapremi(){
	kodeunit	     	= document.getElementById('kodeunit').value;
	jenis	     		= document.getElementById('jenis').value;
	minimalpembagi     	= document.getElementById('minimalpembagi').value; 
	nilaipengali	    = document.getElementById('nilaipengali').value;

    param = 'method=simpandatapremi';
	param += '&kodeunit=' + kodeunit;
	param += '&jenis=' + jenis;
	param += '&minimalpembagi=' + minimalpembagi;
	param += '&nilaipengali=' + nilaipengali;

    // alert(param);

	tujuan = 'kebun_slave_5premimandor.php';
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off(); 
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    if (con.responseText == 'false') {
                        alert('Data Sudah Ada');
                    } else {
                        loaddata();
                        canceldatapremi();
                    }
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function ubahdatapremi(){
    kodeunit            = document.getElementById('kodeunit').value;
    jenis               = document.getElementById('jenis').value;
    minimalpembagi      = document.getElementById('minimalpembagi').value;
    nilaipengali        = document.getElementById('nilaipengali').value;
    
    param = 'method=ubahdatapremi';
    param += '&kodeunit=' + trim(kodeunit);
    param += '&jenis=' + trim(jenis);
    param += '&minimalpembagi=' + trim(minimalpembagi);
    param += '&nilaipengali=' + trim(nilaipengali);

    tujuan = 'kebun_slave_5premimandor.php';
    post_response_text(tujuan, param, respog);

    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off(); 
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    loaddata();
                    canceldatapremi();
                }
            }else{
                busy_off();
                error_catch(con.status);

            }
        }
    }   
}

function batalcari() {
    document.getElementById('find_jenis').value = '';
    document.getElementById('find_unit').value = '';
    document.getElementById('find_status').value = '';
    loaddata();
}

function loaddata(num){
    find_jenis = document.getElementById('find_jenis').value;
    find_unit = document.getElementById('find_unit').value;

    param = 'method=loaddata';
    param += '&page=' + num + '&find_unit=' + find_unit;
    param += '&find_jenis=' + find_jenis;

    // alert(param);

    tujuan='kebun_slave_5premimandor.php';
    post_response_text(tujuan, param, respog);      
    
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else {
                    document.getElementById('container').innerHTML = con.responseText;
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function canceldatapremi(){
    document.getElementById('kodeunit').value='';
    document.getElementById('jenis').selectedIndex = "0";
    document.getElementById('minimalpembagi').value='';     
    document.getElementById('nilaipengali').value=''; 
    document.getElementById('tombolsave').style.display = 'inline'; //save
    document.getElementById('tomboledit').style.display = 'none'; //edit
    document.getElementById('jenis').disabled=false;
    document.getElementById('kodeunit').disabled=false;   
}

function editlistpremi(unit,jenis,minimalpembagi,nilaipengali){
    document.getElementById('tombolsave').style.display = 'none'; //save
    document.getElementById('tomboledit').style.display = 'inline'; //edit
    document.getElementById('kodeunit').value=unit;
    document.getElementById('kodeunit').disabled=true;
    document.getElementById('jenis').value=jenis;
    document.getElementById('jenis').disabled=true;
    document.getElementById('minimalpembagi').value=minimalpembagi;
    document.getElementById('nilaipengali').value=nilaipengali;
   
}

function validasi(){
    minimalpemanen      = document.getElementById('minimalpemanen').value;
    maksimalpemanen     = document.getElementById('maksimalpemanen').value;

    if (parseInt(minimalpemanen) > parseInt(maksimalpemanen)) {
        alert("Nilai Minimal Karyawan harus lebih kecil dari Nilai Maksimal Karyawan");
        throw new Error;
    }
}

// function dellistpremi(kodept, kodeunit, jenis, minimalpemanen, maksimalpemanen, status){
//     param = 'method=dellistpremi';
//     param += '&kodept=' + kodept;
//     param += '&kodeunit=' + kodeunit;
//     param += '&jenis=' + jenis;
//     param += '&minimalpemanen=' + minimalpemanen;
//     param += '&maksimalpemanen=' + maksimalpemanen;
//     param += '&status=' + status;
//     // alert(param);

//     tujuan='kebun_slave_5premimandor.php';
//     if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
    
//     function respog()
//     {
//         if(con.readyState==4){
//             if (con.status == 200){
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)){
//                     alert('ERROR TRANSACTION,\n' + con.responseText);
//                 }else{
//                     loaddata(); 
//                 }
//             }else{
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }   
//     }   
// }