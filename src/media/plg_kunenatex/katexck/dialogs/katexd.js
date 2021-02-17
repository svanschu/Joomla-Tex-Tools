/**
 * @version $Id: $
 * KaTeX Kunena TeX Plugin
 *
 * @package KaTeX
 * @Copyright (C) 2012 - 2021 Sven Schultschik
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 */

CKEDITOR.dialog.add('katexDialog', function (editor) {
        return {
            title: 'LaTeX Code',
            minWidth: 400,
            minHeight: 200,

            contents: [
                {
                    id: 'tab-basic',
                    label: 'Basic',
                    elements: [
                        {
                            type: 'textarea',
                            id: 'katex',
                            label: 'LaTeX Code',
                            validate: CKEDITOR.dialog.validate.notEmpty("LaTeX code field cannot be empty.")
                        }
                    ]
                },
                {
                    id: 'tab-adv',
                    label: 'Advanced',
                    elements: [
                        {
                            type: 'text',
                            id: 'katex-inline',
                            label: 'Inline',
                        }
                    ]
                }],
            onOk: function () {
                var dialog = this;

                var katex = editor.document.createElement('katex');
                //katex.setAttribute('katex', dialog.getValueOf('tab-basic', 'katex'));
                katex.setText("$$" + dialog.getValueOf('tab-basic', 'katex') + "$$");

                editor.insertElement(katex);
            }
        }
    }
);