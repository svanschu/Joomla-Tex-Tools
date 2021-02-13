function kaTeXReady(fn) {
    if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
};

function katexckeditorconf() {
    CKEDITOR.on('instanceReady', function () {
        editor = CKEDITOR.instances["message"];

        var config = CKEDITOR.instances["message"].config;

        if (!config.extraPlugins.includes("katexck")) {

            config.extraPlugins += ",katexck";

            editor.destroy();

            CKEDITOR.plugins.addExternal('katexck', '../../../plg_kunenatex/katexck/');
            CKEDITOR.replace('message', config);
        }
    });
};

kaTeXReady(katexckeditorconf);