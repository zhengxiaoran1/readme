$(function () {
    /*功能封装*/
    var tip = (function () {
        var tip = $(".mask");
        var btns = $(".btn");
        var dis = $(".describe");

        function slideDown() {
            if (isWeiXin()) {
                tip.show()
            }
        }

        function btnsMove() {
            btns.css("transform", "translateX(0)").css("opacity", 1);
        }

        function txtScale() {
            dis.css("transform", "scale(1)").css("opacity", 1)
        }

        function isWeiXin() {
            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) == 'micromessenger') {
                return true;
            } else {
                return false;
            }
        }

        return {
            slideDown: slideDown,
            btnsMove: btnsMove,
            txtScale: txtScale
        }
    })();

    tip.slideDown();
    tip.btnsMove();
    tip.txtScale();
});