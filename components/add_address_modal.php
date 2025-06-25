<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<!-- Modal thêm địa chỉ -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addAddressForm">
        <div class="modal-header">
          <h5 class="modal-title"> Thêm địa chỉ giao hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Họ tên</label>
            <input name="full_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Số điện thoại</label>
            <input name="phone" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Chọn vị trí giao hàng trên bản đồ</label>
            <div id="map" style="height: 300px;"></div>
          </div>
          <div class="mb-2">
            <label class="form-label">Địa chỉ tự động</label>
            <input type="text" id="address" name="address" class="form-control" readonly required>
            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Lưu địa chỉ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
  let mapInitialized = false;

  const initLeafletMap = () => {
    if (mapInitialized) return;
    mapInitialized = true;

    const defaultLatLng = [21.0285, 105.8542]; // Hà Nội
    //thư viện
    const map = L.map('map').setView(defaultLatLng, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    //thư viện
    const marker = L.marker(defaultLatLng, {
      draggable: true
    }).addTo(map);

    function updateAddress(lat, lng) {
      document.getElementById('lat').value = lat;
      document.getElementById('lng').value = lng;

      fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=vi`)
        .then(res => res.json())
        .then(data => {
          document.getElementById('address').value = data.display_name || 'Không tìm được địa chỉ';
        })
        .catch(() => {
          document.getElementById('address').value = 'Lỗi khi truy vấn địa chỉ';
        });
    }
    //thư viện
    // Khi kéo thả marker 
    marker.on('dragend', function() {
      const {
        lat,
        lng
      } = marker.getLatLng();
      updateAddress(lat, lng);
    });
    //thư viện
    // Khi click vào bản đồ
    map.on('click', function(e) {
      const {
        lat,
        lng
      } = e.latlng;
      marker.setLatLng([lat, lng]); // di chuyển marker tới chỗ click
      updateAddress(lat, lng);
    });

    // Lần đầu load
    updateAddress(defaultLatLng[0], defaultLatLng[1]);
  };


  // Khi modal mở thì khởi tạo bản đồ 
  const addAddressModal = document.getElementById('addAddressModal');
  addAddressModal.addEventListener('shown.bs.modal', () => {
    initLeafletMap();
    setTimeout(() => {
      window.dispatchEvent(new Event('resize')); // giúp Leaflet hiển thị đúng size
    }, 200);
  });
</script>