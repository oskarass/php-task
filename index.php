<?php

$page = (isset($_GET['page'])) ? $_GET['page'] : null;

if ($page >= 1 && $page <= 1000000) {

	// combination generator
	$lines = file("cats.txt");
	shuffle($lines);
	$combination = implode(", ", array_slice($lines, 0, 3));

	// Total clicks for all pages
	if(empty(file_get_contents('counter.json'))) {
		file_put_contents('counter.json', 0);
	}

	$countAll = file_get_contents('counter.json') + 1;
	file_put_contents('counter.json', $countAll);

	// clicks for this page
    if(empty(file_get_contents('page_counter.json'))) {
        file_put_contents('page_counter.json', 0);
    }

    $pageCount = file_get_contents('page_counter.json');
    $tempArray = json_decode($pageCount, true);

	if($tempArray == 0) {
		$tempArray = [];
	}

	if (!array_key_exists($page, $tempArray)) {
        $tempArray[$page]["Page: " . $page] = 0;
	}

    $countN = $tempArray[$page]["Page: " . $page] + 1;

	$page_array = ["Page: " . $page => $countN];

	$tempArray[$page] = $page_array;

	ksort($tempArray);

    $encodedTempArray = json_encode($tempArray, JSON_PRETTY_PRINT);
    file_put_contents('page_counter.json', $encodedTempArray);

	//encode data into log file for every new open:
	$log_arr = ['datetime' => date('yy-M-d H:m:s'), 'N' => $page, 'Cats' => $combination, 'countAll' => $countAll, 'CountN' => $countN];

	$current_data = file_get_contents('log.json');
	$array_data = json_decode($current_data, true);
	$array_data[] = $log_arr;
	$final_data = json_encode($array_data, JSON_PRETTY_PRINT);

	file_put_contents('log.json', $final_data);

	} else {
		print "Page does not exist";
	}

	// cache page
	$cacheFile = 'cache/'.basename($_SERVER['QUERY_STRING']).'.cache';
	$cacheTime = 60;

	if(file_exists($cacheFile) && time()-$cacheTime <= filemtime($cacheFile)){
		$cache = file_get_contents($cacheFile);
		print $cache;
		exit;
	}

	ob_start();
?>

<html>
    <head>
        <title>Cats</title>
    </head>
    <body>
		<?php if($page >= 1 && $page <= 1000000):?>
    		<h2>Combination: <?php print $combination; ?></h2>
		<?php endif; ?>
    </body>
</html>

<?php
	$cache = ob_get_contents();
	file_put_contents($cacheFile, $cache);

?>