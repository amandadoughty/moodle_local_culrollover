/*
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module local_culrollover/x-editable
  */

define(['jquery', 'jqueryui', 'jqueryui-editable'], function($) {
// define(['jquery'], function(jQuery) {
    return {
        // initialise: function (params) {
        initialise: function (params) {
            // $.fn.editable.defaults.mode = 'inline'; 

            // Commenting out until I can get something basic working.
            
            // $(function(){ 
            //     $('.groups').editable({
            //         type: 'select',
            //         success: function(response, newValue) { 
            //             $('[name="' + $(this).attr('id') + '"]').val(newValue);
            //         },
            //         value: params.groups.value,
            //         source: params.groups.source,
            //     }); 
            // });
            // $(function(){ 
            //     $('.merge').editable({
            //         type: 'select', 
            //         success: function(response, newValue) { 
            //             $('[name="' + $(this).attr('id') + '"]').val(newValue);
            //         }, 
            //         value: params.merge.value,
            //         source: params.merge.source
            //     }); 
            // });
            // $(function(){ 
            //     $('.roles').editable({
            //         type: 'checklist',
            //         success: function(response, newValue) { 
            //             $('[name="' + $(this).attr('id') + '"]').val(newValue);
            //         },
            //         value: params.roles.value,
            //         source: params.roles.source
            //     });
            // });
            // $(function(){
            //     $('.visible').editable({
            //         type: 'select',
            //         value: params.visible.value,
            //         success: function(response, newValue) { 
            //             $('[name="' + $(this).attr('id') + '"]').val(newValue);
            //         }, 
            //         source: params.visible.source
            //     });
            // });
            // $(function(){ 
            //     $('.visibleondate').editable({
            //         type: 'dateui',
            //         success: function(response, newValue) { 
            //             $('[name="' + $(this).attr('id') + '"]').val($.datepicker.formatDate( '@', newValue));
            //         }, 
            //         format: 'dd-mm-yy',
            //         viewformat: 'dd/M/yyyy', 
            //         datepicker: {firstDay: 1}
            //     }); 
            // });
        }
    };
});