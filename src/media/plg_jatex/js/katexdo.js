document.addEventListener('DOMContentLoaded', function() {
    const macros = {};

    var jatexDivs = document.querySelectorAll('div.jatex'); 

    for (let element of jatexDivs) {
        katex.render(element.textContent, element, {
            throwOnError: false,
            macros
        });
    }
});
