<?php
require 'db.php';  
$sql = "SELECT MASP, TENSP, IMG FROM SANPHAM WHERE TRANGTHAI = 1";
$result = $conn->query($sql);
?>

<style>
  

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
    <a href="/LaptopStore-master/LaptopStore/Store/layout/chitietsp.php?masp=<?php echo htmlspecialchars($row['MASP']); ?>" onclick="return showModal(this.href)" style="text-decoration: none; color: inherit;">
      <div class="product">
        <img src="/LaptopStore-master/LaptopStore/Store/assets/img/product/<?php echo htmlspecialchars($row['IMG']); ?>" alt="<?php echo htmlspecialchars($row['TENSP']); ?>">
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