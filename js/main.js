require(['core/first', 'local_culrollover/common'], function() {
    require(['local_culrollover/defaultoptions', 'core/log'], function(defaultoptions, log) {
        log.debug('CUL Rollover initialised');
    });
});