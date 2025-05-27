<?php
session_start();

if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = realpath(__DIR__ . '/../reports/' . $filename);
    $reportsDir = realpath(__DIR__ . '/../reports');

    if ($filepath && strpos($filepath, $reportsDir) === 0) {

        if (preg_match('/^relatorio_mesa_\d+(_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})?\.(xlsx|xls|csv)$/', $filename)) {

            if (file_exists($filepath)) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);

                if ($extension === 'csv') {
                    header('Content-Type: text/csv; charset=UTF-8');
                } elseif ($extension === 'xls' || $extension === 'xlsx') {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                } else {
                    header('Content-Type: application/octet-stream');
                }

                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($filepath));
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

                readfile($filepath);
                exit();
            } else {
                http_response_code(404);
                echo "Arquivo não encontrado no servidor.";
                exit();
            }
        }
    } else {
        header("Location: ../index.php");
        exit();
    }
}
?>
