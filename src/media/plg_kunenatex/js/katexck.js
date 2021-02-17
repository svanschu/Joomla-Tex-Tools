/**
 * @version $Id: $
 * KaTeX Kunena TeX Plugin
 *
 * @package KaTeX
 * @Copyright (C) 2012 - 2021 Sven Schultschik
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 */



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