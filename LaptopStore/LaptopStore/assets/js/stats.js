function vnd(price) {
    return Number(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
}

function showProductStatistics(isTop5 = false, sortMode = null) {
    const from = document.getElementById("time-start-tk").value;
    const to = document.getElementById("time-end-tk").value;
    const search = document.getElementById("form-search-tk").value.trim(); // giữ search chính xác

    let url = `api/stats/product.php?from=${from}&to=${to}&search=${search}`;
    if (isTop5 === true) url += `&limit=5`;
    if (sortMode !== null) url += `&sort=${sortMode}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (!data.summary || !data.details) return;

            const totalProducts = data.summary.total_product;
            const totalQuantity = data.summary.total_quantity;
            const totalRevenue = data.summary.total_revenue;

            document.getElementById("product-total").innerText = totalProducts;
            document.getElementById("product-quantity").innerText = totalQuantity;
            document.getElementById("product-revenue").innerText = vnd(totalRevenue);

            let html = "";
            data.details.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.TENSP}</td>
                        <td>${item.quantity}</td>
                        <td>${vnd(item.revenue)}</td>
                        <td>
                            <button class="btn-detail" onclick="fetchOrderDetail('${item.TENSP}')">
                                <i class="fa-regular fa-eye"></i> Chi tiết
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById("showProductStats").innerHTML = html;
        })
        .catch(error => {
            console.error("Lỗi khi gọi product.php:", error);
        });
}


function showCustomerStatistics(isTop5 = false, sortMode = null) {
    const from = document.getElementById("time-start-tk").value;
    const to = document.getElementById("time-end-tk").value;
    const search = document.getElementById("form-search-tk").value;

    let url = `api/stats/customer.php?from=${from}&to=${to}&search=${search}`;
    if (isTop5 === true) url += `&limit=5`;
    if (sortMode !== null) url += `&sort=${sortMode}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (!data.summary || !data.details) return;

            document.getElementById("customer-total").innerText = data.summary.total_customers;
            document.getElementById("customer-orders").innerText = data.summary.total_orders;
            document.getElementById("customer-revenue").innerText = vnd(data.summary.total_revenue);

            let html = "";
            data.details.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.customer_name}</td>
                        <td>${item.order_count}</td>
                        <td>${vnd(item.total_revenue)}</td>
                        <td>
                            <button class="btn-detail" onclick="fetchCustomerDetail('${item.customer_id}')">
                                <i class="fa-regular fa-eye"></i> Chi tiết
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById("showCustomerStats").innerHTML = html;
        });
}


function fetchOrderDetail(tensp) {
    const formData = new FormData();
    formData.append("tensp", tensp);

    fetch("api/stats/orderDetail.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(html => {
            const modal = document.getElementById("product-order-modal");
            const content = document.getElementById("modal-product-order-content");

            if (!modal || !content) {
                console.error("Không tìm thấy modal hoặc nội dung modal.");
                return;
            }

            content.innerHTML = html;
            modal.style.zIndex = "2001"; // cao hơn top5-modal
            modal.classList.add("open");
        });
}

function closeModal() {
    const modal = document.getElementById("product-order-modal");
    if (modal) {
        modal.classList.remove("open");
    }
}

function fetchCustomerDetail(customerId) {
    const formData = new FormData();
    formData.append("customer_id", customerId);

    fetch("api/stats/customerDetail.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(html => {
            const modal = document.getElementById("customer-detail-modal");
            const content = document.getElementById("modal-customer-detail-content");

            if (!modal || !content) {
                console.error("Không tìm thấy modal hoặc nội dung.");
                return;
            }

            content.innerHTML = html;
            modal.style.zIndex = "2001"; // cao hơn top5
            modal.classList.add("open");
        });
}

function closeCustomerDetailModal() {
    const modal = document.getElementById("customer-detail-modal");
    if (modal) {
        modal.classList.remove("open");
        modal.style.zIndex = "";
    }
}

function fetchOrderDetailById(madh) {
    const formData = new FormData();
    formData.append("madh", madh);

    fetch("api/stats/orderDetail.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(html => {
            const modal = document.getElementById("order-detail-modal");
            const content = document.getElementById("modal-order-detail-content");

            if (!modal || !content) {
                console.error("Không tìm thấy modal hoặc nội dung modal.");
                return;
            }

            content.innerHTML = html;
            modal.style.zIndex = "4000"; // cao hơn các modal khác
            modal.classList.add("open");
        });
}

function closeOrderDetailModal() {
    const modal = document.getElementById("order-detail-modal");
    if (modal) {
        modal.classList.remove("open");
        modal.style.zIndex = "";
    }
}

function showTop5Dynamic() {
    const type = document.getElementById("select-statistics").value;
    const from = document.getElementById("time-start-tk").value;
    const to = document.getElementById("time-end-tk").value;
    const search = document.getElementById("form-search-tk").value;

    if (type === "product") {
        fetch(`api/stats/product.php?from=${from}&to=${to}&search=${search}&limit=5`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("top5-title").innerText = "TOP 5 SẢN PHẨM BÁN CHẠY NHẤT";
                document.getElementById("top5-header").innerHTML = `
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Doanh thu</th>
                    <th></th>
                `;

                let html = "";
                data.details.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.TENSP}</td>
                            <td>${item.quantity}</td>
                            <td>${vnd(item.revenue)}</td>
                            <td>
                                <button class="btn-detail" onclick="fetchOrderDetail('${item.TENSP}')">
                                    <i class="fa-regular fa-eye"></i> Chi tiết
                                </button>
                            </td>
                        </tr>
                    `;
                });

                document.getElementById("top5-content").innerHTML = html;
                document.getElementById("top5-modal").classList.add("open");
            });

    } else {
        fetch(`api/stats/customer.php?from=${from}&to=${to}&search=${search}&limit=5`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("top5-title").innerText = "TOP 5 KHÁCH HÀNG MUA NHIỀU NHẤT";
                document.getElementById("top5-header").innerHTML = `
                    <th>STT</th>
                    <th>Tên khách hàng</th>
                    <th>Số đơn</th>
                    <th>Doanh thu</th>
                    <th></th>
                `;

                let html = "";
                data.details.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.customer_name}</td>
                            <td>${item.order_count}</td>
                            <td>${vnd(item.total_revenue)}</td>
                            <td>
                                <button class="btn-detail" onclick="fetchCustomerDetail('${item.customer_id}')">
                                    <i class="fa-regular fa-eye"></i> Chi tiết
                                </button>
                            </td>
                        </tr>
                    `;
                });

                document.getElementById("top5-content").innerHTML = html;
                document.getElementById("top5-modal").classList.add("open");
            });
    }
}

function closeTop5Modal() {
    document.getElementById("top5-modal").classList.remove("open");
}

//Phúc

function openUpdateOrderModal(madh) {
    fetch(`api/stats/updateOrderForm.php?madh=${madh}`, { // Sửa endpoint
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById("modal-update-order-content").innerHTML = html;
        document.getElementById("update-order-modal").style.zIndex = 2001;
        document.getElementById("update-order-modal").classList.add("open");

        // Gắn sự kiện submit cho form
        const form = document.getElementById("update-order-form");
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append("action", "update_order");

                fetch("admin.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, "success");
                        closeUpdateOrderModal();
                        // Làm mới bảng đơn hàng
                        findOrder();
                    } else {
                        showToast(data.message, "error");
                    }
                })
                .catch(error => {
                    showToast("Đã có lỗi xảy ra", "error");
                    console.error("Lỗi:", error);
                });
            });
        }
    })
    .catch(error => {
        showToast("Lỗi khi tải form cập nhật", "error");
        console.error("Lỗi:", error);
    });
}

function closeUpdateOrderModal() {
    document.getElementById("update-order-modal").classList.remove("open");
}

function showToast(message, type) {
    const toast = document.getElementById("toast");
    const toastDiv = document.createElement("div");
    toastDiv.className = `toast toast-${type}`;
    toastDiv.innerHTML = `
        <div class="toast-icon">
            <i class="fa-light ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        </div>
        <div class="toast-message">${message}</div>
        <div class="toast-close" onclick="this.parentElement.remove()">×</div>
    `;
    toast.appendChild(toastDiv);
    setTimeout(() => toastDiv.remove(), 3000);
}

// Hàm findOrder để làm mới bảng đơn hàng
function findOrder() {
    const status = document.getElementById("tinh-trang").value;
    const search = document.getElementById("form-search-order").value;
    const from = document.getElementById("time-start").value;
    const to = document.getElementById("time-end").value;
    fetch(`api/stats/orders.php?status=${status}&address=${search}&from_date=${from}&to_date=${to}`)
        .then(res => res.json())
        .then(data => {
            let html = "";
            if (data.length > 0) {
                data.forEach(order => {
                    html += `
                        <tr>
                            <td>${order.MADH}</td>
                            <td>${order.HOTEN}</td>
                            <td>${new Date(order.NGAYDH).toLocaleString('vi-VN')}</td>
                            <td>${Number(order.TONGTIEN).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })}</td>
                            <td>${order.DIACHI_KH}</td>
                            <td>${order.TRANGTHAI_TEXT}</td>
                            <td>
                                <button class="btn-detail" onclick="fetchOrderDetailById('${order.MADH}')">
                                    <i class="fa-regular fa-eye"></i> Chi tiết
                                </button>
                                ${order.TRANGTHAI < 3 ? `<button class="btn btn-warning" onclick="openUpdateOrderModal('${order.MADH}')"><i class="fa-light fa-edit"></i> Cập nhật TT</button>` : ''}
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">Không có đơn hàng nào</td></tr>';
            }
            document.getElementById("showOrder").innerHTML = html;
        })
        .catch(error => console.error("Lỗi khi gọi API:", error));
}

document.addEventListener("DOMContentLoaded", function() {
    findOrder(); // Làm mới bảng đơn hàng khi tải trang
});









