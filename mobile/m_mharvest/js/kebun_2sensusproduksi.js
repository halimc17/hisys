function listAction(getPage) {
    tujuan = $.options.slave + getPage;
    let options = {
        url: tujuan,
        title: 'Detail Sensus Produksi',
        success: function (arg) {
            console.log(arg);
        }
    };
    winUpdate = $.openWindow(options);
}