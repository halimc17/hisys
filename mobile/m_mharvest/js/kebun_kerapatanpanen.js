function listAction(getPage) {
    tujuan = $.options.slave + getPage;
    let options = {
        url: tujuan,
        title: 'Detail Kerapatan Panen',
        success: function (arg) {
            console.log(arg);
        }
    };
    winUpdate = $.openWindow(options);
}