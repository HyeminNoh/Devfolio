/* 사용자 블로그 최신 피드 데이터 로드 & 컴포넌트 생성 */

function blogLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/" + this.userIdx + "/blog/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        const timeDiff = new Date() - new Date(result[0].updated_dt)
        const dayDiff = Math.floor(timeDiff / 1000 / 60 / 60 / 24);
        // 날짜가 하루 이상 차이날 때 피드 새로 조회
        if (dayDiff >= 1) {
            blogUpdate()
        }
        // blog post card view 그리기
        drawBlogCards(result)
    }).fail(function () {
        $('div#blog-div').empty();
        const blogDiv = document.getElementById('blog-div')
        blogDiv.style.textAlign='center'
        blogDiv.append(dataLoadFailTxt)
    })
}

function blogUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/report/" + this.userIdx + "/blog/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        blogLoad()
    })
}

function drawBlogCards(data) {
    $('div#blog-div').empty();
    const blogDiv = document.getElementById('blog-div')
    const row = document.createElement('div')
    row.className = "row"

    data = JSON.parse(data[0].data)
    if (data.length) {
        data.forEach(function (node) {
            const col = document.createElement('div')
            col.className = "col-12 col-sm-12 col-md-12"
            col.style.padding = "1em"

            const card = makeBlogCard(node)
            col.append(card)
            row.append(col)
        })
    } else {
        const blogEmptyTxt = document.createElement("div")
        blogEmptyTxt.className = "col text-center"
        blogEmptyTxt.style.margin = "1em"
        blogEmptyTxt.style.color = "gray"
        blogEmptyTxt.innerHTML = "<h4>블로그 포스팅이 없습니다</h4>"
        row.append(blogEmptyTxt)
    }
    blogDiv.append(row);
}

function makeBlogCard(node) {
    const card = document.createElement('div')
    card.className = "card"

    const cardBody = document.createElement('div')
    cardBody.className = "card-body"

    const titleRow = document.createElement('div')
    titleRow.className = 'row'

    // 블로그 포스팅 제목
    const titleLeftCol = document.createElement('div')
    titleLeftCol.className = 'col-auto'
    titleLeftCol.innerHTML = "<a href=" + node.link + "><h5>" + node.title + "</h5></a>"

    // 블로그 포스팅 게시 날짜
    const titleRightCol = document.createElement('div')
    titleRightCol.className = 'col'
    titleRightCol.style.textAlign = 'right'
    titleRightCol.innerHTML = "<p style='color: gray'>" + node.date + "</p>"

    // 게시글 카테고리 종류
    const tagRow = document.createElement('div')
    tagRow.className = 'row'
    const tagCol = document.createElement('div')
    tagCol.className = 'col'

    // 카테고리가 한개일 경우, 문자열로 반환됨
    if (typeof node.category === 'string') {
        const pillBadge = document.createElement('span')
        pillBadge.className = "badge badge-pill badge-secondary"
        pillBadge.style.padding = '0.5em'
        pillBadge.style.margin = '0.2em'
        pillBadge.innerText = node.category
        tagCol.append(pillBadge)
    }
    // 한개 이상일 때, 배열 형식 반환
    if (typeof node.category === 'object') {
        for (let i = 0; i < node.category.length; i++) {
            const pillBadge = document.createElement('span')
            pillBadge.className = "badge badge-pill badge-secondary"
            pillBadge.style.padding = '0.5em'
            pillBadge.style.margin = '0.2em'
            pillBadge.innerText = node.category[i]
            tagCol.append(pillBadge)
        }
    }

    // 카드 뷰 전체를 클릭 영역으로 지정
    const stretchedLink = document.createElement('a')
    stretchedLink.className = 'stretched-link'
    stretchedLink.href = node.link;

    titleRow.append(titleLeftCol)
    titleRow.append(titleRightCol)
    tagRow.append(tagCol)
    cardBody.append(titleRow)
    cardBody.append(tagRow)
    cardBody.append(stretchedLink)

    card.append(cardBody)
    return card
}
