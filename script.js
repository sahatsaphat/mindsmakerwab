document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileNav = document.getElementById('mobileNav');
    const pageOverlay = document.getElementById('pageOverlay');
    const closeBtn = document.querySelector('.close-menu-btn'); // ปุ่มปิดใหม่
    const body = document.body;
    const icon = menuToggle ? menuToggle.querySelector('i') : null;

    function closeMenu() {
        if (mobileNav) mobileNav.classList.remove('active');
        if (menuToggle) menuToggle.classList.remove('active');
        if (pageOverlay) pageOverlay.classList.remove('active');
        if (body) body.classList.remove('mobile-menu-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
        if (icon) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }

    function openMenu() {
        if (mobileNav) mobileNav.classList.add('active');
        if (menuToggle) menuToggle.classList.add('active');
        if (pageOverlay) pageOverlay.classList.add('active');
        if (body) body.classList.add('mobile-menu-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'true');
        if (icon) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        }
    }

    if (menuToggle && mobileNav && pageOverlay && icon) {
        menuToggle.addEventListener('click', () => {
            const isNavActive = mobileNav.classList.contains('active');
            if (isNavActive) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        if (pageOverlay) {
            pageOverlay.addEventListener('click', closeMenu);
        }

        if (closeBtn) { // เพิ่ม Event Listener ให้ปุ่มปิด
            closeBtn.addEventListener('click', closeMenu);
        }


        mobileNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (event) => {
                 // ถ้าเป็นปุ่ม Let's Talk หรือลิงก์ # ก็ปิดเมนู
                if (link.classList.contains('btn') || link.getAttribute('href').startsWith('#')) {
                    const targetId = link.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);

                    if (targetElement && link.getAttribute('href').startsWith('#')) {
                        event.preventDefault();
                        closeMenu();
                        setTimeout(() => {
                            targetElement.scrollIntoView({ behavior: 'smooth' });
                        }, 350);
                    } else {
                         closeMenu(); // ปิดเมนูเสมอเมื่อคลิก
                    }
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileNav.classList.contains('active')) {
                closeMenu();
            }
        });
    }
});document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});