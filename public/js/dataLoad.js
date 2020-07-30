let userIdx;
window.onload = function () {
    this.userIdx = window.location.pathname.split("/").pop();
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
    // 블로그가 들어갈 div 요소가 있는 페이지 확인
    if(document.getElementById('blog-div')){
        blogLoad()
    }
}
