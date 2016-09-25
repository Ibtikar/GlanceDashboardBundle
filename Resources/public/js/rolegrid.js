$('body').on('ajaxCallback',function(){
    var permissions=['view','create','edit','delete','viewone','unpublish','publishControl','activate','advancesearch','manage','stopresume','userdata','move','viewcomment','managecomment','board','autopublish','resendmail','export','import'];
    $('.permission-grid-container .permission-grid tr').not(':first').remove();

    $('.checkbox').each(function(){
        var moduleElm=this;
        var moduleName=$(this).find('input').val().match(/_(.*?)_/i)[1];
        if($('tr.'+moduleName.toLowerCase()).length !== 1){
            var permissionsRow = "";
            $(permissions).each(function(){
                permissionsRow += '<td class="'+this+' text-center"></td>';
            });
            $('.permission-grid tbody').append('<tr class="'+moduleName.toLowerCase()+'"><td class="address"></td>'+permissionsRow+'</tr>');
            $('.'+moduleName.toLowerCase()+' .address').html($(this).find('label').text().split(' ')[1].replace(/-/g,' '));
        }
        $(permissions).each(function(index,val){
            if($(moduleElm).find('[value$="'+val.toUpperCase()+'"]').length > 0)
                $(moduleElm).find('input').appendTo($('.'+moduleName.toLowerCase()+' .'+val));
        });

    });

    var grid = $('.permission-grid-container .permission-grid').parent().clone();
    $('#form_permissions').replaceWith(grid);
    $(".table-responsive").parent().removeClass("col-md-6").addClass("col-md-12");
});
$('body').trigger('ajaxCallback');
