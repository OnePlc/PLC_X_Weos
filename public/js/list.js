$(function() {
    console.log('init list');
    $('.listFilterCategory').on('click', function () {
        var oCard = $(this).parent('li').parent('ul').parent('div');
        oCard.find('h3').addClass('d-none');
        oCard.find('.bg-primary').removeClass('bg-primary');
        oCard.find('.labelFull').addClass('d-none');
        oCard.find('.labelSmall').removeClass('d-none');
        oCard.find('.p-4').addClass('p-2').removeClass('p-4');
        oCard.parent('div').removeClass('py-4').addClass('py-1');

        $(this).addClass('bg-primary');

        var iCategoryID = $(this).attr('plc-category-id');

        $('.companyList').html('<img src="/vendor/oneplace-weos/img/ajax-loader.gif" /> <br/> Einträge werden geladen');

        $.post('/web/contactlist', JSON.stringify({"zip":"8212","category_idfs":iCategoryID,"is_external":"true"}), function(retHTML) {
            $('.companyList').html(retHTML);
        });

        //  VT1C-GHFZ-WZW0-MBHK-2YKR-XU8D-RYQV-9ADX // websrv01
        /*
        $.post('/app/supplier/list?authtoken=websrv01&authkey=VT1C-GHFZ-WZW0-MBHK-2YKR-XU8D-RYQV-9ADX', JSON.stringify({"zip":"8212","category_idfs":iCategoryID,"is_external":"true"}), function(retJSON) {
            console.log(retJSON);
        }); */

        return false;
    });
})