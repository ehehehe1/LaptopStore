
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



