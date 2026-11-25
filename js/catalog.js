/* ————————————————————————————————
   Черновик данных каталога.
   Позже этот файл заменит data/catalog.json из админ-панели:
   структура полей специально сделана под неё.
   ———————————————————————————————— */

window.CATALOG = {
  segments: [
    { id: "all",      label: "Все" },
    { id: "to2000",   label: "До 2 000 ₽" },
    { id: "2000-4000",label: "2 000 – 4 000 ₽" },
    { id: "4000-6000",label: "4 000 – 6 000 ₽" },
    { id: "from6000", label: "От 6 000 ₽" }
  ],

  bouquets: [
    { id: "b01", name: "Мини",           desc: "5 роз, эвкалипт, крафт",              price: 1200, img: "img/mini.jpg",          available: true  },
    { id: "b02", name: "Белый гипсофил", desc: "Гипсофила, лен, бумага",              price: 1500, img: "img/gypsophila.jpg",    available: true  },
    { id: "b03", name: "Гербера",        desc: "5 гербер, зелень",                    price: 1600, img: "img/gerbera.jpg",       available: true  },
    { id: "b04", name: "Утро",           desc: "15 тюльпанов, крафт",                 price: 1800, img: "img/tulips-bunch.jpg",  available: true  },

    { id: "b05", name: "Эвкалипт",       desc: "Эвкалипт, хлопок, сухоцветы",         price: 2000, img: "img/eucalyptus.jpg",    available: true  },
    { id: "b06", name: "Двухцветная",    desc: "Хризантема кустовая, 7 веток",        price: 2100, img: "img/chrys-bicolor.jpg", available: false },
    { id: "b07", name: "Солнечный",      desc: "Подсолнухи, солидаго",                price: 2200, img: "img/sunflower.jpg",     available: true  },
    { id: "b08", name: "Розовый сад",    desc: "11 кустовых роз, эвкалипт",           price: 2400, img: "img/roses-pink.jpg",    available: true  },

    { id: "b09", name: "Нежность",       desc: "Эустома, гортензия, гипсофила",       price: 4200, img: "img/eustoma-pink.jpg",  available: true  },
    { id: "b10", name: "Классика",       desc: "25 роз 60 см, крафт",                 price: 4500, img: "img/roses-big.jpg",     available: true  },
    { id: "b11", name: "Композиция",     desc: "Шляпная коробка, микс сезона",        price: 4800, img: "img/gift.jpg",          available: true  },
    { id: "b12", name: "Гортензия",      desc: "Гортензия, розы, эвкалипт",           price: 5400, img: "img/hydrangea.jpg",     available: false },

    { id: "b13", name: "Пион",           desc: "Сезонный, 9 пионов",                  price: 6200, img: "img/peony.jpg",         available: true  },
    { id: "b14", name: "Свадебный",      desc: "Белые розы, эустома, лен",            price: 6500, img: "img/bride.jpg",         available: true  },
    { id: "b15", name: "Белый сад",      desc: "Лизиантус, гортензия, зелень",        price: 7200, img: "img/lisianthus-white.jpg", available: true },
    { id: "b16", name: "51 роза",        desc: "Розы 70 см, премиум упаковка",        price: 9800, img: "img/roses-mix.jpg",     available: true  }
  ],

  flowers: [
    { id: "f01", name: "Роза кустовая, 50 см",  price: 190, available: true  },
    { id: "f02", name: "Роза, 60 см",           price: 250, available: true  },
    { id: "f03", name: "Роза, 70 см",           price: 320, available: true  },
    { id: "f04", name: "Роза, 90 см",           price: 450, available: true  },
    { id: "f05", name: "Пион",                  price: 690, available: false },
    { id: "f06", name: "Хризантема кустовая",   price: 180, available: true  },
    { id: "f07", name: "Тюльпан",               price: 120, available: true  },
    { id: "f08", name: "Гербера",               price: 160, available: true  },
    { id: "f09", name: "Эустома",               price: 220, available: true  },
    { id: "f10", name: "Лилия",                 price: 320, available: true  },
    { id: "f11", name: "Альстромерия",          price: 170, available: false },
    { id: "f12", name: "Гортензия",             price: 550, available: true  },
    { id: "f13", name: "Ранункулюс",            price: 240, available: true  },
    { id: "f14", name: "Гипсофила, ветка",      price: 90,  available: true  },
    { id: "f15", name: "Эвкалипт, ветка",       price: 80,  available: true  },
    { id: "f16", name: "Сухоцветы, пучок",      price: 150, available: true  }
  ]
};
