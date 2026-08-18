function numberFormat(number,digit) {
    number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
    //Seperates the components of the number
    var components = (parseFloat(number).toFixed(digit)).split(".");
    //Comma-fies the first part
    components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //Combines the two sections
    return components.join(".");
}

function getreknopol(rekening,nopol){
	unit=trim(document.getElementById('unit').value);
	param='unit='+unit+'&method=getreknopol'+'&rekening='+rekening+'&nopol='+nopol;
    tujuan='keu_slave_leasing.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if(con.readyState==4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
                    data=con.responseText.split("####");
                    document.getElementById('rekening').innerHTML=data[0];
					document.getElementById('nopol').innerHTML=data[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getnilai(){
    hargabarang=document.getElementById('hargabarang').value;
    hargabarang=hargabarang.replace(new RegExp(/,/i, "gm"),'');
    uangmuka=document.getElementById('uangmuka').value;
    uangmuka=uangmuka.replace(new RegExp(/,/i, "gm"),'');
    administrasi=document.getElementById('administrasi').value;
    administrasi=administrasi.replace(new RegExp(/,/i, "gm"),'');
    survey=document.getElementById('survey').value;
    survey=survey.replace(new RegExp(/,/i, "gm"),'');
    asuransi=document.getElementById('asuransi').value;
    asuransi=asuransi.replace(new RegExp(/,/i, "gm"),'');
    fidusia=document.getElementById('fidusia').value;
    fidusia=fidusia.replace(new RegExp(/,/i, "gm"),'');
    provisi=document.getElementById('provisi').value;
    provisi=provisi.replace(new RegExp(/,/i, "gm"),'');
    notaris=document.getElementById('notaris').value;
    notaris=notaris.replace(new RegExp(/,/i, "gm"),'');
    sukubunga=document.getElementById('sukubunga').value;
    tenor=document.getElementById('tenor').value;

    //perhitungan
    utangpokok=hargabarang-uangmuka;
    bunga=utangpokok*(sukubunga/100);
    totalkredit=parseFloat(utangpokok)+parseFloat(bunga);
    if (tenor!=0) {
        sisatenor=tenor-1;
        angsuran=totalkredit/sisatenor;
        angsuran=Math.round(angsuran,-2);
    }else{
        angsuran=0;
    }
    pembayaran=parseFloat(uangmuka)+parseFloat(angsuran)+parseFloat(administrasi)+parseFloat(survey)+parseFloat(fidusia)+parseFloat(provisi)+parseFloat(notaris)+parseFloat(asuransi);
    
    document.getElementById('utangpokok').value=numberFormat(utangpokok,2);
    document.getElementById('bunga').value=numberFormat(bunga,2);
    document.getElementById('totalkredit').value=numberFormat(totalkredit,2);
    document.getElementById('angsuran').value=numberFormat(angsuran,2);
    document.getElementById('pembayaran').value=numberFormat(pembayaran,2);
}

function getstatus(namaasuransi,status){
    namaasuransi=trim(document.getElementById('namaasuransi').value);
    if(namaasuransi==1){
        document.getElementById('status').value=1;
        document.getElementById('status').disabled=true;
    }else if(namaasuransi==2){
        document.getElementById('status').value=0;
        document.getElementById('status').disabled=true;
    }else{
        if(status!=''){
            document.getElementById('status').value=status;
        }else{
        document.getElementById('status').value='';
        }
        document.getElementById('status').disabled=false;
    }
}

function getBulan(){
    tgl=document.getElementById('tglefektif').value;
    tgllunas=document.getElementById('tgllunas').value;
    param='method=getBulan'+'&tglefektif='+tgl+'&tgllunas='+tgllunas;
    tujuan='keu_slave_leasing.php';
    post_response_text(tujuan, param, respog); 
    function respog(){
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					if(tgllunas !== ""){
						
                        alert(con.responseText);
						document.getElementById('tgllunas').value = "";
					}
                }
                else 
                {
					console.log(con.responseText);
                    document.getElementById('tenor').value=con.responseText;
                    getnilai();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function searchnotadebet(title,content,ev){
    metbayar=document.getElementById('metbayar').value;

    if (metbayar=='Transfer') {
        alert('Pemilihan NO. Cek hanya utk metode pembayaran Giro dan Cheque.');
        return;
    }

    width='auto';
    height='auto';
    showDialog1(title,content,width,height,ev);
    getdatadebet();
}

function getdatadebet(){
    unit=document.getElementById('unit').value;
    param='method=getdatadebet'+'&unit='+unit;
    
    tujuan='keu_slave_leasing.php';
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
                    document.getElementById('formPencariandata').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function setdata(notadebet,hargabarang,uangmuka,utangpokok){
    document.getElementById('notadebet').value=notadebet;
    document.getElementById('hargabarang').value=hargabarang;
    document.getElementById('uangmuka').value=uangmuka;
    document.getElementById('utangpokok').value=utangpokok;
    closeDialog();
    closeDialog2();
    getnilai();
}

function getdetailnocek(){
    notrans_cek=document.getElementById('notrans_cek').value;
    param='method=getdetailnocek'+'&notrans_cek='+notrans_cek;
    
    tujuan='keu_slave_leasing.php';
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
                    document.getElementById('nocekpil').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function checkAll()
// {   
//     totrow = document.getElementById('totrow').value;
//     btn = document.getElementById('btnall');
//     if (btn.checked == true){
//         chk = true;
//     } else {
//         chk = false;
//     }

//     for (i=1; i <= totrow; i++)
//     {
//         document.getElementById('no_'+i).checked = chk;
//     }
// }

// function adddetail(notrans_cek) {
//     totrow=trim(document.getElementById('totrow').value);
    
//     var allData='';
//     var cekpil=0;
//     for(dwc=1;dwc<=totrow;dwc++){
//         if (document.getElementById('no_'+dwc).checked==true) {
//             allData+="&nocek[]="+document.getElementById('nocek_'+dwc).innerHTML;
//             cekpil+=1;
//         }
//     }

//     if(cekpil==0){
//         alert('Data belum terpilih.');
//         return;
//     }

//     param='totrow='+cekpil+'&notrans_cek='+notrans_cek+'&method=adddetail';
//     param+=allData;
    
//     tujuan='keu_slave_leasing.php';
//     post_response_text(tujuan, param, respog);
//     function respog() {
//         if(con.readyState==4) {
//             if (con.status == 200){
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 }
//                 else {
//                     closeDialog();
//                 }
//             }
//             else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }

function saveData(fileTarget, passParam) {
    var passP = passParam.split('##');
    var param = ""
        for (i = 1; i < passP.length; i++) {
            var tmp = document.getElementById(passP[i]);
            if (i == 1) {
                param += passP[i] + "=" + getValue(passP[i]);
            } else {
                param += "&" + passP[i] + "=" + getValue(passP[i]);
            }
        }
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    clearData();
                    displaylist();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget + '.php', param, respon);
}

function deleteht(notrans){
    param='method=deleteht'+'&notransaksi='+notrans;
    tujuan='keu_slave_leasing.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
		if(con.readyState==4)
		{
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   displaylist();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
		} 
    }
}

function updateht(notrans,notadebet,unit,rekening,namaasuransi,kontrakasuransi,namavendor,kontrakvendor,tglefektif,tgllunas,statuskontrak,nopol,kuantitas,harga_barang,uangmuka,utangpokok,sukubunga,bunga,tenor,totalkredit,angsuran,metbayar,pembayaran,administrasi,survey,asuransi,fidusia,provisi,notaris,denda){
    document.getElementById('unit').value=unit;
    document.getElementById('notransaksi').value=notrans;
    document.getElementById('notadebet').value=notadebet;
    document.getElementById('rekening').value=rekening;
    document.getElementById('namaasuransi').value=namaasuransi;
    document.getElementById('kontrakasuransi').value=kontrakasuransi;
    document.getElementById('namavendor').value=namavendor;
    document.getElementById('kontrakvendor').value=kontrakvendor;
    document.getElementById('tglefektif').value=tglefektif;
    document.getElementById('tgllunas').value=tgllunas;
    document.getElementById('statuskontrak').value=statuskontrak;
    document.getElementById('nopol').value=nopol;
    document.getElementById('kuantitas').value=kuantitas;
    document.getElementById('hargabarang').value=harga_barang;
    document.getElementById('uangmuka').value=uangmuka;
    document.getElementById('utangpokok').value=utangpokok;
    document.getElementById('sukubunga').value=sukubunga;
    document.getElementById('bunga').value=bunga;
    document.getElementById('tenor').value=tenor;
    document.getElementById('totalkredit').value=totalkredit;
    document.getElementById('angsuran').value=angsuran;
    document.getElementById('metbayar').value=metbayar;
    document.getElementById('pembayaran').value=pembayaran;
    document.getElementById('administrasi').value=administrasi;
    document.getElementById('survey').value=survey;
    document.getElementById('asuransi').value=asuransi;
    document.getElementById('fidusia').value=fidusia;
    document.getElementById('provisi').value=provisi;
    document.getElementById('notaris').value=notaris;
    document.getElementById('denda').value=denda;
    document.getElementById('method').value='updateht';
    getreknopol(rekening,nopol);
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
}

function postinght(notrans){
    param='method=postinght'+'&notransaksi='+notrans;
    tujuan='keu_slave_leasing.php';
    if(confirm('Anda yakin ingin memposting data ini ??'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else 
                {
                    displaylist();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function clearData(){
	document.getElementById('unit').value='';
    document.getElementById('notransaksi').value='';
	document.getElementById('notadebet').value='';
	document.getElementById('rekening').value='';
	document.getElementById('namaasuransi').value='';
	document.getElementById('kontrakasuransi').value='';
    document.getElementById('namavendor').value='';
    document.getElementById('kontrakvendor').value='';
    document.getElementById('tglefektif').value='';
    document.getElementById('tgllunas').value='';
    document.getElementById('statuskontrak').value='';
    document.getElementById('kuantitas').value=0;
    document.getElementById('nopol').value='';
    document.getElementById('hargabarang').value=0;
    document.getElementById('uangmuka').value=0;
    document.getElementById('utangpokok').value=0;
    document.getElementById('sukubunga').value=0;
    document.getElementById('bunga').value=0;
    document.getElementById('tenor').value=0;
    document.getElementById('totalkredit').value=0;
    document.getElementById('angsuran').value=0;
    document.getElementById('metbayar').value='';
    document.getElementById('pembayaran').value=0;
    document.getElementById('administrasi').value=0;
    document.getElementById('survey').value=0;
    document.getElementById('asuransi').value=0;
    document.getElementById('fidusia').value=0;
    document.getElementById('provisi').value=0;
    document.getElementById('notaris').value=0;
	document.getElementById('denda').value=0;
	document.getElementById('method').value='insertht';
}

function displayFormInput(){
    document.getElementById('formInput').style.display='block';
    document.getElementById('listData').style.display='none';
    clearData();
}

function displaylist(){
    document.getElementById('nocr').value='';
	document.getElementById('listData').style.display='block';
	document.getElementById('formInput').style.display='none';
    clearData();
	loadData(0);
}

function loadData(num){
    nocr=document.getElementById('nocr').value;

    param='method=loadData';
    param+='&page='+num;

    if (nocr != '') {
        param += '&notransaksi=' + nocr;
    }

    tujuan='keu_slave_leasing.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('continerlist').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
          		error_catch(con.status);
            }
        }   
    }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.ounitions[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);  
}

function form(){
    width = '';
    height = '';
    content = "<fieldset><div id=containerd align=center style=overflow:auto;></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog1(title, content, width, height, ev); 
}


function adddetail(notrans){
    form();
    param = 'method=adddetail'+'&notransaksi='+notrans;
    tujuan = 'keu_slave_leasing.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedetail(){
    tglangsuran=document.getElementById('tglangsuran').value;
    tenor_ke=document.getElementById('tenor_ke').value;
    notransaksidt=document.getElementById('notransaksidt').value;
    notrans_cek=document.getElementById('notrans_cek').value;
    nocekpil=document.getElementById('nocekpil').value;
    methoddt=document.getElementById('methoddt').value;
    param='method='+methoddt+'&tglangsuran='+tglangsuran+'&tenor='+tenor_ke+'&notransaksi='+notransaksidt+'&notrans_cek='+notrans_cek+'&nocekpil='+nocekpil;
    
    tujuan='keu_slave_leasing.php';
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
                    adddetail(notransaksidt);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletedt(notrans,tenor_ke,nocekpil){
    param='method=deletedt'+'&notransaksi='+notrans+'&tenor='+tenor_ke+'&nocekpil='+nocekpil;
    tujuan='keu_slave_leasing.php';
    if(confirm(' Anda yakin ingin menghapus data ini?'))
    {
        post_response_text(tujuan, param, respog);  
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                   adddetail(notrans);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }
}

function viewdetail(notrans){
    form();
    param = 'method=viewdetail'+'&notransaksi='+notrans;
    tujuan = 'keu_slave_leasing.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else
                {
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function makepdfx(notrans){
    param = 'method=makepdfx'+'&notransaksi='+notrans;
    tujuan = 'keu_slave_leasing.php?' + param;
    title = '';
    width = '1000';
    height = '400';
    ev = 'event';
    content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
        showDialog2(title, content, width, height, ev);
}
// function saveDatadt(notransaksi,notranskasbank){
//     notranskasbank=trim(document.getElementById('notranskasbank').value);
//     tglcair=trim(document.getElementById('tglcair').value);
//     tglterima=trim(document.getElementById('tglterima').value);
//     jumlahbunga=trim(document.getElementById('jumlahbunga').value);
//     jumlahpajak=trim(document.getElementById('jumlahpajak').value);
//     jumlahpenalti=trim(document.getElementById('jumlahpenalti').value);
//     realisasi=trim(document.getElementById('realisasi').value);
//     methoddt=trim(document.getElementById('methoddt').value);

//     param='notransaksi='+notransaksi+'&tglcair='+tglcair+'&method='+methoddt+'&tglterima='+tglterima+'&notranskasbank='+notranskasbank;
//     param+='&jumlahbunga='+jumlahbunga+'&jumlahpajak='+jumlahpajak+'&jumlahpenalti='+jumlahpenalti+'&realisasi='+realisasi;
//     tujua='keu_slave_leasing.php';
//     post_response_text(tujuan, param, respog);
//     function respog() {
//         if(con.readyState==4) {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 } else {
//                     clearDatadt();
//                     viewdetail(notransaksi,'');
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         }
//     }
// }

// function clearDatadt(){
//     document.getElementById('notranskasbank').value='';
//     document.getElementById('tglcair').value='';
//     document.getElementById('tglterima').value='';
//     document.getElementById('jumlahbunga').value='';
//     document.getElementById('jumlahpajak').value='';
//     document.getElementById('jumlahpenalti').value='';
//     document.getElementById('realisasi').value='';
//     document.getElementById('total').value='';
//     document.getElementById('variance').value='';
//     document.getElementById('methoddt').value='insertdetail';
// }

// function deldetail(notrans,notranskasbank){
//     param='method=deldetail'+'&notransaksi='+notrans+'&notranskasbank='+notranskasbank;
//     tujuan='keu_slave_leasing.php';
//     if(confirm(' Anda yakin ingin menghapus data ini?'))
//     {
//         post_response_text(tujuan, param, respog);  
//     }
//     function respog()
//     {
//         if(con.readyState==4)
//         {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                     alert(con.responseText);
//                 }else{
//                    viewdetail(notrans,'');
//                 }
//             }else{
//                 busy_off();
//                 error_catch(con.status);
//             }
//         } 
//     }
// }

// function postingdetail(notrans,notranskasbank){
//     param='method=postingdetail'+'&notransaksi='+notrans+'&notranskasbank='+notranskasbank;
//     tujuan='keu_slave_leasing.php';
//     if(confirm('Anda yakin ingin memposting data ini ??'))
//     {
//         post_response_text(tujuan, param, respog);  
//     }
//     function respog()
//     {
//         if(con.readyState==4)
//         {
//             if (con.status == 200) {
//                 busy_off();
//                 if (!isSaveResponse(con.responseText)) {
//                         alert(con.responseText);
//                 }
//                 else 
//                 {
//                     viewdetail(notrans,'');
//                 }
//             } else {
//                 busy_off();
//                 error_catch(con.status);
//             }
//         } 
//     }
// }
