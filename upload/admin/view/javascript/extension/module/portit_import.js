jQuery(function () {
    var token = jQuery('#token').val();
    var dropzoneId = [
        {
            id: '#market',
            route: 'addFileMarket'
        }, {
            id: '#options',
            route: 'addFileOptions'
        }, {
            id: '#prices',
            route: 'addFilePrices'
        }
    ];
    for (var i = 0; i < dropzoneId.length; i++) {
        var dropzoneInfo = dropzoneId[i];
        // console.log(".portit-import__dropzone" + data.id);
        $(".portit-import__dropzone" + dropzoneInfo.id).dropzone({
            url: '/admin/index.php?route=extension/module/portit_import/' + dropzoneInfo.route + '&token=' + token,
            paramName: 'file',
            acceptedFiles: '.xlsx',
            success: function (file) {
                refreshList();
            } 
        });
    }

    jQuery(document).on('click', '.portit-import__files-item-remove', function (e) {
        var cur = jQuery(this);
        jQuery.ajax({
            url: '/admin/index.php?route=extension/module/portit_import/removeFile&token=' + token,
            type: 'POST',
            data: {
                filename: cur.attr('href').slice(1),
                dir: cur.data('dir')
            },
            success: function () {
                refreshList();
            }
        });
        return false;
    });

    refreshList = function () {
        var settings = [
            {
                route: 'listOfMarket',
                id: '#filesListMarket',
                type: 'market'
            },
            {
                route: 'listOfOptions',
                id: '#filesListOptions',
                type: 'options'
            },
            {
                route: 'listOfPrices',
                id: '#filesListPrices',
                type: 'prices'
            }
        ]
        for (var i = 0; i < settings.length; i++) {
            // var set = settings[i];
            refreshItem(settings[i]);
        }
    }

    refreshItem = function (set) {
        jQuery.ajax({
            url: '/admin/index.php?route=extension/module/portit_import/' + set.route + '&token=' + token,
            dataType: 'json',
            success: function (data) {
                jQuery(set.id).html('');
                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var span = jQuery('<span/>').text(item);
                    var link = jQuery('<a/>').attr('href', '#' + item).addClass('portit-import__files-item-remove').data('dir', set.type).text('Удалить');
                    var li = jQuery('<li/>').append(span).append(link);
                    jQuery(set.id).append(li);
                }
            }
        });
    }
});