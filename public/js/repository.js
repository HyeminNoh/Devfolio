/* 대표 저장소 데이터 로드 & 카드 뷰 생성 */

// 데이터 로드
function initRepo(userIdx) {
    const result = getData('repository', userIdx);
    if(result.length===0){
        deleteAll('repositories-div');
        const repoDiv = document.getElementById('repositories-div');
        repoDiv.append(dataLoadFailTxt);
        return false;
    }
    drawRepoCards(result);
    return true;
}

// 데이터 갱신
let isRepo = false;
function updateRepo(userIdx) {
    if(isRepo){
        swal('1분 후 시도해 주세요', 'Repository 정보가 이미 최신 상태 입니다.', 'warning');
        return false;
    }
    isRepo = true;
    const state = updateData('repository', userIdx);
    if(!state){
        return false;
    }
    swal('갱신 성공', 'Repository 정보가 갱신되었습니다.', 'info');
    initRepo(userIdx);
    setTimeout(()=>{
        isRepo = false;
    }, 60000)
}

// 리포짓 정보 카드들을 담을 div 컴포넌트 구성
function drawRepoCards(data) {
    deleteAll('repositories-div');

    // 마지막 갱신 시간
    document.getElementById('repository-updated-text').innerHTML = `Last Updated: ${data[0].updated_dt}`;

    const repositoriesDiv = document.getElementById('repositories-div');

    // pinned reposit 정보만 추출
    data = JSON.parse(data[0].data);
    if (data.length) {
        const cardTemplate = document.querySelector('#repo-card-template').innerHTML;
        let cards = "";
        // 리포짓 개수 만큼 카드 생성
        data.forEach((node) => {
            let stat = makeStat(node);
            cards += cardTemplate.replace("{name}",node.name)
                .replace("{data}", encodeURIComponent(JSON.stringify(node)))
                .replace("{description}", node.description)
                .replace("{stat}",stat)
                .replace("{disk}", node.diskUsage);
            repositoriesDiv.innerHTML = cards;
        });
    } else { // pinned reposit 0개일 때
        repositoriesDiv.append(dataNullDiv("지정된 대표 저장소가 없어요 🤔 <br> 깃허브에 지정해주세요!"));
    }
}

function makeStat(node) {
    const starCol = document.getElementById('star-count-template').innerHTML;
    const langCol = document.getElementById('primary-lang-template').innerHTML;
    const forkCol = document.getElementById('fork-count-template').innerHTML;
    let stat = '';
    // star 개수
    if (node.totalCount !== 0) {
        stat += starCol.replace("{count}", node.totalCount);
    }
    // 주요 언어
    if (node.primaryLanguage) {
        stat += langCol.replace("{color}", node.primaryLanguage.color)
            .replace("{name}",node.primaryLanguage.name)
    }
    // fork 개수
    if (node.forkCount !== 0) {
        stat += forkCol.replace("{count}",node.forkCount);
    }
    return stat
}

// 카드 뷰를 눌렀을 때 보여질 모달 정보 설정
function makeRepoModal(el) {
    const node = JSON.parse(decodeURIComponent(el.parentElement.querySelector('input').value));

    // 저장소 이름, 깃헙 주소
    document.getElementById('repoModalTitle').innerText = node.name;
    document.getElementById('titleLink').setAttribute('href', node.url);

    // star, fork 개수
    const starTemplate = document.getElementById('star-count-template').innerHTML
    const forkTemplate = document.getElementById('fork-count-template').innerHTML
    let stat = ''
    if(node.totalCount){
        stat+=starTemplate.replace("{count}", node.totalCount);
    }
    if(node.forkCount){
        stat+=forkTemplate.replace("{count}", node.forkCount);
    }
    document.getElementById('modalCount').innerHTML = stat;

    // 저장소 설명
    if(node.description){
        document.getElementById('modalDescDiv').hidden = false;
        document.getElementById('repoModalDesc').innerText = node.description;
    } else {
        document.getElementById('modalDescDiv').hidden = true;
    }

    // 연결된 페이지 링크
    if (node.homepageUrl) {
        document.getElementById('modalPageUrlDiv').hidden=false;
        const pageUrl = document.getElementById('modalPageUrl');
        pageUrl.innerText = node.homepageUrl
        pageUrl.setAttribute('href', node.homepageUrl);
    } else {
        document.getElementById('modalPageUrlDiv').hidden=true;
    }

    // 언어 정보가 존재할 때
    if(node.languages.edges.length!==0){
        document.getElementById('modalLangDiv').hidden=false;
        // 사용된 언어 정보 내림차순 정렬
        const languages = node.languages.edges;
        languages.sort((a, b) => {
            return b.size - a.size;
        })

        let langLabels = [];
        let langValues = [];
        let langColors = [];

        // 언어 사용 설명
        const langDesc = document.getElementById('modalLangDesc');
        deleteAll('modalLangDesc');
        const langTemplate = document.querySelector('#lang-desc-text').innerHTML;
        let langText = '';
        for (let i = 0; i < languages.length; i++) {
            langText += langTemplate.replace("{color}", languages[i].node.color)
                .replace("{name}", languages[i].node.name)
                .replace("{size}", languages[i].size)
            langLabels.push(languages[i].node.name);
            langValues.push(languages[i].size);
            langColors.push(languages[i].node.color);
        }
        langDesc.innerHTML = langText;

        // 언어 사용 통계 그래프 -> 지우고 다시 그림
        const langChartDiv = document.getElementById('modalLangChart');
        deleteAll('modalLangChart');
        const langChart = drawDoughnut(langValues, langColors, langLabels, false, 'repo');
        langChartDiv.append(langChart);
    } else {
        document.getElementById('modalLangDiv').hidden=true;
    }


    if(node.contributor.length!==0){
        document.getElementById('modalContriDiv').hidden=false;
        // 기여자 기여도에 따른 내림차순 정렬
        const contributor = node.contributor;
        contributor.sort((a, b) => {
            return b.contributions - a.contributions;
        })

        let contriNames = [];
        let contriValues = [];

        // 기여자 프로필 생성
        const contriCol = document.getElementById('contriProfileCol');
        deleteAll('contriProfileCol');

        const profileTemplate = document.getElementById('modal-profile-thumbnail').innerHTML;
        let profiles = ''
        for (let i = 0; i < contributor.length; i++) {
            profiles += profileTemplate.replace("{src}", contributor[i].avatar_url)
                .replace("{profileLink}", contributor[i].html_url)
                .replace("{name}", contributor[i].login)
                .replace("{commit}", contributor[i].contributions)
            contriNames.push(contributor[i].login);
            contriValues.push(contributor[i].contributions);
        }
        contriCol.innerHTML = profiles;

        // 랜덤 색상 생성
        let randomColor = [];
        for (let i = 0; i < contriValues.length; i++) {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            randomColor.push(`rgb(${r},${g},${b})`);
        }

        // 기여도 통계 그래프 -> 지우고 다시 그림
        const contriChartDiv = document.getElementById('contriChartDiv');
        deleteAll('contriChartDiv');
        const contriChart = drawDoughnut(contriValues, randomColor, contriNames, false, 'repo');
        contriChartDiv.append(contriChart);
    } else {
        document.getElementById('modalContriDiv').hidden=true;
    }

    document.getElementById('modalEmptyDiv').hidden = !(node.languages.edges.length === 0 && node.contributor.length === 0);
}
