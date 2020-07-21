window.onload = function () {
    calendarLoad()
}

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
    })
}

function drawCalendar(data) {
    const calendarDiv = document.getElementById('calendar-div')
    const totalContribution = document.createElement("p")
    // console.log(JSON.parse(data[0].data));

    const contributions = JSON.parse(data[0].data);
    totalContribution.innerText = "Total Contribution: "+contributions.totalContributions

    const updatedDate  = document.createElement('p');
    updatedDate.innerText = "Last Updated time: "+data[0].updated_dt

    calendarDiv.append(totalContribution)
    calendarDiv.append(updatedDate)
}
