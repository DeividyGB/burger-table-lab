<?php
if (isset($_GET['file'])) {
    $filename = $_GET['file'];
    $filepath = '../reports/' . $filename;
    
    if (file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        exit;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Arquivo não encontrado.";
        exit;
    }
} else {
    echo "Parâmetro 'file' não fornecido.";
    exit;
}
?>