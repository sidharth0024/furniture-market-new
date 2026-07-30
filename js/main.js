
(function () {
  'use strict';

  /* 
     Sticky Header + Category Slider scroll behaviour
      */
  const utilityBar = document.getElementById('utilityBar');
  const mainHeader = document.getElementById('mainHeader');
  const primaryNav = document.getElementById('primaryNav');

  if (!mainHeader || !primaryNav) return;

  const utilityH = utilityBar ? utilityBar.offsetHeight : 0;
  const headerH = mainHeader.offsetHeight;

  let lastScrollY = window.scrollY;
  let ticking = false;

  function onScroll() {
    const currentY = window.scrollY;
    const scrollingUp = currentY < lastScrollY;

    /* Sticky shadow on main header */
    if (currentY > utilityH) {
      mainHeader.classList.add('scrolled');
    } else {
      mainHeader.classList.remove('scrolled');
    }

    /* Primary nav offset — when stickied, its top should sit at headerH */
    if (currentY > utilityH) {
      primaryNav.style.top = headerH + 'px';
    } else {
      primaryNav.style.top = '';
    }

    lastScrollY = currentY;
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      requestAnimationFrame(onScroll);
      ticking = true;
    }
  }, { passive: true });

  /* 
     Wishlist button toggle
      */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.product-card-wishlist');
    if (btn) {
      btn.classList.toggle('active');
      const icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-heart');
        icon.classList.toggle('bi-heart-fill');
      }
    }
  });

  /* 
     Swatch selection
      */
  document.addEventListener('click', function (e) {
    const sw = e.target.closest('.swatch');
    if (!sw) return;
    const parent = sw.closest('.product-card-swatches');
    if (!parent) return;
    parent.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
    sw.classList.add('active');
  });

})();

document.addEventListener("click", function (e) {

  if (!e.target.closest(".whatsapp-enquiry")) return;

  const btn = e.target.closest(".whatsapp-enquiry");

  const name = btn.dataset.name;
  const category = btn.dataset.category;
  const price = btn.dataset.price;
  const oldPrice = btn.dataset.oldPrice;
  const image = btn.dataset.image;

  // Current page URL
  const pageUrl = window.location.href;

  // Full image URL
  const imageUrl = image.startsWith("http")
    ? image
    : window.location.origin + "/" + image;

  let message = `*New Product Enquiry*%0A%0A`;
  message += `*Product:* ${name}%0A`;
  message += `*Category:* ${category}%0A`;
  message += `*Price:* ₹${price}%0A`;

  if (oldPrice && oldPrice != "0") {
    message += `*Old Price:* ₹${oldPrice}%0A`;
  }

  message += `%0A*Product Image:* ${imageUrl}`;
  message += `%0A*Product Page:* ${pageUrl}`;
  message += `%0A%0AI am interested in this product. Please share more details.`;

  const phone = "919876543210";

  window.open(`https://wa.me/${phone}?text=${message}`, "_blank");

});
