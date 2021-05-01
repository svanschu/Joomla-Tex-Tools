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
        let prevelem;
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
                            validate: CKEDITOR.dialog.validate.notEmpty("LaTeX code field cannot be empty."),

                            onLoad: function () {
                                const inputElement = this.getInputElement();
                                this.getInputElement().on('keyup', function () {
                                    MathJax.texReset(prevelem);
                                    MathJax.typesetClear(prevelem);
                                    let equation = inputElement.getValue().trim();
                                    if (!(equation.startsWith('$') || equation.startsWith('\\('))) {
                                        equation = '$'.concat(equation);
                                    }
                                    if (!(equation.endsWith('$') || equation.endsWith('\\)'))) {
                                        equation = equation.concat('$');
                                    }
                                    prevelem.innerHTML = equation;
                                    MathJax.typesetPromise()
                                        .catch(function (err) {
                                            prevelem.innerHTML = '';
                                            prevelem.appendChild(document.createElement('pre')).appendChild(document.createTextNode(err.message));
                                        })
                                })
                            },
                        },
                        {
                            type: 'html',
                            id: 'preview',
                            html: '<div id="dia-preview" style="width:100%;text-align:center;"></div>',

                            onLoad: function () {
                                prevelem = document.getElementById('dia-preview');
                                prevelem.innerHTML = '';
                            },
                            onHide: function () {
                                prevelem.innerHTML = '';
                                MathJax.texReset(prevelem);
                                MathJax.typesetClear(prevelem);
                            }
                        }
                    ]
                }],
            onOk: function () {
                let dialog = this;
                const katex = editor.document.createElement('katex');

                let equation = dialog.getValueOf('tab-basic', 'katex');
                if (!(equation.startsWith('$') || equation.startsWith('\\('))) {
                    equation = '$'.concat(equation);
                }
                if (!(equation.endsWith('$') || equation.endsWith('\\)'))) {
                    equation = equation.concat('$');
                }
                katex.setText(equation);

                editor.insertElement(katex);
            }
        }
    }
);