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
        swal('1분 후 시도해 주세요', 'Repository 정보가 이미 최신 상태 입니다.', 'info');
        return false;
    }
    isRepo = true;
    const state = updateData('repository', userIdx);
    if(!state){
        return false;
    }
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
        repositoriesDiv.append(dataNullDiv('지정된 대표 저장소가 없습니다.'));
    }
}

function makeStat(node) {
    let stat = '';
    // star 개수
    if (node.totalCount !== 0) {
        stat += `<div class="col-auto"><p style='color:#808080;'><i class='fas fa-star' style='color: #808080'></i>&nbsp${node.totalCount}</p></div>`;
    }
    // 주요 언어
    if (node.primaryLanguage) {
        stat += `<div class="col-auto"><p style='color:#808080;'><i class='fas fa-circle' style='color: ${node.primaryLanguage.color}'></i>&nbsp${node.primaryLanguage.name}</p></div>`;
    }
    // fork 개수
    if (node.forkCount !== 0) {
        stat += `<div class="col-auto"><p style='color:#808080;'><i class='fas fa-code-branch' style='color: #808080'></i>&nbsp${node.forkCount}</p></div>`;
    }
    return stat

}

// 카드 뷰를 눌렀을 때 보여질 모달 정보 설정
function makeRepoModal(el) {
    const node = JSON.parse(decodeURIComponent(el.parentElement.querySelector('input').value));
    // 저장소 이름
    const title = document.getElementById('repoModalTitle');
    title.innerText = node.name;
    const titleLink = document.getElementById('titleLink');
    titleLink.setAttribute('href', node.url);

    // star 개수
    const star = document.getElementById('modalCount');
    star.innerHTML = `<i class='fas fa-star' style='color: #808080'></i>&nbsp${node.totalCount}&nbsp;<i class='fas fa-code-branch' style='color: #808080'></i>&nbsp ${node.forkCount}`;

    // 저장소 설명
    const description = document.getElementById('repoModalDesc');
    description.innerText = node.description;

    // 연결된 페이지 링크
    deleteAll('modalPageUrl');
    if (node.homepageUrl) {
        const pageUrl = document.getElementById('modalPageUrl');
        pageUrl.innerHTML = `<p style='overflow: hidden; text-overflow: ellipsis; display: inline-block; width: 100%; white-space: nowrap;'><i class='fas fa-link' style='color:gray;'></i>&nbsp;&nbsp;<a style='color:gray;' href='${node.homepageUrl}'>${node.homepageUrl}</a></p>`
    }

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
    const langTemplate = document.querySelector('#lang-text').innerHTML;
    let langText = '';
    if (languages.length) {
        for (let i = 0; i < languages.length; i++) {
            langText += langTemplate.replace("{color}", languages[i].node.color)
                .replace("{name}", languages[i].node.name)
                .replace("{size}", languages[i].size)
            langLabels.push(languages[i].node.name);
            langValues.push(languages[i].size);
            langColors.push(languages[i].node.color);
        }
    } else { // 주요 사용 언어가 없는 경우, markdown언어로만 작성된 경우임
        langText += langTemplate.replace("{color}",'gray')
            .replace("{name}", 'Markdown')
            .replace("{size}", '-');
        langLabels.push('Markdown');
        langValues.push(1);
        langColors.push('gray');
    }
    langDesc.innerHTML = langText;

    // 언어 사용 통계 그래프
    const langChartDiv = document.getElementById('modalLangChart');
    deleteAll('modalLangChart');
    const langChart = drawDoughnut(langValues, langColors, langLabels, false, 'repo');
    langChartDiv.append(langChart);

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
    for (let i = 0; i < contributor.length; i++) {
        const contributorProfile = document.createElement('div');
        contributorProfile.className = 'row';
        contributorProfile.style.marginTop = '1em';
        const profileImg = document.createElement('div');
        profileImg.className = 'col';
        profileImg.innerHTML = `<img src='${contributor[i].avatar_url}' class='img-thumbnail' alt='avatar'/>`;

        const profileDesc = document.createElement('div');
        profileDesc.className = 'col';
        profileDesc.innerHTML = `<p><a href='${contributor[i].html_url}'><span style='font-weight: bold'>${contributor[i].login}</span></a>${contributor[i].contributions} commits</p>`;

        contributorProfile.append(profileImg);
        contributorProfile.append(profileDesc);
        contriCol.append(contributorProfile);

        contriNames.push(contributor[i].login);
        contriValues.push(contributor[i].contributions);
    }

    // 랜덤 색상 생성
    let randomColor = [];
    for (let i = 0; i < contriValues.length; i++) {
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        randomColor.push(`rgb(${r},${g},${b})`);
    }

    // 언어 사용 통계 그래프
    const contriChartDiv = document.getElementById('contriChartDiv');
    deleteAll('contriChartDiv');
    const contriChart = drawDoughnut(contriValues, randomColor, contriNames, false, 'repo');
    contriChartDiv.append(contriChart);
}
