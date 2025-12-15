// mobile-menu.js
document.addEventListener('DOMContentLoaded', function () {
	const menuToggle = document.getElementById('menu-toggle');
	const menuClose = document.getElementById('menu-close');
	const mobileMenu = document.getElementById('mobile-menu');
	const overlay = document.getElementById('overlay');
	const mobileMenuLinks = document.querySelectorAll('.mobile-menu__link');

	// Function to open the mobile menu
	function openMenu() {
		mobileMenu.classList.add('active');
		overlay.classList.add('active');
		document.body.style.overflow = 'hidden'; // Disable scrolling on body
	}

	// Function to close the mobile menu
	function closeMenu() {
		mobileMenu.classList.remove('active');
		overlay.classList.remove('active');
		document.body.style.overflow = ''; // Enable scrolling on body
		mobileMenu.blur(); // Remove focus from menu
	}

	// Toggle menu on button click
	menuToggle.addEventListener('click', openMenu);

	// Close menu on close button click
	menuClose.addEventListener('click', closeMenu);

	// Close menu on overlay click
	overlay.addEventListener('click', closeMenu);

	// Handle clicks on mobile menu links
	mobileMenuLinks.forEach(link => {
		link.addEventListener('click', function (event) {
			// Close the menu
			closeMenu();

			// Get the target URL or ID
			let target = this.getAttribute('href');

			// If href is #, use data-target instead
			if (target === '#') {
				target = this.getAttribute('data-target');
			}

			// Check if target is a section ID or a URL
			if (target.startsWith('#')) {
				// Smooth scroll to the target element
				const targetElement = document.querySelector(target);
				if (targetElement) {
					event.preventDefault(); // Prevent default link behavior
					targetElement.scrollIntoView({
						behavior: 'smooth'
					});
				}
			} else if (target) {
				// Redirect to the URL
				window.location.href = target;
			}
		});
	});
});
