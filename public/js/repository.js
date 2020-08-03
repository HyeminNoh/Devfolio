/* 대표 저장소 데이터 로드 & 카드 뷰 생성 */

// 데이터 로드
function repositoriesLoad(userIdx) {
    $.ajax({
        url: `http://127.0.0.1:8000/report/ ${userIdx} /repository/show`,
        method: 'GET',
        dataType: 'json'
    }).done((result) => {
        // calendar view 자리 다시 그리기
        drawRepoCards(result);
    }).fail(() => {
        deleteAll('repositories-div');
        const repoDiv = document.getElementById('repositories-div');
        repoDiv.append(dataLoadFailTxt);
    })
}

// 데이터 갱신
function repositoriesUpdate(userIdx) {
    $.ajax({
        url: `http://127.0.0.1:8000/report/${userIdx}/repository/update`,
        method: 'GET',
        dataType: 'json'
    }).always(() => {
        repositoriesLoad(userIdx);
    })
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
        // 리포짓 개수 만큼 카드 생성
        data.forEach((node) => {
            const cardCol = document.createElement('div');
            cardCol.className = 'col-12 col-sm-12 col-md-12';
            cardCol.style.padding = '1em';
            const card = makeRepoCard(node);
            cardCol.append(card);
            repositoriesDiv.append(cardCol);
        });
    } else { // pinned reposit 0개일 때
        repositoriesDiv.append(dataNullDiv('지정된 대표 저장소가 없습니다.'));
    }
}

// 전달받은 데이터 기준 카드뷰 생성
function makeRepoCard(node) {
    const card = document.createElement('div');
    card.className = 'card';

    const cardBody = document.createElement('div');
    cardBody.className = 'card-body';

    // 저장소 이름
    const title = document.createElement('div');
    title.innerHTML = `<a href='${node.url}'><h5>${node.name}</h5></a><hr>`;

    // 저장소 설명
    const description = document.createElement('div');
    description.innerHTML = `<p>${node.description}</p>`;

    // 저장소 상태를 담을 div
    const stat = document.createElement('div');
    stat.className = 'row';
    const leftRow = document.createElement('div');
    leftRow.className = 'row';
    const leftCol = document.createElement('div');
    leftCol.className = 'col';
    const rightCol = document.createElement('div');
    rightCol.className = 'col';
    rightCol.style.textAlign = 'right';
    const stargazer = document.createElement('col');
    stargazer.style.marginLeft = '1em';
    const language = document.createElement('col');
    language.style.marginLeft = '0.3em';
    const fork = document.createElement('col');
    fork.style.marginLeft = '0.3em';

    // star 개수
    if (node.totalCount !== 0) {
        stargazer.innerHTML = `<p style='color:#808080;'><i class='fas fa-star' style='color: #808080'></i>&nbsp${node.totalCount}</p>`;
    }

    // 주요 언어
    if (node.primaryLanguage) {
        language.innerHTML = `<p style='color:#808080;'><i class='fas fa-circle' style='color: ${node.primaryLanguage.color}'></i>&nbsp${node.primaryLanguage.name}</p>`;
    }

    // fork 개수
    if (node.forkCount !== 0) {
        fork.innerHTML = `<p style='color:#808080;'><i class='fas fa-code-branch' style='color: #808080'></i>&nbsp${node.forkCount}</p>`;
    }

    // 용량
    rightCol.innerHTML = `<p>${node.diskUsage} KB</p>`;

    // 카드 뷰 전체를 클릭 영역으로 지정
    const stretchedLink = document.createElement('a');
    stretchedLink.className = 'stretched-link';
    stretchedLink.style.cursor = 'pointer';
    stretchedLink.setAttribute('data-toggle', 'modal');
    stretchedLink.setAttribute('data-target', '#repoModal');
    stretchedLink.onclick = () => {
        makeRepoModal(node);
    }

    leftRow.append(stargazer);
    leftRow.append(language);
    leftRow.append(fork);
    leftCol.append(leftRow);

    // 카드 바디 안에 내용 채우기
    stat.append(leftCol);
    stat.append(rightCol);
    cardBody.append(title);
    cardBody.append(description);
    cardBody.append(stat);
    cardBody.append(stretchedLink);
    card.append(cardBody);
    return card;
}

// 카드 뷰를 눌렀을 때 보여질 모달 정보 설정
function makeRepoModal(node) {
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
    if (languages.length) {
        for (let i = 0; i < languages.length; i++) {
            const langText = document.createElement('p');
            langText.innerHTML = `<i class='fas fa-circle' style='color:${languages[i].node.color}'></i>&nbsp;&nbsp;${languages[i].node.name}: ${languages[i].size} bytes`;
            langDesc.append(langText);
            langLabels.push(languages[i].node.name);
            langValues.push(languages[i].size);
            langColors.push(languages[i].node.color);

        }
    } else { // 주요 사용 언어가 없는 경우, markdown언어로만 작성된 경우임
        const langText = document.createElement('p');
        langText.innerHTML = `<i class='fas fa-circle' style='color:gray;'></i>&nbsp;&nbsp;Markdown All files`;
        langDesc.append(langText);
        langLabels.push('Markdown');
        langValues.push(1);
        langColors.push('gray');
    }

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
