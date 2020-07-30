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
        drawRepositCards(result)
    }).fail(function () {
        $('div#repositories-div').empty();
        const repositDiv = document.getElementById('repositories-div')
        const failTxt = document.createElement("h4")
        failTxt.innerText="데이터 로드에 실패했습니다."
        repositDiv.append(failTxt)
    })
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

function drawRepositCards(data) {
    document.getElementById('repository-updated-text').innerHTML="Last Updated: "+data[0].updated_dt

    const repositoriesDiv = document.getElementById('repositories-div')

    const row = document.createElement('div')
    row.className="row"

    // pinned reposit 정보만 추출
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
    stretchedLink.style.cursor = "pointer"
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

function makeModal(node) {
    // draw modal
    const title = document.getElementById('repoModalTitle')
    title.innerText = node.name
    const titleLink = document.getElementById('titleLink')
    titleLink.setAttribute('href', node.url)
    const star = document.getElementById('modalCount')
    star.innerHTML = "<i class=\"fas fa-star\" style=\"color: #808080\"></i>&nbsp"+node.totalCount+"&nbsp;<i class=\"fas fa-code-branch\" style=\"color: #808080\"></i>&nbsp"+node.forkCount
    const description = document.getElementById('repoModalDesc')
    description.innerText = node.description

    $('div#ModalPageUrl').empty()
    if(node.homepageUrl){
        const pageUrl = document.getElementById('ModalPageUrl')
        pageUrl.innerHTML="<p style='overflow: hidden; text-overflow: ellipsis; display: inline-block; width: 100%; white-space: nowrap;'><i class=\"fas fa-link\" style='color:gray;'></i>&nbsp;&nbsp;<a style='color:gray;' href='"+node.homepageUrl+"'>"+node.homepageUrl+"</a></p>"
    }
    // 언어 사용 정렬
    const languages = node.languages.edges
    languages.sort(function (a, b) {
        return b.size - a.size;
    })

    const langLabels = []
    const langValues = []
    const langColors = []

    // 언어 사용 설명
    const langDesc = document.getElementById("modalLangDesc");
    $('div#modalLangDesc').empty();
    if(languages.length) {
        for (let i = 0; i < languages.length; i++) {
            const langText = document.createElement("p")
            langText.innerHTML = "<i class='fas fa-circle' style='color:" + languages[i].node.color + " '></i>&nbsp;&nbsp;" + languages[i].node.name + ": " + languages[i].size + " lines"
            langDesc.append(langText)
            langLabels.push(languages[i].node.name)
            langValues.push(languages[i].size)
            langColors.push(languages[i].node.color)

        }
    } else {
        const langText = document.createElement("p")
        langText.innerHTML = "<i class='fas fa-circle' style='color:gray;'></i>&nbsp;&nbsp;Markdown All files"
        langDesc.append(langText)
        langLabels.push('Markdown')
        langValues.push(1)
        langColors.push('gray')
    }

    // 언어 사용 통계 그래프
    $('div#modalLangChart').empty();
    const langChartDiv = document.getElementById('modalLangChart')
    const langChart = document.createElement('canvas')
    langChart.width=100
    langChart.height=100
    const ctx = langChart.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: langValues,
                backgroundColor: langColors,
            }],

            // These labels appear in the legend and in the tooltips when hovering different arcs
            labels: langLabels
        },
        options: {
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        //get the concerned dataset
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        //calculate the total of this data set
                        const total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                            return previousValue + currentValue;
                        });
                        //get the current items value
                        const currentValue = dataset.data[tooltipItem.index];
                        //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        return langLabels[tooltipItem.index]+" "+percentage + "%";
                    }
                }
            },
            legend: {
                display: false
            }
        }
    });
    langChartDiv.append(langChart);

    // 기여도 정렬
    const contributor = node.contributor
    contributor.sort(function (a, b) {
        return b.contributions - a.contributions;
    })

    const contriNames = []
    const contriValues = []

    const contriCol = document.getElementById('contriProfileCol')
    $('div#contriProfileCol').empty();
    for(let i=0; i<contributor.length; i++){
        const contributorProfile = document.createElement('div')
        contributorProfile.className="row"
        contributorProfile.style.marginTop="1em"
        const profileImg = document.createElement('div')
        profileImg.className='col'
        profileImg.innerHTML = "<img src='"+contributor[i].avatar_url+"' class=\"img-thumbnail\"/>"

        const profileDesc = document.createElement('div')
        profileDesc.className='col'
        profileDesc.innerHTML="<p><a href="+contributor[i].html_url+"><span style='font-weight: bold'>"+contributor[i].login+"</span></a> "+contributor[i].contributions+" commits</p>"

        contributorProfile.append(profileImg)
        contributorProfile.append(profileDesc)
        contriCol.append(contributorProfile)

        contriNames.push(contributor[i].login)
        contriValues.push(contributor[i].contributions)
    }

    // 랜덤 색상 생성
    randomColor = []
    for(let i=0; i<contriValues.length; i++){
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        randomColor.push("rgb(" + r + "," + g + "," + b + ")");
    }

    // 언어 사용 통계 그래프
    $('div#contriChartDiv').empty();
    const contriChartDiv = document.getElementById('contriChartDiv')
    const contriChart = document.createElement('canvas')
    contriChart.width=100
    contriChart.height=100
    const ctx2 = contriChart.getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: contriValues,
                backgroundColor: randomColor,
            }],

            // These labels appear in the legend and in the tooltips when hovering different arcs
            labels: contriNames
        },
        options: {
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        //get the concerned dataset
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        //calculate the total of this data set
                        const total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                            return previousValue + currentValue;
                        });
                        //get the current items value
                        const currentValue = dataset.data[tooltipItem.index];
                        //calculate the precentage based on the total and current item, also this does a rough rounding to give a whole number
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        return contriNames[tooltipItem.index]+" "+percentage + "%";
                    }
                }
            },
            legend: {
                display: false
            }
        }
    });
    contriChartDiv.append(contriChart);
}
