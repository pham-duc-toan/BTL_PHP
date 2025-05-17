<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$toastTypes = ['success', 'error', 'info', 'warning'];
foreach ($toastTypes as $type):
  if (isset($_SESSION[$type])):
    $class = match ($type) {
      'success' => 'bg-success text-white',
      'error'   => 'bg-danger text-white',
      'info'    => 'bg-info text-dark',
      'warning' => 'bg-warning text-dark',
    };
?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
      <div class="toast align-items-center <?= $class ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <?= $_SESSION[$type];
            unset($_SESSION[$type]); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
<?php
  endif;
endforeach;
?>