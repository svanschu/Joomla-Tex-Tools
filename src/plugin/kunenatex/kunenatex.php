<?php
/**
 * @version $Id: $
 * KaTeX Kunena TeX Plugin
 *
 * @package KaTeX
 * @Copyright (C) 2012 - 2019 Sven Schultschik
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;

class plgKunenaKunenatex extends CMSPlugin
{

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

	    $mathjaxSource           = "/media/plg_kunenatex/js/mathjax/tex-mml-chtml.js";
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

        // style to add button image
	    /** @noinspection PhpDeprecationInspection */
	    Factory::getDocument()
		    ->addStyleDeclaration(".markItUpHeader .texbutton a { background-image: url(\"" . JURI::base(true) . "/plugins/kunena/kunenatex/images/tex.png\"); }")
		    ->addScript("/media/plg_kunenatex/js/kunenatex.js")
		    ->addScript($mathjaxSource, array(), $mathjaxSourceAttributes)
	        // We need to add it in here already, because the BBcode parser is only loaded in a second request.
	        ->addScript("/media/plg_kunenatex/js/katex.js");
    }

    /*
     * This method is for the editor on new topic or editing
     * Default Kunena BBCode Editor Preview and button adding
     */
    public function onKunenaBbcodeEditorInit($editor)
    {
        $this->loadLanguage();
        $btn = new KunenaBbcodeEditorButton('tex', 'texbutton', 'tex', 'PLG_KUNENATEX_BTN_TITLE', 'PLG_KUNENATEX_BTN_ALT');
        $btn->addWrapSelectionAction(null, null, null, "[tex]", "[/tex]");
        $editor->insertElement($btn, 'after', 'code');
    }

    /*
     * This method is used on displaying the thread
     */
    public function onKunenaBbcodeConstruct($bbcode)
    {
	    // if less 5.2
	    if (version_compare(KunenaForum::version(), "5.2.0", "<"))
	    {
		    $bbcode->AddRule('tex', array(
				    'mode'          => BBCODE_MODE_CALLBACK,
				    'method'        => 'plgKunenaKunenatex::onTex',
				    'allow'         => array('type' => '/^[\w]*$/',),
				    'allow_in'      => array('listitem', 'block', 'columns'),
				    'content'       => BBCODE_VERBATIM,
				    'before_tag'    => "sns",
				    'after_tag'     => "sn",
				    'before_endtag' => "sn",
				    'after_endtag'  => "sns",
				    'plain_start'   => "\n",
				    'plain_end'     => "\n")
		    );


	        if (!(KunenaFactory::getTemplate()->isHmvc())) {
		        Factory::getDocument()->addScriptDeclaration("
	            function katexbBBCodeConstruct() {
	                var elements = document.querySelectorAll('.katex');
	                Array.prototype.forEach.call(elements, function(item, index){
	                    item.style.display = '';
	                });
	            };
	            
	            kaTeXReady(katexbBBCodeConstruct);
	            ");
	        }
	    }

        return true;
    }

    static public function onTex($bbcode, $action, $name, $default, $params, $content)
    {

        if ($action == BBCODE_CHECK) {
            $bbcode->autolink_disable = 1;
            return true;
        }

        $bbcode->autolink_disable = 0;

        $pconf = PluginHelper::getPlugin('kunena', 'kunenatex');
        $pconf = json_decode($pconf->params);
        //get the mimetex URL
        $url = $pconf->mimetex;

        $content_urlencoded = rawurlencode(html_entity_decode($content));
        $html = '';
        $style = '';
        if ($pconf->usetexrender == 'both') $style = "style=\"display: none\"";
        if ($pconf->usetexrender == 'mathjax' || $pconf->usetexrender == 'both') {
            $html .= "<div class=\"katex\" {$style}>\[" . $content . "\]</div>\n";
        }
        if ((isset($url) && ($pconf->usetexrender == 'mimetex') || $pconf->usetexrender == 'both')) {
            if ($pconf->usetexrender == 'both') $html .= "<noscript>";
            $html .= "<img src=\"$url?$content_urlencoded\" alt=\"{$content}\" title=\"{$content}\"/><br />";
            if ($pconf->usetexrender == 'both') $html .= "</noscript>";
        }
        return $html;
    }
}

