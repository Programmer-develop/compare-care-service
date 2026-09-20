<?php
$search_result = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_term = trim($_POST['search']);
    $service_term = isset($_POST['service']) ? trim($_POST['service']) : '';
    $file = "registrations.txt";

    if (file_exists($file)) {
        $lines = file($file);
        foreach ($lines as $line) {
            // Match both search term and service
            if (
                stripos($line, $search_term) !== false &&
                ($service_term === '' || preg_match('/Care Type:\s*([^|]+)/i', $line, $care_match) && stripos($care_match[1], $service_term) !== false)
            ) {
                // Extract Images
                preg_match('/Images:\s*(.*?)(\s*\| Rate Cards:|$)/', $line, $image_match);
                $images = isset($image_match[1]) ? explode(",", trim($image_match[1])) : [];

                // Extract Rate Cards
                preg_match('/Rate Cards:\s*(.*)$/', $line, $ratecard_match);
                $ratecards = isset($ratecard_match[1]) ? explode(",", trim($ratecard_match[1])) : [];

                // Remove file info for clean text display
                $line_without_files = preg_replace('/\| Images:.*$/', '', $line);

                preg_match('/Care Type: (.*?) \|/', $line, $care_match);
                $care_type = isset($care_match[1]) ? htmlspecialchars(trim($care_match[1])) : '';

                $fields = explode('|', $line_without_files);

                // Extract organization name
                preg_match('/Organization:\s*(.*?)\s*\|/', $line, $org_match);
                $org_name = isset($org_match[1]) ? trim($org_match[1]) : '';

                $search_result .= "
                <div class='result-flex-container'>
                    <div class='provider-details'>
                        <strong>Care Provider Details:</strong><br>";
                
                foreach ($fields as $field) {
                    if (stripos(trim($field), 'Description:') === 0) continue;
                    $search_result .= "<div>" . htmlspecialchars(trim($field)) . "</div>";
                }

                // Images
                if (!empty($images)) {
                    $search_result .= "<br><strong>Images:</strong><br><div class='images-container'>";
                    foreach ($images as $img) {
                        $img = trim($img);
                        if ($img != "") {
                            $search_result .= "<img src='uploads/$img' alt='Uploaded Image' onclick='openModal(this.src)'>";
                        }
                    }
                    $search_result .= "</div>";
                }

                // Rate Cards
                if (!empty($ratecards)) {
                    $search_result .= "<br><strong>Rate Cards:</strong><br><div class='ratecards-container'>";
                    foreach ($ratecards as $file) {
                        $file = trim($file);
                        if ($file != "") {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $search_result .= "<img src='uploads/$file' alt='Rate Card Image' onclick='openModal(this.src)'>";
                            } else {
                                $search_result .= "<a href='uploads/$file' target='_blank'>📄 " . htmlspecialchars($file) . "</a>";
                            }
                        }
                    }
                    $search_result .= "</div>";
                }

                // Application Form
                $search_result .= "
                    </div> <!-- end provider-details -->

                    <div class='apply-form'>
                        <h3>Apply for $org_name</h3>
                        <form action='send_application.php' method='POST'>
                            <input type='hidden' name='organization' value='" . htmlspecialchars($org_name) . "'>
                            <div><input type='text' name='name' placeholder='Your Name' required></div>
                            <div><input type='email' name='email' placeholder='Your Email' required></div>
                            <div><input type='text' name='phone' placeholder='Phone Number' required></div>
                            <div><textarea name='message' placeholder='Message' required></textarea></div>
                            <div class='g-recaptcha' data-sitekey='6LfHxtgrAAAAAMTeUJIJS7TUy2AnfIYe4x9nhV21'></div>
                            <div><button type='submit'>Apply</button></div>
                        </form>
                    </div> <!-- end apply-form -->
                </div> <!-- end result-flex-container -->
                ";
            }
        }

        if ($search_result === "") {
            $search_result = "<p class='no-results'>No matching entries found.</p>";
        }
    } else {
        $search_result = "<p style='color:red;'>Registration file not found.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        * { box-sizing: border-box; }
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
        .logo { font-size: 22px; font-weight: bold; }
        nav ul { list-style: none; display: flex; gap: 15px; margin: 0; padding: 0; }
        nav ul li a { color: white; text-decoration: none; font-weight: 500; padding: 6px 10px; }
        nav ul li a:hover { background-color: #495057; border-radius: 4px; }

        .container { max-width: 100%; margin: 0 auto; }

        .result-flex-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            background-color: #fff;
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 50, 0.1);
            border-left: 6px solid #0056b3;
        }
        .provider-details { flex: 1 1 50%; }
        .apply-form {
            flex: 1 1 40%;
            background-color: #f0f4ff;
            padding: 20px;
            border-radius: 10px;
        }
        .apply-form form div { margin-bottom: 15px; }
        .apply-form button {
            background-color: #0056b3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .apply-form button:hover { background-color: #003d80; }

        .images-container, .ratecards-container {
            display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;
        }
        .images-container img, .ratecards-container img {
            width: 300px; height: 300px; object-fit: cover;
            border-radius: 10px; box-shadow: 0 3px 8px rgba(0,0,0,0.15);
            cursor: pointer; transition: transform 0.3s ease; border: 2px solid transparent;
        }
        .images-container img:hover, .ratecards-container img:hover {
            transform: scale(1.1); border-color: #0056b3; box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .ratecards-container a {
            display: inline-block; background-color: #e1ecff; color: #0056b3;
            padding: 8px 14px; border-radius: 8px; text-decoration: none;
            font-weight: 600; box-shadow: 0 2px 7px rgba(0, 86, 179, 0.15);
            transition: background-color 0.3s ease; margin: 5px 10px 5px 0;
        }
        .ratecards-container a:hover { background-color: #c2d3ff; }

        .no-results { font-size: 24px; color: #cc0000; text-align: center; margin-top: 100px; font-weight: 600; }

        /* Modal Styles */
        .image-modal {
            display: none; position: fixed; z-index: 10000; left: 0; top: 0;
            width: 100%; height: 100%; background-color: rgba(0,0,0,0.85);
            justify-content: center; align-items: center; animation: fadeIn 0.3s ease forwards;
        }
        .image-modal img { max-width: 85%; max-height: 85%; border-radius: 15px; box-shadow: 0 0 40px rgba(255,255,255,0.5); animation: scaleIn 0.3s ease forwards; }
        .image-modal .close-btn {
            position: absolute; top: 25px; right: 40px; font-size: 48px; color: white;
            cursor: pointer; font-weight: 900; user-select: none; transition: color 0.25s ease;
        }
        .image-modal .close-btn:hover { color: #ff4444; }

        @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
        @keyframes scaleIn { from {transform: scale(0.8);} to {transform: scale(1);} }

        @media (max-width: 900px) {
            .result-flex-container { flex-direction: column; }
            .provider-details, .apply-form { flex: 1 1 100%; }
        }

        @media (max-width: 720px) {
            body { padding: 25px 10px; font-size: 16px; }
            .images-container img, .ratecards-container img { width: 90px; height: 70px; }
        }
		
		.apply-form {
    flex: 1 1 40%;
    background: url('images/form-bg.png') no-repeat center center; /* <-- your image */
    background-size: cover;
    padding: 20px;
    border-radius: 10px;
    color: #fff; /* ensures text stands out */
    position: relative;
}

/* Optional overlay for readability */
.apply-form::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.5); /* dark overlay */
    border-radius: 10px;
    z-index: 0;
}

.apply-form h3,
.apply-form form {
    position: relative;
    z-index: 1; /* keep text above overlay */
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
    <?php echo $search_result; ?>
</div>

<!-- Modal -->
<div id="imageModal" class="image-modal" onclick="closeModal(event)">
    <span class="close-btn" onclick="closeModal(event)">&times;</span>
    <img id="modalImage" src="" alt="Preview">
</div>

<script>
    function openModal(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modalImg.src = src;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(event) {
        if(event.target.id === 'modalImage') return;
        const modal = document.getElementById('imageModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
</script>

</body>
</html>