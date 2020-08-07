/* Contribution 달력 차트 데이터 로드 & 컴포넌트 생성 */

// 데이터 로드
function initCalendar(userIdx) {
    const result = getData('contribution', userIdx);
    if(result.length===0){
        deleteAll('cal-heatmap');
        const calendarDiv = document.getElementById('cal-heatmap');
        calendarDiv.append(dataLoadFailTxt);
        return false;
    }
    // calendar view 자리 다시 그리기
    drawCalendar(result);
    return true;
}

// 데이터 갱신
let isCal = false;
function updateCalendar(userIdx) {
    if(isCal){
        swal('1분 후 시도해 주세요', 'Contribution 정보가 이미 최신 상태 입니다.', 'warning');
        return false;
    }
    isCal = true;
    const state = updateData('contribution', userIdx);
    if(!state){
        return false;
    }
    swal('갱신 성공', 'Contribution 정보가 갱신되었습니다.', 'info');
    initCalendar(userIdx);
    setTimeout(()=>{
        isCal = false;
    }, 60000);
}

function drawCalendar(data) {
    const contributions = JSON.parse(data[0].data);

    // 전체 기여도 수
    document.getElementById('total-contribution-text').innerHTML = `Last Year Total Contribution: ${contributions.totalContributions}`;
    // 마지막 갱신 날짜
    document.getElementById('calendar-updated-text').innerHTML = `Last Updated:  ${data[0].updated_dt}`;

    // 차트에 사용되는 데이터
    const dailyData = contributions.dailyData;
    const dataKeyList = Object.keys(dailyData);
    // 최초 일자
    const startUnixTime = dataKeyList[0];
    const minDate = new Date(new Date(startUnixTime * 1000).getFullYear(), new Date(startUnixTime * 1000).getMonth());
    // 마지막 일자
    const lastUnixTime = dataKeyList[dataKeyList.length - 1];
    const maxDate = new Date(new Date(lastUnixTime * 1000).getFullYear(), new Date(lastUnixTime * 1000).getMonth());
    // 그래프 초기 기준 일자
    const startDate = new Date(new Date(lastUnixTime * 1000).getFullYear(), new Date(lastUnixTime * 1000).getMonth() - 10);
    const colors = contributions.colors;

    // calendar가 그려질 div 안 요소 삭제
    deleteAll('cal-heatmap');

    // calendar heatmap 생성
    const cal = new CalHeatMap();
    cal.init({
        start: startDate,
        minDate: minDate,
        maxDate: maxDate,
        data: dailyData,
        domain: 'month',
        subDomain: 'day',
        range: 11,
        legend: [1, 3, 5, 10],
        legendColors: {
            min: colors[0],
            max: colors[colors.length-1],
            empty: "#efefef"
        },
        considerMissingDataAsZero: true,
        legendHorizontalPosition: 'right',
        nextSelector: '#next-btn',
        previousSelector: '#prev-btn',
        onClick: (date, cnt) => {
            document.getElementById('onClick-placeholder').innerHTML =
                "<p style='font-size: 1.2em'>" + date.toDateString() +
                "</b> <br/>with <b>" + (cnt === null ? "0" : cnt) + "</b> contributions </p>";
        }
    });

    document.getElementById('prev-btn').onclick = (e) => {
        e.preventDefault();
        if (!cal.previous()) {
            return false;
        }
    }

    document.getElementById('next-btn').onclick = (e) => {
        e.preventDefault();
        if (!cal.next()) {
            return false;
        }
    }
}
