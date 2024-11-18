<?php
/**
 * JaTeX content plugin
 * 
 * @version     sw.build.version
 * @copyright   Copyright (C) 2014 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Plugin\Content\JaTeX\Extension;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class JaTeX extends CMSPlugin implements SubscriberInterface
{
    /**
     * 
     * 
     * @since 2.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare' => 'replaceShortcodes',
        ];
    }

    protected $pconf = null;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Callback method to convert the latex code
     * 
     * @since 1.0.0
     */
    static function convertLatex($treffer)
    {
        $pconf = PluginHelper::getPlugin('content', 'jatex');
        $pconf = json_decode($pconf->params);

        $class = '';
        if (!empty($treffer[1])) {

            $pieces = explode(',', $treffer[1]);

            foreach ($pieces as $piece) {
                switch ($piece) {
                    case "inline":
                        $class = "jatex-inline";
                        $css = ".jatex-inline{display:inline;}
                            .jatex-inline div.MathJax_Display{display: inline !important; width: auto;}";
                        Factory::getDocument()
	                        ->addStyleDeclaration($css);
                        break;
                }
            }
        }

        $html = '';
        $style = '';
        if ($pconf->usetexrender == 'mathjax') {
            $html .= "<div class=\"latex {$class}\" {$style}>\[" . $treffer[2] . "\]</div>";
        }

        if ($pconf->usetexrender == 'katex') {
            $html .= "<div class=\"jatex {$class}\" {$style}>
                " . $treffer[2] . "
            </div>";

            // $script = "katex.render(\"$treffer[2]\", element, { throwOnError: false});";

            // $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

            // $wa->addInlineScript($script);

        }

        return $html;
    }

    /**
     * this will be called whenever the onContentPrepare event is triggered
     * 
     * @since 2.0.0
     */
    public function replaceShortcodes(Event $event): void{
        if (!$this->getApplication()->isClient(identifier: 'site')) {
            return;
        }

        [$context, $article, $params, $page] = array_values(array: $event->getArguments());

        if ($context !== "com_content.article" 
            && $context !== "com_content.featured" 
            && $context !== "com_content.category") return;

        $text = preg_replace_callback(
            pattern: "/\{jatex(?: options:)??(.*)\}((?:.|\n)*)\{\/jatex\}/U", 
            callback: ['SchuWeb\Plugin\Content\JaTeX\Extension\JaTeX', 'convertLatex'], 
            subject: $article->text);

        if ($text != NULL) {
            $article->text = $text;
        } else {
            //TODO add Log entry on faile
        }

        switch ($this->params->get('usetexrender')) {
            case 'mathjax':
                $this->replaceShortcodesMathjax();
                break;
            case 'katex':
                $this->replaceShortcodesKatex();
                break;
        }
    }

    /**
     * this will be called whenever the onContentPrepare event is triggered
     * 
     * @since __BUMP_VERSION__
     */
    private function replaceShortcodesMathjax()
    {
        $mathjaxSource           = Uri::base() . "media/plg_jatex/js/mathjax/tex-mml-chtml.js";
        $mathjaxSourceAttributes = array('id' => 'MathJax-script');

        if (strcmp($this->params->get('mathjaxcdn'), "cdn") == 0) {
            $defaultCdn              = "https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js";
            $mathjaxSourceAttributes = array('id' => 'MathJax-script', 'async' => 'async');

            if (strcmp($this->params->get("mathjaxcdnsource"), "url") == 0) {
                $mathjaxSource = $this->params->get('mathjax', $defaultCdn);
            } else {
                $mathjaxSource = $defaultCdn;
            }
        }

        Factory::getDocument()
            // Only (url, mime, defer, async) is depricated, we use only (url)
            ->addScript(Uri::base() . "media/plg_jatex/js/jatex.js")
            ->addScript($mathjaxSource, array(), $mathjaxSourceAttributes)
            ->addScriptDeclaration("
		    function jatex() {
		        var elements = document.querySelectorAll('.latex');
		        Array.prototype.forEach.call(elements, function(item, index){
					item.style.display = '';
				});
		    };
		    
		    function ready(fn) {
                if (document.attachEvent ? document.readyState === \"complete\" : document.readyState !== \"loading\"){
                    fn();
                } else {
                    document.addEventListener('DOMContentLoaded', fn);
                }
			};
			
			ready(jatex);
			"
            );
    }

    /**
     * this will be called whenever the onContentPrepare event is triggered
     * 
     * @since __BUMP_VERSION__
     */
    private function replaceShortcodesKatex()
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        $cdn_local = $this->params->get('katexcdn');
        if (strcmp($cdn_local, "local") == 0) {
            $wa->useScript('plg_jatex.katex.js')
                ->useStyle('plg_jatex.katex.css');
        } else if (strcmp($cdn_local, "cdn") == 0) {
            $cdn_uri = 'https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/';

            $wa->registerAndUseStyle('plg_jatex.katex.css.cdn', 
                uri: "{$cdn_uri}katex.min.css",
                attributes: [ 
                    'crossorigin' => 'anonymous',
                    'integrity' => 'sha384-nB0miv6/jRmo5UMMR1wu3Gz6NLsoTkbqJghGIsx//Rlm+ZU03BU6SQNC66uf4l5+'
                ]
            )
            ->registerAndUseScript('plg_jatex.katex.js.cdn', 
                uri: "{$cdn_uri}katex.min.js",
                attributes: [ 
                    'crossorigin' => 'anonymous',
                    'integrity' => 'sha384-7zkQWkzuo3B5mTepMUcHkMB5jZaolc2xDwL6VFqjFALcbeS9Ggm/Yr2r3Dy4lfFg'
                ]
            )
            // ->registerAndUseScript('plg_jatex.auto-render.js.cdn', 
            //     uri: "{$cdn_uri}contrib/auto-render.min.js",
            //     attributes: [ 
            //         'crossorigin' => 'anonymous',
            //         'integrity' => 'sha384-43gviWU0YVjaDtb/GhzOouOXtZMP/7XUzwPTstBeZFe/+rCMvRwr4yROQP43s0Xk',
            //         'onload'=>'renderMathInElement(document.body);'
            //     ]
            // )
            ;
// TODO        <script>
//   window.WebFontConfig = {
//     custom: {
//       families: ['KaTeX_AMS', 'KaTeX_Caligraphic:n4,n7', 'KaTeX_Fraktur:n4,n7',
//         'KaTeX_Main:n4,n7,i4,i7', 'KaTeX_Math:i4,i7', 'KaTeX_Script',
//         'KaTeX_SansSerif:n4,n7,i4', 'KaTeX_Size1', 'KaTeX_Size2', 'KaTeX_Size3',
//         'KaTeX_Size4', 'KaTeX_Typewriter'],
//     },
//   };
// </script>
// <script defer src="https://cdn.jsdelivr.net/npm/webfontloader@1.6.28/webfontloader.js" integrity="sha256-4O4pS1SH31ZqrSO2A/2QJTVjTPqVe+jnYgOWUVr7EEc=" crossorigin="anonymous"></script>

        }

        $wa->useScript('plg_jatex.katexdo.js');
    }
}
