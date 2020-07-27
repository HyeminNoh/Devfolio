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
