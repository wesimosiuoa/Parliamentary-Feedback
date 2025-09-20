<?php
/**
 * Display a Bootstrap Toast popup with optional redirect.
 *
 * @param string $type  'success', 'error', 'warning'
 * @param string $message The message to show
 * @param string|null $redirectUrl URL to redirect to (optional)
 * @param int $delay Seconds before redirect (default 3)
 */
function flashMessage($type, $message, $redirectUrl = null, $delay = 3)
{
    $icons = [
        'success' => 'fas fa-check-circle',
        'error'   => 'fas fa-times-circle',
        'warning' => 'fas fa-exclamation-circle'
    ];

    $bgClasses = [
        'success' => 'bg-success text-white',
        'error'   => 'bg-danger text-white',
        'warning' => 'bg-warning text-dark'
    ];

    $icon = $icons[$type] ?? 'fas fa-info-circle';
    $bg   = $bgClasses[$type] ?? 'bg-info text-white';

    // Toast markup
    echo '
    <div aria-live="polite" aria-atomic="true" class="position-relative">
      <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
        <div class="toast ' . $bg . ' show" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex align-items-center p-2">
            <i class="' . $icon . ' me-2 fa-lg"></i>
            <div class="toast-body fw-bold flex-grow-1">
              ' . htmlspecialchars($message) . '
            </div>
            <button type="button" class="btn-close btn-close-white ms-2 me-2" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      </div>
    </div>
    ';

    // Optional auto redirect
    if ($redirectUrl) {
        echo "<script>
                setTimeout(function() {
                    window.location.href = '" . addslashes($redirectUrl) . "';
                }, " . ($delay * 1000) . ");
              </script>";
    }

    // Initialise toast
    echo "<script>
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, {autohide:true, delay:" . ($delay * 1000) . "}).show();
        });
    </script>";
}
?>

<!-- Include Bootstrap & Font Awesome once in your layout -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
