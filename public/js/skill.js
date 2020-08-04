/* 사용자 기술 통계 데이터 로드 & 컴포넌트 생성 */

// 데이터 로드
function initSkill(userIdx) {
    const result = getData('skill', userIdx);
    if(!result){
        // draw fail result div
        deleteAll("pie-chart-div");
        deleteAll("chart-desc-div");
        const chartDiv = document.getElementById('pie-chart-div');
        chartDiv.append(dataLoadFailTxt);
    }
    drawSkillChart(result);
}

// 데이터 갱신
let isSkill = false;
function updateSkill(userIdx) {
    if(isSkill){
        swal('1분 후 시도해 주세요.', 'Skill 정보가 이미 최신 상태 입니다.', 'info');
        return false;
    }
    isSkill = true;
    const state = updateData('skill', userIdx);
    if(!state){
        return false;
    }
    initSkill(userIdx);
    setTimeout(()=>{
        isSkill = false;
    }, 60000)
}

function drawSkillChart(data) {
    // div spinner 비우기
    deleteAll("pie-chart-div");
    deleteAll("chart-desc-div");
    document.getElementById('skill-updated-text').innerHTML = `Last Updated: ${data[0].updated_dt}`;

    data = JSON.parse(data[0].data);

    // size 기준 내림차순 정렬
    data.sort((a, b) => {
        return b.size - a.size;
    });

    let labels = [];
    let values = [];
    let colors = [];
    let etcSize = 0;
    for (let i = 0; i < data.length; i++) {
        if (i < 10) {
            labels.push(data[i].name);
            values.push(data[i].size);
            colors.push(data[i].color);
        } else {
            etcSize += data[i].size;
        }
    }
    if (data.length > 10) {
        labels.push("etc");
        values.push(etcSize);
        colors.push("#C0C0C0");
    }

    // 그래프
    const chartDiv = document.getElementById('pie-chart-div');
    const chart = drawDoughnut(values, colors, labels, true, 'skill');
    chartDiv.append(chart);

    // 텍스트 설명
    const chartDescDiv = document.getElementById('chart-desc-div');
    const textColLeft = document.createElement('div');
    textColLeft.className = "col";
    const textColRight = document.createElement('div');
    textColRight.className = "col";
    for (let i = 0; i < values.length; i++) {
        const descriptionRow = document.createElement("div");
        descriptionRow.className = "row";
        descriptionRow.style.marginLeft = "1em";
        descriptionRow.innerHTML = `<p><i class="fas fa-circle" style="color:${colors[i]}"></i>&nbsp;&nbsp; ${labels[i]}: ${values[i]} bytes</p>`;

        if(Math.floor(values.length/2)>=i){
            textColRight.append(descriptionRow);
        } else {
            textColLeft.append(descriptionRow);
        }
    }
    chartDescDiv.append(textColLeft);
    chartDescDiv.append(textColRight);
}
