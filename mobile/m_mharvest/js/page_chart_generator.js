var winUpdate;

function newAction() {
  $.get(false, $.options.slave+"?switcher=init", (e) => {
    const data = JSON.parse(e.response);
    const file = $.PChart.init(data.listTable, data.listChart);
    const tujuan = $.options.slave +'?switcher=new';
    let options = {
      url: tujuan,
      title: 'Create New Page of Chart',
      success: () => {
        winUpdate.target.body.querySelector('#sidebar').appendChild(file);
      }
    };
    winUpdate = $.openWindow(options);
  });
}

function viewAction(getpage, title) {
  $.get(false, $.options.slave+"?switcher=load"+getpage, (e) => {
    const data = JSON.parse(e.response);
    console.log(data);
    tujuan = `${$.options.slave}?switcher=view`;
    let options = {
      url: tujuan,
      title: title,
      success: () => {
        data.charts.forEach((chart, i) => {
          $.PChart.createCanvasView(chart.chartid, {
            h: chart.attributes.h,
            w: chart.attributes.w,
            x: chart.attributes.x,
            y: chart.attributes.y,
            id: chart.chartid
          });
          $.PChart.selectedChart.push({
            id: chart.chartid,
            kolomdata: chart.format.kolomdata,
            kolomlabel: chart.format.kolomlabel,
            nama: '',
            operation: chart.format.operation,
            rnumber: chart.chartid,
            status: '1',
            type: chart.type,
            version: ''
          });
          $.PChart.oldData[chart.chartid] = chart.data;
          $.PChart.update(chart.chartid);
        });
      }
    };
    winUpdate = $.openWindow(options);
  });
}

function publishAction(getpage) {
  tujuan = $.options.slave + getpage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin publish tabel ini? ', () => {
    $.get(ele, tujuan, function callback(Result) {
      if (!Result.response.error) {
        $.refresh();
      } else {
        $.Alert(Result.response.message);
      }
    });
  });
}

function unpublishAction(getpage) {
  tujuan = $.options.slave + getpage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin unpublish tabel ini? ', () => {
    $.get(ele, tujuan, function callback(Result) {
      if (!Result.response.error) {
        $.refresh();
      } else {
        $.Alert(Result.response.message);
      }
    });
  });
}

function deleteAction(getpage) {
  tujuan = $.options.slave + getpage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin delete tabel ini? ', () => {
    $.get(ele, tujuan, function callback(Result) {
      if (!Result.response.error) {
        $.refresh();
      } else {
        $.Alert(Result.response.message);
      }
    });
  });
}
