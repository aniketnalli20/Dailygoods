CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name TEXT NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone TEXT,
  password_hash TEXT NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS addresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  line1 TEXT NOT NULL,
  line2 TEXT,
  city TEXT NOT NULL,
  state TEXT,
  pincode TEXT NOT NULL,
  lat DOUBLE,
  lng DOUBLE,
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS packaging_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name TEXT NOT NULL,
  type VARCHAR(50) NOT NULL,
  milk_type VARCHAR(50),
  unit VARCHAR(20) NOT NULL,
  default_unit_qty INT,
  price DECIMAL(10,2) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  address_id INT,
  plan VARCHAR(50) NOT NULL,
  frequency VARCHAR(50) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'active',
  start_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  paused_until DATE,
  wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_subscriptions_address FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS subscription_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT,
  product_id INT,
  packaging_option_id INT,
  quantity DECIMAL(10,2) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_items_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_items_packaging FOREIGN KEY (packaging_option_id) REFERENCES packaging_options(id) ON DELETE RESTRICT
);

-- Delivery calendar per subscription
CREATE TABLE IF NOT EXISTS delivery_dates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT,
  delivery_date DATE NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'scheduled',
  note TEXT,
  UNIQUE(subscription_id, delivery_date),
  CONSTRAINT fk_dates_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS delivery_extras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT,
  delivery_date DATE NOT NULL,
  product_id INT,
  packaging_option_id INT,
  quantity DECIMAL(10,2) NOT NULL,
  UNIQUE(subscription_id, delivery_date, product_id, packaging_option_id),
  CONSTRAINT fk_extras_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
  CONSTRAINT fk_extras_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  CONSTRAINT fk_extras_packaging FOREIGN KEY (packaging_option_id) REFERENCES packaging_options(id) ON DELETE RESTRICT
);