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
        if ($pconf->usetexrender == 'mathjax' || $pconf->usetexrender == 'both') {
            $html .= "<div class=\"latex {$class}\" {$style}>\[" . $treffer[2] . "\]</div>";
        }

        return $html;
    }

    /**
     * this will be called whenever the onContentPrepare event is triggered
     * 
     * @since 2.0.0
     */
    public function replaceShortcodes(Event $event){
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        [$context, $article, $params, $page] = array_values($event->getArguments());
        if ($context !== "com_content.article" && $context !== "com_content.featured" && $context !== "com_content.category") return;

        $text = preg_replace_callback("/\{jatex(?: options:)??(.*)\}((?:.|\n)*)\{\/jatex\}/U", ['SchuWeb\Plugin\Content\JaTeX\Extension\JaTeX', 'convertLatex'], $article->text);

        if ($text != NULL) {
            $article->text = $text;
        } else {
            //TODO add Log entry on faile
        }

	    $mathjaxSource           = Uri::base() . "media/plg_jatex/js/mathjax/tex-mml-chtml.js";
	    $mathjaxSourceAttributes = array('id' => 'MathJax-script');

	    if (strcmp($this->params->get('mathjaxcdn'), "cdn") == 0)
	    {
		    $defaultCdn              = "https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js";
		    $mathjaxSourceAttributes = array('id' => 'MathJax-script', 'async' => 'async');

		    if (strcmp($this->params->get("mathjaxcdnsource"), "url") == 0)
		    {
			    $mathjaxSource = $this->params->get('mathjax', $defaultCdn);
		    }
		    else
		    {
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

        return true;
    }
}
