<?php
$company = $_GET['c'];
$city = $_GET['l'];
$title = $_GET['t'];
?>
<html xmlns:og="http://ogp.me/ns#">
<head>
   <title>TechCompanyPay</title>
	<!-- SET FACEBOOK META -->
	<meta property="og:title" content="TechCompanyPay"/>
    <meta property="og:url" content="http://techcompanypay.com/<?php if ($company){ echo "?c=$company&l=$city"; } ?>"/>
    <meta property="og:image" content="http://d2dixsj1sor9iw.cloudfront.net/images/og.png"/>
    <meta property="og:site_name" content="TechCompanyPay"/>
    <meta property="og:description" content="A hack showing the titles and average pay of most of the tech giants employees with LinkedIn profiles."/>
	
	<!-- IMPORT CSS -->
	<link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
	<link href='https://d2dixsj1sor9iw.cloudfront.net/css/stylesheet.css' rel='stylesheet' type='text/css'>
	<!-- IMPORT JAVASCRIPT -->
	<script type='text/javascript' src='https://dorkzvyk9srha.cloudfront.net/js/jquery-1.6.2.min.js'></script>
		<script type='text/javascript' src='http://d2dixsj1sor9iw.cloudfront.net/javascript/core2.js'></script>
</head>	
<body>
	<div id="header">
	<div class="column">
	<a href="/" id="logo"></a>
	</div>
	</div>

	<div id='page' class='column'>
		<h1>Welcome to TechCompanyPay</h1>
		<p> This project was mashed up in 3 hours from open sources of information publicly available to anyone on the internet. No warranty is given for the accuracy of 
the data 
on the site. See <a href='#disclaimer'>the disclaimer</a> for more of this stuff.</p>

	<button class='button' id='topcompany'>Top Company Info</button>
	<div id='companywrap' style='display:none'>	
	<div id='companydata'></div>
	</div>
	<script type='text/javascript'>
	$("#topcompany").click(function(){
		$("#companywrap").toggle();
		var on = 1;
		if ($("#topcompany").html() == 'Top Company Info'){
			$("#topcompany").html('Top Company Info - Hide');
		} else{
			$("#topcompany").html('Top Company Info');
		}
	});
	</script>
	
	
	<form id="searchform" method="post" action=''> 

		<h2>Basic Search:</h2> 
	<p> Enter a company and city and hit search.</p>
	<div> 
		<table>
			<tr>
				<td>
	        <label class='label' for="search_term">Search Company</label> 
			<br />
	        <input class='input' type="text" name="search_term" id="search_term" <?php if ($company){ echo "value='".$company."'"; }?>/>
				</td>
			<td>
			<label class='label' for="city">Search City</label> 
			<br />
			<input class='input' type="text" name="city" id="city" <?php if ($city){ echo "value='".$city."'"; }?> />
			</td>
			<td>
				<input type='submit' style='margin-top:20px' id='submitbsearch' class='button'/>
				
			</td>
		</tr>
		</table>
	 
	</div> 
	</form>
	    <div id="search_results"></div>
		
		<div style='clear:both;content: ".";display: block; margin-top:10px;'></div>
	<div id='disclaimer'>
		<p>This is a legal disclaimer, it's needed these days. The data on the site may not reflect the salary of actual people 
working at these companies. For example if you click on a link to LinkedIn profiles of possible employees they may not be actually earning the specific amount. Most roles and titles 
are averaged to provide a guide. All data on this site was obtained via open sources of information on the internet and no warranty of any kind is given to the accuracy of the 
information provided on this 
site.</p>

<p>Created by <a href='https://plus.google.com/112601017731183943047/posts'>Gareth Paul Jones</a>.</p>

	</div>
	</div>

	<div id='likes'>
		
			
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) {return;}
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
			</script>
			<div class="fb-like" data-href="http://techcompanypay.com/<?php if ($company){ echo "?c=$company&l=$city"; } ?>" data-send="true" data-width="450" data-show-faces="false"></div>
			
			<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
			<g:plusone></g:plusone>

	</div>
	
	<div id='message' class='column' style='display:none;'></div>
	<script type='text/javascript'>
	<?php
	if ($company){
		echo "ajax_search();";
	}
	?>
	</script>

<script type="text/javascript">
var sc_project=7264690; 
var sc_invisible=1; 
var sc_security="94fdf985"; 
</script>
<script type="text/javascript" src="http://www.statcounter.com/counter/counter_xhtml.js"></script>
</body>
</html>
