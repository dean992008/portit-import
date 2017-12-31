jQuery(function () {
    var token = jQuery('#token').val();
    var dropzone = $(".portit-import__dropzone").dropzone({ 
        url: '/admin/index.php?route=extension/module/portit_import/addFileMarket&token=' + token,
        paramName: 'file',
        acceptedFiles: '.xlsx',
        success: function (file) {
            refreshList();
        }
    });
    

    jQuery(document).on('click', '.portit-import__files-item-remove', function(e){
        var cur = jQuery(this);
        jQuery.ajax({
            url: '/admin/index.php?route=extension/module/portit_import/removeFile&token='+token,
            type: 'POST',
            data: {
                filename: cur.attr('href').slice(1),
                dir: cur.data('dir')
            },
            success: function(){
                refreshList();
            }
        });
        return false;
    });

    refreshList = function(){
        jQuery.ajax({
            url: '/admin/index.php?route=extension/module/portit_import/listOfMarket&token=' + token,
            dataType: 'json',
            success: function(data){
                jQuery('#filesListMarket').html('');
                for(var i = 0; i < data.length; i++){
                    var item = data[i];
                    var span = jQuery('<span/>').text(item);
                    var link = jQuery('<a/>').attr('href', '#' + item).addClass('portit-import__files-item-remove').data('dir', 'market').text('Удалить');
                    var li = jQuery('<li/>').append(span).append(link);
                    jQuery('#filesListMarket').append(li)
                }
            }
        })
    }
});