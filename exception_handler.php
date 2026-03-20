<?php
set_exception_handler(function ($exception) {
    http_response_code($exception->getCode() ?: 500);
    echo json_encode($exception->getMessage() ?: "Unexpected error");
    exit();
});
?>