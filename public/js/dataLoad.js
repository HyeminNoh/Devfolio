let userIdx;
window.onload = function () {
    this.userIdx = window.location.pathname.split("/").pop();
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
}
/* Contribution 달력 차트 데이터 로드 & 컴포넌트 생성 */

function calendarUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/contribution/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        calendarLoad()
    })
}

function calendarLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/contribution/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        $('div#cal-div').empty();
        // calendar view 자리 다시 그리기
        drawCalendar(result);
    }).fail(function () {
        $('div#cal-div').empty();
        const calendarDiv = document.getElementById('cal-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="데이터로드에 실패했습니다."
        calendarDiv.append(failTxt)
    });
}

function drawCalendar(data) {
    const contributions = JSON.parse(data[0].data)
    // console.log(contributions)
    document.getElementById('total-contribution-text').innerHTML="Total Contribution: "+contributions.totalContributions
    document.getElementById('calendar-updated-text').innerHTML="Last Updated: "+data[0].updated_dt

    const calDiv = document.getElementById('cal-div')
    const calRow = document.createElement('div')
    calRow.className="row"
    calRow.style.verticalAlign="center"

    // 이전, 다음 버튼
    const prevBtnCol = document.createElement('div')
    prevBtnCol.className="col-auto"
    const nextBtnCol = document.createElement('div')
    nextBtnCol.className="col-auto"

    const prevBtn = document.createElement('button')
    const nextBtn = document.createElement('button')
    prevBtn.className="btn btn-light"
    nextBtn.className="btn btn-light"
    prevBtn.style.height="80%"
    nextBtn.style.height="80%"
    prevBtn.id = "prev-btn"
    nextBtn.id = "next-btn"
    prevBtn.innerText="<"
    nextBtn.innerText=">"

    prevBtnCol.append(prevBtn)
    nextBtnCol.append(nextBtn)

    // heatmap이 들어갈 div
    const heatmapCol = document.createElement('div')
    heatmapCol.className="col-auto"
    const calHeatMap = document.createElement('div')
    calHeatMap.id="cal-heatmap"
    heatmapCol.append(calHeatMap)

    // heatmap click 시 보일 텍스트 div
    const calDescription = document.createElement('div')
    calDescription.id = "onClick-placeholder"
    calDescription.className = "col-auto"
    calDescription.style.textAlign="right"
    calDescription.innerHTML="<p>Click Your Calendar</p>"

    calRow.append(prevBtnCol)
    calRow.append(heatmapCol)
    calRow.append(calDescription)
    calRow.append(nextBtnCol)
    calDiv.append(calRow)

    // 차트에 사용되는 데이터
    const dailyData = contributions.dailyData
    const dataKeyList = Object.keys(dailyData)
    const startUnixTime = dataKeyList[0]
    const minDate = new Date(new Date(startUnixTime*1000).getFullYear(), new Date(startUnixTime*1000).getMonth())
    const lastUnixTime = dataKeyList[dataKeyList.length-1]
    const maxDate = new Date(new Date(lastUnixTime*1000).getFullYear(), new Date(lastUnixTime*1000).getMonth())
    const startDate = new Date(new Date(lastUnixTime*1000).getFullYear(), new Date(lastUnixTime*1000).getMonth()-5)
    const colors = contributions.colors

    const cal = new CalHeatMap();
    cal.init({
        start: startDate,
        minDate: minDate,
        maxDate: maxDate,
        data: dailyData,
        domain: "month",
        subDomain: "day",
        range: 6,
        legend: [1, 3, 5, 10],
        legendColors: ["#efefef"].concat(colors[colors.length-2]),
        legendHorizontalPosition: "right",
        nextSelector: "#next-btn",
        previousSelector: "#prev-btn",
        onClick: function(date, cnt) {
            $("#onClick-placeholder").html(
                date.toDateString() + "</b> <br/>with <b>" +
                (cnt === null ? "0" : cnt) + "</b> contributions"
            );
        }
    });

    $("#prev-btn").on("click", function(e) {
        e.preventDefault();
        if (!cal.previous()) {
            return false
        }
    });

    $("#next-btn").on("click", function(e) {
        e.preventDefault();
        if (!cal.next()) {
            return false
        }
    });
}

/* 대표 저장소 데이터 로드 & 카드 뷰 생성 */

function repositoriesLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/repository/show",
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
            const node = edge.node // Json obejct

            const col = document.createElement('div')
            col.className = "col-lg-6 col-md-12"
            col.style.padding = "1em"

            const card = document.createElement('div')
            card.className = "card"

            const cardBody = document.createElement('div')
            cardBody.className = "card-body"

            const title = document.createElement('div')
            const description = document.createElement('div')
            const stat = document.createElement('div')
            stat.className="row"
            const leftRow = document.createElement('div')
            leftRow.className="row"
            const leftCol = document.createElement('div')
            leftCol.className="col"
            const rightCol = document.createElement('div')
            rightCol.className="col"
            rightCol.style.textAlign="right"
            const stargazer = document.createElement('col')
            stargazer.style.marginLeft="1em"
            const language = document.createElement('col')
            language.style.marginLeft="0.3em"
            const fork = document.createElement('col')
            fork.style.marginLeft="0.3em"

            title.innerHTML="<a href=" + node.url + "><h5>" + node.name + "</h5></a><hr>"
            description.innerHTML = "<p>"+node.description+"</p>"
            if(node.stargazers.totalCount!==0){
                stargazer.innerHTML="<p style='color:#808080;'><i class=\"fas fa-star\" style=\"color: #808080\"></i>&nbsp"+node.stargazers.totalCount+"</p>"
            }
            if(node.primaryLanguage) {
                language.innerHTML = "<p style='color:#808080;'><i class=\"fas fa-circle\" style='color:"+node.primaryLanguage.color+"'></i>&nbsp" + node.primaryLanguage.name + "</p>"
            }
            if(node.forkCount!==0) {
                fork.innerHTML = "<p style='color:#808080;'><i class=\"fas fa-code-branch\" style=\"color: #808080\"></i>&nbsp" + node.stargazers.totalCount + "</p>"
            }

            rightCol.innerHTML = "<p>"+node.diskUsage+" KB</p>"

            leftRow.append(stargazer)
            leftRow.append(language)
            leftRow.append(fork)
            leftCol.append(leftRow)
            // 카드 바디 안에 내용 채우기
            stat.append(leftCol)
            stat.append(rightCol)
            cardBody.append(title)
            cardBody.append(description)
            cardBody.append(stat)
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
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/repository/update",
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
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/skill/show",
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
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        //get the concerned dataset
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        //calculate the total of this data set
                        var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                            return previousValue + currentValue;
                        });
                        //get the current items value
                        var currentValue = dataset.data[tooltipItem.index];
                        //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                        var percentage = Math.floor(((currentValue/total) * 100)+0.5);

                        return labels[tooltipItem.index]+" "+percentage + "%";
                    }
                }
            },
            legend: {
                display: true,
                position: 'right',
            },
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
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/skill/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        skillsLoad()
    })
}
