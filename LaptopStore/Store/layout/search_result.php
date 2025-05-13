<?php
require 'db.php';  
$sql = "SELECT MASP, TENSP, IMG FROM SANPHAM WHERE TRANGTHAI = 1";
$result = $conn->query($sql);
?>

<style>
  /* Style cho thanh tìm kiếm */
  .search-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 10px;
    text-align: center;
  }

  .search-input {
    width: 100%;
    max-width: 500px;
    padding: 12px 20px;
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 25px;
    outline: none;
    transition: border-color 0.3s ease;
  }

  .search-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
  }

  .loading {
    display: none;
    margin: 10px auto;
    text-align: center;
    font-size: 0.9rem;
    color: #666;
  }

  /* Style cho giá sản phẩm */
  .product .price {
    color: #e63946;
    font-weight: bold;
    margin: 10px 0;
  }
</style>

<!-- Thanh tìm kiếm -->


<div class="product-list" id="product-results">
  <?php while ($row = $result->fetch_assoc()): ?>
    <a href="/LaptopStore/Store/layout/chitietsp.php?masp=<?php echo htmlspecialchars($row['MASP']); ?>" onclick="return showModal(this.href)" style="text-decoration: none; color: inherit;">
      <div class="product">
        <img src="/LaptopStore/Store/assets/img/product/<?php echo htmlspecialchars($row['IMG']); ?>" alt="<?php echo htmlspecialchars($row['TENSP']); ?>">
        <p><?php echo htmlspecialchars($row['TENSP']); ?></p>
        <!-- Giá sẽ được thêm từ backend trong AJAX -->
      </div>
    </a>
  <?php endwhile; ?>
</div>

<!-- Modal Popup sử dụng iframe -->
<div class="overlay" onclick="closeModal()"></div>
<div class="modal">
  <span class="close-btn" onclick="closeModal()">×</span>
  <iframe id="modal-iframe" src=""></iframe>
</div>