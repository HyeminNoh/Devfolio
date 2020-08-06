/* 사용자 기술 통계 데이터 로드 & 컴포넌트 생성 */

// 데이터 로드
function initSkill(userIdx) {
    const result = getData('skill', userIdx);
    if(result.length===0){
        // draw fail result div
        deleteAll("pie-chart-div");
        const chartDiv = document.getElementById('pie-chart-div');
        chartDiv.append(dataLoadFailTxt);
        return false;
    }
    drawSkillChart(result);
    return true;
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
    // pie chart가 들어갈 div 요소 비우기
    deleteAll("pie-chart-div");

    const chartDiv = document.getElementById('pie-chart-div');
    document.getElementById('skill-updated-text').innerHTML = `Last Updated: ${data[0].updated_dt}`;

    data = JSON.parse(data[0].data);

    if(data.length!==0){
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
        const chart = drawDoughnut(values, colors, labels, true, 'skill');
        chartDiv.append(chart);

        // 텍스트 설명
        const langDescTemplate = document.getElementById('lang-desc-text').innerHTML;
        let langCol = ''
        const halfIdx = Math.floor(values.length/2);
        for (let i = 0; i < values.length; i++) {
            langCol += langDescTemplate.replace("{color}", colors[i])
                .replace("{name}", labels[i])
                .replace("{size}", values[i]);
            if(i===halfIdx){
                document.getElementById('skill-desc-left-col').innerHTML=langCol;
                langCol='';
            }
        }
        document.getElementById('skill-desc-right-col').innerHTML=langCol;
    } else {
        chartDiv.append(dataNullDiv('프로그래밍 활동 내역이 없어 분석할 수 없어요 😥'));
    }
}
