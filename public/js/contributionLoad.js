/* Contribution 달력 차트 데이터 로드 & 컴포넌트 생성 */

function calendarLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/" + this.userIdx + "/contribution/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        // calendar view 자리 다시 그리기
        drawCalendar(result);
    }).fail(function () {
        $('div#cal-div').empty();
        const calendarDiv = document.getElementById('cal-div')
        calendarDiv.append(dataLoadFailTxt)
    });
}

function calendarUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/report/" + this.userIdx + "/contribution/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        calendarLoad()
    })
}

function drawCalendar(data) {
    $('div#cal-div').empty();
    const contributions = JSON.parse(data[0].data)

    // 전체 기여도 수
    document.getElementById('total-contribution-text').innerHTML = "Last Year Total Contribution: " + contributions.totalContributions
    // 마지막 갱신 날짜
    document.getElementById('calendar-updated-text').innerHTML = "Last Updated: " + data[0].updated_dt

    const calDiv = document.getElementById('cal-div')

    // 이전, 다음 버튼
    const prevBtnCol = document.createElement('div')
    prevBtnCol.className = "col-1"
    const nextBtnCol = document.createElement('div')
    nextBtnCol.className = "col-1"

    const prevBtn = document.createElement('button')
    const nextBtn = document.createElement('button')
    prevBtn.className = "btn btn-light"
    nextBtn.className = "btn btn-light"
    prevBtn.style.height = "80%"
    nextBtn.style.height = "80%"
    prevBtn.id = "prev-btn"
    nextBtn.id = "next-btn"
    prevBtn.innerText = "<"
    nextBtn.innerText = ">"

    prevBtnCol.append(prevBtn)
    nextBtnCol.append(nextBtn)

    // heatmap이 들어갈 div
    const heatmapCol = document.createElement('div')
    heatmapCol.className = "col"
    const calHeatMap = document.createElement('div')
    calHeatMap.id = "cal-heatmap"
    calHeatMap.style.overflow = "hidden"
    heatmapCol.append(calHeatMap)

    calDiv.append(prevBtnCol)
    calDiv.append(heatmapCol)
    calDiv.append(nextBtnCol)

    // 차트에 사용되는 데이터
    const dailyData = contributions.dailyData
    const dataKeyList = Object.keys(dailyData)
    const startUnixTime = dataKeyList[0]
    const minDate = new Date(new Date(startUnixTime * 1000).getFullYear(), new Date(startUnixTime * 1000).getMonth())
    const lastUnixTime = dataKeyList[dataKeyList.length - 1]
    const maxDate = new Date(new Date(lastUnixTime * 1000).getFullYear(), new Date(lastUnixTime * 1000).getMonth())
    const startDate = new Date(new Date(lastUnixTime * 1000).getFullYear(), new Date(lastUnixTime * 1000).getMonth() - 10)
    const colors = contributions.colors

    const cal = new CalHeatMap();
    cal.init({
        start: startDate,
        minDate: minDate,
        maxDate: maxDate,
        data: dailyData,
        domain: "month",
        subDomain: "day",
        range: 11,
        legend: [1, 3, 5, 7, 10],
        legendColors: ['#efefef', colors[colors.length - 2]],
        legendHorizontalPosition: "right",
        nextSelector: "#next-btn",
        previousSelector: "#prev-btn",
        onClick: function (date, cnt) {
            $("#onClick-placeholder").html(
                "<p style='font-size: 1.2em'>" + date.toDateString() + "</b> <br/>with <b>" +
                (cnt === null ? "0" : cnt) + "</b> contributions </p>"
            );
        }
    });

    $("#prev-btn").on("click", function (e) {
        e.preventDefault();
        if (!cal.previous()) {
            return false
        }
    });

    $("#next-btn").on("click", function (e) {
        e.preventDefault();
        if (!cal.next()) {
            return false
        }
    });
}
