
//search


$(document).ready(function() {
    // Tìm kiếm thời gian thực khi nhập
    let timeout;
    $('#search').on('keyup', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            searchProduct();
        }, 300); // Trễ 300ms để giảm yêu cầu AJAX
    });

    // Hàm tìm kiếm khi nhấp vào biểu tượng tìm kiếm
    window.searchProduct = function() {
        var query = $('#search').val();
        var price = $('#filter-price').val() || '';
       
        var type = $('#filter-type').val() || '';
        var $results = $('#product-results').empty();
        var $loading = $('#loading').show();

        $.ajax({
            type: 'POST',
            url: '/LaptopStore/Store/layout/search.php',
            data: {
                query: query,
                price: price,
                type: type
            },
            dataType: 'json',
            success: function(response) {
                $loading.hide();
                if (response.success && response.products.length > 0) {
                    $.each(response.products, function(index, product) {
                        var imageSrc = product.hinhanh ? 
                            `/LaptopStore/Store/assets/img/product/${product.hinhanh}` : 
                            '/LaptopStore/Store/assets/img/product/default-product.jpg';
                        var html = `
                            <a href="/LaptopStore/Store/layout/chitietsp.php?masp=${product.masp}" onclick="return showModal(this.href)" style="text-decoration: none; color: inherit;">
                                <div class="product">
                                    <img src="${imageSrc}" alt="${product.tensp}" loading="lazy">
                                    <p>${product.tensp}</p>
                                    <p class="price">${product.gia.toLocaleString('vi-VN')} đ</p>
                                </div>
                            </a>
                        `;
                        $results.append(html);
                    });
                } else {
                    $results.append('<p>Không tìm thấy sản phẩm nào.</p>');
                }
            },
            error: function(xhr) {
                $loading.hide();
                $results.html('<p>Lỗi: ' + xhr.responseText + '</p>');
            }
        });
    };

    // Hàm áp dụng bộ lọc
    window.applyFilters = function() {
        searchProduct(); // Tái sử dụng searchProduct để thống nhất logic
    };
});




// Đóng modal khi bấm ra ngoài
window.onclick = function(event) {
  const modal = document.getElementById("loginModal");
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
function showproductMenu(type) {
    console.log('Filtering products for type:', type);
    $.ajax({
        type: 'POST',
        url: '/LaptopStore/Store/layout/fetch_products.php',
        data: {
            type: type,
            page: 1,
            paginate: true // Thêm cờ để bật phân trang
        },
        dataType: 'json',
        success: function(response) {
            console.log('Fetch products response:', response);
            if (response.success) {
                $('#product-container').html(response.html);
                $('#pagination').html(response.pagination);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: response.error
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi server',
                text: 'Lỗi: ' + xhr.responseText
            });
        }
    });
}

// Xử lý phân trang
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    var page = $(this).data('page');
    var type = $(this).data('type') || '';
    console.log('Loading page:', page, 'Type:', type);
    $.ajax({
        type: 'POST',
        url: '/LaptopStore/Store/layout/fetch_products.php',
        data: {
            type: type,
            page: page,
            paginate: true
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#product-container').html(response.html);
                $('#pagination').html(response.pagination);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: response.error
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi server',
                text: 'Lỗi: ' + xhr.responseText
            });
        }
    });
});

