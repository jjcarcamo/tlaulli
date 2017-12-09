-- Retrieve an item's wholesale pricing.
select i.item_id, p.* from item i, item_sug_ws_price_aff ip, sug_ws_price p where
i.item_id = ip.item_id and ip.price_id = p.price_id and i.item_id = 21;

-- Associate an item and an image
insert into item_img_aff (item_id, img_id) values (1, 16);

-- Retrieve item groups
SELECT item_id FROM item WHERE item_type = 'Apron' AND style='Double-sided' GROUP BY prim_color, sec_color, item_type, style, pattern, material;

-- Retrieve an item and its images
select * from item i, item_img_aff iia, img g where i.item_id = iia.item_id and iia.img_id = g.img_id;

-- Retrieve an item and its bias
SELECT i1.item_id FROM item i1, item_item_aff ii, item i2 WHERE i1.item_id = ii.parent_id AND 
         ii.child_id = i2.item_id AND ii.relation = 'bias' AND i1.item_id = 175;

