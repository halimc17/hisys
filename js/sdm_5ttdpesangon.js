var tujuan = "sdm_slave_5ttdpesangon.php";

function loadData(page) {
    param = "";
    param += "method=loadData";
    param += "&page=" + page;

    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4) {
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('listForm').innerHTML = con.responseText;
                    leftFixedTable();
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

function save() {
    id = getValue('id');
    karyawan = getValue('karyawan');
    level = getValue('level');
    tipe = getValue('tipe');
    unit = getValue('unit');
    ket = getValue('ket');
    method = getValue('method');
    
    param = "";
    param += `method=${method}`;
    param += '&karyawan=' + karyawan + '&level=' + level + '&tipe=' + tipe + '&unit=' + unit + '&ket=' + ket + '&id=' + id;

    /* Validasi */
    if (karyawan == '') {
        alertify.alert('karyawan tidak boleh kosong!');
        return false;
    }
    if (level == '') {
        alertify.alert('level tidak boleh kosong!');
        return false;
    }
    if (tipe == '') {
        alertify.alert('tipe tidak boleh kosong!');
        return false;
    }
    if (unit == '') {
        alertify.alert('unit tidak boleh kosong!');
        return false;
    }
    if (ket == '') {
        alertify.alert('keterangan tidak boleh kosong!');
        return false;
    }
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4) {
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    batal();
                    loadData();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function deleteData(id){
    param = "";
    param += "method=deleteData";
    param += "&id=" + id;

    if (confirm("Are You Sure Delete This Data ?")) {
        post_response_text(tujuan, param, respog);
    }
    function respog(){
        if(con.readyState==4) {
            if(con.status == 200){
                busy_off();
                if(!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    loadData(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function batal() {
    add = document.querySelectorAll('.add');
    add.forEach(el => {
        if (el.classList.contains('select2')) {
            setValue2(el.id, '')
        }else{
            el.value = '';
        }
    });
    document.getElementById('method').value = 'insert';
}

function edit(id,karyawan,level,tipe,unit,keterangan) {
    setValue('id', id)
    setValue('karyawan', karyawan)
    setValue2('level', level)
    setValue2('tipe', tipe)
    setValue2('unit', unit)
    setValue('ket', keterangan)
    setValue('method', 'update')
}