
function postingAction(getPage) {
    tujuan = $.options.slave + getPage;
    let ele = $.dataAction.target;
    $.Confirm('Anda yakin POSTING data ini? ', function () {
        $.get(ele, tujuan, function callback(Result) {
            if (!Result.response.error) {
                //Result.element.remove();
                $.refresh();
            } else {
                $.Alert(Result.response.message);
            }
        });
    });
}
var winUpdate;
function listAction(getPage) {
    tujuan = $.options.slave + getPage;
    let options = {
        url: tujuan,
        title: 'Detail Buku Panen',
        success: function (arg) {
            console.log(arg);
        }
    };
    winUpdate = $.openWindow(options);
}
function pascaSubmit(Result) {
    if (!Result.response.error) {
        $.refresh();
        if (typeof winUpdate != 'undefined') {
            $.clearNewContainer();
        } else {
            $.buatbaru.close();
        }

    } else {
        $.Alert(Result.response.message);
    }
}
function pascaSubmitSearch(Result) {
}

function deleteDtl() { }

function editDtl(id) {
    // console.log("masuk");
    const editBtn = document.getElementById('editDtlBtn' + id);
    const deleteBtn = document.getElementById('deleteDtlBtn' + id);
    const saveBtn = document.getElementById('saveDtlBtn' + id);
    const cancelBtn = document.getElementById('cancelDtlBtn' + id);

    editBtn.addEventListener('click', () => {
        editBtn.style.display = 'none';
        deleteBtn.style.display = 'none';
        saveBtn.style.display = 'inline';
        cancelBtn.style.display = 'inline';
        for (let i = 1; i < 6; i++) {
            document.getElementById('inputDtlrw' + id + i).disabled = false;
            console.log('inputDtlrw' + id + i);
        }
    });
}

function onchangeinput(index, length) {
    console.log(index, length);
    num = 0;
    for (let i = 1; i <= length; i++) {
        val = document.getElementById('inputDtlrw' + index + i).value;
        if (val != null && val != "") {
            num = num + parseInt(val);
        }
    }
    var totJjg = document.getElementById("hasilkerja" + index);
    totJjg.innerHTML = num.toString();
}

function validateSaveDetail(id) {
    const nilai = document.getElementById('hasilkerja' + id).innerText;
    num = 0;

    for (let i = 1; i < 6; i++) {
        val = document.getElementById('inputDtlrw' + id + i).value;
        if (val != null && val != "") {
            num = num + parseInt(val);
        }
    }

    if (num <= parseInt(nilai)) {
        return true;
    }
    return false;
}

function updateTotJjg(tot, id) {
    const noTxn = document.getElementById('notransid' + id).innerText;
    const kodeorg = document.getElementById('kodeorgid' + id).innerText;
    const tph = document.getElementById('tphid' + id).innerText;
    const sesi = document.getElementById('idsesi' + id).innerText;

    raw = "?switcher=updateTotJjg&notransaksi=" + noTxn + "&kodeorg=" + kodeorg + "&tph=" + tph + "&sesi=" + sesi + "&totJjg=" + tot;
    tujuan = $.options.slave + raw;
    let ele = $.dataAction.target;
    $.get(ele, tujuan, function callback(Result) {
        console.log(Result);
        if (!Result.response.error) {
            //Result.element.remove();
            $.refresh();
        } else {
            $.Alert(Result.response.message);
        }
    });
}

function saveDtl(id) {
    const noTxn = document.getElementById('notransid' + id).innerText;
    const kodeorg = document.getElementById('kodeorgid' + id).innerText;
    const tph = document.getElementById('tphid' + id).innerText;
    const sesi = document.getElementById('idsesi' + id).innerText;
    idjenis = "";
    tot = 0;
    // LOOPING GET VALUE AND IDMUTU

    $.Confirm('Anda yakin menyimpan data ini? ', function () {
        // $valid = validateSaveDetail(id);

        for (let i = 1; i < 6; i++) {
            console.log('MULAI ADD DATA');
            raw = "?switcher=savedetail&notransaksi=" + noTxn + "&kodeorg=" + kodeorg + "&tph=" + tph + "&sesi=" + sesi;
            val = document.getElementById('inputDtlrw' + id + i).value;
            switch (i) {
                case i = 1:
                    idjenis = "8";
                    break;
                case i = 2:
                    idjenis = "9";
                    break;
                case i = 3:
                    idjenis = "10";
                    break;
                case i = 4:
                    idjenis = "11";
                    break;
                case i = 5:
                    idjenis = "13";
                    break;
                default:
                //code block
            }
            if (val != null && val != "") {
                tot = tot + parseInt(val);
                param = raw + "&idjenis=" + idjenis + "&mhvalue=" + val;
                console.log("EXECUTING PROCCESS WITH PARAMS: " + param);
                tujuan = $.options.slave + param;
                let ele = $.dataAction.target;
                $.get(ele, tujuan, function callback(Result) {
                    console.log(Result);
                    if (!Result.response.error) {
                        //Result.element.remove();
                        $.refresh();
                    } else {
                        $.Alert(Result.response.message);
                    }
                });
            }
        }
        // console.log(tot);
        updateTotJjg(tot, id);
        $.Alert('Berhasil ubah data!');

        //! With validation
        // if (!$valid) {
        //     $.Alert('Jumlah lebih dari hasil kerja!');
        // } else {
        //     for (let i = 1; i < 6; i++) {
        //         console.log('MULAI ADD DATA');
        //         raw = "?switcher=savedetail&notransaksi=" + noTxn + "&kodeorg=" + kodeorg + "&tph=" + tph + "&sesi=" + sesi;
        //         val = document.getElementById('inputDtlrw' + id + i).value;
        //         switch (i) {
        //             case i = 1:
        //                 idjenis = "8";
        //                 break;
        //             case i = 2:
        //                 idjenis = "9";
        //                 break;
        //             case i = 3:
        //                 idjenis = "10";
        //                 break;
        //             case i = 4:
        //                 idjenis = "11";
        //                 break;
        //             case i = 5:
        //                 idjenis = "13";
        //                 break;
        //             default:
        //             //code block
        //         }
        //         if (val != null && val != "") {
        //             param = raw + "&idjenis=" + idjenis + "&mhvalue=" + val;
        //             console.log("EXECUTING PROCCESS WITH PARAMS: " + param);
        //             tujuan = $.options.slave + param;
        //             let ele = $.dataAction.target;
        //             $.get(ele, tujuan, function callback(Result) {
        //                 console.log(Result);
        //                 if (!Result.response.error) {
        //                     //Result.element.remove();
        //                     $.refresh();
        //                 } else {
        //                     $.Alert(Result.response.message);
        //                 }
        //             });
        //         }
        //     }
        //     $.Alert('Berhasil ubah data!');
        //     cancelEditDtl(id);
        //     cancelEditDtl(id);
        // }
    });

}

function postingKebun(id) {
    const txn = document.getElementById('tmtxn' + id).innerText;
    const kodeorg = document.getElementById('tmorgcd' + id).innerText;
    const gang = document.getElementById('tmgangcd' + id).innerText;
    const nikmandor = document.getElementById('tmnikmandor' + id).innerText;

    param = "?switcher=posting&notransaksi=" + txn + "&kodeorg=" + kodeorg + "&gangcode=" + gang + "&nikmandor=" + nikmandor;
    // param = "?switcher=posting&notransaksi=" + notxn;
    // console.log(param);
    tujuan = $.options.slave + param;
    let ele = $.dataAction.target;
    $.Confirm('Anda yakin POSTING data ini? ', function () {
        $.get(ele, tujuan, function callback(Result) {
            if (!Result.response.error) {
                //Result.element.remove();
                $.refresh();
            } else {
                $.Alert(Result.response.message);
            }
        });
    });
}

function unpostingSync(id) {
    const txn = document.getElementById('tmtxn' + id).innerText;
    const kodeorg = document.getElementById('tmorgcd' + id).innerText;
    const gang = document.getElementById('tmgangcd' + id).innerText;
    const nikmandor = document.getElementById('tmnikmandor' + id).innerText;

    param = "?switcher=unSync&notransaksi=" + txn + "&kodeorg=" + kodeorg;
    // param = "?switcher=posting&notransaksi=" + notxn;
    // console.log(param);
    tujuan = $.options.slave + param;
    let ele = $.dataAction.target;
    $.Confirm('Anda yakin UN-POSTING data synchronize ini? ', function () {
        $.get(ele, tujuan, function callback(Result) {
            if (!Result.response.error) {
                //Result.element.remove();
                $.refresh();
            } else {
                $.Alert(Result.response.message);
            }
        });
    });
}

function unpostingKebun(id) {
    const txn = document.getElementById('tmtxn' + id).innerText;
    const kodeorg = document.getElementById('tmorgcd' + id).innerText;
    const gang = document.getElementById('tmgangcd' + id).innerText;
    const nikmandor = document.getElementById('tmnikmandor' + id).innerText;

    param = "?switcher=unposting&notransaksi=" + txn + "&kodeorg=" + kodeorg + "&gangcode=" + gang + "&nikmandor=" + nikmandor;
    // param = "?switcher=posting&notransaksi=" + notxn;
    // console.log(param);
    tujuan = $.options.slave + param;
    let ele = $.dataAction.target;
    $.Confirm('Anda yakin POSTING data ini? ', function () {
        $.get(ele, tujuan, function callback(Result) {
            if (!Result.response.error) {
                //Result.element.remove();
                $.refresh();
            } else {
                $.Alert(Result.response.message);
            }
        });
    });
}

function cancelEditDtl(id) {
    const editBtn = document.getElementById('editDtlBtn' + id);
    const deleteBtn = document.getElementById('deleteDtlBtn' + id);
    const saveBtn = document.getElementById('saveDtlBtn' + id);
    const cancelBtn = document.getElementById('cancelDtlBtn' + id);

    cancelBtn.addEventListener('click', () => {
        editBtn.style.display = 'inline';
        deleteBtn.style.display = 'inline';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
        for (let i = 1; i < 6; i++) {
            document.getElementById('inputDtlrw' + id + i).disabled = true;
            console.log('inputDtlrw' + id + i);
        }
    });
}