window.onload = function () {
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
}

/* Contribution 달력 차트 데이터 로드 & 컴포넌트 생성 */

function calendarUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/contributions/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        calendarLoad()
    })
}

function calendarLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/contributions/show",
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
        failTxt.innerText="데이터가 없습니다."
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
        url: "http://127.0.0.1:8000/repositories/show",
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
        url: "http://127.0.0.1:8000/repositories/update",
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
        url: "http://127.0.0.1:8000/skills/show",
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

    console.log(JSON.parse(data[0].data));
    data = JSON.parse(data[0].data);
    const values = []
    for(let lanName in data) {
        values.concat(data[lanName].size)
    }
    // 그래프
    const chartDiv = document.getElementById('pie-chart-div')
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
            datasets: [{
                label: '# of Votes',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
    chartDiv.append(canvas)

    // 텍스트 설명
    const chartDescDiv = document.getElementById('chart-desc-div');
    const textCol = document.createElement('div')
    for(let lanName in data) {
        const descriptionRow = document.createElement("div")
        descriptionRow.className="row"
        descriptionRow.style.marginLeft = "1em"
        descriptionRow.innerHTML="<p><i class='fas fa-circle' style='color:"+data[lanName].color+" '></i>&nbsp;&nbsp;"+lanName+": "+data[lanName].size+" lines</p>"
        textCol.append(descriptionRow)
    }
    chartDescDiv.append(textCol)
}

function skillsUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/skills/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        skillsLoad()
    })
}
