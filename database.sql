DROP TABLE IF EXISTS "product" CASCADE;
DROP TABLE IF EXISTS "category" CASCADE;
DROP TABLE IF EXISTS "order_product" CASCADE;
DROP TABLE IF EXISTS "order" CASCADE;
DROP SEQUENCE IF EXISTS "order_id";
DROP SEQUENCE IF EXISTS "product_id";
DROP SEQUENCE IF EXISTS "category_id";
DROP SEQUENCE IF EXISTS "order_product_id";

CREATE SEQUENCE "category_id";
CREATE TABLE "category" (
    "id" BIGINT NOT NULL default nextval('category_id'),
    "name" CHARACTER VARYING(255) NOT NULL,
    "parent_id" BIGINT NULL REFERENCES "category"("id") ON DELETE RESTRICT ON UPDATE CASCADE,
    PRIMARY KEY("id")
);

CREATE SEQUENCE "product_id";
CREATE TABLE "product" (
  "id" BIGINT NOT NULL default nextval('product_id'),
  "name" CHARACTER VARYING(255) NOT NULL,
  "image" CHARACTER VARYING(255) NOT NULL,
  "price" NUMERIC(9,2) NOT NULL,
  "category_id" BIGINT NOT NULL REFERENCES "category"("id") ON DELETE RESTRICT ON UPDATE CASCADE,
  PRIMARY KEY("id")
);

CREATE SEQUENCE "order_id";
CREATE TABLE "order" (
  "id" BIGINT NOT NULL default nextval('order_id'),
  "date" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  "client_name" CHARACTER VARYING(255) NOT NULL,
  "client_phone" CHARACTER VARYING(255) NOT NULL,
  "client_address" CHARACTER VARYING(1024) NOT NULL,
  "status" INT NOT NULL,
  PRIMARY KEY("id")
);

CREATE SEQUENCE "order_product_id";
CREATE TABLE "order_product" (
  "id" BIGINT NOT NULL default nextval('order_product_id'),
  "order_id" BIGINT NOT NULL REFERENCES "order"("id") ON DELETE RESTRICT ON UPDATE CASCADE,
  "product_id" BIGINT NOT NULL REFERENCES "product"("id") ON DELETE RESTRICT ON UPDATE CASCADE,
  "count" INT NOT NULL,
  PRIMARY KEY("id")
);


INSERT INTO "category" VALUES (1, 'Фрукты', null);
INSERT INTO "category" VALUES (11, 'Яблоки и груши', 1);
INSERT INTO "category" VALUES (12, 'Цитрусовые', 1);
INSERT INTO "category" VALUES (13, 'Экзотические', 1);
INSERT INTO "category" VALUES (14, 'Бахчевые', 1);
INSERT INTO "category" VALUES (15, 'Ягоды', 1);
INSERT INTO "category" VALUES (151, 'Свежие', 15);
INSERT INTO "category" VALUES (152, 'Замороженные', 15);

INSERT INTO "category" VALUES (2, 'Овощи', null);
INSERT INTO "category" VALUES (21, 'Листовые', 2);
INSERT INTO "category" VALUES (22, 'Плодовые', 2);
INSERT INTO "category" VALUES (221, 'Паслёновые', 22);
INSERT INTO "category" VALUES (222, 'Тыквенные', 22);
INSERT INTO "category" VALUES (23, 'Бобовые', 2);
INSERT INTO "category" VALUES (24, 'Корнеплоды', 2);
INSERT INTO "category" VALUES (25, 'Луковичные', 2);

-- Фрукты
-- -- Яблоки и груши
INSERT INTO "product" VALUES (default, 'Яблоко зеленое',  'apple-green.jpg',  35.80, 11);
INSERT INTO "product" VALUES (default, 'Яблоко красное',  'apple-red.jpg',    37.50, 11);
INSERT INTO "product" VALUES (default, 'Груша',           'pear.jpg',         33.00, 11);
-- -- Цитрусовые
INSERT INTO "product" VALUES (default, 'Апельсин',        'orange.jpg',       55.25, 12);
INSERT INTO "product" VALUES (default, 'Мандарин',        'mandarin.jpg',     60.00, 12);
INSERT INTO "product" VALUES (default, 'Лимон',           'lemon.jpg',        25.40, 12);
INSERT INTO "product" VALUES (default, 'Лайм',            'lime.jpg',         43.70, 12);
INSERT INTO "product" VALUES (default, 'Грейпфрут',       'grapefruit.jpg',   81.15, 12);
-- -- Экзотические
INSERT INTO "product" VALUES (default, 'Бананы',          'bananas.jpg',      39.90, 13);
INSERT INTO "product" VALUES (default, 'Кокос',           'coconut.jpg',      93.30, 13);
INSERT INTO "product" VALUES (default, 'Киви',            'kiwi.jpg',         49.95, 13);
INSERT INTO "product" VALUES (default, 'Ананас',          'ananas.jpg',       98.80, 13);
INSERT INTO "product" VALUES (default, 'Папайя',          'papaya.jpg',      115.20, 13);
INSERT INTO "product" VALUES (default, 'Дуриан',          'durian.jpg',      156.25, 13);
-- -- Бахчевые
INSERT INTO "product" VALUES (default, 'Арбуз',           'watermelon.jpg',   72.25, 14);
INSERT INTO "product" VALUES (default, 'Арбуз квадратный','melonsquared.jpg', 99.95, 14);
INSERT INTO "product" VALUES (default, 'Дыня',            'muskmelon.jpg',    76.50, 14);
-- -- Ягоды
-- -- -- Свежие
INSERT INTO "product" VALUES (default, 'Клубника',        'strawberry.jpg',   64.40, 151);
INSERT INTO "product" VALUES (default, 'Земляника',       'wstrawberry.jpg',  53.55, 151);
INSERT INTO "product" VALUES (default, 'Малина',          'raspberry.jpg',    47.10, 151);
INSERT INTO "product" VALUES (default, 'Голубика',        'blueberry.jpg',    72.25, 151);
-- -- -- Замороженные
INSERT INTO "product" VALUES (default, 'Клубника замороженная', 'strawberry-fr.jpg',  62.25, 152);
INSERT INTO "product" VALUES (default, 'Черника замороженная',  'blueberry-fr.jpg',   66.15, 152);
INSERT INTO "product" VALUES (default, 'Клюква замороженная',   'cranberry-fr.jpg',   40.50, 152);

-- Овощи
-- -- Листовые
INSERT INTO "product" VALUES (default, 'Капуста',   'cabbage.jpg',  19.95, 21);
INSERT INTO "product" VALUES (default, 'Укроп',     'dill.jpg',     12.50, 21);
INSERT INTO "product" VALUES (default, 'Базилик',   'basil.jpg',    35.70, 21);
INSERT INTO "product" VALUES (default, 'Мята',      'mint.jpg',     26.35, 21);
-- -- Плодовые
-- -- -- Паслёновые
INSERT INTO "product" VALUES (default, 'Помидор',   'tomato.jpg',   65.00, 221);
INSERT INTO "product" VALUES (default, 'Перец',     'pepper.jpg',   42.50, 221);
INSERT INTO "product" VALUES (default, 'Баклажан',  'eggplant.jpg', 39.90, 221);
-- -- -- Тыквенные
INSERT INTO "product" VALUES (default, 'Огурец',    'cucumber.jpg', 22.30, 222);
INSERT INTO "product" VALUES (default, 'Кабачок',   'zucchini.jpg', 37.75, 222);
INSERT INTO "product" VALUES (default, 'Тыква',     'pumpkin.jpg',  50.00, 222);
-- -- Бобовые
INSERT INTO "product" VALUES (default, 'Горох',     'peas.jpg',     45.90, 23);
INSERT INTO "product" VALUES (default, 'Фасоль',    'beans.jpg',    41.90, 23);
-- -- Корнеплоды
INSERT INTO "product" VALUES (default, 'Картофель', 'potato.jpg',   27.75, 24);
INSERT INTO "product" VALUES (default, 'Морковь',   'carrot.jpg',   12.50, 24);
INSERT INTO "product" VALUES (default, 'Свекла',    'beet.jpg',     17.80, 24);
INSERT INTO "product" VALUES (default, 'Редис',     'radish.jpg',   15.85, 24);
INSERT INTO "product" VALUES (default, 'Хрен',      'hradish.jpg',  16.00, 24);
-- -- Луковичные
INSERT INTO "product" VALUES (default, 'Лук',       'onion.jpg',    19.95, 25);
INSERT INTO "product" VALUES (default, 'Спаржа',    'asparagus.jpg',29.95, 25);
