<?php
$type = $_GET['type'] ?? 'info';
$msg = $_GET['msg'] ?? '';

$toastTypes = ['success', 'error', 'info', 'warning'];
if (!in_array($type, $toastTypes) || !$msg) {
  http_response_code(400);
  exit;
}

// Map kiểu sang Bootstrap class
$class = match ($type) {
  'success' => 'bg-success text-white',
  'error'   => 'bg-danger text-white',
  'info'    => 'bg-info text-dark',
  'warning' => 'bg-warning text-dark',
};

echo <<<HTML
<div class="toast $class position-fixed top-0 end-0 p-3" role="alert" style="z-index: 9999;">
  <div class="d-flex">
    <div class="toast-body">{$msg}</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>
HTML;
