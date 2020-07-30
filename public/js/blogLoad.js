function blogLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/blog/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        const timeDiff = new Date()-new Date(result[0].updated_dt)
        const dayDiff = Math.floor(timeDiff/1000/60/60/24);
        if(dayDiff>=1){
            blogUpdate()
        }
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

            const card = makeBlogCard(node)
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

function makeBlogCard(node) {
    const card = document.createElement('div')
    card.className = "card"

    const cardBody = document.createElement('div')
    cardBody.className = "card-body"

    const titleRow = document.createElement('div')
    titleRow.className = 'row'
    const titleLeftCol = document.createElement('div')
    titleLeftCol.className = 'col-auto'
    titleLeftCol.innerHTML = "<a href=" + node.link + "><h5>" + node.title + "</h5></a>"
    const titleRightCol = document.createElement('div')
    titleRightCol.className = 'col'
    titleRightCol.style.textAlign='right'
    titleRightCol.innerHTML = "<p style='color: gray'>"+node.date+"</p>"

    const tagRow = document.createElement('div')
    tagRow.className = 'row'
    const tagCol = document.createElement('div')
    tagCol.className = 'col'

    if(typeof node.category === 'string'){
        const pillBadge = document.createElement('span')
        pillBadge.className = "badge badge-pill badge-secondary"
        pillBadge.style.padding = '0.5em'
        pillBadge.style.margin = '0.2em'
        pillBadge.innerText = node.category
        tagCol.append(pillBadge)
    }
    if(typeof node.category === 'object'){
        for(let i=0; i<node.category.length; i++){
            const pillBadge = document.createElement('span')
            pillBadge.className = "badge badge-pill badge-secondary"
            pillBadge.style.padding = '0.5em'
            pillBadge.style.margin = '0.2em'
            pillBadge.innerText = node.category[i]
            tagCol.append(pillBadge)
        }
    }

    const stretchedLink = document.createElement('a')
    stretchedLink.className='stretched-link'
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
