<?php if ($_SERVER["REQUEST_METHOD"] === "POST") { 
	// Sanitize inputs
	$org_name = htmlspecialchars($_POST['org_name']);
	$address = htmlspecialchars($_POST['address']); 
	$postcode = htmlspecialchars($_POST['postcode']); 
	$country = htmlspecialchars($_POST['country']);
	    // Handle multiple care types (array)
    $care_types = isset($_POST['care_type']) ? $_POST['care_type'] : [];
    $care_types = array_map('htmlspecialchars', $care_types);
    $care_type  = implode(", ", $care_types);
	$description = htmlspecialchars($_POST['description']);
	$cqc = htmlspecialchars($_POST['cqc']);
	$status = htmlspecialchars($_POST['status']);
	// Set upload directory (shared for images and rate cards)
	$upload_dir = "uploads/"; 
	if (!file_exists($upload_dir)) 
	{
		mkdir($upload_dir, 0777, true);
	} 
	// Handle image uploads
	$uploaded_images = []; 
	if (!empty($_FILES['images']['name'][0]))
	{
		foreach ($_FILES['images']['name'] as $key => $image_name)
		{
			$tmp_name = $_FILES['images']['tmp_name'][$key];
			$safe_name = time() . '_' . basename($image_name);
			$target_path = $upload_dir . $safe_name;
			if (move_uploaded_file($tmp_name, $target_path))
			{
				$uploaded_images[] = $safe_name; 
			} } } 
	$image_list = implode(", ", $uploaded_images);
	// Handle rate card uploads
	$uploaded_ratecards = []; 
	if (!empty($_FILES['rate_cards']['name'][0])) 
	{ 
		foreach ($_FILES['rate_cards']['name'] as $key => $file_name) 
		{
			$tmp_name = $_FILES['rate_cards']['tmp_name'][$key]; $safe_name = time() . '_' . basename($file_name);
			$target_path = $upload_dir . $safe_name;
			if (move_uploaded_file($tmp_name, $target_path)) { $uploaded_ratecards[] = $safe_name;
															 } } } 
	$ratecard_list = implode(", ", $uploaded_ratecards); 
	// Prepare the data line 
	$entry = "Organization: $org_name | Address: $address | Postcode: $postcode | Country: $country | Care Type: $care_type | Description: $description | CQC: $cqc | Status: $status | Images: $image_list | Rate Cards: $ratecard_list\n";
	// Save to file 
	$file = "registrations.txt";
	file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
	echo "<p style='color:green;'>Submitted successfully!</p>";
	echo "<a href='learning.html'>Go Back</a>";
} 
else { echo "<p>Invalid request method.</p>"; } 
?>