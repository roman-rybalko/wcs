<?php

namespace WebConstructionSet\CampaignString;

class Referer implements \WebConstructionSet\CampaignString {
	public function get() {
		$req_headers = getallheaders();
		$hdr_name = 'Referer';
		if (!isset($req_headers[$hdr_name]))
			$hdr_name = 'referer';
		if (!isset($req_headers[$hdr_name]))
			return null;
		$url = parse_url($req_headers[$hdr_name]);
		if (!isset($url['query']))
			return null;
		$params = parse_str($url['query']);
		$param = 'query';
		if (!isset($params[$param]))
			$param = 'search';
		if (!isset($params[$param]))
			$param = 'text';
		if (!isset($params[$param]))
			$param = 'etext';
		if (!isset($params[$param]))
			return null;
		return urldecode($params[$param]);
	}
}

?>