let userIdx;
window.onload = function () {
    this.userIdx = window.location.pathname.split("/").pop();
    calendarLoad()
    repositoriesLoad()
    skillsLoad()
    if(document.getElementById('blog-div')){
        blogLoad()
    }
}
