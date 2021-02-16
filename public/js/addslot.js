$(function() {
    $('.plcAddTimeSlot').on('click', function () {
        $('.timeSlotList').append('<li class="list-group-item">\n' +
            '                    <div class="row">\n' +
            '                        <div class="col-md-2">\n' +
            '                            <input type="time" name="slot_timestart[]" class="form-control" value="08:00" />\n' +
            '                        </div>\n' +
            '                        <div class="col-md-2">\n' +
            '                            <input type="time" name="slot_timeend[]" class="form-control" value="17:00" />\n' +
            '                        </div>\n' +
            '                    <div class="col-md-1">\n' +
            '                            <a href="#" class="plcRmTimeSlot"><i class="fas fa-trash"></i></a>\n' +
            '                        </div>\n' +
            '                    </div>\n' +
            '                </li>');
        return false;
    });

    $(document).on('click', '.plcRmTimeSlot', function () {
        console.log('rm slot');
       $(this).parent('div').parent('div').parent('li').remove();

       return false;
    });
})