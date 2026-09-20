<?php
if (!isset($_GET['org']) || trim($_GET['org']) === '') {
    die("No organization specified.");
}

$org_name = trim($_GET['org']);
$file = "registrations.txt";
$description = "No description available";
$first_image = "";

if (file_exists($file)) {
    $lines = file($file);
    foreach ($lines as $line) {
        if (stripos($line, "Organization: $org_name") !== false) {
            
            // Extract description
            preg_match('/Description:\s*(.*?)\s*\|/', $line, $desc_match);
            $description = isset($desc_match[1]) ? htmlspecialchars(trim($desc_match[1])) : "No description available";
            
            // Extract first image
            preg_match('/Images:\s*([^|]+)/i', $line, $img_match);
            if (!empty($img_match[1])) {
                $images = array_map('trim', explode(",", $img_match[1]));
                if (!empty($images[0])) {
                    $first_image = "uploads/" . $images[0];
                }
            }

            break;
        }
    }
} else {
    die("Registration file not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Description - <?php echo htmlspecialchars($org_name); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f4ff, #d9e8ff);
            padding: 0px 20px;
            color: #333;
            font-size: 18px;
            min-height: 100vh;
            margin: 0;
        }
		    header {
      background-color: #343a40;
      color: white;
      padding: 16px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo {
      font-size: 22px;
      font-weight: bold;
    }
    nav ul {
      list-style: none;
      display: flex;
      gap: 15px;
      margin: 0;
      padding: 0;
    }
    nav ul li a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      padding: 6px 10px;
    }
    nav ul li a:hover {
      background-color: #495057;
      border-radius: 4px;
    }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }
		.box { 
			background: white; 
			padding: 20px; 
			border-radius: 10px; 
			border: 2px solid #007BFF; 
			width: 100%; /* full width */
			margin: auto;
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			box-sizing: border-box; /* ensures padding/border don't overflow */
		}
        h1 { 
            color: #007BFF; 
            margin-bottom: 20px; 
        }
        p { 
            font-size: 18px; 
            line-height: 1.6; 
        }
        a.button { 
            background: #007BFF;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        a.button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
	
	
	
		<header>
		<div class="logo">Care Providers</div>
  <nav>
    <ul>
      <li><a href="index.html">Home</a></li>
      <li><a href="nursing.html">Nursing Care</a></li>
      <li><a href="domiciliary.html">Domiciliary Care</a></li>
      <li><a href="residential.html">Residential Care</a></li>
      <li><a href="learning.html">Learning Disabilities Care</a></li>
      <li><a href="contact.html">Contact</a></li>
    </ul>
  </nav>
</header>
	
	
	
<div class="container">
    <h1><?php echo htmlspecialchars($org_name); ?></h1>

    <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap;margin-top:20px;">
        <?php if ($first_image): ?>
            <div style="flex:1;min-width:250px;">
                <img src="<?php echo htmlspecialchars($first_image); ?>" 
                     alt="Main image" 
                     style="max-width:100%;height:auto;border:1px solid #ccc;padding:5px;border-radius:5px;">
            </div>
        <?php endif; ?>

        <div style="flex:2;min-width:300px;">
            <p style="line-height:1.6;"><?php echo nl2br($description); ?></p>
        </div>
    </div>
</div>

</body>
</html>