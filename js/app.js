/* ————————————————————————————————
   Рендер каталога и мелкая логика страниц.

   Данные берутся из data/catalog.json — этот файл пишет админ-панель.
   Если JSON недоступен (например, страница открыта с диска через file://),
   используется встроенный резервный набор из js/catalog.js.
   ———————————————————————————————— */
(function () {
  "use strict";

  var fmt = new Intl.NumberFormat("ru-RU");
  var fallback = window.CATALOG || { bouquets: [], flowers: [] };
  var data = fallback;
  var state = { segment: "all", onlyAvailable: false };

  function rub(n) {
    return fmt.format(Number(n) || 0) + " ₽";
  }

  function esc(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (ch) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[ch];
    });
  }

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

  function imgUrl(src) {
    return /^(img\/|https:\/\/)/.test(src || "") ? src : "";
  }

  function makeCard(item) {
    var el = document.createElement("article");
    el.className = "card" + (item.available ? "" : " is-out");
    var src = imgUrl(item.img);
    var media = src
      ? '<a class="card-media" href="catalog.html">' +
          '<img src="' + esc(src) + '" alt="' + esc(item.name) + '" loading="lazy" width="400" height="500">' +
        "</a>"
      : '<div class="card-media ph ph-4x5"></div>';
    el.innerHTML =
      media +
      '<div class="card-body">' +
        '<h3 class="card-name">' + esc(item.name) + "</h3>" +
        (item.desc ? '<p class="card-desc">' + esc(item.desc) + "</p>" : "") +
        '<div class="card-price">' + rub(item.price) +
          (item.available ? "" : ' <span class="badge-out">под заказ</span>') +
        "</div>" +
      "</div>";
    return el;
  }

  function makeFlowerCard(item) {
    var el = document.createElement("article");
    el.className = "card" + (item.available ? "" : " is-out");
    var src = imgUrl(item.img);
    var media = src
      ? '<div class="card-media"><img src="' + esc(src) + '" alt="' + esc(item.name) + '" loading="lazy" width="400" height="500"></div>'
      : '<div class="card-media ph ph-4x5"></div>';
    el.innerHTML =
      media +
      '<div class="card-body">' +
        '<h3 class="card-name">' + esc(item.name) + "</h3>" +
        '<div class="card-price">' + rub(item.price) +
          (item.available ? "" : ' <span class="badge-out">под заказ</span>') +
        "</div>" +
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
    mobileNav.addEventListener("click", function (e) {
      if (e.target.tagName === "A") {
        mobileNav.classList.remove("is-open");
        burger.setAttribute("aria-expanded", "false");
      }
    });
  }

  /* ————— табы каталога ————— */
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

  /* ————— фильтры ————— */
  document.querySelectorAll(".chip").forEach(function (chip) {
    chip.addEventListener("click", function () {
      state.segment = chip.getAttribute("data-segment");
      document.querySelectorAll(".chip").forEach(function (c) {
        c.setAttribute("aria-pressed", c === chip ? "true" : "false");
      });
      paintCatalog();
    });
  });

  var toggle = document.querySelector("[data-available-toggle]");
  if (toggle) {
    toggle.addEventListener("change", function () {
      state.onlyAvailable = toggle.checked;
      paintCatalog();
    });
  }

  /* ————— отрисовка ————— */
  function paintFeatured() {
    var box = document.querySelector("[data-featured]");
    if (!box) return;
    box.innerHTML = "";
    data.bouquets.slice(0, 4).forEach(function (b) {
      box.appendChild(makeCard(b));
    });
  }

  function paintFlowers() {
    var box = document.querySelector("[data-flowers]");
    if (!box) return;
    box.innerHTML = "";
    data.flowers.forEach(function (f) {
      box.appendChild(makeFlowerCard(f));
    });
  }

  function paintCatalog() {
    var grid = document.querySelector("[data-catalog]");
    if (!grid) return;

    var list = data.bouquets.filter(function (b) {
      if (state.onlyAvailable && !b.available) return false;
      if (state.segment !== "all" && segmentOf(Number(b.price) || 0) !== state.segment) return false;
      return true;
    });

    grid.innerHTML = "";
    list.forEach(function (b) {
      grid.appendChild(makeCard(b));
    });

    var counter = document.querySelector("[data-count]");
    if (counter) {
      counter.textContent = list.length
        ? list.length + " " + plural(list.length, ["букет", "букета", "букетов"])
        : "В этой категории пока пусто";
    }
  }

  function paintAll() {
    paintFeatured();
    paintFlowers();
    paintCatalog();
  }

  function normalize(json) {
    if (!json || typeof json !== "object") return null;
    return {
      bouquets: Array.isArray(json.bouquets) ? json.bouquets : [],
      flowers: Array.isArray(json.flowers) ? json.flowers : []
    };
  }

  /* ————— загрузка данных ————— */
  var done = false;
  function boot(json) {
    if (done) return;
    done = true;
    var parsed = normalize(json);
    if (parsed) data = parsed;
    paintAll();
  }

  if (window.fetch) {
    fetch("data/catalog.json", { cache: "no-store" })
      .then(function (r) {
        if (!r.ok) throw new Error("no json");
        return r.json();
      })
      .then(boot)
      .catch(function () {
        boot(null);
      });
  } else {
    boot(null);
  }
})();
