function createNew(){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
    document.getElementById('method').value ='insert';
    batalcari();
    hapus();
}

function displayList(){
    hapus();
    batalcari();
    document.getElementById('addNew').style.display ='none';
    document.getElementById('listData').style.display ='block';
    loadData(0);
}

function batalcari(){
	document.getElementById('tanggalsch').value='';
	document.getElementById('kodeorgsch').value='';
}

function hapus(){
    document.getElementById('listdetail').style.display ='none';
    document.getElementById('kodeorg').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('method').value='insert';
    document.getElementById('tanggal').disabled=false;
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('barang').disabled=false;
}

function getPage(){
	pg      = document.getElementById('pages');
	pg      = pg.options[pg.selectedIndex].value;
	paged   = parseFloat(pg) - 1;
	loadData(paged);
}

function loadData(num){
	tanggalsch      = document.getElementById('tanggalsch').value;
	kodeorgsch      = document.getElementById('kodeorgsch').value;
    barangsch      = document.getElementById('barangsch').value;

    param   ='method=loadData&page=' + num;
    if(tanggalsch != '' && kodeorgsch != '' && barangsch != ''){      
        param  +='&tanggalsch=' + tanggalsch;
        param  +='&kodeorgsch=' + kodeorgsch;
    }else if(tanggalsch != ''){
        param  +='&tanggalsch=' + tanggalsch;
    }else if(kodeorgsch != ''){
        param  +='&kodeorgsch=' + kodeorgsch;
    }else if(barangsch != ''){
        param  +='&barangsch=' + barangsch;
    }
    tujuan  ='pabrik_slave_stockforsale.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    dataSlave = con.responseText.split("####");
                    document.getElementById('addNew').style.display ='none';
                    document.getElementById('listData').style.display ='block';
                    document.getElementById('container').innerHTML      = dataSlave[0];
                    document.getElementById('footData').innerHTML       = dataSlave[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }  
}

function fillField(tanggal,kodeorg,barang){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
    document.getElementById('tanggal').disabled=true;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('barang').disabled=true;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('barang').value=barang;
    document.getElementById('method').value='update';
    loaddetail(tanggal,kodeorg,barang);
}

function preview(){
    kodeorg     =document.getElementById('kodeorg').value;
    tanggal     =document.getElementById('tanggal').value;
    barang     =document.getElementById('barang').value;
    param       = 'method=preview';
    param       += '&kodeorg=' + kodeorg;
    param       += '&tanggal=' + tanggal;
    param       += '&barang=' + barang;

    tujuan  ='pabrik_slave_stockforsale.php';
    post_response_text(tujuan, param, respog);
    
    function respog()
    {
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    dt = con.responseText.split('#');
                    if(dt[2] == ''){
                        loaddetail(dt[0],dt[1],dt[3]);
                    }else{
                        alert(dt[2]);
                    }
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function loaddetail(tanggal,kodeorg,barang){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
	kodeorg  = document.getElementById('kodeorg').value;
	tanggal  = document.getElementById('tanggal').value;
    barang  = document.getElementById('barang').value;
    if(kodeorg == ''){
        alert('Harap pilih Pabrik !');
        return false;
    }else if(tanggal == ''){
        alert('Harap pilih Tanggal !');
        return false;
    }else{
        met  = document.getElementById('method').value;
        param = 'method=loaddetail';
        param += '&tanggal=' + tanggal;
        param += '&kodeorg=' + kodeorg;
        param += '&barang=' + barang;
        param += '&mode=' + met;

        post_response_text(tujuan, param, respog);
    }
    
    function respog()
    {
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    data = con.responseText.split("###");
                    document.getElementById('tanggal').disabled=true;
                    document.getElementById('kodeorg').disabled=true;
                    document.getElementById('listdetail').style.display = 'block'; //save
                    document.getElementById('containerdetail').innerHTML=con.responseText;
                    // document.getElementById('tombolsave').style.display = 'none'; //save
                    // document.getElementById('tomboledit').style.display = 'inline'; //edit
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

// function hitungsiapdijual(nomor){
//     ac  = document.getElementById('alreadycontract'+nomor).value;
//     rfs = document.getElementById('readyforsaleasli'+nomor).value;
//     if(rfs != ''){
//         hasil= rfs-ac;
//         document.getElementById('readyforsale'+nomor).value = hasil;
//         document.getElementById('readyforsale'+nomor);
//     }
// }


maxf    =0
sekarang=1;
function simpan(maxRow){    
	maxf=maxRow+1;
    loopsave(1,maxRow);
}

function loopsave(currRow,maxRow) {
    param           = "";
	kodeorg         =trim(document.getElementById('kodeorg').value);
	tanggal         =trim(document.getElementById('tanggal').value);
    barang          =trim(document.getElementById('barang').value);
    tangki          =trim(document.getElementById('tangki'+currRow).innerHTML);
    readyforsale    =parseFloat(document.getElementById('readyforsale'+currRow).value);
    
    alreadycontract="";
    upper5persen="";
    if (barang == 'CPO') {
        alreadycontract=parseFloat(document.getElementById('alreadycontract'+currRow).value);
        upper5persen=parseFloat(document.getElementById('upper5persen'+currRow).innerHTML);
    }

    sold="";
    if (barang == 'KER') {
        sold=parseFloat(document.getElementById('sold'+currRow).value);
    }
    
	
    param+='&method=insert';
    param+='&kodeorg='+kodeorg;
    param+='&tanggal='+tanggal;
    param+='&barang='+barang;

    param+='&kodetangki='+tangki;
    param+='&alreadycontract='+alreadycontract;
    param+='&readyforsale='+readyforsale;
    param+='&sold='+sold;
    param+='&upper5persen='+upper5persen;
    tujuan = 'pabrik_slave_stockforsale.php';
    post_response_text(tujuan, param, respog);
    document.getElementById('row'+currRow).style.backgroundColor='';
    document.getElementById('row'+currRow).style.display='none';
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					 document.getElementById('row'+currRow).style.backgroundColor='yellow';
					unlockScreen();
                } else {
                    currRow+=1;
                    sekarang=currRow;
                    if(currRow>maxRow){
                        if (barang == 'CPO') {
                            saveakhir();
                        }else{
                            displayList();
                        }
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

function saveakhir(){
    kodeorg         =trim(document.getElementById('kodeorg').value);
    tanggal         =trim(document.getElementById('tanggal').value);
    barang          =trim(document.getElementById('barang').value);
    inprocess       =trim(document.getElementById('inprocess').innerHTML);
    inprocessac     =trim(document.getElementById('inprocessac').value);
    inprocessrfs    =trim(document.getElementById('inprocessrfs').value);
    method          =trim(document.getElementById('method').value);

    param           = ""
    param           +='&method=insert';
    param           +='&perulangan=akhir';
    param           +='&met='+method;
    param           +='&kodeorg='+kodeorg;
    param           +='&tanggal='+tanggal;
    param           +='&barang='+barang;
    param           +='&inprocess='+inprocess;
    param           +='&inprocessac='+inprocessac;
    param           +='&inprocessrfs='+inprocessrfs;
    tujuan          = 'pabrik_slave_stockforsale.php';
    
    post_response_text(tujuan, param, respog);
    document.getElementById('rowx').style.backgroundColor='';
    document.getElementById('rowx').style.display='none';
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                    document.getElementById('rowx').style.backgroundColor='yellow';
                    unlockScreen();
                } else {
                    dt = con.responseText.split('##');
                    loaddetail(dt[0],dt[1]);
                    displayList();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }	
}

function del(kodeorg,tanggal, barang){
    param   ='method=delete'+'&kodeorg='+kodeorg+'&tanggal='+tanggal+'&barang='+barang;
    tujuan  ='pabrik_slave_stockforsale.php';
    if(confirm("Hapus pabrik  "+kodeorg+" di tanggal "+tanggal+"?"))
    {
        post_response_text(tujuan, param, respog);
    }

    function respog()
    {
        if(con.readyState==4)
        {
            if(con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    hapus();
                    document.getElementById('container').innerHTML=con.responseText;
                    alert("Data berhasil dihapus !!!");
                    loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
                hapus();
            }
        }	
    }	
}