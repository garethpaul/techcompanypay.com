<?php
function tcp_get($key) {
	return isset($_GET[$key]) && is_scalar($_GET[$key]) ? substr((string) $_GET[$key], 0, 100) : '';
}

function tcp_html($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function tcp_send_security_headers() {
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=UTF-8');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
		header("Content-Security-Policy: default-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'");
	}
}

function tcp_share_url($company, $city) {
	$params = array();
	if ($company !== '') {
		$params['c'] = $company;
	}
	if ($city !== '') {
		$params['l'] = $city;
	}
	$query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
	return 'https://techcompanypay.com/' . ($query === '' ? '' : '?' . $query);
}

tcp_send_security_headers();

$company = tcp_get('c');
$city = tcp_get('l');
$company_html = tcp_html($company);
$city_html = tcp_html($city);
$share_url_html = tcp_html(tcp_share_url($company, $city));
?>
<!doctype html>
<html lang="en" xmlns:og="http://ogp.me/ns#">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
   <title>TechCompanyPay</title>
	<meta property="og:title" content="TechCompanyPay"/>
    <meta property="og:url" content="<?php echo $share_url_html; ?>"/>
    <meta property="og:site_name" content="TechCompanyPay"/>
    <meta property="og:description" content="A hack showing the titles and average pay of most of the tech giants employees with LinkedIn profiles."/>
	
	<link href="assets/app.css" rel="stylesheet" />
	<script src="assets/app.js" defer></script>
</head>	
<body>
	<div id="header">
	<div class="column">
	<a href="/" id="logo">TechCompanyPay</a>
	</div>
	</div>

	<div id='page' class='column'>
		<h1>Welcome to TechCompanyPay</h1>
		<p> This project was mashed up in 3 hours from open sources of information publicly available to anyone on the internet. No warranty is given for the accuracy of 
the data 
on the site. See <a href='#disclaimer'>the disclaimer</a> for more of this stuff.</p>

		<form id="searchform" method="post" action="find.php">

		<h2>Basic Search:</h2> 
	<p> Enter a company and city and hit search.</p>
	<div> 
		<table>
			<tr>
				<td>
	        <label class='label' for="search_term">Search Company</label> 
			<br />
	        <input class='input' type="text" name="search_term" id="search_term" value="<?php echo $company_html; ?>"/>
				</td>
			<td>
			<label class='label' for="city">Search City</label> 
			<br />
			<input class='input' type="text" name="city" id="city" value="<?php echo $city_html; ?>" />
			</td>
			<td>
				<button type="submit" id="submitbsearch" class="button">Search salaries</button>
				
			</td>
		</tr>
		</table>
	 
	</div> 
	</form>
		    <div id="search_results" role="status" aria-live="polite"></div>

			<div class="clear"></div>
	<div id='disclaimer'>
		<p>This is a legal disclaimer, it's needed these days. The data on the site may not reflect the salary of actual people 
working at these companies. For example if you click on a link to LinkedIn profiles of possible employees they may not be actually earning the specific amount. Most roles and titles 
are averaged to provide a guide. All data on this site was obtained via open sources of information on the internet and no warranty of any kind is given to the accuracy of the 
information provided on this 
site.</p>

<p>Created by <a href="https://github.com/garethpaul">Gareth Paul Jones</a>.</p>

	</div>
	</div>

</body>
</html>
