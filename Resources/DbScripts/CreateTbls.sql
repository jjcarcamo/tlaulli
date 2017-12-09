-- Inventory subsystem
CREATE TABLE  item (
  item_id int(11) NOT NULL AUTO_INCREMENT,
  cur_qty int,
  cum_qty int,
  prim_color varchar(50) DEFAULT NULL,
  sec_color varchar(50) DEFAULT NULL,
  size_letter enum('OZFA', 'SP', 'S', 'M', 'L', 'XL', 'XL1', 'XL2', 'XL3'),
  size_w decimal(5,2),
  size_h decimal(5,2),
  size_units varchar(10),
  item_type varchar(50) DEFAULT NULL,
  style varchar(50) DEFAULT NULL,
  kwds text,
  pattern varchar(20) DEFAULT NULL,
  des text,
  unit_cost decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (item_id)
) engine = innodb;

create table sug_ws_price(
  price_id int auto_increment,
  l_qty int,
  h_qty int,
  price decimal(8,2),
  primary key(price_id)
) engine = innodb;

create table item_sug_ws_price_aff(
  item_id int,
  price_id int,
  primary key(item_id, price_id),
  foreign key(item_id) references item(item_id),
  foreign key(price_id) references sug_ws_price(price_id)
) engine = innodb;

create table img(
  img_id int auto_increment,
  width int,
  height int,
  loc varchar(200),
  des text,
  perspective varchar(20),
  img_type enum('thumbnail', 'normal', 'zoom'),
  primary key(img_id)
) engine = innodb;

create table item_img_aff(
  item_id int,
  img_id int,
  foreign key(item_id) references item(item_id),
  foreign key(img_id) references img(img_id)
) engine = innodb;

create table always_avail(
  item_id int,
  foreign key(item_id) references item(item_id)
);

create table item_item_aff(
  parent_id int,
  child_id int,
  relation varchar(30),
  primary key(parent_id, child_id, relation),
  foreign key(parent_id) references item(item_id),
  foreign key(child_id) references item(item_id)
) engine = innodb;

create table special_discounts(
  name varchar(100),
  start date,
  end date,
  amount decimal(8,2),
  percent decimal(3,2),
  description text,
  qty int,
  primary key(name)
) engine = innodb;