-- Script para adicionar coluna is_active na tabela categories
-- Execute no phpMyAdmin se der erro ao salvar

ALTER TABLE categories 
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;
