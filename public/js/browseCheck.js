if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        var agent = navigator.userAgent.toLowerCase();

        // IE11 -> Netscape, IE8이상 -> Trident
        if ((navigator.appName == 'Netscape' && navigator.userAgent.search('Trident') != -1) || (agent.indexOf("msie") != -1)) {
            alert("인터넷 익스플로러는 지원하지 않습니다.");
        }
    });
}
