/* 사용자 블로그 최신 피드 데이터 로드 & 컴포넌트 생성 */

// 데이터 로드
function initBlog(userIdx) {
    const result = getData('blog', userIdx);
    if(result.length===0){
        deleteAll('blog-div');
        const blogDiv = document.getElementById('blog-div');
        blogDiv.style.textAlign='center';
        blogDiv.append(dataLoadFailTxt);
        return false;
    }
    if(!updatedState(result[0].updated_dt)){
        // 마지막 업데이트가 하루 이상 지났을 시 다시 갱신
        updateBlog(userIdx);
    }
    drawBlogCards(result);
    return true;
}

// 데이터 갱신
function updateBlog(userIdx) {
    const state = updateData('blog', userIdx);
    if(!state){
        return false;
    }
    initBlog(userIdx);
}

// blog-div 내부 채우기
function drawBlogCards(data) {
    deleteAll('blog-div');
    const blogCardsDiv = document.getElementById('blog-div');

    data = JSON.parse(data[0].data);
    if (data.length) {
        const cardTemplate = document.querySelector('#blog-card-template').innerHTML;
        let cards = "";
        // 데이터 개수 만큼 카드뷰 생성
        data.forEach((node) => {
            let badges = makeBadge(node);
            cards += cardTemplate.replace("{title}",node.title)
                .replace("{date}", node.date)
                .replace("{badge}",badges)
                .replace("{link}", node.link);
            blogCardsDiv.innerHTML = cards;
        })
    } else {
        blogCardsDiv.append(dataNullDiv('블로그 포스팅이 없습니다.'));
    }
}

function makeBadge(node) {
    const badge = `<span class="badge badge-pill badge-secondary" style="padding: 0.5em; margin-top: 0.2em; margin-right: 0.3em;">{category}</span>`;
    let badges = '';
    // 카테고리가 한개일 경우, 문자열로 반환됨
    if (typeof node.category === 'string') {
        badges = badge.replace("{category}", node.category);
    }
    // 한개 이상일 때, 배열 형식 반환
    if (typeof node.category === 'object') {
        for (let i = 0; i < node.category.length; i++) {
            badges += badge.replace("{category}", node.category[i]);
        }
    }
    return badges;
}
