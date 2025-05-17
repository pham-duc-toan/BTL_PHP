</div> <!-- end container -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toastEl = document.getElementById('toastSuccess');
    if (toastEl) {
      const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
      toast.show();
    }
  });
</script>

<footer class="bg-dark text-white text-center py-3 mt-5">
  &copy; <?= date("Y") ?> Shopee Clone. All rights reserved.
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
