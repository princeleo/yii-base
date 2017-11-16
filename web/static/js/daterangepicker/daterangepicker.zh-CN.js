var locale = {
    "format": 'YYYY-MM-DD',
    //"direction": "rtl",
    "separator": " 至 ",
    "applyLabel": "确定",
    "cancelLabel": "取消",
    "fromLabel": "起始时间",
    "toLabel": "结束时间'",
    "customRangeLabel": "自定义",
    "weekLabel": "W",
    "daysOfWeek": ["日", "一", "二", "三", "四", "五", "六"],
    "monthNames": ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    "firstDay": 1
}
var locale2 = {
    "format": 'YYYY-MM-DD HH:mm:ss',
    "direction": "br",
    "separator": " 至 ",
    "applyLabel": "确定",
    "cancelLabel": "取消",
    "fromLabel": "起始时间",
    "toLabel": "结束时间'",
    "customRangeLabel": "自定义",
    "weekLabel": "W",
    "daysOfWeek": ["日", "一", "二", "三", "四", "五", "六"],
    "monthNames": ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    "firstDay": 1
}
var ranges ={
    //'最近1小时': [moment().subtract('hours',1), moment()],
    '今日': [moment().startOf('day'), moment()],
    '昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
    '最近7日': [moment().subtract('days', 6), moment()],
    '最近30日': [moment().subtract('days', 29), moment()],
    '最近90日': [moment().subtract('days', 89), moment()]
}
var congif_daterangepicker={
    "single":{
        "showDropdowns": true,
        "singleDatePicker": true,
        "locale":locale,
        "opens": "right",
        "drops": "down",
        "autoUpdateInput": false
    },
    "oneTimePicker":{
        "showDropdowns": true,
        "singleDatePicker": true,
        "timePicker": false,
        "timePicker24Hour": true,
        "timePickerSeconds": true,
        "locale":locale2,
        "opens": "right",
        "drops": "down",
        "autoUpdateInput": false
    },
    "singleTimePicker":{
        "showDropdowns": true,
        "singleDatePicker": true,
        "timePicker": true,
        "timePicker24Hour": true,
        "timePickerSeconds": true,
        "locale":locale2,
        "opens": "right",
        "drops": "down",
        "autoUpdateInput": false
    },
    "currentTimePicker":{
        "showDropdowns": true,
        "singleDatePicker": true,
        "timePicker": false,
        "timePicker24Hour": true,
        "timePickerSeconds": true,
        "minDate": moment().add(1,'day'),
        "locale":locale2,
        "opens": "right",
        "drops": "down",
        "autoUpdateInput": false
    },
    "startTimePicker":{
        "showDropdowns": true,
        "singleDatePicker": true,
        "timePicker": true,
        "timePicker24Hour": true,
        "timePickerSeconds": true,
        "locale":locale2,
        "opens": "right",
        "drops": "down",
        "autoUpdateInput": false
    },
    "endTimePicker":{
        "showDropdowns": true,
        "singleDatePicker": true,
        "timePicker": true,
        "timePicker24Hour": true,
        "timePickerSeconds": true,
        "locale":locale2,
        "opens": "right",
        "drops": "down",
        startDate:moment().format("YYYY-MM-DD 23:59:59"),
        "autoUpdateInput": false
    },
    "double":{
        "linkedCalendars": false,
        "showDropdowns": true,
        "alwaysShowCalendars": true,
        "locale":locale,
        "ranges":ranges,
        "opens": "right",
        "drops": "down",
        "autoUpdateInput": false
    },
    "finishDate":{
      "linkedCalendars": false,
      "showDropdowns": true,
      "alwaysShowCalendars": true,
      "maxDate": new Date(),
      "locale":locale,
      "ranges":ranges,
      "opens": "right",
      "drops": "down",
      "autoUpdateInput": false
    }
}
