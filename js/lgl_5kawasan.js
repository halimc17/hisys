function loaddata() {
    param = 'method=loaddata';
    tujuan = 'lgl_5kawasan_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
                            paging: false,
                            // "pagingType": "simple_numbers",
                            // columnDefs: [
                            // {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
                            // ],
                            // drag kolom
                            //colReorder: true,
                            // jumlah per page
                            // "iDisplayLength": 1,
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
                            buttons: ['searchBuilder', 'csv', 'excel', 'print', {
                                text: 'New',
                                action: function () {
                                    newdata('New Data');
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

    tujuan = 'lgl_5kawasan_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup(jenis, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('55%', '80%');
                    $(document).ready(function () {
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



function editdata(jenis, idx, jenis) {
    param = '';
    param += '&mode=update';
    param += '&method=addnew';

    tujuan = 'lgl_5kawasan_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup(jenis, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('55%', '80%');
                    $(document).ready(function () {
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
                            'line-height': '31px'
                        });
                    });

                    setValue2('idx', idx);
                    setValue('jenis', jenis);
                    setValue2('method', 'update');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan() {
    idx = document.getElementById('idx').value;
    jenis = document.getElementById('jenis').value;
    method = document.getElementById('method').value;


    param = '';
    param += '&idx=' + idx;
    param += '&jenis=' + jenis;
    param += '&method=' + method;


    tujuan = 'lgl_5kawasan_slave.php';
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