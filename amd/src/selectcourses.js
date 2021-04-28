/*
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
  * @module local_culrollover/selectcourses
  */

    define(['jquery', 'local_culrollover/select2'], function($) {
        return {
            initialise: function (delay, chars) {
                // $.fn.select2.defaults.set("theme", "bootstrap");
                window.console.log(chars);
                
                $('.source_select select.custom-select').select2({
                    placeholder: "Select a source module", // @TODO lang string
                    allowClear: true,
                    minimumInputLength: chars,
                    ajax: {
                        url: "/local/culrollover/select2ajax.php",
                        dataType: 'json',
                        delay: delay,
                        data: function (params) {
                        return {
                                q: params.term, // search term
                                page: params.page,
                                type: 'src'
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },                      
                        cache: true
                    }
                });

                $('.dest_select select.custom-select').select2({
                    placeholder: "Select a destination module", // @TODO lang string
                    allowClear: true,
                    minimumInputLength: chars,
                    ajax: {
                        url: "/local/culrollover/select2ajax.php",
                        dataType: 'json',
                        delay: delay,
                        // params: {
                        //     error: function(err) {
                        //         if (err.status == 0) {
                        //             //this happens when typing too fast in the Select2 type-ahead - DO NOTHING!!
                        //             $.console.log('Select2.js fast type-ahead error condition encountered and handled properly');
                        //         }
                        //     }
                        // },
                        data: function (params) {
                        return {
                                q: params.term, // search term
                                page: params.page,
                                type: 'dst'
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                });
            }
        };
    });
