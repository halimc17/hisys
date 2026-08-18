function postingMutuhancak(notransaksi) {
    param = "?switcher=posting&notransaksi=" + notransaksi;
    tujuan = $.options.slave + param;
    let ele = $.dataAction.target;
    $.Confirm('Anda yakin memposting data ini?', function () {
        $.get(ele, tujuan, function callback(Result) {
            console.log(Result);
            if (!Result.response.error) {
                //Result.element.remove();
                $.refresh();
            } else {
                $.Alert(Result.response.message);
            }
        });
    });
}

function deleteMutuhancak(notransaksi) {
    param = "?switcher=delete&notransaksi=" + notransaksi;
    tujuan = $.options.slave + param;
    let ele = $.dataAction.target;
    $.Confirm('Anda yakin menghapus data ini?', function () {
        $.get(ele, tujuan, function callback(Result) {
            console.log(Result);
            if (!Result.response.error) {
                //Result.element.remove();
                $.refresh();
            } else {
                $.Alert(Result.response.message);
            }
        });
    });
}

function listAction(getPage) {
    tujuan = $.options.slave + getPage;
    let options = {
        url: tujuan,
        title: 'Detail Mutu Hancak',
        success: function (arg) {
            console.log(arg);
        }
    };
    winUpdate = $.openWindow(options);
}