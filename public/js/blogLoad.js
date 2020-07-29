function blogLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/blog/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        $('div#blog-div').empty();
        // blog post card view 그리기
        drawBlogCards(result)
    }).fail(function () {
        $('div#blog-div').empty();
        const blogDiv = document.getElementById('blog-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="데이터 로드에 실패했습니다."
        blogDiv.append(failTxt)
    })
}

function blogUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/blog/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        blogLoad()
    })
}

function drawBlogCards(data) {
    const blogDiv = document.getElementById('blog-div')
    const row = document.createElement('div')
    row.className="row"

    data = JSON.parse(data[0].data)
    if(data.length) {
        data.forEach(function (node) {
            const col = document.createElement('div')
            col.className = "col-12 col-sm-12 col-md-12"
            col.style.padding = "1em"

            const card = makeCard(node)
            col.append(card)
            row.append(col)
        })
    } else {
        const failTxt = document.createElement("div")
        failTxt.className="col text-center"
        failTxt.style.margin="1em"
        failTxt.style.color="gray"
        failTxt.innerHTML="<h4>블로그 포스팅이 없습니다</h4>"
        row.append(failTxt)
    }
    blogDiv.append(row);
}

function makeCard(node) {
    const card = document.createElement('div')
    card.className = "card"

    const cardBody = document.createElement('div')
    cardBody.className = "card-body"

    const title = document.createElement('div')
    const description = document.createElement('div')
    const stat = document.createElement('div')
    stat.className="row"
    const leftRow = document.createElement('div')
    leftRow.className="row"
    const leftCol = document.createElement('div')
    leftCol.className="col"
    const rightCol = document.createElement('div')
    rightCol.className="col"
    rightCol.style.textAlign="right"
    const stargazer = document.createElement('col')
    stargazer.style.marginLeft="1em"
    const language = document.createElement('col')
    language.style.marginLeft="0.3em"
    const fork = document.createElement('col')
    fork.style.marginLeft="0.3em"

    title.innerHTML="<a href=" + node.url + "><h5>" + node.name + "</h5></a><hr>"
    description.innerHTML = "<p>"+node.description+"</p>"
    if(node.totalCount!==0){
        stargazer.innerHTML="<p style='color:#808080;'><i class=\"fas fa-star\" style=\"color: #808080\"></i>&nbsp"+node.totalCount+"</p>"
    }
    if(node.primaryLanguage) {
        language.innerHTML = "<p style='color:#808080;'><i class=\"fas fa-circle\" style='color:"+node.primaryLanguage.color+"'></i>&nbsp" + node.primaryLanguage.name + "</p>"
    }
    if(node.forkCount!==0) {
        fork.innerHTML = "<p style='color:#808080;'><i class=\"fas fa-code-branch\" style=\"color: #808080\"></i>&nbsp" + node.forkCount + "</p>"
    }

    rightCol.innerHTML = "<p>"+node.diskUsage+" KB</p>"

    const stretchedLink = document.createElement('a')
    stretchedLink.className='stretched-link'
    stretchedLink.setAttribute('data-toggle', 'modal')
    stretchedLink.setAttribute('data-target', '#repoModal')
    stretchedLink.onclick = function(){
        makeModal(node)
    }

    leftRow.append(stargazer)
    leftRow.append(language)
    leftRow.append(fork)
    leftCol.append(leftRow)
    // 카드 바디 안에 내용 채우기
    stat.append(leftCol)
    stat.append(rightCol)
    cardBody.append(title)
    cardBody.append(description)
    cardBody.append(stat)
    cardBody.append(stretchedLink)
    card.append(cardBody)
    return card
}
