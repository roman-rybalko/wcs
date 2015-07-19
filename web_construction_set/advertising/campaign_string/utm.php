<?php

namespace WebConstructionSet\Advertising\CampaignString;

class Utm implements \WebConstructionSet\Advertising\CampaignString {
	public function get() {
		if (isset($_GET["utm_content"]))
			return $_GET["utm_content"];
		return null;
	}
}
