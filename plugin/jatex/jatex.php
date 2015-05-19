<?php
/**
 * @version $Id: $
 * JaTeX content Plugin
 *
 * @package        JaTeX
 * @Copyright (C) 2014 Schultschik Websolution, Sven Schultschik
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://extensions.schultschik.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentJatex extends JPlugin
{
    protected $pconf = null;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    static function convertLatex($treffer)
    {
        $pconf = JPluginHelper::getPlugin('content', 'jatex');
        $pconf = json_decode($pconf->params);
        //get the mimetex URL
        $url = $pconf->mimetex;

        $class = '';
        if (!empty($treffer[1])) {

            $pieces = explode(',', $treffer[1]);

            foreach ($pieces as $piece) {
                switch ($piece) {
                    case "inline":
                        $class = "jatex-inline";
                        $css = ".jatex-inline{display:inline;}
                            .jatex-inline div.MathJax_Display{display: inline !important; width: auto;}";
                        $document = JFactory::getDocument();
                        $document->addStyleDeclaration($css);
                        break;
                }
            }
        }

        $content_urlencoded = rawurlencode(html_entity_decode($treffer[2]));
        $html = '';
        $style = '';
        if ($pconf->usetexrender == 'both') $style = "style=\"display: none\"";
        if ($pconf->usetexrender == 'mathjax' || $pconf->usetexrender == 'both') {
            $html .= "<div class=\"latex {$class}\" {$style}>\[" . $treffer[2] . "\]</div>";
        }
        if ((isset($url) && ($pconf->usetexrender == 'mimetex') || $pconf->usetexrender == 'both')) {
            if ($pconf->usetexrender == 'both') $html .= "<noscript>";
            $html .= "<img src=\"$url?$content_urlencoded\" alt=\"{$treffer[2]}\" title=\"{$treffer[2]}\"/><br />";
            if ($pconf->usetexrender == 'both') $html .= "</noscript>";
        }

        return $html;
    }

    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        $text = preg_replace_callback("/\{jatex(?: options:)??(.*)\}((?:.|\n)*)\{\/jatex\}/U", array('plgContentJatex', 'convertLatex'), $row->text);

        if ($text != NULL) {
            $row->text = $text;
        } else {
            //TODO add Log entry on faile
        }

        $url = $this->params->get('mathjax', 'https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');

        $document = JFactory::getDocument();
        $document->addScript($url)
            ->addScriptDeclaration("window.addEvent('domready', function() {
            document.getElements('.latex').each(function(item, index) {
                item.setStyle('display', '');
            });
        });");

        return true;
    }
}