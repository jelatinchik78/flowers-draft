/* ————————————————————————————————
   Рендер каталога и мелкая логика страниц
   ———————————————————————————————— */
(function () {
  "use strict";

  var fmt = new Intl.NumberFormat("ru-RU");
  var data = window.CATALOG || { bouquets: [], flowers: [], segments: [] };

  function rub(n) { return fmt.format(n) + " ₽"; }

  function plural(n, forms) {
    var a = Math.abs(n) % 100, b = a % 10;
    if (a > 10 && a < 20) return forms[2];
    if (b > 1 && b < 5) return forms[1];
    if (b === 1) return forms[0];
    return forms[2];
  }

  function segmentOf(price) {
    if (price <= 2000) return "to2000";
    if (price <= 4000) return "2000-4000";
    if (price <= 6000) return "4000-6000";
    return "from6000";
  }

  function makeCard(b) {
    var el = document.createElement("article");
    el.className = "card";
    var media = b.img
      ? '<a class="card-media" href="catalog.html">' +
          '<img src="' + b.img + '" alt="' + b.name + '" loading="lazy" width="400" height="500">' +
        "</a>"
      : '<div class="card-media ph ph-4x5"></div>';
    el.innerHTML =
      media +
      '<div class="card-body">' +
        '<h3 class="card-name">' + b.name + "</h3>" +
        '<p class="card-desc">' + b.desc + "</p>" +
        '<div class="card-price">' + rub(b.price) + "</div>" +
      "</div>";
    return el;
  }

  /* ————— мобильное меню ————— */
  var burger = document.querySelector(".burger");
  var mobileNav = document.querySelector(".mobile-nav");
  if (burger && mobileNav) {
    burger.addEventListener("click", function () {
      var open = mobileNav.classList.toggle("is-open");
      burger.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  /* ————— блок на главной ————— */
  var featured = document.querySelector("[data-featured]");
  if (featured) {
    data.bouquets.slice(0, 4).forEach(function (b) {
      featured.appendChild(makeCard(b));
    });
  }

  /* ————— прайс «цветы поштучно» ————— */
  var priceList = document.querySelector("[data-flowers]");
  if (priceList) {
    data.flowers.forEach(function (f) {
      var row = document.createElement("div");
      row.className = "price-row" + (f.available ? "" : " is-out");
      row.innerHTML =
        '<span class="price-name">' + f.name + "</span>" +
        '<span class="price-dots"></span>' +
        (f.available
          ? '<span class="price-tag">' + rub(f.price) + "</span>"
          : '<span class="badge-out">под заказ</span>');
      priceList.appendChild(row);
    });
  }

  /* ————— каталог букетов ————— */
  var grid = document.querySelector("[data-catalog]");
  if (!grid) return;

  var state = { segment: "all", onlyAvailable: false };

  document.querySelectorAll(".tab").forEach(function (tab) {
    tab.addEventListener("click", function () {
      document.querySelectorAll(".tab").forEach(function (t) {
        t.setAttribute("aria-selected", t === tab ? "true" : "false");
      });
      document.querySelectorAll("[data-panel]").forEach(function (p) {
        p.hidden = p.getAttribute("data-panel") !== tab.getAttribute("data-target");
      });
    });
  });

  function visible() {
    return data.bouquets.filter(function (b) {
      if (state.onlyAvailable && !b.available) return false;
      if (state.segment !== "all" && segmentOf(b.price) !== state.segment) return false;
      return true;
    });
  }

  function paint() {
    var list = visible();
    grid.innerHTML = "";
    list.forEach(function (b) { grid.appendChild(makeCard(b)); });
    var counter = document.querySelector("[data-count]");
    if (counter) {
      counter.textContent = list.length
        ? list.length + " " + plural(list.length, ["букет", "букета", "букетов"])
        : "В этой категории пока пусто";
    }
  }

  document.querySelectorAll(".chip").forEach(function (chip) {
    chip.addEventListener("click", function () {
      state.segment = chip.getAttribute("data-segment");
      document.querySelectorAll(".chip").forEach(function (c) {
        c.setAttribute("aria-pressed", c === chip ? "true" : "false");
      });
      paint();
    });
  });

  var toggle = document.querySelector("[data-available-toggle]");
  if (toggle) {
    toggle.addEventListener("change", function () {
      state.onlyAvailable = toggle.checked;
      paint();
    });
  }

  paint();
})();
