<?php
require_once '../../../web_construction_set/autoload.php';
if (empty($_GET['client_customer_id']) && empty($_GET['callback'])) {
?>
<html><body>
<form>
<a href="https://support.google.com/adwords/answer/29198" target="blank">Client Customer Id</a>:<input type="text" name="client_customer_id" value="000-000-0000"/>
<input type="submit" value="Get"/>
</form>
</body></html>
<?php
} else {
	$authData = [
			'client_id' => \Config::CLIENT_ID,
			'client_secret' => \Config::CLIENT_SECRET,
			'scope' => 'https://www.googleapis.com/auth/adwords',
			'redirect_uri' => \WebConstructionSet\Url\Tools::getMyUrlName() . '?callback=1',
	];
	$auth = new \WebConstructionSet\Accounting\OAuth\Google($authData);
	if (isset($_GET['client_customer_id']))
		$auth->setState(['client_customer_id' => $_GET['client_customer_id']]);
	if ($auth->process()) {
		header('Content-Type: text/plain');
		if ($auth->getError()) {
			echo 'Error: ', $auth->getError();
		} else {
			$authData['access_token'] = $auth->getToken();
			$clientCustomerId = $auth->getState()['client_customer_id'];
			$strings = new \WebConstructionSet\Advertising\CampaignStrings\Google($authData, \Config::DEVELOPER_TOKEN, \Config::COMPANY_NAME, $clientCustomerId);
			echo implode("\n", $strings->get());
		}
	}
}
