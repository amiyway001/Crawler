<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

function search_darkweb($query) {
    $results = [];
    $client = new Client();
    try {
        $response = $client->request('GET', "https://ahmia.fi/search/?q=$query", ['timeout' => 10]);
        $body = $response->getBody()->getContents();
        $soup = new \DOMDocument();
        @$soup->loadHTML($body);
        $xpath = new \DOMXPath($soup);
        $links = $xpath->query("//a[@href]");
        foreach ($links as $link) {
            $results[] = $link->getAttribute('href');
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    return $results;
}

function search_surfaceweb($query) {
    $results = [];
    $params = [
        "q" => $query,
        "hl" => "en",
        "gl" => "us",
        "google_domain" => "google.com",
        "api_key" => "your-serpapi-api-key"
    ];
    $client = new Client();
    try {
        $response = $client->request('GET', 'https://serpapi.com/search.json', ['query' => $params]);
        $data = json_decode($response->getBody()->getContents(), true);
        foreach ($data['organic_results'] as $result) {
            $results[] = $result['link'];
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    return $results;
}

$results = [];
$query = "";
$nav = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = $_POST['query'];
    $nav = $_POST['nav'];
    if ($nav === 'darkweb') {
        $results = search_darkweb($query);
    } elseif ($nav === 'surfaceweb') {
        $results = search_surfaceweb($query);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Index</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        
        main {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }
        
        .search-container {
            max-width: 600px;
            width: 100%;
            padding: 30px;
            margin: 20px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .search-container h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }
        
        .search-form {
            display: flex;
            flex-direction: column;
        }
        
        .search-form label {
            margin-bottom: 10px;
            color: #495057;
            font-weight: bold;
        }
        
        .search-form select, 
        .search-form input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .search-button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: #0056b3;
        }
        
        .results-container {
            margin-top: 20px;
        }

        .results-container h2 {
            color: #343a40;
            margin-bottom: 20px;
        }
        
        .result {
            margin-bottom: 10px;
        }
        
        .result a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .result a:hover {
            color: #0056b3;
        }
        
    </style>
</head>
<body>
    <div class="search-container">
        <h1>Search Index</h1>
        <form class="search-form" action="index.php" method="post">
            <label for="nav">Search Type:</label>
            <select id="nav" name="nav">
                <option value="surfaceweb">Surface Web</option>
                <option value="darkweb">Dark Web</option>
            </select>
            <br>
            <input type="text" id="query" name="query" class="search-input" required placeholder="Search Query...">
            <br>
            <button type="submit" class="search-button">Search</button>
        </form>
        
        <?php if (!empty($results)): ?>
            <div class="results-container">
                <h2>Results for "<?php echo htmlspecialchars($query); ?>" in <?php echo htmlspecialchars($nav); ?>:</h2>
                <?php foreach ($results as $result): ?>
                    <div class="result">
                        <a href="<?php echo htmlspecialchars($result); ?>"><?php echo htmlspecialchars($result); ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
