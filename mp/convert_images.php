<?php
$uploadDir = __DIR__ . "/uploads/";
$allowedExts = ['jpg', 'jpeg', 'png', 'jfif', 'webp'];
$converted = 0;

foreach (scandir($uploadDir) as $file) {
    if ($file === '.' || $file === '..') continue;

    $path = $uploadDir . $file;
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $newName = pathinfo($file, PATHINFO_FILENAME) . ".jpg";
    $newPath = $uploadDir . $newName;

    if (!in_array($ext, $allowedExts)) continue;
    if (file_exists($newPath)) continue; // skip if already exists

    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            copy($path, $newPath);
            break;
        case 'png':
            $img = imagecreatefrompng($path);
            imagejpeg($img, $newPath, 90);
            imagedestroy($img);
            break;
        case 'jfif':
        case 'webp':
            $img = ($ext === 'jfif') ? @imagecreatefromjpeg($path) : imagecreatefromwebp($path);
            if ($img) {
                imagejpeg($img, $newPath, 90);
                imagedestroy($img);
            }
            break;
    }

    echo "Converted: $file → $newName<br>";
    $converted++;
}

if ($converted === 0) {
    echo "No images converted. Everything is already fine or unsupported.";
}
?>
