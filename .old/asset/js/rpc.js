$.oxygen = function(options) {
    var defaults = {
        component: 'Oxygen_Page_Missing',
        method: 'getData',
        args: {},
        callback: function (err,data) {}
    };
    options = _.extend(defaults, options);
    $.post({
        url: OXYGEN_ROOT,
        headers: {
            'X-Oxygen-Request': 'POST',
            'X-Oxygen-Class': ''
        }

    })
};