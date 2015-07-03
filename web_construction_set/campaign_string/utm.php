<?php

namespace WebConstructionSet\CampaignString;

class Utm implements \WebConstructionSet\CampaignString {
	public function get() {
		if (isset($_GET["utm_content"]))
			return $_GET["utm_content"];
		return null;
	}
}

?>