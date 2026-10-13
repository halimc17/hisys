//true saat form sedang diisi lewat kode (edit/hapus): handler isian tidak boleh ikut berjalan
var isiForm = false;

//halaman ini tidak memuat js/zSelect2.js, jadi select2 diinisialisasi di sini
$(document).ready(function () {
    $('.select2').select2({dropdownAutoWidth: true});

    //isian apa pun yang diubah sesudah detail diproses menyembunyikan Simpan lagi, detail harus diproses ulang
    $('#karyawanid, #jenisangsuran, #ket, #tothutang, #rpbulan').on('change input', function () {
        if (!isiForm) {
            cekdataubah();
        }
    });
    //tipe nominal: bulan akhir dihitung ulang bila bulan awal atau total hutang berubah
    $('#bulandari, #tothutang').on('change', function () {
        if (!isiForm) {
            hitungulangbulan();
        }
    });
});

//pilihan bawaan select (opsi yang bertanda selected di halaman) tanpa memicu onchange
function setSelAwal(id) {
    var el = document.getElementById(id);
    var v = el.options.length ? el.options[0].value : '';
    for (var i = 0; i < el.options.length; i++) {
        if (el.options[i].defaultSelected) {
            v = el.options[i].value;
            break;
        }
    }
    setSel(id, v);
}

//hitung ulang bulan akhir (tipe nominal) tanpa alert kalau total hutang atau rupiah/bulan belum diisi
function hitungulangbulan() {
    var tot = parseFloat(document.getElementById('tothutang').value.replace(/,/gi, ''));
    var rp = parseFloat(document.getElementById('rpbulan').value.replace(/,/gi, ''));
    if (document.getElementById('tipeangsuran').value != 'angsurannominal' || document.getElementById('tothutang').disabled) {
        return;
    }
    if (isNaN(tot) || tot <= 0 || isNaN(rp) || rp <= 0) {
        return;
    }
    hitungbulan();
}

//validasi isian header sebelum detail diproses/disimpan
function cekIsianAngsuran() {
    var tipe = document.getElementById('tipeangsuran').value;
    var tot = parseFloat(document.getElementById('tothutang').value.replace(/,/gi, ''));
    var rp = parseFloat(document.getElementById('rpbulan').value.replace(/,/gi, ''));
    var bd = document.getElementById('bulandari').value;
    var bs = document.getElementById('bulansampai').value;
    if (isNaN(tot) || tot <= 0) {
        alert('Warning : Total hutang harus lebih besar dari 0');
        return false;
    }
    if (tipe == 'angsurannominal') {
        if (isNaN(rp) || rp <= 0) {
            alert('Warning : Rupiah/bulan harus lebih besar dari 0');
            return false;
        }
        if (rp > tot) {
            alert('Warning : Rupiah/bulan tidak boleh lebih besar dari total hutang');
            return false;
        }
    }
    if (bs < bd) {
        alert('Warning : Bulan akhir tidak boleh lebih kecil dari bulan awal');
        return false;
    }
    return true;
}

//validasi detail per bulan sebelum disimpan: angka tidak negatif dan jumlahnya sama dengan total hutang
function cekDetailAngsuran() {
    var n = parseInt(document.getElementById('totaldetail').value);
    var tot = parseFloat(document.getElementById('tothutang').value.replace(/,/gi, ''));
    var jml = 0;
    for (var i = 1; i <= n; i++) {
        var v = document.getElementById('rpdetailx_' + i).value.replace(/,/gi, '');
        if (v == '' || isNaN(v) || parseFloat(v) < 0) {
            alert('Warning : Nilai angsuran bulan ke-' + i + ' tidak valid');
            return false;
        }
        jml += parseFloat(v);
    }
    if (Math.abs(jml - tot) > 0.5) {
        alert('Warning : Total detail (' + jml.toLocaleString('en-US') + ') tidak sama dengan total hutang (' + tot.toLocaleString('en-US') + ')');
        return false;
    }
    return true;
}
//set nilai select2 tanpa memicu onchange bawaan select (loadData)
//onchange inline dimatikan sebentar karena trigger jQuery ikut menjalankannya
function setSel(id, v) {
    var el = document.getElementById(id);
    var handler = el.onchange;
    el.onchange = null;
    $('#' + id).val(v).trigger('change.select2');
    el.onchange = handler;
}

function getFilterParamAng() {
    var p = '';
    p += '&notransaksisch=' + encodeURIComponent(document.getElementById('notransaksisch').value);
    p += '&namasch=' + encodeURIComponent(document.getElementById('namasch').value);
    p += '&lokasitugassch=' + encodeURIComponent(document.getElementById('lokasitugassch').value);
    p += '&jenisangsuransch=' + encodeURIComponent(document.getElementById('jenisangsuransch').value);
    p += '&statussch=' + encodeURIComponent(document.getElementById('statussch').value);
    return p;
}

function exportExcelAng() {
    tujuan = 'sdm_slave_angsuran.php?method=excellist' + getFilterParamAng();
    alertify.popup('Report Ms.Excel', "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable': true, 'maximizable': true, 'overflow': false}).resizeTo('80%', '70%');
}

function exportPdfAng() {
    tujuan = 'sdm_slave_angsuran.php?method=pdflist' + getFilterParamAng();
    alertify.popup('Report PDF', "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable': true, 'maximizable': true, 'overflow': false}).resizeTo('80%', '70%');
}

//tarikan Excel/PDF per transaksi
function exportTrx(notransaksi, jenis) {
    tujuan = 'sdm_slave_angsuran.php?method=' + (jenis == 'excel' ? 'excelTrx' : 'pdfTrx') + '&notransaksi=' + encodeURIComponent(notransaksi);
    alertify.popup(jenis == 'excel' ? 'Report Ms.Excel' : 'Report PDF', "<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable': true, 'maximizable': true, 'overflow': false}).resizeTo('80%', '70%');
}

function createNew(){
    document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=dtlForm onclick=getdetail()>Proses List Detail</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
    document.getElementById('method').value ='insert';
    hapus();
}

function displayList(){
    hapus();
    document.getElementById('addNew').style.display ='none';
    document.getElementById('listData').style.display ='block';
    loadData(0);
}

function cancelForm(){
    hapus();
}


function getPage(){
  pg      = document.getElementById('pages');
  pg      = pg.options[pg.selectedIndex].value;
  paged   = parseFloat(pg) - 1;
  loadData(paged);
}

function loadData(num){
  notransaksisch  = document.getElementById('notransaksisch').value;
  namasch         = document.getElementById('namasch').value;
  lokasitugassch  = document.getElementById('lokasitugassch').value;
  jenisangsuransch= document.getElementById('jenisangsuransch').value;

    param   ='method=loadData&page=' + num;     
    param  +='&notransaksisch=' + encodeURIComponent(notransaksisch);
    param  +='&namasch=' + encodeURIComponent(namasch);
    param  +='&lokasitugassch=' + encodeURIComponent(lokasitugassch);
    param  +='&jenisangsuransch=' + encodeURIComponent(jenisangsuransch);
    param  +='&statussch=' + encodeURIComponent(document.getElementById('statussch').value);
    tujuan  ='sdm_slave_angsuran.php';
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

function hapus(){
    document.getElementById('notransaksisch').value='';
    document.getElementById('namasch').value='';
    setSel('lokasitugassch','');
    setSel('jenisangsuransch','');
    setSel('statussch','');
    document.getElementById('notransaksi').value='';
    document.getElementById('method').value='insert';

    //edit angsuran aktif mengunci sebagian isian: semuanya dibuka lagi dan dikembalikan ke nilai awal
    isiForm=true;
    var kunci=['karyawanid','jenisangsuran','tipeangsuran','bulandari','bulansampai','tothutang','rpbulan','ket'];
    for(var k=0;k<kunci.length;k++){
        document.getElementById(kunci[k]).disabled=false;
    }
    document.getElementById('ket').value='';

    document.getElementById('notransaksi').value='';
    document.getElementById('tothutang').value='0';
    document.getElementById('rpbulan').value='0';
    document.getElementById('jenisangsuran').innerHTML="<option value=''>Pilih Data</option>";
    $('#jenisangsuran').trigger('change.select2');
    setSelAwal('tipeangsuran');
    setSelAwal('bulandari');
    setSelAwal('bulansampai');
    document.getElementById('datadetail').innerHTML="";
    document.getElementById('datasblm').value='';
    setValue2('karyawanid','');
    isiForm=false;
    disableinputan();
    document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=dtlForm onclick=getdetail()>Proses List Detail</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";
    
}

function loadperiodecuti(){
  karyawanid  = document.getElementById('karyawanid').value;

    param   ='method=loadperiodecuti';     
    param  +='&karyawanid=' + karyawanid;
    tujuan  ='sdm_slave_angsuran.php';
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
                    document.getElementById('periodecuti').innerHTML=con.responseText;
                    clearinputan();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function clearinputan(){
    document.getElementById('notransaksisch').value='';

     document.getElementById('keperluan').value='';
     document.getElementById('ket').value='';
     document.getElementById('tglAwal').value='';
     document.getElementById('jumlahhk').value='';
     document.getElementById('tglEnd').value='';
     setValue2('idjenis','');
     document.getElementById('pengganti').value='';
     document.getElementById('tanggalkerja').value='';
     document.getElementById('tglIzin').value='';
     document.getElementById('tglMasuk').value='';
     document.getElementById('tglPengangkatan').value='';
     document.getElementById('alamatcuti').value='';
     document.getElementById('nohp').value='';
     document.getElementById('hometrip').value='';
     document.getElementById('tglberangkat').value='';
      document.getElementById('rutekeberangkatan').value='';
      document.getElementById('tglpulang').value='';
      document.getElementById('rutekepulangan').value='';
      document.getElementById('sis').innerHTML='0';
     // closeDialog();
}

function hitungbulan(){
    param   ='method=hitungbulan';     
    bulandari=document.getElementById('bulandari').value;
    totalhutang=document.getElementById('tothutang').value.replace(/,/gi,'');
    rpbulan=document.getElementById('rpbulan').value.replace(/,/gi,'');
    param  +='&bulandari=' + bulandari;
    param  +='&totalhutang=' + totalhutang;
    param  +='&rpbulan=' + rpbulan;
    tujuan  ='sdm_slave_angsuran.php';
    post=0;
    if(document.getElementById('tipeangsuran').value=='angsurannominal'){
        if(totalhutang==0 || totalhutang==''){
            alertify.alert("Warning: Total hutang tidak boleh kosong atau 0");
            document.getElementById('rpbulan').value=0;
        }else{
            post=1;
        }
    }else if(document.getElementById('tipeangsuran').value=='angsuranjangkawaktu'){
        
    }else{
        if(totalhutang==0 || totalhutang==''){
            alertify.alert("Warning: Total hutang tidak boleh kosong atau 0");
            document.getElementById('rpbulan').value=0;
        }else{
            post=1;
        }
    }

    if(post==1){
        post_response_text(tujuan, param, respog);
    }


    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                    document.getElementById('rpbulan').value=0;
                }else{
                    setValue2('bulansampai',con.responseText);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function hitungbulantopup(){
    param   ='method=hitungbulan';     
    bulandari=document.getElementById('bulandari').value;
    totalhutang=document.getElementById('tothutangtopup').value.replace(/,/gi,'');
    rpbulan=document.getElementById('rpbulantopup').value.replace(/,/gi,'');
    param  +='&bulandari=' + bulandari;
    param  +='&totalhutang=' + totalhutang;
    param  +='&rpbulan=' + rpbulan;
    tujuan  ='sdm_slave_angsuran.php';
    post=0;
    if(document.getElementById('tipeangsuran').value=='angsurannominal'){
        if(totalhutang==0 || totalhutang==''){
            alertify.alert("Warning: Total hutang tidak boleh kosong atau 0");
            document.getElementById('rpbulantopup').value=0;
        }else{
            post=1;
        }
    }else if(document.getElementById('tipeangsuran').value=='angsuranjangkawaktu'){
        
    }else{
        if(totalhutang==0 || totalhutang==''){
            alertify.alert("Warning: Total hutang tidak boleh kosong atau 0");
            document.getElementById('rpbulantopup').value=0;
        }else{
            post=1;
        }
    }

    if(post==1){
        post_response_text(tujuan, param, respog);
    }


    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                    document.getElementById('rpbulan').value=0;
                }else{
                    setValue2('bulansampai',con.responseText);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function disableinputan(){
    if(document.getElementById('tipeangsuran').value=='angsurannominal'){
        document.getElementById('bulansampai').disabled=true;
        document.getElementById('rpbulan').disabled=false;
    }else if(document.getElementById('tipeangsuran').value=='angsuranjangkawaktu'){
        document.getElementById('bulansampai').disabled=false;
        document.getElementById('rpbulan').disabled=true;
    }else{
       document.getElementById('bulansampai').disabled=false;
       document.getElementById('rpbulan').disabled=false; 
    }
    document.getElementById('rpbulan').value=0;
    cekdataubah();
}

function perbuahanjenistopup(notransaksi){
    if(document.getElementById('jenistopup').value=='1'){
        if(document.getElementById('bulansampaitopuptd')){
            document.getElementById('bulansampaitopuptd').style.display='none';
            document.getElementById('bulansampaitopupth').style.display='none';
        }
        document.getElementById('rpbulantopup').disabled=true;
        document.getElementById('tothutangtopup').disabled=true;
        document.getElementById('bulansampaitopup').value=document.getElementById('bulanakhirsebenarnya').innerHTML;
        document.getElementById('bulanmulaiperubahan').innerHTML=document.getElementById('datarange').value;
        hitunghutangsisa(notransaksi);
    }else{
        if(document.getElementById('bulansampaitopuptd')){
           document.getElementById('bulansampaitopupth').style.display='';
        document.getElementById('bulansampaitopuptd').style.display='';
        }
        
        document.getElementById('rpbulantopup').disabled=false;
        document.getElementById('tothutangtopup').disabled=false; 
        document.getElementById('bulanmulaiperubahan').innerHTML=document.getElementById('datarange2').value;
    }
    document.getElementById('rpbulantopup').value=0;
}

function loadjenisangsuran(){
    karyawanid  = document.getElementById('karyawanid').value;

    notransaksi  = document.getElementById('notransaksi').value;


    //karyawan belum dipilih (mis. form dikosongkan): tidak perlu ke server
    if(karyawanid==''){
        document.getElementById("jenisangsuran").innerHTML = "<option value=''>Pilih Data</option>";
        return;
    }

    param   ='method=loadjenisangsuran';
    param  +='&karyawanid=' + karyawanid;
    tujuan  ='sdm_slave_angsuran.php';
    if(notransaksi==''){
        post_response_text(tujuan, param, respog);
    }
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById("jenisangsuran").innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function loaddatakar(){
  karyawanid  = document.getElementById('karyawanid').value;

    param   ='method=loaddatakar';     
    param  +='&karyawanid=' + karyawanid;
    tujuan  ='sdm_slave_angsuran.php';
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
                    data = con.responseText.split("###");
                    document.getElementById("tglMasuk").value = data[0];
                    document.getElementById("tglPengangkatan").value = data[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}
function cekdataubah(){
    karyawanid         =document.getElementById('karyawanid').value;
    notransaksi         =document.getElementById('notransaksi').value;
    jenisangsuran             =document.getElementById('jenisangsuran').value;
    tipeangsuran         =document.getElementById('tipeangsuran').value;
    
    bulandari        =document.getElementById('bulandari').value;
    bulansampai   =document.getElementById('bulansampai').value;
    tothutang           =document.getElementById('tothutang').value.replace(/,/gi,'');
    rpbulan      =document.getElementById('rpbulan').value.replace(/,/gi,'');
    ket      =document.getElementById('ket').value;

        data   ='notransaksi=' + notransaksi;
        data  +='&karyawanid=' + karyawanid;
        data  +='&jenisangsuran=' + jenisangsuran;
        data  +='&tipeangsuran=' + tipeangsuran;
        data  +='&bulandari=' + bulandari;
        data  +='&bulansampai=' + bulansampai;
        data  +='&tothutang=' + tothutang;
        data  +='&rpbulan=' + rpbulan;
        data  +='&ket=' + ket;

    if(data==document.getElementById('datasblm').value){
         document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=dtlForm onclick=getdetail()>Proses List Detail</button><button class=mybutton id=saveFormx onclick=saveForm()>Simpan</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";
    }else{
        document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=dtlForm onclick=getdetail()>Proses List Detail</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";
    }
}

function del(notransaksi){
    param   ='method=delete'+'&notransaksi='+notransaksi;
    tujuan  ='sdm_slave_angsuran.php';

    if(confirm("Hapus data dengan notransaksi = "+notransaksi+"?"))
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
                    alert("Data Berhasil dihapus !!!");
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

function postdata(notransaksi){
    param   ='method=postdata'+'&notransaksi='+notransaksi;
    tujuan  ='sdm_slave_angsuran.php';

    if(confirm("Post data dengan notransaksi = "+notransaksi+" ?"))
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
                    alert("Data Berhasil diposting !!!");
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

function posttopup(notransaksi,nomotopup,tipeangsuran){
    param   ='method=posttopup'+'&notransaksi='+notransaksi+'&nomotopup='+nomotopup+'&tipeangsuran='+tipeangsuran;
    tujuan  ='sdm_slave_angsuran.php';

    if(confirm("Post data dengan nomor top-up = "+nomotopup+" ?"))
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
                    alertify.popup().destroy();
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

function deltopup(notransaksi,nomotopup,tipeangsuran){
    param   ='method=deltopup'+'&notransaksi='+notransaksi+'&nomotopup='+nomotopup+'&tipeangsuran='+tipeangsuran;
    tujuan  ='sdm_slave_angsuran.php';

    if(confirm("Hapus data dengan nomor top-up = "+nomotopup+" ?"))
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
                    alertify.popup().destroy();
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

function hitunghutangsisa(notransaksi){
    bulanmulaiperubahan         =document.getElementById('bulanmulaiperubahan').value;
    jenistopup         =document.getElementById('jenistopup').value;
    param   ='method=hitunghutangsisa'+'&notransaksi='+notransaksi+'&bulanmulaiperubahan='+bulanmulaiperubahan;
    tujuan  ='sdm_slave_angsuran.php';

    if(jenistopup==1)
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
                    document.getElementById('tothutangtopup').value=con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
                hapus();
            }
        }   
    }   
}
function getdetail(){
    karyawanid         =document.getElementById('karyawanid').value;
    notransaksi         =document.getElementById('notransaksi').value;
    jenisangsuran             =document.getElementById('jenisangsuran').value;
    tipeangsuran         =document.getElementById('tipeangsuran').value;
    
    bulandari        =document.getElementById('bulandari').value;
    bulansampai   =document.getElementById('bulansampai').value;
    tothutang           =document.getElementById('tothutang').value.replace(/,/gi,'');
    rpbulan      =document.getElementById('rpbulan').value.replace(/,/gi,'');
    ket      =document.getElementById('ket').value;

    method              =document.getElementById('method').value;

    if(notransaksi=='' && method !='insert')
    {
        alert('Notransaksi Kosong');return;
    }
    else if(karyawanid=='')
    {
        alert('Harap Pilih Karyawan');return;
    }
    else if(jenisangsuran=='')
    {
        alert('Harap Pilih Jenis Angsuran');return;
    }
    else if(tipeangsuran=='')
    {
        alert('Harap Pilih Tipe Angsuran');return;
    }
    else if(bulandari=='')
    {
        alert('Harap Pilih Bulan Awal');return;
    }
    else if(bulansampai=='')
    {
        alert('Harap Pilih Bulan Akhir');return;
    }
    else if(tothutang =='')
    {
        alert('Harap Mengisi Total Hutang');return;
    }
    else if(rpbulan =='')
    {
        alert('Harap Mengisi Rupiah/Bulan');return;
    }
    else if(ket =='')
    {
        alert('Harap Mengisi Keterangan');return;
    }
    
        if(!cekIsianAngsuran()){return;}
        method='getdetail';
        //pengganti tidak boleh kosong di batasi di Slave
        param   ='notransaksi=' + notransaksi;
        param  +='&karyawanid=' + karyawanid;
        param  +='&jenisangsuran=' + jenisangsuran;
        param  +='&tipeangsuran=' + tipeangsuran;
        param  +='&bulandari=' + bulandari;
        param  +='&bulansampai=' + bulansampai;
        param  +='&tothutang=' + tothutang;
        param  +='&rpbulan=' + rpbulan;
        param  +='&ket=' + encodeURIComponent(ket);
        param  +='&method=' + method;
        tujuan  ='sdm_slave_angsuran.php';

        data   ='notransaksi=' + notransaksi;
        data  +='&karyawanid=' + karyawanid;
        data  +='&jenisangsuran=' + jenisangsuran;
        data  +='&tipeangsuran=' + tipeangsuran;
        data  +='&bulandari=' + bulandari;
        data  +='&bulansampai=' + bulansampai;
        data  +='&tothutang=' + tothutang;
        data  +='&rpbulan=' + rpbulan;
        data  +='&ket=' + ket;

        post_response_text(tujuan, param, respon);
    

    function respon()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('datadetail').innerHTML=con.responseText;
                    document.getElementById('datasblm').value=data;
                    document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=dtlForm onclick=getdetail()>Proses List Detail</button><button class=mybutton id=saveFormx onclick=saveForm()>Simpan</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function saveForm(){
    karyawanid         =document.getElementById('karyawanid').value;
    notransaksi         =document.getElementById('notransaksi').value;
    jenisangsuran             =document.getElementById('jenisangsuran').value;
    tipeangsuran         =document.getElementById('tipeangsuran').value;
    
    bulandari        =document.getElementById('bulandari').value;
    bulansampai   =document.getElementById('bulansampai').value;
    tothutang           =document.getElementById('tothutang').value.replace(/,/gi,'');
    rpbulan      =document.getElementById('rpbulan').value.replace(/,/gi,'');
    ket      =document.getElementById('ket').value;

    totalcountdetail      =document.getElementById('totaldetail').value;
    

    method              =document.getElementById('method').value;

    if(notransaksi=='' && method !='insert')
    {
        alert('Notransaksi Kosong');return;
    }
    else if(karyawanid=='')
    {
        alert('Harap Pilih Karyawan');return;
    }
    else if(jenisangsuran=='')
    {
        alert('Harap Pilih Jenis Angsuran');return;
    }
    else if(tipeangsuran=='')
    {
        alert('Harap Pilih Tipe Angsuran');return;
    }
    else if(bulandari=='')
    {
        alert('Harap Pilih Bulan Awal');return;
    }
    else if(bulansampai=='')
    {
        alert('Harap Pilih Bulan Akhir');return;
    }
    else if(tothutang =='')
    {
        alert('Harap Mengisi Total Hutang');return;
    }
    else if(rpbulan =='')
    {
        alert('Harap Mengisi Rupiah/Bulan');return;
    }
    else if(ket =='')
    {
        alert('Harap Mengisi Keterangan');return;
    }
    else if(totalcountdetail =='' || totalcountdetail ==0)
    {
        alert('Data detail masih kosong');return;
    }
    
        if(!cekIsianAngsuran() || !cekDetailAngsuran()){return;}
        //pengganti tidak boleh kosong di batasi di Slave
        param   ='notransaksi=' + notransaksi;
        for (var i = 1; i <= totalcountdetail; i++) {
            param += "&" + 'bulan'+ i + "=" + document.getElementById('bulan_'+i).innerHTML;
            param += "&" + 'rpbulandet'+ i + "=" + document.getElementById('rpdetailx_'+i).value.replace(/,/gi,'');
        }
        param  +='&karyawanid=' + karyawanid;
        param  +='&jenisangsuran=' + jenisangsuran;
        param  +='&tipeangsuran=' + tipeangsuran;
        param  +='&bulandari=' + bulandari;
        param  +='&bulansampai=' + bulansampai;
        param  +='&tothutang=' + tothutang;
        param  +='&rpbulan=' + rpbulan;
        param  +='&ket=' + encodeURIComponent(ket);
        param  +='&totalcountdetail=' + totalcountdetail;
        param  +='&method=' + method;
        tujuan  ='sdm_slave_angsuran.php';
        post_response_text(tujuan, param, respon);
    

    function respon()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    hapus();
                    loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
        
    }
}

function addTopup(notransaksi,tipeangsuran){
    jenistopup             =document.getElementById('jenistopup').value;
    bulanmulaiperubahan         =document.getElementById('bulanmulaiperubahan').value;
    bulansampaitopup        =document.getElementById('bulansampaitopup').value;
    tothutangtopup   =document.getElementById('tothutangtopup').value.replace(/,/gi,'');
    rpbulantopup           =document.getElementById('rpbulantopup').value.replace(/,/gi,'');
    

    method              ='inserttopup';

    if(notransaksi=='')
    {
        alert('Notransaksi Kosong');return;
    }
    else if(jenistopup=='')
    {
        alert('Harap Pilih Jenis Topup');return;
    }
    else if(bulanmulaiperubahan=='')
    {
        alert('Harap Pilih Bulan Mulai Perubahan');return;
    }
    else if(bulansampaitopup=='')
    {
        alert('Harap Pilih Tipe Bulan Sampai Topup');return;
    }
    else if(tothutangtopup=='' || tothutangtopup==0)
    {
        alert('Harap Input data Total Topup');return;
    }
    else if(tipeangsuran=='angsurannominal' && rpbulantopup==0 && jenistopup==0)
    {
        alert('Harap Input data Rupiah/Bulan Angsuran');return;
    }

        param  +='&notransaksi=' + notransaksi;
        param  +='&jenistopup=' + jenistopup;
        param  +='&bulanmulaiperubahan=' + bulanmulaiperubahan;
        param  +='&bulansampaitopup=' + bulansampaitopup;
        param  +='&tothutangtopup=' + tothutangtopup;
        param  +='&rpbulantopup=' + rpbulantopup;
        param  +='&tipeangsuran=' + tipeangsuran;
        param  +='&method=' + method;
        tujuan  ='sdm_slave_angsuran.php';
        post_response_text(tujuan, param, respon);
    

    function respon()
    {
        if(con.readyState==4)
        {
            if (con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alertify.popup().destroy();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}


function previewDetail(notransaksi){
    param   =  'method=preview';
    param   += '&notransaksi=' + notransaksi;
    tujuan  =  'sdm_slave_angsuran.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup('Detail Angsuran',"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('100%','70%');
                    $(document).ready(function() {
                        $('.select2').select2({
                            // dropdownAutoWidth:true,
                            // width: 'auto'
                        });
                        $('.select2-selection--single').height(30).css({
                            cursor: "auto"
                        });
                        $('.select2-selection__arrow b').css({
                            top: "70%"
                        });
                        $('.select2-selection__rendered').css({
                            'line-height': '30px'
                        });
                    });
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function close_a(notransaksi,periodeakhir){
    param   =  'method=close_a';
    param   += '&notransaksi=' + notransaksi;
    param   += '&periodeakhir=' + periodeakhir;
    tujuan  =  'sdm_slave_angsuran.php';
    // post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    getPage();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

    alertify.confirm("Info","Akan dilakukan close untuk transaksi nomor "+notransaksi+". Anda yakin ?",
    function(){
        post_response_text(tujuan, param, respon);
    },
    function(){
        return;
    }
    );


}
function topup(notransaksi){
    param   =  'method=topup';
    param   += '&notransaksi=' + notransaksi;
    tujuan  =  'sdm_slave_angsuran.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup('Top-Up Angsuran',"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('100%','70%');
                    $(document).ready(function() {
                        $('.select2').select2({
                            dropdownAutoWidth:true,
                            width: 'auto'
                        });
                        $('.select2-selection--single').height(30).css({
                            cursor: "auto"
                        });
                        $('.select2-selection__arrow b').css({
                            top: "70%"
                        });
                        $('.select2-selection__rendered').css({
                            'line-height': '30px'
                        });
                    });
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function edit(notransaksi,status){
    document.getElementById('addNew').style.display ='block';
    document.getElementById('listData').style.display ='none';
    document.getElementById('method').value='update';

    param = 'method=getedit';
    param += '&notransaksi=' + notransaksi;
    tujuan  ='sdm_slave_angsuran.php';
    post_response_text(tujuan, param, respog);
    
    function respog()
    {
        if(con.readyState==4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    isiForm = true;
                    arrx    = con.responseText.split("$$$");
                    dataht  =arrx[0].split('###');
                    datadt  =arrx[1];
                    arrjenisangsuran=arrx[2];

                    document.getElementById('notransaksi').value=trim(dataht[0]);

                    setValue2('karyawanid',trim(dataht[1]));
                    document.getElementById("jenisangsuran").innerHTML = arrjenisangsuran;
                    setValue('jenisangsuran',trim(dataht[2]));
                    setValue2('tipeangsuran',trim(dataht[3]));
                    setValue2('bulandari',trim(dataht[4]));
                    setValue2('bulansampai',trim(dataht[5]));
                    document.getElementById('tothutang').value=dataht[6];
                    document.getElementById('rpbulan').value=dataht[7];
                    //nominal diberi pemisah ribuan seperti saat diketik
                    if(trim(dataht[6])!=''){ z.numberFormat('tothutang',2); }
                    if(trim(dataht[7])!=''){ z.numberFormat('rpbulan',2); }
                    document.getElementById('ket').value=dataht[8];
                    isiForm = false;
                    document.getElementById('karyawanid').disabled=true;
                    document.getElementById('jenisangsuran').disabled=true;
                    document.getElementById('bulandari').disabled=true;


                    document.getElementById('datadetail').innerHTML=datadt;
                    
                    if(status==1){
                        document.getElementById('bulansampai').disabled=false;
                        document.getElementById('tipeangsuran').disabled=true;
                        document.getElementById('tothutang').disabled=true;
                        document.getElementById('rpbulan').disabled=true;
                        document.getElementById('ket').disabled=true;
                        document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=saveFormx onclick=saveForm()>Simpan</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";    
                    }else{
                        document.getElementById('bulansampai').disabled=false;
                        document.getElementById('tipeangsuran').disabled=false;
                        document.getElementById('tothutang').disabled=false;
                        document.getElementById('ket').disabled=false;
                        document.getElementById('tmblHeader').innerHTML="<button class=mybutton id=dtlForm onclick=getdetail()>Proses List Detail</button><button class=mybutton id=saveFormx onclick=saveForm()>Simpan</button><button class=mybutton id=cancelForm onclick=cancelForm()>Batal</button>";
                    }

                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }   
    } 
}

function pdf(notransaksi){
    param   =  'method=pdf';
    param   += '&notransaksi=' + notransaksi;
    tujuan  =  'sdm_slave_angsuran.php?'+param;
    judul   =  'Report PDF ' + notransaksi;
    ev      ='event';
    closeDialog();
    alertify.popuppdf(judul,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}


function loadfiles(notransaksi, karyawanid) {
param = "";
param += "notransaksi=" + notransaksi;
param += "&karyawanid=" + karyawanid;
param += '&method=loadfiles';
tujuan = 'sdm_slave_angsuran.php';
post_response_text(tujuan, param, respog);
function respog() {
    if (con.readyState == 4) {
        if (con.status == 200) {
            busy_off();
            if (!isSaveResponse(con.responseText)) {
                alert(con.responseText);
            } else {
                if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                }
                if (document.getElementById('loadfilesdetail') !== null) {
                    document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                }
            }
        } else {
            busy_off();
            error_catch(con.status);
        }
    }
}
}


function showformupload(ev) {
	title = "UPLOAD FILES";
	width = '';
	height = '';
	content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
	showDialog2(title, content, width, height, ev);

	pos = new Array();
	pos = getMouseP(ev);

	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, notransaksi, karyawanid) {
	showformupload(ev);
        
	param = "";
	param += "notransaksi=" + notransaksi;
	param += "&karyawanid=" + karyawanid;
	param += '&method=showupload';
	tujuan = 'sdm_slave_angsuran.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contUpload').innerHTML = con.responseText;
					loadfiles(notransaksi,karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksix').innerHTML;
	var karyawanid = document.getElementById('karyawanidx').innerHTML;
	var formdata = new FormData();


	formdata.append("notransaksi", notransaksi);
	formdata.append("karyawanid", karyawanid);
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));

	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST","sdm_slave_angsuran.php?method=submitfile", true);
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
					alert('Uploaded Success.');
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, karyawanid,namafile) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&karyawanid=" + karyawanid;
	param += "&namafile=" + namafile;

	tujuan = 'sdm_slave_angsuran.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi,karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function viewfile(namafile,sumber) {
	//formupload();
	param = 'method=viewfile&namafile=' + namafile;
	tujuan = 'sdm_slave_angsuran.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//document.getElementById('contviewupload').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}