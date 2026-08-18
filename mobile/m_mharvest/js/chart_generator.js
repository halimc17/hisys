var winUpdate;

function callbackParam(e) {
  if (document.getElementById('myChart').attributes.length > 1) {
    $.CGen.init(e.response.rnumber, e.response.columns, e.response.rows);
    $.CGen.generateChart();
  } else {
    $.CGen.init(e.response.rnumber, e.response.columns, e.response.rows);
    
    document.getElementById('errorChart').style.display = 'none';
    document.getElementById('menu').style.display = '';
  }
}

function callbackView(e) {
  $.get(false, $.options.slave+"?switcher=data&id="+e.response.id, (ev) => {
    $.CGen.init(e.response.rnumber, e.response.columns, e.response.rows);
    const data = JSON.parse(ev.response);
    $.CGen.type = data[0].type;
    $.CGen.columnLabel = {
      id: 'columnLabel',
      value: data[0].kolomlabel,
    }
    data[0].operation.split(',').forEach((item, index) => {
      $.CGen.operation[index] = {
        index: index,
        id: 'operation',
        value: item
      };
    });
    data[0].kolomdata.split(',').forEach((item, index) => {
      $.CGen.columnData[index] = {
        index: index,
        id: 'columnData',
        value: item
      };
    });
    $.CGen.generateChart();
  });
}

function viewAction(getpage, title) {
  $.get(false, $.options.slave+"?switcher=parameter"+getpage, (e) => {
    file = $.CGen.generateParamView(e.response);
    tujuan = $.options.slave +'?switcher=view'+getpage;
    let options = {
      url: tujuan,
      title: title,
      success: () => {
        winUpdate.target.body.querySelector('#listParam').insertAdjacentElement('afterbegin', file);
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
