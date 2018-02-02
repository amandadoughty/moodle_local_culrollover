/*
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module local_culrollover/reviewsettings
  */

define(['jquery'], function($) {
    return {
        initialise: function () {
            // Remove a row from the Review Options form.
            $('a.DeleteRow').click(function(event) {
                if(window.confirm('Delete Entry?')) {
                    event.stopPropagation();

                    var i = this.parentNode.parentNode.rowIndex;          
                    document.getElementById('datatable').deleteRow(i);

                    // Array index starts at 0.
                    i = i - 1;

                    $('#source_' + (i)).attr('name', 'deleted_' + (i));
                    $('#dest_' + (i)).attr('name', 'deleted_' + (i));
                    $('#migrateondate_' + (i)).attr('name', 'deleted_' + (i));
                    $('#what_' + (i)).attr('name', 'deleted_' + (i));
                    $('#groups_' + (i)).attr('name', 'deleted_' + (i));
                    $('#merge_' + (i)).attr('name', 'deleted_' + (i));
                    $('#roles_' + (i)).attr('name', 'deleted_' + (i));
                    $('input[name="rolloverrepeats"]').val($('input[name="rolloverrepeats"]').val() - 1);
                }
            });
        }
    };

});