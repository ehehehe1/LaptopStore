
            <!-- Bộ lọc bên trái -->
            <div class="filter-sidebar">
                <h3>Bộ lọc</h3>
                <div class="filter-group">
                    <label for="filter-price">Giá:</label>
                    <select id="filter-price" class="filter-price">
                        <option value="">Tất cả</option>
                        <option value="under_20">Dưới 20 triệu</option>
                        <option value="20_30">20 - 30 triệu</option>
                        <option value="over_30">Trên 30 triệu</option>
                        <option value="low_to_high">Giá thấp đến cao</option>
                        <option value="high_to_low">Giá cao đến thấp</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-type">Loại sản phẩm:</label>
                    <select id="filter-type" class="filter-type">
                        <option value="">Tất cả</option>
                        <?php
                        require 'db.php';
                        $sql = "SELECT MALOAI, TENLOAI FROM LOAISP WHERE TRANGTHAI = 1";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['MALOAI']) . "'>" . htmlspecialchars($row['TENLOAI']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button onclick="applyFilters()">Áp dụng bộ lọc</button>
            