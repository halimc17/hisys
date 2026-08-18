function loaddata() {
    param = 'method=loaddata';
    tujuan = 'log_5regionalprocurement_slave.php';

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

function newdata(namaRegion,unitRegion) {
    param = '';
    param += '&namaRegion=' + namaRegion;
    param += '&unitRegion=' + unitRegion;
    param += '&method=addnew';

    tujuan = 'log_5regionalprocurement_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup(namaRegion, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('55%', '50%');
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

                    setValue2('namaRegion', '');
                    setValue2('unitRegion', '');
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
    idRegion = document.getElementById('idRegion').value;
    namaRegion = document.getElementById('namaRegion').value;
    ptRegion = document.getElementById('pt').value;
    unitRegion = document.getElementById('unitRegion').value;
    method = document.getElementById('mode').value;

    validate([
        ["namaRegion", "Nama Regional tidak boleh kosong."],
        ["pt", "PT tidak boleh kosong."],
        ["unitRegion", "Unit Regional tidak boleh kosong"]
    ]);

    param = 'namaRegion=' + namaRegion;
    param += '&ptRegion=' + ptRegion;
    param += '&unitRegion=' + unitRegion;
    param += '&idRegion=' + idRegion;
    param += '&method=' + method;
    tujuan = 'log_5regionalprocurement_slave.php';

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

function editdata(header, idRegion, namaRegion, ptRegion, unitRegion) {
    param = 'method=addnew';

    tujuan = 'log_5regionalprocurement_slave.php';
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

                    setValue2('idRegion', idRegion);
                    setValue2('namaRegion', namaRegion);
                    setValue2('pt', ptRegion);
                    setValue2('unitRegion', unitRegion);
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
    param += '&idRegion=' + id;
    tujuan = 'log_5regionalprocurement_slave.php';

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

function getUnitRegion() {
    var pt = document.getElementById('pt').value

    param = 'method=getUnitRegion';
    param += '&pt=' + pt;
    tujuan = 'log_5regionalprocurement_slave.php';

    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    /* Success Response */
                    document.getElementById('unitRegion').innerHTML = con.responseText
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}