<?php

namespace WebConstructionSet\Marketing\CampaignString;

class Utm implements \WebConstructionSet\Marketing\CampaignString {
	public function get() {
		if (isset($_GET["utm_content"]))
			return $_GET["utm_content"];
		return null;
	}
}

?>