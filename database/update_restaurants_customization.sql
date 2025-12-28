-- Adiciona colunas para personalização do Cardápio Web
ALTER TABLE restaurants
ADD COLUMN banner_image VARCHAR(255) DEFAULT NULL AFTER logo,
ADD COLUMN delivery_time VARCHAR(50) DEFAULT '30-45 min' AFTER address;
