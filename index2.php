<?php

include 'Awsm/Service/Untappd.php';

$debug=false; //set to true to use a static JS array and not call teh API to avoid the rate limit.

?>


<?php
$apiKey   = '68fc36c4d9e498293f8edfe6ab9453a2';
//use the username and passowrd passed via POST submission
$username= (isset($_POST['username']) ? $_POST['username'] : '');
$password= (isset($_POST['password']) ? $_POST['password'] : '');
        
        
        
$untappd = new Awsm_Service_Untappd($apiKey, $username, $password);
        
//if someone logged in...        
if ($username != '') {       
	//pull the list of distinct beers
	
	$offset=0;
	$loop=25;
	$autocomplete="[";
	$passes=1;

	if ($debug) {
	//array of objects for advanced autocomplete display
	//value is beer_id for checkins, or a search query for unknown beers
		$autocomplete.="{
											\"value\":\"Thunderbolt (Wandering Star Brewing Company)\",
											\"beer_name\":\"Thunderbolt\",
											\"brewery_name\":\"Wandering Star Brewing Company\",
											\"beer_id\":129834,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Slyfox Maibock (Sly Fox Brewing Company)\",
											\"beer_name\":\"Slyfox Maibock ()\",
											\"brewery_name\":\"Sly Fox Brewing Company\",
											\"beer_id\":171487,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Lake Erie Monster (Great Lakes Brewing Company)\",
											\"beer_name\":\"Lake Erie Monster\",
											\"brewery_name\":\"Great Lakes Brewing Company\",
											\"beer_id\":10525,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Edmund Fitzgerald (Great Lakes Brewing Company)\",
											\"beer_name\":\"Edmund Fitzgerald ( )\",
											\"brewery_name\":\"Great Lakes Brewing Company\",
											\"beer_id\":12049,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Holy Moses White Ale (Great Lakes Brewing Company)\",
											\"beer_name\":\"Holy Moses White Ale ( )\",
											\"brewery_name\":\"Great Lakes Brewing Company\",
											\"beer_id\":10530,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Dortmunder Gold (Great Lakes Brewing Company)\",
											\"beer_name\":\"Dortmunder Gold ( )\",
											\"brewery_name\":\"Great Lakes Brewing Company\",
											\"beer_id\":6288,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Maibock (Greenport Harbor Brewing Company)\",
											\"beer_name\":\"Maibock ()\",
											\"brewery_name\":\"Greenport Harbor Brewing Company\",
											\"beer_id\":165441,
											\"action\":\"checkin\"
										}, {
											\"value\":\"He'Brew Hop Manna IPA (Shmaltz Brewing Company)\",
											\"beer_name\":\"He'Brew Hop Manna IPA ()\",
											\"brewery_name\":\"Shmaltz Brewing Company\",
											\"beer_id\":52671,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Excelsior! Fourteen (Ithaca Beer Company)\",
											\"beer_name\":\"Excelsior! Fourteen ()\",
											\"brewery_name\":\"Ithaca Beer Company\",
											\"beer_id\":97433,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Linchpin White IPA (Green Flash Brewing Co.)\",
											\"beer_name\":\"Linchpin White IPA\",
											\"brewery_name\":\"Green Flash Brewing Co.\",
											\"beer_id\":164168,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Warlord Imperial IPA (McNeill's Brewery)\",
											\"beer_name\":\"Warlord Imperial IPA ()\",
											\"brewery_name\":\"McNeill's Brewery\",
											\"beer_id\":13435,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Moo Thunder Stout (Butternuts Beer & Ale)\",
											\"beer_name\":\"Moo Thunder Stout\",
											\"brewery_name\":\"Butternuts Beer & Ale\",
											\"beer_id\":8601,
											\"action\":\"checkin\"
										}, {
											\"value\":\"Scotch Ale (Rooster Fish Brewing)\",
											\"beer_name\":\"Scotch Ale\",
											\"brewery_name\":\"Rooster Fish Brewing\",
											\"beer_id\":153970,
											\"action\":\"checkin\"
										}]";
	} else {
	
		//loop through API calls while there are a max number of results: 25
		while ($loop==25) {
			try {
			    $result = $untappd->userDistinctBeers($username, $offset);
			} catch (Awsm_Service_Untappd_Exception $e) {
			    die($e->getMessage());
			}
			

		  $beer=0;

		  while ($result->results[$beer]) {
				$item=$result->results[$beer];
				
				if ($beer>0) {
					$autocomplete.= "}, ";
				} 
				$autocomplete.= "{\n";
				$autocomplete.= "\"value\" : \"" . utf8_decode ($item->beer_name) . " " . utf8_decode ($item->brewery_name) . ")\",\n";
				$autocomplete.= "\"beer_name\" : \"" . utf8_decode ($item->beer_name) ."\",\n";
				$autocomplete.= "\"brewery_name\" : \"" . utf8_decode ($item->brewery_name) . "\",\n";
				$autocomplete.= "\"beer_id\" : " . $item->beer_id . ",\n";
				$autocomplete.= "\"action\" : \"checkin\"\n";
				
				$beer++;
			} 
			$autocomplete.= "}";
	
	/*
			echo "offset= $offset\n";
			echo"pass number $passes\n";
			$passes++;
	*/
			
			//increment the offset
			$offset+=$result->returned_results;
			
			//stop looping if less than 25 results returned 
			$loop=$result->returned_results;
			$loop=0; //prevents looping 
		}
	
		//close the array
		$autocomplete.= "]";
	}
}
//$result=json_encode($result);
//$result="{\"hi\":\"hello\"}";
?>
<head>
	<title>Brewnique</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
		<link type="text/css" href="css/reset.css" rel="stylesheet" />
		<link type="text/css" href="css/styles.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>

  
  	<script>
			$(function() {
		
			  var beerlist= <?php echo $autocomplete; ?>;
			
				//alphbetical search function to use hte name fomr the objects
				beerlist.sort(function(a,b){return b.label-a.label; });
		
		
				$( "#beers" ).autocomplete({
					source: beerlist,
					focus: function( event, ui ) {
						$( "#beers" ).val( ui.item.value );
						return false;
					},
					select: function( event, ui ) {
						$( "#beers" ).val( ui.item.value );
						
//						$( "#project-id" ).val( ui.item.value );
//						$( "#project-description" ).html( ui.item.desc );
//						$( "#project-icon" ).attr( "src", "images/" + ui.item.icon );
						
						// check in this beer
						
						return false;
					}
					
				})
				.data( "autocomplete" )._renderItem = function( ul, item ) {
				  var term = this.term.split(' ').join('|');
				  var re = new RegExp("(" + term + ")", "gi") ;
				  var t1 = item.beer_name.replace(re,"<b>$1</b>");
				  var t2 = item.brewery_name.replace(re,"<b>$1</b>");
				  return $( "<li></li>" )
				     .data( "item.autocomplete", item )
				     .append( "<a><span class=\"beer\">" + t1 + "</span><br><span class=\"brewery\">" + t2 + "</span></a>" )
				     .appendTo( ul );
				};
			});
		</script>


</head>
<body>

<?php if (!((isset($_POST['username'])) && (isset($_POST['password'])))): ?>

<h1>Brewnique <span class="powered-by">powered by Untappd</span></h1>
<h2>Brewnique helps you find beers you've already logged on Untappd, so you can make sure you're trying a new one!</h2>
<p>Sign in with your Untappd username and password.  Don't worry, we don't store it.</p>

  <form id="login" action="" method="post">
    <label for="username">Username</label><br>
    <input type="text" name="username" id="username" placeholder="Untappd username" value=""> <br />
    
    <label for="password">Password</label><br/>
    <input type="password" name="password" id="password" placeholder="Untappd password"> <br/>
    
    <input type="submit" value="Sign in">
  </form>
<?php else: ?>
  <h2><?php echo "Hi, $username!" ?></h2>
  
<div class="ui-widget">
	<label for="beers">Which beer are you looking for? </label><br />
	<input type="search" id="beers" placeholder="Beers or breweries" autocomplete="off" />
</div>
<?php endif; ?>

	<script type="text/javascript">
/*
		alert("end of DOM");
			$( "#beers" ).autocomplete("search","");
*/
	
	</script>
  </body>
</html>
