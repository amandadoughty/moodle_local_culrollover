requirejs.config({
    // 'baseUrl': '/local/culrollover',
    'paths': {
        'datatables': '/local/culrollover/amd/lib/datatables',
        // 'datatables': '//cdn.datatables.net/1.10.10/js/jquery.dataTables.min',
        // 'jqueryui-editable': '/local/culrollover/amd/lib/jqueryui-editable',
        // 'editable': '//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jqueryui-editable/js/jqueryui-editable.min'
        'select2': '/local/culrollover/amd/lib/select2',
    },
    // Sets the configuration for your third party scripts that are not AMD compatible.
    'shim': {
        // 'jqueryui-editable': ['jquery', 'jqueryui'],
        // 'jqueryuieditable': {
        //     'deps': ['jquery', 'jqueryui'],
        //     'exports': 'jqueryuieditable'  //attaches 'editable' to the window object
        // }
    }
});