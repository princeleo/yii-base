var Boss = Boss || {};

Boss.tools = {
    getQueryString : function(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }
};

/*
 * ajax请求
 * 调用: DUOBAO.ajax.loading(url, type, callback)
 * DUOBAO.ajax.loading('http://localhost/more.json', 'post', function(result){console.log(result);})
 * 参数: url: ajax请求地址 type: ajax请求类型 callback: ajax请求成功回调函数
 */
Boss.ajax = {
    show: function(){
        Boss.successText('',true);
    },
    hide: function(){
        $('#maskText').hide();
    },
    loading: function(url, data,callback, affirmEvent,dataType,type){
        var _ajax = function(){
            $.ajax({
                url: url,
                type: type ? type : 'get',
                data : data,
                dataType: dataType,
                beforeSend: function(){
                    Boss.ajax.show();
                },
                success: function(result) {
                    if(result['retCode'] == -100002){ //未登录
                        //do something
                        $('.login-modal-lg').modal('show');
                    }else if(result['retCode'] == -100016){ //无权限
                        Boss.alert(result['retCode'] || '无权限操作');
                    }else if(typeof callback == 'function') {
                        callback(result);
                    }
                },
                complete: function(){
                    Boss.is_submit = false;
                    Boss.ajax.hide();
                },
                error: function(){
                    Boss.alert('数据返回失败');
                    // 提交按钮已被禁用
                }
            })
        };
        if(typeof affirmEvent == 'object'){
            //var title = typeof affirmEvent.title === 'function' ? affirmEvent.title() : affirmEvent.title;
            Boss.alert(affirmEvent,function(){
                Boss.is_submit = false;
                $('button[type="submit"]').removeAttr('disabled');
            },{
                affirmEvent:_ajax
            });
        }else{
            _ajax();
        }
    }
};
Boss._post = function(url,data,callback,affirmEvent,dataType){
    if(Boss.is_submit){
        return false;
    }
    Boss.is_submit = true;
    Boss.ajax.loading(url, data,callback,affirmEvent,dataType || 'JSON', 'post');
};
Boss._get = function(url,callback,affirmEvent,dataType){
    Boss.ajax.loading(url, {},callback,affirmEvent,dataType || 'JSON', 'get');
};
Boss.alert = function($msg,callBack,$options){
    var box_id = '#boss-alert';
    var title = typeof $msg.title === 'function' ? $msg.title() : $msg.title;
    var content = typeof $msg.content === 'function' ? $msg.content() : $msg.content;
    $msg = {
        title : typeof $msg == 'object' ? title : $msg,
        content : typeof $msg == 'object' ? content : ''
    };
    $(box_id).remove(); //清空之前的modal
    var html = [];
    html.push('<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true" id="boss-alert">');
    html.push('<div class="modal-dialog modal-sm">');
    html.push('<div class="modal-content">');

    html.push('<div class="modal-header">');
    html.push('<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>');
    html.push('<h4 class="modal-title">温馨提示</h4>');
    html.push('</div>');

    html.push('<div class="modal-body"><div class="new-modal-body"><div class="reinforce-hint">');
    //html.push('<span class="new-hint-blue"><em class="question-icon"></em></span>');
    html.push('<h5 class="reinforce-text modal-h1" style="text-align:center;">'+$msg.title+'</h5>');
    if($msg.content){
        html.push('<div class="modal-text text-muted fs12" style="text-align:center;">'+$msg.content+'</div>');
    }
    html.push('</div></div></div>');

    html.push('<div class="modal-footer">');
    html.push('<button type="button" class="btn new-btn new-btn-primary">确定</button><button type="button" class="btn btn-alt new-btn btn-default" data-dismiss="modal">取消</button>');
    html.push('</div>');

    html.push('</div></div></div>');
    $('body').append(html.join(''));

    $options = $options || {};
    if(typeof $options.title != undefined){
        $(box_id).find('.modal-title').text($options.title);
    }
    if(typeof $options.affirmEvent == 'function'){
        $(box_id).find('.new-btn-primary').click(function(){
            $(box_id).modal('hide');
            $('.modal-backdrop').remove();
            $options.affirmEvent();
        });
    }else{
        $(box_id).find('.modal-footer').hide();
    }
    $(box_id).modal('show');
    if(typeof callBack == 'function'){
        $(box_id).on('hidden.bs.modal',function(e){
            callBack();
        });
    }
};

Boss.successText = function (text,laoding,issuccess, callback) {
    var pop;
    if($('#maskText').html() == undefined || $('#maskText').html() == ''){
        var html = [];
        html.push(' <div class="poplayer-box layer-type2" id="maskText" style="z-index:999999;display: none">');
        html.push('<div class="poplayer-bg"></div>');
        html.push('<div class="poplayer-content">');
        html.push('<div class="poplayer-section">');
        html.push(' <div class="layer-type layer-style">');
        html.push('<div class="layercont">');
        html.push('<div class="layer-text"></div>');
        html.push('</div></div></div></div></div>');
        $('body').append(html.join(''));
    }
    pop =  $('#maskText');
    if(!laoding){
        pop.show().find('.layer-text').text(text);
    }else{
        pop.show().find('.layer-text').html('<div class="layer-loading"></div><div class="layer-loading-text">加载中...</div>');
    }
    setTimeout(function () {
        typeof callback === 'function' && callback.call(this);
        pop.hide();
    }, 3000);
};

Boss.is_submit = false;
Boss.hideModal = function(){
    $('.modal').modal('hide');
};
Boss.init = (function(){
    $(function(){
        $('a[ajax-load="true"]').click(function(){
            var url = $(this).attr('href');
            Boss._post(url,{},function($res){
                if($res.retCode != 0){
                    Boss.alert($res.retMsg || '操作失败');
                }else{
                    Boss.alert('操作成功',function(){
                        location.href = location.href;
                    });
                }
            },$(this).attr('affirm') == 'true' ? ($(this).attr('affirm-title') ? {title:$(this).attr('affirm-title'),content:$(this).attr('affirm-content')} : {}) : false);
            return false;
        });
        $('.table tr th .checkall').click(function(){
            if($(this).is(':checked')){
                $(this).parents('table').find('tr').addClass('active');
                $(this).parents('table').find('tr td input[type="checkbox"]').prop("checked","true");
            }else{
                $(this).parents('table').find('tr').removeClass('active');
                $(this).parents('table').find('tr td input[type="checkbox"]').removeProp('checked');
            }
        });
        $('.table tr td input[type="checkbox"]').click(function(){
            if($(this).is(':checked')){
                $(this).parents('tr').addClass('active');
            }else{
                $(this).parents('tr').removeClass('active');
            }
            if($(this).parents('table').find('tr td input[type="checkbox"]').not(':checked').size() > 0){
                $('.table tr th .checkall').prop("checked",false);
            }else{
                $('.table tr th .checkall').prop("checked",true);
            }
        });
    })
})();

Boss.login = function(){
    $(function(){
        var istrue = true;
        function gosubmit(){
            var _this = $('#submitLogin');
            var check = true;
            _this.parent().find('input').each(function(i,item){
                if($(item).val() == '' && $(item).attr('id') != 'goto'){
                    check = false;
                    var msg = ($(item).attr('placeholder')).replace('请输入','');
                    $(item).parents('.fm-wrap').addClass('has-error');
                    $(item).parents('.fm-wrap').find('.help-block').text(msg+'不能为空');
                }
            })
            if(!check) return false;
            _this.text('登录中....');
            _this.addClass("disabled");
            istrue =false;
            Boss._post('/admin/login/ajax',_this.parent().serialize(),function($rs){
                if($rs.retCode != 0){
                    $("#captchaimg").click();
                    $('.fm-wrap').removeClass('has-error');
                    $('.fm-wrap').find('.help-block').text('');
                    if(typeof($rs.retMsg) == 'string'){
                        _this.parent().find('.fm-wrap').eq(0).addClass('has-error');
                        _this.parent().find('.fm-wrap').eq(0).find('.help-block').text($rs.retMsg);
                    }else{
                        $.each($rs.retMsg,function(i,obj){console.log(obj);
                            $('#'+i).parents('.fm-wrap').addClass('has-error');
                            $('#'+i).parents('.fm-wrap').find('.help-block').text(obj[0]);
                        })
                    }
                    _this.text('登录');
                    _this.removeClass("disabled");
                    istrue = true;
                }else{
                    if(self != top){
                        window.parent.Boss.hideModal();
                    }else if(location.pathname != '/admin/login/index'){
                        Boss.hideModal();
                    }else{
                        location.href = $rs.retData && $rs.retData.redirect ? $rs.retData.redirect : '/admin/index/index';
                    }
                }
            })
        }
        $('#submitLogin').click(function(){
            gosubmit();
        });
        $('body').keydown(function(e){
            if(e.keyCode==13){
                if (istrue){
                    gosubmit();
                }
            }
        })
        $('input').focus(function(){
            $(this).parents('.fm-wrap').removeClass('has-error');
            $(this).parents('.fm-wrap').find('.help-block').text('');
        });
        $("#captchaimg").click(function(){
            var $this = $(this);
            $this.attr('src','/admin/login/captcha?rand='+Math.random());
            $("#verifyCode").val('');
            $($this.parents('.fm-wrap')).removeClass('has-error');
        });
    });
}();


/*
 * tips组件
 * author: yilujun
 * date: 2017-1-17
 */
Boss.tips = {
    success: function($msg,callback) {
        var tempSucc = this.template.success($msg);
        this.parseFn(tempSucc,callback);
    },
    fail: function($msg,callback) {
        var tempFail = this.template.fail($msg);
        this.parseFn(tempFail,callback);
    },
    warn: function($msg,callback) {
        var tempWarn = this.template.warn($msg);
        this.parseFn(tempWarn,callback);
    },
    template: {
        success: function(msg) {
            var arr = [];
            arr.push('<div class="popup-tips-mask"></div>');
            arr.push('<div class="popup-tips popup-tips-success">');
            arr.push('<div class="popup-tips-content">');
            arr.push('<h3>'+msg+'</h3>');
            arr.push('</div>');
            arr.push('<a href="javascript:;" class="btn-popup-tips-close">关闭</a>');
            arr.push('</div>');
            return arr.join('');
        },
        fail: function(msg) {
            var arr = [];
            arr.push('<div class="popup-tips-mask"></div>');
            arr.push('<div class="popup-tips popup-tips-fail">');
            arr.push('<div class="popup-tips-content">');
            arr.push('<h3>'+msg+'</h3><p>请刷新后重试</p>');
            arr.push('</div>');
            arr.push('<a href="javascript:;" class="btn-popup-tips-close">关闭</a>');
            arr.push('</div>');
            return arr.join('');
        },
        warn: function(msg) {
            var arr = [];
            arr.push('<div class="popup-tips-mask"></div>');
            arr.push('<div class="popup-tips popup-tips-warn">');
            arr.push('<div class="popup-tips-content">');
            arr.push('<h3>'+msg+'</h3>');
            arr.push('</div>');
            arr.push('<a href="javascript:;" class="btn-popup-tips-close">关闭</a>');
            arr.push('</div>');
            return arr.join('');
        }
    },
    close: function(callback) {
        $('.popup-tips-mask, .popup-tips').remove();
        if(typeof callback == 'function'){
                callback();
        }
    },
    autoClose: function(callback) {
        var that = this;
        setTimeout(function() {
            that.close(callback);
        }, 2000);
    },
    show: function() {
        $('.popup-tips-mask, .popup-tips').fadeIn();
    },
    hide: function() {
        $('.popup-tips-mask, .popup-tips').fadeOut();
    },
    clickClose: function(callback) {
        var that = this;
        $('.btn-popup-tips-close').on('click', function() {
            that.close(callback);
        });
    },
    parseFn: function(temp,callback) {
        var that = this;
        $('body').append(temp);
        that.show();
        that.autoClose(callback);
        that.clickClose(callback);
    }
};
