/*
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module local_culrollover/rollovertable
  */

    define(['jquery', 'local_culrollover/datatables'], function($) {
        return {
            initialise: function (sEmptyTable) {
                $('#previous').dataTable({
                    "columnDefs": [
                        { 
                            'orderData':[3, 2], 
                            'targets': 10
                        },
                        {
                            "targets": [0, 1],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [2, 3, 4],
                            "visible": false,
                            "searchable": true
                        },
                        {
                            "targets": [11, 12],
                            "sortable": false
                        }
                    ],
                    "order": [
                        [0, "desc"]
                    ],
                    "processing": true,
                    "serverSide": true,
                    "ajax": "/local/culrollover/datatablesajax.php",
                    "oLanguage": {
                        "sEmptyTable": sEmptyTable
                      }
                });
            }
        };
    });
