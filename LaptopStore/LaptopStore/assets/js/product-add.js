document.getElementById('add-product-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('api/stats/product/add.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: data.success ? "success" : "error",
                    title: data.success ? "Thêm thành công" : "Lỗi",
                    timer: 2000,
                    showConfirmButton: false
                });

                document.getElementById("add-product-form").reset();
                document.getElementById("preview-img").style.display = "none";
                document.getElementById("modal-add-product").classList.remove("open");
                showProduct();
            }
        });
});

function showProduct() {
    const search = document.getElementById('form-search-product')?.value || '';
    const brand = document.getElementById('filter-brand')?.value || '';
    const category = document.getElementById('filter-category')?.value || '';

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (brand) params.append('brand', brand);
    if (category) params.append('category', category);

    fetch('api/stats/product/list.php?' + params.toString())
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('show-product');
            tbody.innerHTML = '';
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Không có sản phẩm nào</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.TENSP}</td>
                    <td>${item.TENLOAI}</td>
                    <td>${item.THUONGHIEU}</td>
                    <td>
                        <button class="btn-detail" onclick="viewProductDetail('${item.MASP}')"><i class="fa-light fa-eye"></i> Chi tiết</button>
                        <button class="btn-edit" onclick="editProduct('${item.MASP}')"><i class="fa-light fa-pen-to-square"></i> Cập nhật</button>
                        <button class="btn-delete" onclick="deleteProduct('${item.MASP}')"><i class="fa-light fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Lỗi khi tải danh sách sản phẩm:", error);
        });
}


function cancelSearchProduct() {
    document.getElementById('form-search-product').value = '';
    showProduct();
}

function viewProductDetail(masp) {
    const formData = new FormData();
    formData.append("masp", masp);

    fetch("api/stats/product/detail.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(html => {
            const modal = document.getElementById("modal-product-detail");
            const content = document.getElementById("modal-product-detail-content");
            content.innerHTML = html;
            modal.classList.add("open");
        })
        .catch(err => {
            console.error("Lỗi xem chi tiết sản phẩm:", err);
        });
}


document.addEventListener('DOMContentLoaded', showProduct);

function previewImage(event) {
    const img = document.getElementById("preview-img");
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = 'block';
    img.style.height = 'auto';
}

// Update-product 
function editProduct(masp) {
    const formData = new FormData();
    formData.append("masp", masp);
    fetch("api/stats/product/get.php", {
        method: "POST",
        body: formData,
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return alert(data.message);

            const f = document.getElementById("update-product-form");
            f.masp.value = data.product.MASP;
            f.tensp.value = data.product.TENSP;
            f.thuonghieu.value = data.product.THUONGHIEU;
            f.maloai.value = data.product.MALOAI;
            f.mau.value = data.detail.MAU;
            f.size.value = data.detail.SIZE;
            f.gianhap.value = data.detail.GIANHAP;
            f.giaban.value = data.detail.GIABAN;
            f.soluong.value = data.detail.SOLUONG;
            f.thongso.value = data.detail.THONGSO;

            const preview = document.getElementById("preview-update-img");
            preview.src = "Store/assets/img/product/" + data.product.IMG;
            preview.style.display = "block";

            document.getElementById("modal-update-product").classList.add("open");
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const updateForm = document.getElementById("update-product-form");
    if (updateForm) {
        updateForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("/LaptopStore/api/stats/product/update.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {

                    Swal.fire({
                        icon: data.success ? "success" : "error",
                        title: data.success ? "Cập nhật thành công" : "Cập nhật thất bại",
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (data.success) {
                        document.getElementById("modal-update-product").classList.remove("open");
                        showProduct();
                    }
                })
                .catch(() => {
                    Swal.fire("Lỗi", "❌ Có lỗi xảy ra khi xử lý yêu cầu!", "error");
                });

        });
    }
});

function previewUpdateImage(event) {
    const img = document.getElementById("preview-update-img");
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = "block";
}

// DELETE 
function deleteProduct(masp, force = false) {
    Swal.fire({
        title: "Xác nhận xoá?",
        text: "Bạn có chắc chắn muốn xoá sản phẩm này?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Xoá ngay",
        cancelButtonText: "Huỷ",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#aaa",
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append("masp", masp);
            if (force) formData.append("force", "1");
            console.log("🔍 Xoá sản phẩm:", masp, "Force:", force);

            fetch("api/stats/product/delete.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.needConfirm) {
                        // Xác nhận nếu còn tồn kho
                        Swal.fire({
                            title: "Sản phẩm còn tồn kho!",
                            text: data.message,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Vẫn xoá",
                            cancelButtonText: "Huỷ",
                        }).then(res2 => {
                            if (res2.isConfirmed) {
                                deleteProduct(masp, true);
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        icon: data.success ? "success" : "error",
                        title: data.success ? "Thành công" : "Thất bại",
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    if (data.success) showProduct();
                })
                .catch((err) => {
                    console.error("❌ Xoá lỗi fetch:", err);
                    Swal.fire("Lỗi", "❌ Có lỗi khi xoá sản phẩm!", "error");
                });




        }
    });
}










