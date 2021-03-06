function kaTeXReady(fn) {
    if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading"){
    fn();
} else {
    document.addEventListener('DOMContentLoaded', fn);
}
};

function katexPreview() {
    var kbbcodepreview = document.getElementById('kbbcode-preview');
    if (kbbcodepreview) {
        function mutationCallback(mutationsList, observer) {
            for (const mutation of mutationsList) {
                if (mutation.type === 'attributes') {
                    if (mutation.attributeName === 'style') {
                        var styleAttribute = mutation.target.attributes.style.value;
                        if (styleAttribute.includes('display: block')) {
                            setTimeout(function() {
                                MathJax.typesetClear([kbbcodepreview]);
                                MathJax.typeset([kbbcodepreview]);
                            }, 100);
                        }
                    }
                }
            }
        }

        // Create an observer instance linked to the callback function
        const observer = new MutationObserver(mutationCallback);
        // What to observe
        const mutationConfig = {attributes: true};
        observer.observe(kbbcodepreview, mutationConfig);
    }
};

kaTeXReady(katexPreview);