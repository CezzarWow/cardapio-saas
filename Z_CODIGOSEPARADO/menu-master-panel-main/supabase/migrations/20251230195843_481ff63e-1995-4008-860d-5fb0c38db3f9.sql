-- Store settings table
CREATE TABLE public.store_settings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  is_online BOOLEAN DEFAULT true,
  whatsapp_enabled BOOLEAN DEFAULT false,
  whatsapp_number TEXT,
  whatsapp_message TEXT DEFAULT 'Olá! Obrigado por entrar em contato.',
  default_prep_time INTEGER DEFAULT 30,
  min_delivery_value DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Business hours table
CREATE TABLE public.business_hours (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  day_of_week INTEGER NOT NULL CHECK (day_of_week >= 0 AND day_of_week <= 6),
  is_open BOOLEAN DEFAULT false,
  open_time TIME,
  close_time TIME,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  UNIQUE(day_of_week)
);

-- Delivery zones table
CREATE TABLE public.delivery_zones (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  neighborhood_name TEXT NOT NULL,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  radius_km DECIMAL(5,2),
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Menu categories table
CREATE TABLE public.menu_categories (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  description TEXT,
  priority INTEGER DEFAULT 0,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Menu items table
CREATE TABLE public.menu_items (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  category_id UUID REFERENCES public.menu_categories(id) ON DELETE CASCADE,
  name TEXT NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  image_url TEXT,
  is_highlight BOOLEAN DEFAULT false,
  is_available BOOLEAN DEFAULT true,
  prep_time_minutes INTEGER,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Combos/Promotions table
CREATE TABLE public.combos (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  original_price DECIMAL(10,2),
  valid_until DATE,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Enable RLS on all tables
ALTER TABLE public.store_settings ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.business_hours ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.delivery_zones ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.menu_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.menu_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.combos ENABLE ROW LEVEL SECURITY;

-- For now, allow public read/write (admin auth will be added later)
CREATE POLICY "Allow public read store_settings" ON public.store_settings FOR SELECT USING (true);
CREATE POLICY "Allow public insert store_settings" ON public.store_settings FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public update store_settings" ON public.store_settings FOR UPDATE USING (true);

CREATE POLICY "Allow public read business_hours" ON public.business_hours FOR SELECT USING (true);
CREATE POLICY "Allow public insert business_hours" ON public.business_hours FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public update business_hours" ON public.business_hours FOR UPDATE USING (true);
CREATE POLICY "Allow public delete business_hours" ON public.business_hours FOR DELETE USING (true);

CREATE POLICY "Allow public read delivery_zones" ON public.delivery_zones FOR SELECT USING (true);
CREATE POLICY "Allow public insert delivery_zones" ON public.delivery_zones FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public update delivery_zones" ON public.delivery_zones FOR UPDATE USING (true);
CREATE POLICY "Allow public delete delivery_zones" ON public.delivery_zones FOR DELETE USING (true);

CREATE POLICY "Allow public read menu_categories" ON public.menu_categories FOR SELECT USING (true);
CREATE POLICY "Allow public insert menu_categories" ON public.menu_categories FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public update menu_categories" ON public.menu_categories FOR UPDATE USING (true);
CREATE POLICY "Allow public delete menu_categories" ON public.menu_categories FOR DELETE USING (true);

CREATE POLICY "Allow public read menu_items" ON public.menu_items FOR SELECT USING (true);
CREATE POLICY "Allow public insert menu_items" ON public.menu_items FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public update menu_items" ON public.menu_items FOR UPDATE USING (true);
CREATE POLICY "Allow public delete menu_items" ON public.menu_items FOR DELETE USING (true);

CREATE POLICY "Allow public read combos" ON public.combos FOR SELECT USING (true);
CREATE POLICY "Allow public insert combos" ON public.combos FOR INSERT WITH CHECK (true);
CREATE POLICY "Allow public update combos" ON public.combos FOR UPDATE USING (true);
CREATE POLICY "Allow public delete combos" ON public.combos FOR DELETE USING (true);

-- Insert default store settings
INSERT INTO public.store_settings (is_online, whatsapp_enabled, default_prep_time, min_delivery_value)
VALUES (true, false, 30, 20.00);

-- Insert default business hours (Mon-Sun)
INSERT INTO public.business_hours (day_of_week, is_open, open_time, close_time) VALUES
(0, false, '09:00', '22:00'),
(1, true, '09:00', '22:00'),
(2, true, '09:00', '22:00'),
(3, true, '09:00', '22:00'),
(4, true, '09:00', '22:00'),
(5, true, '09:00', '23:00'),
(6, true, '09:00', '23:00');

-- Insert sample categories
INSERT INTO public.menu_categories (name, description, priority) VALUES
('Lanches', 'Hambúrgueres e sanduíches artesanais', 1),
('Pizzas', 'Pizzas tradicionais e especiais', 2),
('Bebidas', 'Refrigerantes, sucos e cervejas', 3),
('Sobremesas', 'Doces e sobremesas deliciosas', 4);

-- Insert sample menu items
INSERT INTO public.menu_items (category_id, name, description, price, is_highlight) VALUES
((SELECT id FROM public.menu_categories WHERE name = 'Lanches'), 'X-Burger Especial', 'Hambúrguer artesanal 180g, queijo, bacon e molho especial', 28.90, true),
((SELECT id FROM public.menu_categories WHERE name = 'Lanches'), 'X-Salada', 'Hambúrguer 150g, queijo, alface, tomate e maionese', 24.90, false),
((SELECT id FROM public.menu_categories WHERE name = 'Pizzas'), 'Margherita', 'Molho de tomate, mussarela, manjericão fresco', 45.90, true),
((SELECT id FROM public.menu_categories WHERE name = 'Pizzas'), 'Calabresa', 'Molho, mussarela, calabresa fatiada e cebola', 42.90, false),
((SELECT id FROM public.menu_categories WHERE name = 'Bebidas'), 'Coca-Cola 350ml', 'Refrigerante gelado', 6.90, false),
((SELECT id FROM public.menu_categories WHERE name = 'Bebidas'), 'Suco Natural', 'Laranja, limão ou maracujá', 9.90, false),
((SELECT id FROM public.menu_categories WHERE name = 'Sobremesas'), 'Petit Gateau', 'Bolo de chocolate com sorvete de creme', 18.90, true);

-- Insert sample combo
INSERT INTO public.combos (name, description, price, original_price, valid_until, is_active) VALUES
('Combo Família', '2 X-Burger + 2 Coca-Cola + Batata Grande', 59.90, 75.60, '2025-02-28', true);