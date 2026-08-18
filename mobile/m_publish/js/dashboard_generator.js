(() => {
  $.dataJson.forEach((menu, i) => {
    const card = document.createElement('div');
    card.classList.add('card');

    const cardBody = document.createElement('div');
    cardBody.classList.add('card-body', 'd-flex', 'flex-column', 'justify-content-center', 'align-items-center', 'p-4', 'cursor');
    cardBody.style.width = '150px';
    cardBody.style.height = '150px';
    cardBody.onclick = () => {
      console.log("Menu clicked:", menu);
      generateDashboard(menu);
    }

    const icon = document.createElement('i');
    icon.classList.add('fa', `fa-${menu.menu.icon}`, `fa-4x`, `text-${menu.menu.color}`);

    const title = document.createElement('h5');
    title.classList.add('card-title', 'mt-3');
    title.textContent = menu.menu.name;

    cardBody.appendChild(icon);
    cardBody.appendChild(title);
    card.appendChild(cardBody);

    document.getElementById('containerMenu').appendChild(card);
  });

  function generateDashboard(dataJson) {
    window.bodymaster.innerHTML = createContainerChart();

    console.log("Data JSON:", dataJson);
    dataJson.charts.forEach((chart, i) => {
      console.log("Chart data:", chart);
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

  function createContainerChart() {
    return `
      <div id=\"containerDashboard\" class=\"row\">
        <div class=\"col-12 u-margin-t-10\">
          <div class=\"tabs-box panel-frame tabs_roles_0\" style=\"height: 100vh; width: 100%;\">
            <div id=\"containerChart\" class=\"tabs-content chart-tab\" style=\"text-align:left;\"></div>
            <div class=\"clearfix\"></div>
          </div>
        </div>
      </div>
    `
  }
        
})();