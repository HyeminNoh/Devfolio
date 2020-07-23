window.onload = function () {
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
}

/* Contribution 달력 차트 데이터 로드 & 컴포넌트 생성 */

function calendarUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/contribution/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        calendarLoad()
    })
}

function calendarLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/contribution/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        $('div#calendar-div').empty();
        // calendar view 자리 다시 그리기
        drawCalendar(result);
    }).fail(function () {
        $('div#calendar-div').empty();
        const calendarDiv = document.getElementById('calendar-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="데이터로드에 실패했습니다."
        calendarDiv.append(failTxt)
    });
}

function drawCalendar(data) {
    const calendarDiv = document.getElementById('calendar-div')

    // console.log(JSON.parse(data[0].data));
    const contributions = JSON.parse(data[0].data);

    document.getElementById('total-contribution-text').innerHTML="Total Contribution: "+contributions.totalContributions
    document.getElementById('calendar-updated-text').innerHTML="Last Updated: "+data[0].updated_dt
}

/* 대표 저장소 데이터 로드 & 카드 뷰 생성 */

function repositoriesLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/repository/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        $('div#repositories-div').empty();
        // calendar view 자리 다시 그리기
        drawRepositCard(result);
    }).fail(function () {
        $('div#repositories-div').empty();
        const repositDiv = document.getElementById('repositories-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="지정된 대표 저장소가 없습니다."
        repositDiv.append(failTxt)
    })
}

function drawRepositCard(data) {
    document.getElementById('repository-updated-text').innerHTML="Last Updated: "+data[0].updated_dt

    const repositoriesDiv = document.getElementById('repositories-div')

    const row = document.createElement('div')
    row.className="row"

    // pinned reposit 정보만 추출
    data = JSON.parse(data[0].data)
    if(data.totalCount) {
        data.edges.forEach(function (edge) {
            // console.log(edge)
            const node = edge.node // Json obejct

            const col = document.createElement('div')
            col.className = "col-lg-6 col-md-12"
            col.style.padding = "1em"

            const card = document.createElement('div')
            card.className = "card"

            const cardBody = document.createElement('div')
            cardBody.className = "card-body"

            // 카드 바디 안에 내용 채우기
            cardBody.innerHTML = "<a href=" + node.url + "><h5>" + node.name + "</h5></a><hr><p>" + node.description + "</p>"

            card.append(cardBody)
            col.append(card)
            row.append(col)
        })
    } else { // pinned reposit 0개일 때
        const failTxt = document.createElement("div")
        failTxt.className="col text-center"
        failTxt.style.margin="1em"
        failTxt.style.color="gray"
        failTxt.innerHTML="<h4>지정된 대표 저장소가 없습니다.</h4>"
        row.append(failTxt)
    }
    repositoriesDiv.append(row);
}

function repositoriesUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/repository/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        repositoriesLoad()
    })
}

/* 사용자 기술 통계 데이터 로드 & 컴포넌트 생성 */

function skillsLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/skill/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        $('div#pie-chart-div').empty();
        $('div#chart-desc-div').empty();
        // chart view 자리 다시 그리기
        drawSkillChart(result);
    }).fail(function () {
        $('div#pie-chart-div').empty();
        $('div#chart-desc-div').empty();
        const chartDiv = document.getElementById('pie-chart-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="데이터가 없습니다."
        chartDiv.append(failTxt)
    })
}

function drawSkillChart(data) {
    document.getElementById('skill-updated-text').innerHTML="Last Updated: "+data[0].updated_dt

    data = JSON.parse(data[0].data);

    // size 기준 내림차순 정렬
    data.sort(function (a, b) {
        return b.size - a.size;
    })

    let labels = []
    let values = []
    let colors = []
    let etcSize = 0;
    for(let i=0; i<data.length; i++){
        if(i<10){
            labels.push(data[i].name)
            values.push(data[i].size)
            colors.push(data[i].color)
        }
        else{
            etcSize+=data[i].size
        }
    }
    if(data.length>10) {
        labels.push("etc")
        values.push(etcSize)
        colors.push("#C0C0C0")
    }
    // 그래프
    const chartDiv = document.getElementById('pie-chart-div')
    const chart = document.createElement("canvas")
    const chartContext = chart.getContext('2d')
    new Chart(chartContext, {
        // The type of chart we want to create
        type: 'doughnut',

        // The data for our dataset
        data: {
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: colors,
            }],
            labels: labels
        },

        // Configuration options go here
        options: {
            legend: {
                display: false,
            }
        }
    })
    chartDiv.append(chart)

    // 텍스트 설명
    const chartDescDiv = document.getElementById('chart-desc-div');
    const textCol1 = document.createElement('div')
    textCol1.className="col"
    const textCol2 = document.createElement('div')
    textCol2.className="col"
    for(let i=0; i<values.length/2; i++) {
        const descriptionRow1 = document.createElement("div")
        descriptionRow1.className="row"
        descriptionRow1.style.marginLeft = "1em"
        descriptionRow1.innerHTML="<p><i class='fas fa-circle' style='color:"+colors[i]+" '></i>&nbsp;&nbsp;"+labels[i]+": "+values[i]+" lines</p>"
        textCol1.append(descriptionRow1)
    }
    for(let j=parseInt(values.length/2); j<values.length; j++){
        const descriptionRow2 = document.createElement("div")
        descriptionRow2.className="row"
        descriptionRow2.style.marginLeft = "1em"
        descriptionRow2.innerHTML="<p><i class='fas fa-circle' style='color:"+colors[j]+" '></i>&nbsp;&nbsp;"+labels[j]+": "+values[j]+" lines</p>"
        textCol2.append(descriptionRow2)
    }
    chartDescDiv.append(textCol1)
    chartDescDiv.append(textCol2)
}

function skillsUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/skill/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        skillsLoad()
    })
}
