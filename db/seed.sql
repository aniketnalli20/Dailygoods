INSERT INTO packaging_options(name) VALUES
('Glass Bottle'),
('Tetra Pack'),
('Plastic Pouch'),
('Eco Container')
ON CONFLICT DO NOTHING;

INSERT INTO products(name,type,milk_type,unit,default_unit_qty,price,active) VALUES
('Whole Milk 1L','milk','whole','L',1,60,true),
('Skim Milk 1L','milk','skim','L',1,55,true),
('Organic Milk 1L','milk','organic','L',1,80,true),
('A2 Milk 1L','milk','A2','L',1,100,true),
('Chocolate Flavored Milk 250ml','milk','flavored','ml',250,35,true),
('Eggs (6 pack)','addon',NULL,'unit',6,45,true),
('Butter 500g','addon',NULL,'g',500,240,true),
('Cheese 200g','addon',NULL,'g',200,160,true),
('Yogurt 400g','addon',NULL,'g',400,90,true),
('Ghee 500ml','addon',NULL,'ml',500,450,true)
ON CONFLICT DO NOTHING;

-- Demo users for quick testing
INSERT INTO users(name,email,phone,password_hash,role) VALUES
('Demo Admin','admin@demo.local','9999999999','$2y$10$wlKBnl85nkHV0mYmNCsHueQ/b2bHIH8vxdkCIvmv4A2YqTg6WoBy2','admin'),
('Demo Vendor','vendor@demo.local','8888888888','$2y$10$duUkGgvaeUCV7bEZ0bMBDuDAYAJMEEzLkqIYuTk/bV4PccpMozPFy','vendor'),
('Demo Customer','customer@demo.local','7777777777','$2y$10$nbCoz.7L3pn5vmo0alYaQOBQtZxyoU0Xw4cTA9YzXJ2GQjqnnQIQe','customer')
ON CONFLICT (email) DO NOTHING;