function loaddata() {
    param = 'method=loaddata';
    tujuan = 'log_5kategoribarang_slave.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
                    $(document).ready(function () {
                        var table = $('#mytable').DataTable({
                            // supaya tidak ada overflow horisontal
                            // responsive: true,
                            // fixedColumns:   {
                            // leftColumns: 1,
                            // rightColumns: 2
                            // },
                            ordering: true,
                            fixedHeader: true,
                            // pake paging atau tidak
                            paging: true,
                            // columnDefs: [
                            // {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
                            // ],
                            // drag kolom
                            //colReorder: true,
                            // jumlah per page
                            "iDisplayLength": 20,
                            // tinggi / height
                            scrollY: '60vh',
                            scrollX: true,
                            scrollCollapse: true,
                            dom: 'Bfrtip',
                            //select: true,

                            language: {
                                searchBuilder: {
                                    title: 'Filter',
                                    button: 'Filter'
                                }
                            },
                            buttons: ['csv', 'excel', 'print', {
                                text: 'New',
                                action: function () {
                                    newdata('new');
                                }
                            }
                            ]
                        });

                        //double click untuk freeze column
                        $(table.table().container()).on('dblclick', 'td', function () {
                            var row = table.column(this);
                            new $.fn.dataTable.FixedColumns(table, {
                                leftColumns: row.index() + 1
                                //   rightColumns: 1
                            });
                            //console.log('Row Index = ' + row.index());
                        });

                        //right click untuk freeze column
                        $(table.table().container()).on('dblclick', 'th', function () {
                            var row = table.column(this);
                            new $.fn.dataTable.FixedColumns(table, {
                                leftColumns: row.index() + 1
                            });
                            //console.log('Row Index = ' + row.index());
                        });
                    });

                    // leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function newdata(jenis) {
    param = '';
    param += '&jenis=' + jenis;
    param += '&method=addnew';

    tujuan = 'log_5kategoribarang_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup(jenis, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('55%', '50%');
                    $(document).ready(function () {
                        $('.select2').select2({
                            dropdownAutoWidth: false
                        });
                        $('.select2-selection--single').height(30).css({
                            cursor: "auto"
                        });
                        $('.select2-selection__arrow b').css({
                            top: "70%"
                        });
                        $('.select2-selection__rendered').css({
                            'line-height': '31px'
                        });
                    });

                    setValue2('jenis', '');
                    setValue2('keterangan', '');
                    setValue2('color', '');
                    setValue2('mode', 'insert');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan() {
    id         = document.getElementById('id').value;
    jenis      = document.getElementById('jenis').value;
    keterangan = document.getElementById('keterangan').value;
    color = document.getElementById('color').value;
    method     = document.getElementById('mode').value;

    validate([
        ["jenis", "Jenis tidak boleh kosong."],
        ["keterangan", "Keterangan tidak boleh kosong"],
        ["color", "Warna tidak boleh kosong"]
    ]);

    param = 'jenis=' + jenis;
    param += '&keterangan=' + keterangan;
    param += '&color=' + color;
    param += '&id=' + id;
    param += '&method=' + method;
    tujuan = 'log_5kategoribarang_slave.php';

    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.alert("Done");
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function editdata(header,id,jenis,keterangan,color) {
    param = 'method=addnew';    
    
    tujuan = 'log_5kategoribarang_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup(header, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('55%', '50%');
                    $(document).ready(function () {
                        $('.select2').select2({
                            dropdownAutoWidth: false
                        });
                        $('.select2-selection--single').height(30).css({
                            cursor: "auto"
                        });
                        $('.select2-selection__arrow b').css({
                            top: "70%"
                        });
                        $('.select2-selection__rendered').css({
                            'line-height': '31px'
                        });
                    });

                    setValue2('id', id);
                    setValue2('jenis', jenis);
                    setValue2('keterangan', keterangan);
                    setValue2('color', color);
                    setValue2('mode', 'ubah');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function del(id) {
    param = 'method=hapus';
    param += '&id=' + id;
    tujuan = 'log_5kategoribarang_slave.php';

    alertify.confirm("Delete", "Anda yakin?",
        function () {
            post_response_text(tujuan, param, respog);
        },
        function () {
            return;
        }
    );

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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