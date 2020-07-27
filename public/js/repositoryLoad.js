/* 대표 저장소 데이터 로드 & 카드 뷰 생성 */

function repositoriesLoad() {
    // 데이터 로드
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/repository/show",
        method: "GET",
        dataType: "json"
    }).done(function (result) {
        $('div#repositories-div').empty();
        // calendar view 자리 다시 그리기
        drawRepositCard(result);
    }).fail(function () {
        $('div#repositories-div').empty();
        const repositDiv = document.getElementById('repositories-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="지정된 대표 저장소가 없습니다."
        repositDiv.append(failTxt)
    })
}

function drawRepositCard(data) {
    document.getElementById('repository-updated-text').innerHTML="Last Updated: "+data[0].updated_dt

    const repositoriesDiv = document.getElementById('repositories-div')

    const row = document.createElement('div')
    row.className="row"

    // pinned reposit 정보만 추출
    data = JSON.parse(data[0].data)
    if(data.totalCount) {
        data.edges.forEach(function (edge) {
            const node = edge.node // Json obejct

            const col = document.createElement('div')
            col.className = "col-lg-6 col-md-12"
            col.style.padding = "1em"

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
            if(node.stargazers.totalCount!==0){
                stargazer.innerHTML="<p style='color:#808080;'><i class=\"fas fa-star\" style=\"color: #808080\"></i>&nbsp"+node.stargazers.totalCount+"</p>"
            }
            if(node.primaryLanguage) {
                language.innerHTML = "<p style='color:#808080;'><i class=\"fas fa-circle\" style='color:"+node.primaryLanguage.color+"'></i>&nbsp" + node.primaryLanguage.name + "</p>"
            }
            if(node.forkCount!==0) {
                fork.innerHTML = "<p style='color:#808080;'><i class=\"fas fa-code-branch\" style=\"color: #808080\"></i>&nbsp" + node.stargazers.totalCount + "</p>"
            }

            rightCol.innerHTML = "<p>"+node.diskUsage+" KB</p>"

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
            card.append(cardBody)
            col.append(card)
            row.append(col)
        })
    } else { // pinned reposit 0개일 때
        const failTxt = document.createElement("div")
        failTxt.className="col text-center"
        failTxt.style.margin="1em"
        failTxt.style.color="gray"
        failTxt.innerHTML="<h4>지정된 대표 저장소가 없습니다.</h4>"
        row.append(failTxt)
    }
    repositoriesDiv.append(row);
}

function repositoriesUpdate() {
    // 데이터 갱신
    $.ajax({
        url: "http://127.0.0.1:8000/report/"+this.userIdx+"/repository/update",
        method: "GET",
        dataType: "json"
    }).always(function () {
        repositoriesLoad()
    })
}
