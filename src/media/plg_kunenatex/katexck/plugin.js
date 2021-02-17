/**
 * @version $Id: $
 * KaTeX Kunena TeX Plugin
 *
 * @package KaTeX
 * @Copyright (C) 2012 - 2021 Sven Schultschik
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 */

CKEDITOR.plugins.add( 'katexck', {
    icons: 'katex',
    init: function( editor ) {
        editor.addCommand( 'insertKatex', new CKEDITOR.dialogCommand( 'katexDialog' ) );

        editor.ui.addButton( 'KaTeX', {
            label: 'LaTeX',
            command: 'insertKatex',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add( 'katexDialog', this.path + 'dialogs/katexd.js' );
    }
});
