<?php
/**
 * @version $Id: $
 * SW SetGroup User Plugin
 *
 * @package        SW SetGroup
 * @Copyright (C) 2012 Benjamin Berg & Sven Schultschik
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
		$btn = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?>
			<button tag="tex" name="tex" title="PLG_KUNENATEX_BTN_TITLE" alt="PLG_KUNENATEX_BTN_ALT" />');

		$editor->addButton($btn, 'after', 'code');

		// We need to add it in here already, because the BBcode parser is only loaded in a second request.
		$document = &JFactory::getDocument();
		$document->addScript("https://d3eoax9i5htok0.cloudfront.net/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML");
	}

	public function onKunenaBbcodeConstruct($bbcode)
	{
		$bbcode->AddRule('tex', array(
			'mode' => BBCODE_MODE_CALLBACK,
			'method' => 'plgKunenaKunenatex::onTex',
			'allow' => array( 'type' => '/^[\w]*$/', ),
			'allow_in' => array('listitem', 'block', 'columns'),
			'content' => BBCODE_VERBATIM,
			'before_tag' => "sns",
			'after_tag' => "sn",
			'before_endtag' => "sn",
			'after_endtag' => "sns",
			'plain_start' => "\n",
			'plain_end' => "\n")
		);

		$document = &JFactory::getDocument();
		$document->addScript("https://d3eoax9i5htok0.cloudfront.net/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML");

		return true;
	}

	static public function onTex($bbcode, $action, $name, $default, $params, $content)
	{
		if ($action == BBCODE_CHECK) {
			$bbcode->autolink_disable = 1;
			return true;
		}

		$bbcode->autolink_disable = 0;

		$content_urlencoded = rawurlencode($content);
		return "<div class=\"latex\">\[".$content."\]</div>\n<img src=\"https://fachschaft.etec.uni-karlsruhe.de/cgi-bin/mimetex.cgi?$content_urlencoded\" />\n"; // Add noscript again ...
	}
}

