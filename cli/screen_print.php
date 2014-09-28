<?php
// capture the screen
//$img = imagegrabscreen();
//imagepng($img, 'screenshot1.png');

print "Obtaining patient ID: ";
$patientId = getPatientId();
print "Patient ID set to: " . $patientId . PHP_EOL;

$time = new DateTime();
$timestamp = $time->format('Y-m-d H:i:s');
print "Test date set to: " . $timestamp . PHP_EOL;

print "Grabbing a screnshot and cropping the picture..." . PHP_EOL;
$filePath = cropScreen();

print "Uploading picture..." . PHP_EOL;
uploadData($filePath, $patientId, $timestamp);

print "Converting to PDF...";
print "Saved as " . savePage($patientId, $timestamp) . PHP_EOL;

print "Opening a document to print..." . PHP_EOL;
printPage();

/**
 * @return  string
 */
function getPatientId() {
    $fh = fopen('php://stdin', 'r');
    $input = fgets($fh, 1024);

    $patientId = trim($input, PHP_EOL);

    return $patientId;
}

/**
 * @param   string  $filePath
 * @param   int     $patientId
 * @param   string  $timestamp
 *
 * @return  mixed
 */
function uploadData($filePath, $patientId, $timestamp) {
    // $target_url = 'http://akopov.webfactional.com/screenprint/upload';
    $url = 'http://localhost:11101/upload';

    $fileFullPath = realpath($filePath);

    $post = array(
        'patientId' => $patientId,
        'timestamp' => $timestamp,
        'image'     => '@'.$fileFullPath
    );

    $oldErrorSettings = ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close ($ch);

    ini_set('error_reporting',$oldErrorSettings);

    return $result;
}

/**
 * @param   string  $filePath
 *
 * @return  $filePath
 */
function cropScreen($filePath = 'screenshot_cropped.png') {
    $imgScreen = imagegrabscreen();
    $imgCrop = imagecreatetruecolor(620, 650);

    imagecopy($imgCrop, $imgScreen, 0, 0, 8, 87, 620, 650);
    imagepng($imgCrop, $filePath);

    imagedestroy($imgCrop);
    imagedestroy($imgScreen);

    return $filePath;
}


/**
 * @param   string  $patientId
 * @param   string  $timestamp
 *
 * @return  string
 */
function savePage($patientId, $timestamp) {
    $time = strtotime($timestamp);
    $datetime = new DateTime('@' . $time);
    $date = $datetime->format('Y-m-d');

    $pdfFileName = implode(DIRECTORY_SEPARATOR, array(
        dirname(dirname(__FILE__)),
        'pdfs',
         $date . '_' . $patientId . '.pdf'
    ));

    $scriptPath = implode(DIRECTORY_SEPARATOR, array(
        dirname(dirname(__FILE__)),
        'html2any-cmd-windows',
        'bin',
        'html2any.exe'
    ));

    $cmdLine = '"' . $scriptPath . '" http://localhost:11101/show "' . $pdfFileName . '"';

    // print "Running " . $cmdLine . PHP_EOL; exit;
    exec($cmdLine);

    return $pdfFileName;
}

function printPage() {
    $browser = new COM('InternetExplorer.Application');
    $browserHandle = $browser->HWND;

    $browser->Visible	 = true;
    $browser->Fullscreen = true;

    $browser->Navigate('http://localhost:11101/show');
    while ($browser->Busy) {
        com_message_pump(4000);
    }
}
