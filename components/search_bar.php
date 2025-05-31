<form class="mx-auto ms-5 position-relative" action="/cuahangtaphoa/index.php" method="get" style="width: 600px;" autocomplete="off">
  <input id="searchInput" name="q" class="form-control" type="search" placeholder="Tìm sản phẩm..." aria-label="Search">
  <button class="btn btn-outline-success position-absolute top-0 end-0 h-100 px-3" type="submit">
    <i class="bi bi-search"></i>
  </button>

  <!-- Gợi ý sản phẩm -->
  <div id="searchResults" class="bg-white shadow border rounded mt-1 d-none position-absolute"
    style="top: 100%; width: 100%; z-index: 1050; max-height: 320px; overflow-y: auto;"></div>
</form>

<script>
  const input = document.getElementById('searchInput');
  const resultsBox = document.getElementById('searchResults');

  input.addEventListener('input', function() {
    const keyword = this.value.trim();
    if (keyword.length === 0) {
      resultsBox.classList.add('d-none');
      resultsBox.innerHTML = '';
      return;
    }

    fetch(`/cuahangtaphoa/api/search_products.php?q=${encodeURIComponent(keyword)}`)
      .then(res => res.json())
      .then(data => {
        if (data.length > 0) {
          resultsBox.classList.remove('d-none');
          resultsBox.innerHTML = data.map(p => `
          <a href="/cuahangtaphoa/index.php?q=${p.name}" class="d-flex align-items-center text-decoration-none text-dark p-2 border-bottom hover-bg">
            <img src="${p.image}" alt="" width="48" height="48" class="me-2 rounded" style="object-fit: cover;">
            <div>
              <div class="fw-semibold">${p.name}</div>
              <small class="text-muted">${p.brand ?? ''}</small>
            </div>
          </a>
        `).join('');
        } else {
          resultsBox.classList.remove('d-none');
          resultsBox.innerHTML = `<div class="p-2 text-muted">Không tìm thấy sản phẩm</div>`;
        }
      });
  });

  // Ẩn khi click ra ngoài
  document.addEventListener('click', function(e) {
    if (!resultsBox.contains(e.target) && e.target !== input) {
      resultsBox.classList.add('d-none');
    }
  });
</script>