// 포트폴리오 페이지에 들어가는 요소들 로드
function loadPortfolio(userIdx) {
    calendarLoad(userIdx);
    repositoriesLoad(userIdx);
    skillsLoad(userIdx);
    // 블로그 포스트 정보가 들어갈 div 요소가 있는지 확인
    if (document.getElementById('blog-div')) {
        blogLoad(userIdx);
    }
}

// 데이터 로드 실패 텍스트
const dataLoadFailTxt = document.createElement('h4');
dataLoadFailTxt.innerText = '데이터 로드 실패';

// 데이터 개수가 0개 일때 div 내용 변경
function dataNullDiv(content) {
    const emptyDataCol = document.createElement('div');
    emptyDataCol.className = 'col text-center';
    emptyDataCol.style.margin = '1em';
    emptyDataCol.style.color = 'gray';
    emptyDataCol.innerHTML = `<h4>${content}</h4>`;
    return emptyDataCol;
}

// 도넛형 차트 생성
function drawDoughnut(values, colors, labels, legendState, type) {
    const chart = document.createElement('canvas');
    if(type==='repo'){
        chart.width=100;
        chart.height=100;
    }
    const chartContext = chart.getContext('2d');
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
                    label: function (tooltipItem, data) {
                        //get the concerned dataset
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        //calculate the total of this data set
                        const total = dataset.data.reduce(function (previousValue, currentValue) {
                            return previousValue + currentValue;
                        });
                        //get the current items value
                        const currentValue = dataset.data[tooltipItem.index];
                        //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                        const percentage = Math.floor(((currentValue / total) * 100) + 0.5);

                        return `${labels[tooltipItem.index]}: ${percentage} %`;
                    }
                }
            },
            legend: {
                display: legendState,
                position: 'right',
            },
        }
    });
    return chart;
}

// 내부 요소 지우기
function deleteAll(selectId) {
    const selectedDiv= document.getElementById(selectId);
    selectedDiv.querySelectorAll('*').forEach(n => n.remove());
}
