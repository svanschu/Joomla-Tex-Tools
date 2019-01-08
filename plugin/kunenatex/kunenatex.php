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

jimport('joomla.plugin.plugin');

class plgKunenaKunenatex extends JPlugin
{

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onKunenaBbcodeEditorInit($editor)
    {
        $this->loadLanguage();
        $btn = new KunenaBbCodeEditorButton('tex', 'texbutton', 'tex', 'PLG_KUNENATEX_BTN_TITLE', 'PLG_KUNENATEX_BTN_ALT');
        $btn->addWrapSelectionAction(null, null, null, "[tex]", "[/tex]");
        $editor->insertElement($btn, 'after', 'code');

        $url = $this->params->get('mathjax', 'https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
        // We need to add it in here already, because the BBcode parser is only loaded in a second request.
        $document = JFactory::getDocument();
        $document->addScript($url);

        if (KunenaFactory::getTemplate()->isHmvc())
        {
            $document->addStyleDeclaration(".markItUpHeader .texbutton a { background-image: url(\"" . JURI::base(true) . "/plugins/kunena/kunenatex/images/tex.png\"); }");
        } else {
            $document->addStyleDeclaration("#Kunena #kbbcode-toolbar #texbutton { background-image: url(\"" . JURI::base(true) . "/plugins/kunena/kunenatex/images/tex.png\"); }");
        }

        $document->addScriptDeclaration("window.addEvent('domready', function() {
	preview = document.id('kbbcode-preview');

	preview.addEvent('updated', function(event){
				MathJax.Hub.Queue(['Typeset',MathJax.Hub,'kbbcode-preview']);
				document.getElements('.latex').each(function(item, index) {
                    item.setStyle('display', '');
                });
			}
		);
});");

    }

    public function onKunenaBbcodeConstruct($bbcode)
    {
        $bbcode->AddRule('tex', array(
                'mode' => BBCODE_MODE_CALLBACK,
                'method' => 'plgKunenaKunenatex::onTex',
                'allow' => array('type' => '/^[\w]*$/',),
                'allow_in' => array('listitem', 'block', 'columns'),
                'content' => BBCODE_VERBATIM,
                'before_tag' => "sns",
                'after_tag' => "sn",
                'before_endtag' => "sn",
                'after_endtag' => "sns",
                'plain_start' => "\n",
                'plain_end' => "\n")
        );

        $url = $this->params->get('mathjax', 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.5/MathJax.js?config=TeX-AMS-MML_HTMLorMML');

        $document = JFactory::getDocument();
        $document->addScript($url);
        if (!(KunenaFactory::getTemplate()->isHmvc())) {
            $document->addScriptDeclaration("window.addEvent('domready', function() { document.getElements('.latex').each(function(item, index) { item.setStyle('display', ''); }); });");
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

        $pconf = JPluginHelper::getPlugin('kunena', 'kunenatex');
        $pconf = json_decode($pconf->params);
        //get the mimetex URL
        $url = $pconf->mimetex;

        $content_urlencoded = rawurlencode(html_entity_decode($content));
        $html = '';
        $style = '';
        if ($pconf->usetexrender == 'both') $style = "style=\"display: none\"";
        if ($pconf->usetexrender == 'mathjax' || $pconf->usetexrender == 'both') {
            $html .= "<div class=\"latex\" {$style}>\[" . $content . "\]</div>\n";
        }
        if ((isset($url) && ($pconf->usetexrender == 'mimetex') || $pconf->usetexrender == 'both')) {
            if ($pconf->usetexrender == 'both') $html .= "<noscript>";
            $html .= "<img src=\"$url?$content_urlencoded\" alt=\"{$content}\" title=\"{$content}\"/><br />";
            if ($pconf->usetexrender == 'both') $html .= "</noscript>";
        }
        return $html;
    }
}

