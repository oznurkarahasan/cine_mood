// Navbar'daki avatarı yükle
function loadNavbarAvatar() {
    fetch("/CineMood/php/myaccount.php", {
        method: "GET",
        credentials: "include",
    })
    .then((response) => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then((data) => {
        if (data.success) {
            const avatarPath = `../CineMood/res/avatars/${data.avatar}`;
            const navbarAvatar = document.querySelector(".nav-profile .profile-img");
            if (navbarAvatar) {
                navbarAvatar.src = avatarPath;
            }
        }
    })
    .catch((error) => {
        console.error("Avatar yüklenirken hata:", error);
    });
}

// Sayfa yüklendiğinde avatarı yükle
document.addEventListener("DOMContentLoaded", loadNavbarAvatar); 