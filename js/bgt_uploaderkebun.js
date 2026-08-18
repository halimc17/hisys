// JavaScript Document

//upoad saldo awal
function getFormUplaod(type){
    if(type==''){
        document.getElementById('uForm').style.display='none';
        document.getElementById('sample').innerHTML='';
        document.getElementById('jenisdata').value='';
        
    }
    else{
        document.getElementById('uForm').style.display='';
        document.getElementById('jenisdata').value=type;
    }
    
    switch(type){
    case'SDM':  
        document.getElementById('sample').innerHTML='<b>Pastikan Tahun budget terisi,kode kegiatan,rupiah setahun dan volume setahun tidak kosong dan kodebudget sudah sesuai dengan master budget</b><a href=bgt_slave_getExample.php?form=SDM target=frame>Click here for example</a>'; 
    break;
    case'MATANDTOOL':
        document.getElementById('sample').innerHTML='<b>Pastikan Tahun budget terisi,kode kegiatan,kodebarang,rupiah setahun dan volume setahun tidak kosong dan kodebudget sudah sesuai dengan master budget</b><a href=bgt_slave_getExample.php?form=MATANDTOOL target=frame>Click here for example</a>'; 
    break;
    case'VHC':
        document.getElementById('sample').innerHTML='<b>Pastikan Tahun budget terisi,kode kegiatan,kode vhc,rupiah setahun dan volume setahun tidak kosong dan kodebudget sudah sesuai dengan master budget</b><a href=bgt_slave_getExample.php?form=VHC target=frame>Click here for example</a>'; 
    break;        
    case'KONTRAK':
        document.getElementById('sample').innerHTML='<b>Pastikan Tahun budget terisi,kode kegiatan,rupiah setahun dan volume setahun tidak kosong dan kodebudget sudah sesuai dengan master budget</b><a href=bgt_slave_getExample.php?form=KONTRAK target=frame>Click here for example</a>'; 
    break; 
    }
}
function submitFile(){
    if(confirm('Are you sure..?')){
    document.getElementById('frm').submit();
    }
}

