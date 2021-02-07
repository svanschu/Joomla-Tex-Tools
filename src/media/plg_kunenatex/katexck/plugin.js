CKEDITOR.plugins.add( 'katexck', {
    icons: 'katex',
    init: function( editor ) {
        editor.addCommand( 'insertKatex', {
            exec: function( editor ) {

                editor.insertHtml( '[katex] [/katex]' );
            }
        });

        editor.ui.addButton( 'KaTeX', {
            label: 'Insert KaTeX',
            command: 'insertKatex',
            toolbar: 'insert'
        });
    }
});
