/*
 *  Document   : formsValidation.js
 *  Author     : pixelcave
 *  Description: Custom javascript code used in Forms Validation page
 */

var FormsValidation = function() {

    return {
        init: function($box,$rule,$isAjax,$submitHandler) {
            /*
             *  Jquery Validation, Check out more examples and documentation at https://github.com/jzaefferer/jquery-validation
             */

            //combine rule
            $.extend($.validator.messages,{
                required: "必填",
                remote: "请修正此字段",
                email: "请输入有效的电子邮件地址",
                url: "请输入有效的网址",
                date: "请输入有效的日期",
                dateISO: "请输入有效的日期 (YYYY-MM-DD)",
                number: "请输入有效的数字",
                digits: "只能输入数字",
                creditcard: "请输入有效的信用卡号码",
                equalTo: "你的输入不相同",
                extension: "请输入有效的后缀",
                maxlength: $.validator.format("最多可以输入 {0} 个字符"),
                minlength: $.validator.format("最少要输入 {0} 个字符"),
                rangelength: $.validator.format("请输入长度在 {0} 到 {1} 之间的字符串"),
                range: $.validator.format("请输入范围在 {0} 到 {1} 之间的数值"),
                max: $.validator.format("请输入不大于 {0} 的数值"),
                min: $.validator.format("请输入不小于 {0} 的数值")
            });
            /* Initialize Form Validation */
            $box.validate($.extend({
                errorClass: 'help-block animation-slideDown', // You can change the animation class for a different entrance animation - check animations page
                errorElement: 'div',
                errorPlacement: function(error, e) {
                    e.parents('.error-div > div').append(error);
                },
                setError:function($res,_this,_form){
                    if(typeof($res.retMsg) == 'string'){
                        _this.settings.errorPlacement('<div class="'+_this.settings.errorClass+'">'+$res.retMsg+'</div>',$(_form).find('input').eq(0));
                        _this.settings.highlight($(_form).find('input').eq(0));
                    }else{
                        $.each($res.retMsg,function(i,obj){
                            var element = $(_form).find('input[name="'+i+'"]');
                            if($(_form).find('input[name="'+i+'"]').val()){
                                element = $(_form).find('input[name="'+i+'"]');
                            }else if($(_form).find('select[name="'+i+'"]').val()){
                                element = $(_form).find('select[name="'+i+'"]');
                            }else{
                                element = $(_form).find('textarea[name="'+i+'"]');
                            }
                            _this.settings.errorPlacement('<div for="'+i+'" class="'+_this.settings.errorClass+'">'+obj+'</div>',element);
                            _this.settings.highlight(element);
                        })
                    }
                },
                highlight: function(e) {
                    $(e).closest('.error-div').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function(e) {
                    // You can use the following if you would like to highlight with green color the input after successful validation!
                    e.closest('.error-div').removeClass('has-success has-error'); // e.closest('.form-group').removeClass('has-success has-error').addClass('has-success');
                    e.closest('.help-block').remove();
                },
                submitHandler: function(form) {
                    if(typeof $submitHandler == 'function'){
                        $submitHandler();
                    }else if(!$isAjax){
                        form.submit();
                    }else{
                        var _this = this;
                        var _form = $(form);
                        $(form).find('button[type="submit"]').attr('disabled',true);
                        Boss._post($(form).attr('action'),$(form).serialize(),function($res){
                            if(typeof _this.settings.errorback !== 'undefined' && $res.retCode != 0){
                                _this.settings.errorback($res,_this,_form);
                            }else if($res.retCode != 0){
                                _this.settings.setError($res,_this,_form);
                            }else{
                                if(typeof _this.settings.callback !== 'undefined'){
                                    _this.settings.callback($res);
                                }
                            }
                            setTimeout(function(){
                                _form.find('button[type="submit"]').removeAttr('disabled');
                            },1500);
                        },_this.settings.affirmEvent || '');
                    }
                }
            },$rule));

            $.validator.addMethod('is_mobile',function(value,element,params){
                re = /^1\d{10}$/
                if (re.test(value)) {
                    return true;
                } else {
                    return false;
                }
            },$.validator.format("请输入有效手机号"));
            $.validator.addMethod('equallength',function(value,element,params){
                if(value.length == params[0]){
                    return true;
                }else{
                    return false;
                }
            },$.validator.format("请输入长度为{0}的字符串"));
            $.validator.addMethod('check_money',function(value,element,params){
                return (/^\d+(\.\d{0,2})?$/).test(value);
            },$.validator.format("金额仅支持两个小数"));
            $.validator.addMethod('check_identity',function(value,element,params){
                return (/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/).test(value);
            },$.validator.format("请输入有效身份证号"));
        }
    };
}();