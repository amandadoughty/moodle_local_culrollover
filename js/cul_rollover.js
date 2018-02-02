$(document).ready(function() {
    $.fn.editable.defaults.mode = 'inline';    
});

function createEditableFields(params) {
    $(function(){
        $('.migrateondate').editable({
            type: 'dateui',
            success: function(response, newValue) { 
                $('[name="' + $(this).attr('id') + '"]').val($.datepicker.formatDate( '@', newValue))
            }, 
            format: 'dd-mm-yy',
            viewformat: 'dd/M/yyyy', 
            datepicker: {firstDay: 1}
        }); 
    });

    // $(function(){ 
    //     $('.what').editable({
    //         type: 'select',
    //         success: function(response, newValue) { 
    //             $('[name="' + $(this).attr('id') + '"]').val(newValue)
    //         },
    //         value: params.what.value,
    //         source: params.what.source,
    //     });
    // });

    $(function(){ 
        $('.groups').editable({
            type: 'select',
            success: function(response, newValue) { 
                $('[name="' + $(this).attr('id') + '"]').val(newValue)
            },
            value: params.groups.value,
            source: params.groups.source,
        }); 
    });
    $(function(){ 
        $('.merge').editable({
            type: 'select', 
            success: function(response, newValue) { 
                $('[name="' + $(this).attr('id') + '"]').val(newValue)
            }, 
            value: params.merge.value,
            source: params.merge.source
        }); 
    });
    $(function(){ 
        $('.roles').editable({
            type: 'checklist',
            success: function(response, newValue) { 
                $('[name="' + $(this).attr('id') + '"]').val(newValue)
            },
            value: params.roles.value,
            source: params.roles.source
        });
    });
    $(function(){
        $('.visible').editable({
            type: 'select',
            value: params.visible.value,
            success: function(response, newValue) { 
                $('[name="' + $(this).attr('id') + '"]').val(newValue)
            }, 
            source: params.visible.source
        });
    });
    $(function(){ 
        $('.visibleondate').editable({
            type: 'dateui',
            success: function(response, newValue) { 
                $('[name="' + $(this).attr('id') + '"]').val($.datepicker.formatDate( '@', newValue))
            }, 
            format: 'dd-mm-yy',
            viewformat: 'dd/M/yyyy', 
            datepicker: {firstDay: 1}
        }); 
    });
};