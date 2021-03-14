$(function () {
    function loadCalendar(iMonth) {
        var iDay = '01';
        if(typeof iMonth !== 'undefined') {

        } else {
            var d = new Date();
            var n = d.getMonth();
            var o = d.getDate();
            iMonth = n+1;
            iDay = o;
        }
        var iContactID = $('#weosContactID').val();

        console.log('load contact slots for '+iContactID);

        $.post('/web/datepicker', {contact_id:iContactID,month:iMonth}, function(retJSON) {
            let aDates = $.parseJSON(retJSON);
            let enabledDates = [];
            for(let i = 0;i < aDates.length;i++) {
                enabledDates.push(moment(aDates[i]), new Date(parseInt(aDates[i].split('-')[0]), parseInt(aDates[i].split('-')[1]), parseInt(aDates[i].split('-')[2])), aDates[i] + " 08:00");
            }
            $('#datetimepicker13').datetimepicker('destroy');
            $('#datetimepicker13').datetimepicker({
                inline: true,
                locale: 'de',
                format: 'L',
                defaultDate: "2021-"+iMonth+"-"+iDay,
                enabledDates: enabledDates,
                sideBySide: true
            });
        });
    }

    $('#datetimepicker13').on('update.datetimepicker', function (e) {
       var newDate = e.viewDate._d;
       var iNewMonth = newDate.getMonth()+1;
       loadCalendar(iNewMonth);
    });

    $('#datetimepicker13').on('change.datetimepicker', function (e) {
        var newDate = e.date._d;
        var iContactID = $('#weosContactID').val();
        var dateFormatted = newDate.getFullYear()+'-'+(newDate.getMonth()+1)+'-'+newDate.getDate();

        $.post('/web/timeslots', {contact_id:iContactID,date:dateFormatted}, function(retHTML) {
            $('#timeslotpicker').html(retHTML);
        });
    });

    loadCalendar();
});