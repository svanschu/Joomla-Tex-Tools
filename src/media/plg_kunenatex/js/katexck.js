function kaTeXReady(fn) {
    if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
};

function katexckeditorconf() {
    console.log(CKEDITOR.instances);
    CKEDITOR.on('loaded', function () {
        //CKEDITOR.plugins.addExternal('katexck', 'plugins/kunena/kunenatex/katexck/');
    });

    CKEDITOR.on('instanceReady', function () {
        editor = CKEDITOR.instances["message"];

        var config = CKEDITOR.instances["message"].config;

        if (!config.extraPlugins.includes("katexck")) {

            config.extraPlugins += ",katexck";
            console.log(config.extraPlugins.toString());

            console.log(editor.config.extraPlugins.toString());

            editor.destroy();
            CKEDITOR.replace('message', config);
        }
    });
};

kaTeXReady(katexckeditorconf);