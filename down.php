<?php
$inputFile = "todownload.txt";
$cookie_file = "cookies.txt";
$targetPath = dirname(__FILE__)."/";

function downloadFile($url, $targetPath, $cookie_file) {
    echo "Processing url: $url\n";
    $tempFileName = uniqid() . ".tmp";
    $ch = curl_init();
    $fp = fopen ($targetPath . $tempFileName, 'w+');
    $headerBuff = fopen('/tmp/' . $tempFileName, 'w+');
    if (!file_exists($cookie_file) || !is_writable($cookie_file)) {
        echo 'Cookie file missing or not writable.';
        die;
    }

    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_WRITEHEADER, $headerBuff);
    echo "Downloading temp file: $tempFileName\n";
    curl_exec($ch);
    fclose($fp);
    echo "Successfully downloaded temp file: $tempFileName\n";
    if(!curl_errno($ch)) {
        rewind($headerBuff);
        $headers = stream_get_contents($headerBuff);
        if (preg_match('/Content-Disposition: .*filename=([^ ,\n]+)/', $headers, $matches)) {
            $newFileName = preg_replace('/\s+/', '', $matches[1]);
            echo "Renaming $tempFileName to: $newFileName\n";
            rename($tempFileName, $targetPath . $newFileName);
        }
    } else {
        echo "Error! ".curl_error($ch)."\n";
    }

    curl_close($ch);
}
// Using array of urls since I dont want to keep the file open
$urls = array();
$file = fopen($inputFile, "r");
if ($file) {
    while (($url = fgets($file)) !== false) {
        array_push($urls, preg_replace('/\s+/', '', $url));
    }
    fclose($file);
} else {
    echo "Error reading input file!";
}
foreach ($urls as $key => $url) {
    downloadFile($url, $targetPath, $cookie_file);
}
?>
