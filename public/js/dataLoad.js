let userIdx;
// 데이터 로드 실패 텍스트
const dataLoadFailTxt = document.createElement("h4")
dataLoadFailTxt.innerText = "데이터가 없습니다."

window.onload = function () {
    this.userIdx = window.location.pathname.split("/").pop();
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
    // 블로그 포스트 정보가 들어갈 div 요소가 있는지 확인
    if (document.getElementById('blog-div')) {
        blogLoad()
    }
}
