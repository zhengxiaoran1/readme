var COOKIE_NAME = 'sys_em_username';
$(function() {
    choose_bg();
    changeCode();
    if ($.cookie(COOKIE_NAME)){
        $("#j_username").val($.cookie(COOKIE_NAME));
        $("#j_password").focus();
        $("#j_remember").attr('checked', true);
    } else {
        $("#j_username").focus();
    }
    $("#captcha_img").click(function(){
        changeCode();
    });
    $("#login_form").submit(function(){
        var issubmit = true;
        var i_index  = 0;
        $(this).find('.form-control').each(function(i){
            if ($.trim($(this).val()).length == 0) {
                $(this).css('border', '1px #ff0000 solid');
                issubmit = false;
                if (i_index == 0)
                    i_index  = i;
            }
        });
        if (!issubmit) {
            $(this).find('.form-control').eq(i_index).focus();
            return false;
        }
        var $remember = $("#j_remember");
        if ($remember.attr('checked')) {
            $.cookie(COOKIE_NAME, $("#j_username").val(), { path: '/', expires: 15 });
        } else {
            $.cookie(COOKIE_NAME, null, { path: '/' });  //删除cookie
        }

        $("#login_ok").attr("disabled", true).val('登陆中..');
        /*
         var key = CryptoJS.enc.Base64.parse($("#j_randomKey").val());
         var iv = CryptoJS.enc.Latin1.parse('0102030405060708');
         var password = CryptoJS.AES.encrypt($("#j_password").val(), key, {iv:iv, mode:CryptoJS.mode.CBC, padding:CryptoJS.pad.Pkcs7 });

         $("#j_password").val(password)
         */

        //return true;

        location.href = 'index.html'

    });
});
function changeCode(){
    //$("#captcha_img").attr("src", "sys/login/getCaptcha?t="+ (new Date().getTime()));
}
function choose_bg() {
    var bg = Math.floor(Math.random() * 9 + 1);
    $('body').css('background-image', 'url(/assets/admin/demo/layouts/images/loginbg_0'+ bg +'.jpg)');
}
