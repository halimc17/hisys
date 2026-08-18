// Function Get PT
function getPt() {
    var regional = document.getElementById('regional').options[document.getElementById('regional').selectedIndex].value;
    const method = "getPt"

    param = "method=" + method + "&regional=" + regional
    tujuan = "log_2procurement_slave.php"

    post_response_text(tujuan,param,respon)

    function respon() {
        if(con.readyState == 4) {
            if(con.status == 200) {
                busy_off()
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText)
                } else {
                    // InnerHTML optionnya
                    document.getElementById("pt").innerHTML = con.responseText
                    getTipeUnit()
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// Function Get Tipe Unit
function getTipeUnit() {
	var regional = document.getElementById('regional').options[document.getElementById('regional').selectedIndex].value;
    var pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    const method = "getTipe"

    param = "method=" + method + "&pt=" + pt+ "&regional=" + regional
    tujuan = "log_2procurement_slave.php"

    post_response_text(tujuan, param, respon)

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off()
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText)
                } else {
                    console.log(con.responseText)
                    // InnerHTML optionnya
                    const data = JSON.parse(con.responseText)
                    document.getElementById("tipeunit").innerHTML = data.tipe;
                    document.getElementById("unit").innerHTML = data.unit;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// Function Get Unit
function getUnit() {
    var tipeunit = document.getElementById('tipeunit').options[document.getElementById('tipeunit').selectedIndex].value;
    var regional = document.getElementById('regional').options[document.getElementById('regional').selectedIndex].value;
    var pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    const method = "getUnit"

    param = "method=" + method + "&tipeunit=" + tipeunit
    param += "&regional=" + regional
    param += "&pt=" + pt
    tujuan = "log_2procurement_slave.php"

    post_response_text(tujuan, param, respon)

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off()
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText)
                } else {
                    // InnerHTML optionnya
                    document.getElementById("unit").innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// Function Preview Data
function preview(aksi) {
    // Validasi 

    validate([
        ["tipereport", "Tipe Report harus di pilih!!"]
    ])
    
    var tipereport = document.getElementById('tipereport').value
    
    if(tipereport == "quaterly") {
        validate([
            ["periodeawal", "Periode Awal tidak boleh kosong"],
            ["periodeakhir", "Periode Akhir tidak boleh kosong"]
        ])
    } else {
        validate([
            ["tahun", "Tahun harus di pilih!!"]
        ])
    }

    // Get Data
    var pt = document.getElementById('pt').value
    var tipeunit = document.getElementById('tipeunit').value
    var regional = document.getElementById('regional').value
    var unit = document.getElementById('unit').value
    
    if(tipereport == "quaterly") {
        var periodeAwl = document.getElementById('periodeawal').value
        var periodeAkhr = document.getElementById('periodeakhir').value
    } else {
        var tahun = document.getElementById('tahun').value
    }
    
    var method = "preview"

    param = "method=" + method
    param += "&pt=" + pt
    param += "&tipeunit=" + tipeunit
    param += "&regional=" + regional
    param += "&unit=" + unit
    param += "&tipereport=" + tipereport
    param += "&aksi=" + aksi
    
    if(tipereport == "quaterly") {
        param += "&periodeawal=" + periodeAwl
        param += "&periodeakhir=" + periodeAkhr
    } else {
        param += "&tahun=" + tahun
    }

    tujuan = 'log_2procurement_slave.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Notifikasi", con.responseText);
                } else {
					document.getElementById('printContainer').innerHTML = "";
                    //document.getElementById('printContainer').innerHTML = con.responseText;
                    // ldlp = con.responseText.split('##');
                    if(aksi == 'html') {
                        const data = JSON.parse(con.responseText);
                        document.getElementById('printContainer').innerHTML = data.table;
                        // xls = JSON.parse(ldlp[1])
                        // xlsa = JSON.parse(ldlp[2])
                        // canvasChart(xls)
                        // leftFixedTable();
                        // showheader();
                        // var labels = ['Mill', 'Spare']
                        // var data = [100,50,20,90,80,70]
                        var datas = data.values;
                        var value = Object.values(datas);
                        var labels = Object.keys(datas);
                        // var warna = Object.keys(datas);
                        var warna = data.warna;
                        console.log(warna)

                        value = value.map(function (each_element) {
                            return Number(each_element.toFixed(0));
                        });
                        // console.log(value);
                        // const value = [5, 10, 15, 35, 20, 51];
                        canvasChart(labels,value,warna);
                    } else {
                        const d = new Date();
                        // const imageLink = document.createElement('a');
                        // const canvas = document.getElementById('myChart');
                        
                        // // imageLink.download = 'canvas.png';
                        // imageLink.download = "Laporan_Chart_Procurement_"+ d +".png";
                        // // imageLink.href = canvas.toDataURL('image/png', 1);
                        // imageLink.href = canvas.toDataURL('image/png', 1);
                        // imageLink.click();

                        // Sudah bisa tampil di excel tapi tidak sempurna
                        // document.getElementById('xxx').innerHTML = "<img src='" + imageLink.getAttribute('href') + "' width=500 height=500>";
                        // document.getElementById('xxx').src = imageLink.getAttribute('href');
                        // Tampilkan Excelnya

                        var canvas = document.getElementById('myChart');

                        var context = canvas.getContext('2d');

                        //cache height and width        
                        var w = canvas.width;
                        var h = canvas.height;

                        var data = context.getImageData(0, 0, w, h);

                        var compositeOperation = context.globalCompositeOperation;

                        context.globalCompositeOperation = "destination-over";
                        context.fillStyle = "#fff";
                        context.fillRect(0, 0, w, h);
                        context.width = 2000;
                        context.height = 1500;

                        canvas.resizeAndExport = function (width, height) {
                            // create a new canvas
                            var c = document.createElement('canvas');
                            // set its width&height to the required ones
                            c.width = width;
                            c.height = height;
                            // draw our canvas to the new one
                            c.getContext('2d').drawImage(this, 0, 0, this.width, this.height, 0, 0, width, height);
                            // return the resized canvas dataURL
                            return c.toDataURL();
                        }

                        var img = new Image();
                        img.src = canvas.resizeAndExport(1000, 1000);
                        // canvas.width = 1000;
                        // canvas.height = 1000;
                        // canvas.style.width = 1000;
                        // canvas.style.height = 1000;

                        var imageData = canvas.toDataURL("image/png");

                        context.clearRect(0, 0, w, h);
                        context.putImageData(data, 0, 0);
                        context.globalCompositeOperation = compositeOperation;
                        // document.body.appendChild(img);
                        var a = document.createElement('a');
                        a.href = img.src;
                        a.download = 'template.png';
                        a.click();

                        printnopopup("log_2procurement_slave.php" + "?" + param);
                        // download();
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

let chart = null

function canvasChart(labels,data,warna) {
    // console.log(resdata)
    // console.log(labels);
    // console.log(data);
    
    const config = {
        type: 'pie',
        data: {
            // labels: ['Mill', 'Spare', 'Pupuk', 'Consumable', 'Logistic'],
            labels: labels,
            datasets: [{
                label: "Data",
                // data: [12, 19, 3, 5, 2, 3],
                data: data,
                borderWidth: 1,
                backgroundColor: warna
            }]
        },
        options: {
            plugins: {
                labels: {
                    render: (args) => {
                        if(args.percentage > 0) {
                            // return `${args.label} \n \n ${args.percentage}%`
                            return `${args.percentage}%`
                        }
                    
                    },
                    fontColor: [
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff',
                        '#fff'
                    ]
                    // position: 'outside'
                    // textAlign: 'center',
                    // textBaseLine: 'middle'
                }
            }
        },
        // options: {
        //     responsive: true,
        //     plugins: {
        //         legend: {
        //             position: 'top',
        //         },
        //         title: {
        //             display: true,
        //             text: 'Procurement Chart'
        //         },
        //         labels: {
        //             render: (args) => {
        //                 return args.labels
        //             }
        //         }
        //     },
        //     // scales: {
        //     //     y: {
        //     //         beginAtZero: true
        //     //     }
        //     // },
        //     plugins: {
        //         labels: {
        //             render: (args) => {
        //                 return args.labels
        //             }
        //         },
        //         tooltip: {
        //             enabled: true
        //         },
        //         datalabels: {
        //             color: [
        //                 'white',
        //                 'white',
        //                 'white',
        //                 'white',
        //                 'white',
        //                 'white'
        //             ],
        //             align: 'center',    
        //             // formatter: (value, context) => {
        //             //     const datapoints = context.chart.data.datasets[0].data
        //             //     function totalSum(total,datapoint) {
        //             //         return total + datapoint;
        //             //     }
        //             //     const totalValue = datapoints.reduce(totalSum, 0)
        //             //     const percentageValue = (value / totalValue * 100).toFixed(1)
        //             //     // const display = [`Rp. ${totalValue}`, `Total : ${percentageValue}%`]
        //             //     const display = [`Total : ${percentageValue}%`]
        //             //     console.log(context)
        //             //     return display
        //             // }
        //         }
        //     }
        // },
        // options: {
        //     plugins: {
        //         labels: {
        //             render: (args) => {
        //                 // if(args.label.length > 10) {
        //                     return `${args.label}`
        //                 // }
        //             },
        //             // render: 'value',
        //             // fontColor: data.datasets[0].borderColor,
        //             fontColor: '#fff',
        //             fontStyle: 'bolder',
        //             position: 'outside',
        //             textMargin: 6,
        //             textAlign: 'center',
        //             textBaseLine: 'middle'
        //         }
        //     }
        // },
        // plugins: [ChartDataLabels]
    };

    const ctx = document.getElementById('myChart').getContext("2d");
    
    
    if(chart!=null) {
        chart.destroy()
    }
    chart = new Chart(ctx, config)
    
}

function formatReport() {
    var tipereport = document.getElementById('tipereport').options[document.getElementById('tipereport').selectedIndex].value;
    var periodeawal = document.getElementById('periodeawal').options[document.getElementById('periodeawal').selectedIndex].value;
    var periodeakhir = document.getElementById('periodeakhir').options[document.getElementById('periodeakhir').selectedIndex].value;

    if (tipereport == "summary") {
        document.getElementById('quaterly').style.display = "none"
        document.getElementById('summary').removeAttribute('style')
    } else {
        document.getElementById('summary').style.display = "none"
        document.getElementById('quaterly').removeAttribute('style')
    }
}

function popupdetail(periode,idkategori) {
    var header      = `Detail Laporan ${periode}` 
    var regional    = document.getElementById('regional').value
    var pt          = document.getElementById('pt').value
    var unit        = document.getElementById('unit').value
    var tipeunit    = document.getElementById('tipeunit').value
    var tipereport  = document.getElementById('tipereport').value
    const method = "detailRow"

    // alertify.alert('Apadeh?'+pt+tipeunit+tipereport)
    param = "method=" + method
    param += `&regional=${regional}&pt=${pt}&tipeunit=${tipeunit}&tipereport=${tipereport}&periode=${periode}&idkategori=${idkategori}&unit=${unit}`
    
    tujuan = "log_2procurement_slave.php"

    post_response_text(tujuan,param,respon)

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off()
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText)
                } else {
                    // alertify.alert(con.responseText)
                    alertify.popup(header, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': true }).resizeTo('90%', '80%');
                    // InnerHTML optionnya
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function download() {
//     const imageLink = document.createElement('a');
//     const canvas = document.getElementById('myChart');
//     imageLink.download = 'canvas.png';
//     imageLink.href = canvas.toDataURL('image/png', 1);
//     imageLink.click();
//     data = imageLink.getAttribute('href')
// console.log(imageLink.getAttribute('href'))
//     param = "method=preview"
//     param += "&aksi=excel" + "&canvasimg=" + data
//     alert(param)
//     tujuan = 'log_2procurement_slave.php';
//     post_response_text(tujuan, param, respon);

//     if (con.readyState == 4) {
//         if (con.status == 200) {
//             busy_off()
//             if (!isSaveResponse(con.responseText)) {
//                 alert(con.responseText)
//             } else {
//                 // Success

//             }
//         } else {
//             busy_off();
//             error_catch(con.status);
//         }
//     }
// }