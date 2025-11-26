CREATE TABLE IF NOT EXISTS users (
  id serial PRIMARY KEY,
  name text NOT NULL,
  email text UNIQUE NOT NULL,
  phone text,
  password_hash text NOT NULL,
  role text NOT NULL DEFAULT 'customer',
  created_at timestamp NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS addresses (
  id serial PRIMARY KEY,
  user_id int REFERENCES users(id) ON DELETE CASCADE,
  line1 text NOT NULL,
  line2 text,
  city text NOT NULL,
  state text,
  pincode text NOT NULL,
  lat double precision,
  lng double precision
);

CREATE TABLE IF NOT EXISTS packaging_options (
  id serial PRIMARY KEY,
  name text UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
  id serial PRIMARY KEY,
  name text NOT NULL,
  type text NOT NULL,
  milk_type text,
  unit text NOT NULL,
  default_unit_qty integer,
  price numeric(10,2) NOT NULL,
  active boolean NOT NULL DEFAULT true
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id serial PRIMARY KEY,
  user_id int REFERENCES users(id) ON DELETE CASCADE,
  address_id int REFERENCES addresses(id) ON DELETE SET NULL,
  plan text NOT NULL,
  frequency text NOT NULL,
  status text NOT NULL DEFAULT 'active',
  start_date date NOT NULL DEFAULT current_date,
  paused_until date,
  wallet_balance numeric(10,2) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS subscription_items (
  id serial PRIMARY KEY,
  subscription_id int REFERENCES subscriptions(id) ON DELETE CASCADE,
  product_id int REFERENCES products(id) ON DELETE RESTRICT,
  packaging_option_id int REFERENCES packaging_options(id) ON DELETE RESTRICT,
  quantity numeric(10,2) NOT NULL,
  unit_price numeric(10,2) NOT NULL,
  total_price numeric(10,2) NOT NULL
);