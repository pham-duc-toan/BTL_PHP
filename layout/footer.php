</div> <!-- end container -->

<!-- Hiển thị toast nếu có -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const toastEls = document.querySelectorAll('.toast');
    toastEls.forEach(el => {
      const toast = new bootstrap.Toast(el, {
        delay: 3000
      });
      toast.show();
    });
  });
</script>

<footer class="bg-dark text-white text-center py-3 mt-5">
  &copy; <?= date("Y") ?> TapHoaOnline. All rights reserved.
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>