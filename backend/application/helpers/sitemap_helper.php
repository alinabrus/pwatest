<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//define('SITE_MAP_LIST_METHOD_NAME','site_map_list');

function create_sitemap_element($dom, $el, $values)
{
	$url = $dom->createElement("url"); // ������ ���� "url"
	if (array_key_exists ('loc', $values)) {
		$node = $dom->createElement("loc",$values['loc']);
		$url->appendChild($node); // ��������� � ���� "url" ���� "loc"
	}
	if (array_key_exists ('lastmod', $values)) {
		$node = $dom->createElement("lastmod",$values['lastmod']);
		$url->appendChild($node); // ��������� � ���� "url" ���� "lastmod"
	}
	if (array_key_exists ('changefreq', $values)) {
		$node = $dom->createElement("changefreq",$values['changefreq']);
		$url->appendChild($node); // ��������� � ���� "url" ���� "lastmod"
	}
	if (array_key_exists ('priority', $values)) {
		$node = $dom->createElement("priority",$values['priority']);
		$url->appendChild($node); // ��������� � ���� "url" ���� "priority"
	}
	$el->appendChild($url);
}

function sitemap_create(&$model = null) 
/*
 * model $model must have method site_map_list
 * method site_map_list must return data with fields from list: loc, lastmod, priority
 * 
 * example:
 * 		$query = "(SELECT '".$_SERVER['HTTP_HOST']."' AS loc, CURDATE() AS lastmod, 1 AS priority) 
 *			UNION (SELECT CONCAT('".$_SERVER['HTTP_HOST']."/app/#/campaign/',tag) AS loc, add_date AS lastmod, 0.5 AS priority FROM mx_campaigns"; 
 */
{
	$SITE_MAP_LIST_METHOD_NAME = 'site_map_list';
	
	if (!isset($model)||!method_exists($model, $SITE_MAP_LIST_METHOD_NAME)) {
		return false;
	}
	
	$dom = new domDocument("1.0", "utf-8"); // ������ XML-�������� ������ 1.0 � ���������� utf-8
	//logmes('dom = ', 'aaa', 'at');
	$root = $dom->createElement("urlset"); // ������ �������� �������
	$root->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
	$dom->appendChild($root);
	
	//logmes('$model = ', $model, 'at');
	
	//$list = call_user_func($model->{$SITE_MAP_LIST_METHOD_NAME});
	$list = $model->site_map_list();
	//$list = call_user_func($model->site_map_list());

	//logmes('$list = ', $list, 'at');	
	
	if (!empty($list)) {
		foreach ($list as $row) {
			$params = array();
			if (isset($row->loc)) $params['loc'] = $row->loc;
			if (isset($row->lastmod)) $params['lastmod'] = $row->lastmod;
			if (isset($row->changefreq)) $params['changefreq'] = $row->changefreq;
			if (isset($row->priority)) $params['priority'] = $row->priority;
			create_sitemap_element($dom, $root, $params);
			/*
			$url = $dom->createElement("url"); // ������ ���� "url"
			$loc = $dom->createElement("loc","local.maxletics"); 
			$url->appendChild($loc); // ��������� � ���� "url" ���� "loc"
			$root->appendChild($url);
			*/
		}
		$filename = FCPATH."files/sitemap.xml";
		$dom->save($filename); // ��������� ���������� XML-�������� � ����
	
		//readfile($filename);
		//$finishfilename =  $_SERVER['DOCUMENT_ROOT']."/sitemap.xml";
		//rename($filename, $finishfilename);
		
		//logmes('MY = ', $_SERVER, 'at');
		
		return true;
	}
	else return false;
}

	
/* End of file */