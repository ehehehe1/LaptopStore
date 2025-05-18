
// tab for section
const sidebars = document.querySelectorAll(".sidebar-list-item.tab-content");
const sections = document.querySelectorAll(".section");

for (let i = 0; i < sidebars.length; i++) {
    sidebars[i].onclick = function () {
        document.querySelector(".sidebar-list-item.active").classList.remove("active");
        document.querySelector(".section.active").classList.remove("active");
        sidebars[i].classList.add("active");
        sections[i].classList.add("active");
    };
}

function switchStatistics(isTop5 = false, sortMode = null) {
    const type = document.getElementById("select-statistics").value;
    const searchInput = document.getElementById("form-search-tk");

    // KHÔNG reset lại form-search-tk ở đây, mà giữ nguyên
    const searchValue = searchInput ? searchInput.value : '';

    // Ẩn/hiện phần thống kê
    document.getElementById("product-statistics").style.display = "none";
    document.getElementById("customer-statistics").style.display = "none";
    const target = document.getElementById(`${type}-statistics`);
    if (target) target.style.display = "block";

    // Gọi đúng hàm thống kê với search vẫn được giữ
    if (type === "product") {
        showProductStatistics(isTop5, sortMode); // nó vẫn lấy search từ DOM
    } else if (type === "customer") {
        showCustomerStatistics(isTop5, sortMode);
    }

    // Cập nhật placeholder đúng loại
    if (searchInput) {
        searchInput.placeholder = type === "product"
            ? "Tìm kiếm tên sản phẩm..."
            : "Tìm kiếm tên khách hàng...";
    }
}




//Phúc
function handleHash() {
    const hash = window.location.hash.replace("#", "");
    const sectionMap = {
        "": 0, // Trang tổng quan
        "products": 1,
        "customers": 2,
        "orders": 3,
        "statistics": 4
    };

    const index = sectionMap[hash] !== undefined ? sectionMap[hash] : 0;
    document.querySelector(".sidebar-list-item.active").classList.remove("active");
    document.querySelector(".section.active").classList.remove("active");
    sidebars[index].classList.add("active");
    sections[index].classList.add("active");

    // Làm mới bảng đơn hàng nếu vào section Đơn hàng
    if (hash === "orders") {
        findOrder();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    switchStatistics();
    handleHash();

    // Gắn sự kiện cho form lọc đơn hàng
    const statusSelect = document.getElementById("tinh-trang");
    const searchInput = document.getElementById("form-search-order");
    const fromInput = document.getElementById("time-start");
    const toInput = document.getElementById("time-end");

    if (statusSelect) statusSelect.addEventListener("change", findOrder);
    if (searchInput) searchInput.addEventListener("input", findOrder);
    if (fromInput) fromInput.addEventListener("change", findOrder);
    if (toInput) toInput.addEventListener("change", findOrder);
});

document.addEventListener("click", function (e) {
    const reloadBtn = e.target.closest(".btn-reset-order");
    if (reloadBtn) {
        console.log("✅ Đã bấm nút reset đơn hàng");

        // Reset các input
        const formSearch = document.getElementById("form-search-order");
        const timeStart = document.getElementById("time-start");
        const timeEnd = document.getElementById("time-end");
        const tinhTrang = document.getElementById("tinh-trang");

        if (formSearch) formSearch.value = "";
        if (timeStart) timeStart.value = "";
        if (timeEnd) timeEnd.value = "";
        if (tinhTrang) tinhTrang.value = "all";

        findOrder();
    }
});

window.addEventListener("hashchange", handleHash);
//Huy



function findAccounts() {
    const status = document.getElementById("tinh-trang-account").value;
    const search = document.getElementById("form-search-account").value.trim();

    let url = `api/stats/accounts/list.php?status=${status}&search=${encodeURIComponent(search)}`;
    fetch(url)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            let html = "";
            if (data.length > 0) {
                data.forEach((account, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${account.TENDANGNHAP}</td>
                            <td>${account.HOTEN}</td>
                            <td>${account.EMAIL}</td>
                            <td>${account.SDT}</td>
                            <td>${account.TENCV}</td>
                            <td>${account.TRANGTHAI ? 'Hoạt động' : 'Bị khóa'}</td>
                            <td>
                                <button class="btn-detail" onclick="openEditAccountModal('${account.MATK}')">
                                    <i class="fa-light fa-edit"></i> Sửa
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">Không có tài khoản nào</td></tr>';
            }
            document.getElementById("account-list").innerHTML = html;
        })
        .catch(error => {
            console.error("Lỗi khi gọi API tài khoản:", error);
            showToast("Lỗi khi tải danh sách tài khoản: " + error.message, "error");
        });
}

function resetAccountFilter() {
    const statusSelect = document.getElementById("tinh-trang-account");
    const searchInput = document.getElementById("form-search-account");
    if (statusSelect) statusSelect.value = "2";
    if (searchInput) searchInput.value = "";
    findAccounts();
}

function openAddAccountModal() {
    fetch("api/stats/accounts/roles.php")
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(roles => {
            const roleSelect = document.getElementById("add-macv");
            if (!roleSelect) {
                throw new Error("Không tìm thấy phần tử add-macv");
            }
            roleSelect.innerHTML = roles.map(role => `<option value="${role.MACV}">${role.TENCV}</option>`).join("");
            document.getElementById("add-account-modal").classList.add("open");
        })
        .catch(error => {
            console.error("Lỗi khi tải vai trò:", error);
            showToast("Lỗi khi tải danh sách vai trò: " + error.message, "error");
        });
}

function closeAddAccountModal() {
    document.getElementById("add-account-modal").classList.remove("open");
    document.getElementById("add-account-form").reset();
}

function openEditAccountModal(matk) {
    fetch(`api/stats/accounts/get.php?matk=${matk}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById("edit-matk").value = data.account.MATK;
                document.getElementById("edit-hoten").value = data.account.HOTEN;
                document.getElementById("edit-email").value = data.account.EMAIL;
                document.getElementById("edit-sdt").value = data.account.SDT;
                document.getElementById("edit-diachi").value = data.account.DIACHI || "";
                document.getElementById("edit-trangthai").value = data.account.TRANGTHAI;

                fetch("api/stats/accounts/roles.php")
                    .then(res => {
                        if (!res.ok) {
                            throw new Error(`HTTP error! Status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(roles => {
                        const roleSelect = document.getElementById("edit-macv");
                        roleSelect.innerHTML = roles.map(role => 
                            `<option value="${role.MACV}" ${role.MACV === data.account.MACV ? 'selected' : ''}>${role.TENCV}</option>`
                        ).join("");
                        document.getElementById("edit-account-modal").classList.add("open");
                    });
            } else {
                showToast(data.message, "error");
            }
        })
        .catch(error => {
            console.error("Lỗi khi tải thông tin tài khoản:", error);
            showToast("Lỗi khi tải thông tin tài khoản: " + error.message, "error");
        });
}

function closeEditAccountModal() {
    document.getElementById("edit-account-modal").classList.remove("open");
    document.getElementById("edit-account-form").reset();
}

document.getElementById("add-account-form")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "add_account");

    fetch("api/stats/accounts/action.php", {
        method: "POST",
        body: formData
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            showToast(data.message, data.success ? "success" : "error");
            if (data.success) {
                closeAddAccountModal();
                findAccounts();
            }
        })
        .catch(error => {
            console.error("Lỗi khi thêm tài khoản:", error);
            showToast("Lỗi khi thêm tài khoản: " + error.message, "error");
        });
});

document.getElementById("edit-account-form")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "edit_account");

    fetch("api/stats/accounts/action.php", {
        method: "POST",
        body: formData
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            showToast(data.message, data.success ? "success" : "error");
            if (data.success) {
                closeEditAccountModal();
                findAccounts();
            }
        })
        .catch(error => {
            console.error("Lỗi khi cập nhật tài khoản:", error);
            showToast("Lỗi khi cập nhật tài khoản: " + error.message, "error");
        });
});

document.addEventListener("DOMContentLoaded", function() {
    const sidebarItems = document.querySelectorAll(".sidebar-list-item.tab-content");
    const sections = document.querySelectorAll(".section");

    sidebarItems.forEach((item, index) => {
        item.addEventListener("click", function(e) {
            e.preventDefault();

            // Xóa lớp active khỏi tất cả các tab và section
            sidebarItems.forEach(i => i.classList.remove("active"));
            sections.forEach(s => s.classList.remove("active"));
            item.classList.add("active");
            sections[index].classList.add("active");
            if (index === 2) {
                findAccounts();
            }
        });
    });
});
//login
document.getElementById("login-form")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const errorMessage = document.getElementById("error-message");

    fetch("api/stats/login_xuly.php", {
        method: "POST",
        body: formData
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text); 
                if (data.success) {
                    window.location.href = "admin.php"; // Thay bằng trang dashboard của bạn
                } else {
                    errorMessage.style.display = "block";
                    errorMessage.textContent = data.message;
                }
            } catch (error) {
                console.error("Phản hồi không phải JSON:", text);
                errorMessage.style.display = "block";
            }
        })
        .catch(error => {
            console.error("Lỗi khi đăng nhập:", error);
            errorMessage.style.display = "block";
            errorMessage.textContent = "Lỗi khi đăng nhập: " + error.message;
        });
});
//phân quyền
document.addEventListener("DOMContentLoaded", function() {
    const sidebarItems = document.querySelectorAll(".sidebar-list-item.tab-content");
    const sections = document.querySelectorAll(".section");

    // Hàm kích hoạt tab và section
    function activateTab(index) {
        sidebarItems.forEach(i => i.classList.remove("active"));
        sections.forEach(s => s.classList.remove("active"));

        if (sidebarItems[index]) {
            sidebarItems[index].classList.add("active");
        }
        if (sections[index]) {
            sections[index].classList.add("active");
        }

        // Gọi hàm tải dữ liệu tương ứng
        if (window.adminRole === 'CV001') {
            if (index === 2) { // Tab Khách hàng
                findAccounts();
            } else if (index === 3) { // Tab Đơn hàng
                // Gọi hàm tải đơn hàng nếu có (ví dụ: fetchOrders())
            } else if (index === 1) { // Tab Sản phẩm
                showProduct();
            }
        } else if (window.adminRole === 'CV002' && index === 0) { // Nhân viên bán hàng: Tab Đơn hàng
            // Gọi hàm tải đơn hàng
        } else if (window.adminRole === 'CV003' && index === 0) { // Nhân viên kho: Tab Sản phẩm
            showProduct();
        }
    }

    // Xử lý sự kiện nhấp vào sidebar
    sidebarItems.forEach((item, index) => {
        item.addEventListener("click", function(e) {
            e.preventDefault();
            activateTab(index);
        });
    });

    // Kích hoạt tab mặc định khi tải trang
    const defaultTab = window.adminRole === 'CV001' ? 0 : 0; // Admin: Tổng quan, Nhân viên: Tab duy nhất
    activateTab(defaultTab);

    // Cập nhật tên admin
    const nameAcc = document.getElementById("name-acc");
    if (nameAcc) {
        nameAcc.textContent = window.adminName || "Admin";
    }
});


