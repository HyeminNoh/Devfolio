window.onload = function () {
    this.userIdx = window.location.pathname.split("/").pop();
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
    // 블로그 포스트 정보가 들어갈 div 요소가 있는지 확인
    if (document.getElementById('blog-div')) {
        blogLoad()
    }
}

let userIdx;
// 데이터 로드 실패 텍스트
const dataLoadFailTxt = document.createElement("h4")
dataLoadFailTxt.innerText = "데이터 로드 실패"

// 도넛형 차트 생성
function drawDoughnut(values, colors, labels, legendState, type) {
    const chart = document.createElement("canvas")
    if(type==='repo'){
        chart.width=100
        chart.height=100
    }
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
                    label: function (tooltipItem, data) {
                        //get the concerned dataset
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        //calculate the total of this data set
                        var total = dataset.data.reduce(function (previousValue, currentValue) {
                            return previousValue + currentValue;
                        });
                        //get the current items value
                        var currentValue = dataset.data[tooltipItem.index];
                        //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                        var percentage = Math.floor(((currentValue / total) * 100) + 0.5);

                        return labels[tooltipItem.index] + " " + percentage + "%";
                    }
                }
            },
            legend: {
                display: legendState,
                position: 'right',
            },
        }
    })
    return chart
}
