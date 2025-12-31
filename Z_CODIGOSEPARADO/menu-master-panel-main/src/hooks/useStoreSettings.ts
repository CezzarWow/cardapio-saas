import { useState, useEffect, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from 'sonner';

export interface StoreSettings {
  id: string;
  is_online: boolean;
  whatsapp_enabled: boolean;
  whatsapp_number: string | null;
  whatsapp_message: string | null;
  default_prep_time: number;
  min_delivery_value: number;
}

export interface BusinessHour {
  id: string;
  day_of_week: number;
  is_open: boolean;
  open_time: string | null;
  close_time: string | null;
}

export interface DeliveryZone {
  id: string;
  neighborhood_name: string;
  delivery_fee: number;
  radius_km: number | null;
  is_active: boolean;
}

export interface MenuCategory {
  id: string;
  name: string;
  description: string | null;
  priority: number;
  is_active: boolean;
}

export interface MenuItem {
  id: string;
  category_id: string;
  name: string;
  description: string | null;
  price: number;
  image_url: string | null;
  is_highlight: boolean;
  is_available: boolean;
}

export interface Combo {
  id: string;
  name: string;
  description: string | null;
  price: number;
  original_price: number | null;
  valid_until: string | null;
  is_active: boolean;
}

export function useStoreSettings() {
  const [settings, setSettings] = useState<StoreSettings | null>(null);
  const [businessHours, setBusinessHours] = useState<BusinessHour[]>([]);
  const [deliveryZones, setDeliveryZones] = useState<DeliveryZone[]>([]);
  const [categories, setCategories] = useState<MenuCategory[]>([]);
  const [menuItems, setMenuItems] = useState<MenuItem[]>([]);
  const [combos, setCombos] = useState<Combo[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const fetchAll = useCallback(async () => {
    try {
      const [
        settingsRes,
        hoursRes,
        zonesRes,
        categoriesRes,
        itemsRes,
        combosRes
      ] = await Promise.all([
        supabase.from('store_settings').select('*').single(),
        supabase.from('business_hours').select('*').order('day_of_week'),
        supabase.from('delivery_zones').select('*').order('neighborhood_name'),
        supabase.from('menu_categories').select('*').order('priority'),
        supabase.from('menu_items').select('*').order('name'),
        supabase.from('combos').select('*').order('name')
      ]);

      if (settingsRes.data) setSettings(settingsRes.data as StoreSettings);
      if (hoursRes.data) setBusinessHours(hoursRes.data as BusinessHour[]);
      if (zonesRes.data) setDeliveryZones(zonesRes.data as DeliveryZone[]);
      if (categoriesRes.data) setCategories(categoriesRes.data as MenuCategory[]);
      if (itemsRes.data) setMenuItems(itemsRes.data as MenuItem[]);
      if (combosRes.data) setCombos(combosRes.data as Combo[]);
    } catch (error) {
      console.error('Error fetching data:', error);
      toast.error('Erro ao carregar dados');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchAll();
  }, [fetchAll]);

  const updateSettings = async (updates: Partial<StoreSettings>) => {
    if (!settings) return;
    setSaving(true);
    try {
      const { error } = await supabase
        .from('store_settings')
        .update(updates)
        .eq('id', settings.id);
      
      if (error) throw error;
      setSettings({ ...settings, ...updates });
      toast.success('Configurações atualizadas!');
    } catch (error) {
      console.error('Error updating settings:', error);
      toast.error('Erro ao atualizar configurações');
    } finally {
      setSaving(false);
    }
  };

  const toggleStoreStatus = async () => {
    if (!settings) return;
    await updateSettings({ is_online: !settings.is_online });
  };

  const closeStoreEmergency = async () => {
    if (!settings) return;
    await updateSettings({ is_online: false });
    toast.warning('Loja fechada por emergência!');
  };

  const updateBusinessHour = async (id: string, updates: Partial<BusinessHour>) => {
    try {
      const { error } = await supabase
        .from('business_hours')
        .update(updates)
        .eq('id', id);
      
      if (error) throw error;
      setBusinessHours(prev => prev.map(h => h.id === id ? { ...h, ...updates } : h));
    } catch (error) {
      console.error('Error updating business hour:', error);
      toast.error('Erro ao atualizar horário');
    }
  };

  const addDeliveryZone = async (zone: Omit<DeliveryZone, 'id' | 'is_active'>) => {
    try {
      const { data, error } = await supabase
        .from('delivery_zones')
        .insert({ ...zone, is_active: true })
        .select()
        .single();
      
      if (error) throw error;
      if (data) setDeliveryZones(prev => [...prev, data as DeliveryZone]);
      toast.success('Bairro adicionado!');
    } catch (error) {
      console.error('Error adding delivery zone:', error);
      toast.error('Erro ao adicionar bairro');
    }
  };

  const removeDeliveryZone = async (id: string) => {
    try {
      const { error } = await supabase
        .from('delivery_zones')
        .delete()
        .eq('id', id);
      
      if (error) throw error;
      setDeliveryZones(prev => prev.filter(z => z.id !== id));
      toast.success('Bairro removido!');
    } catch (error) {
      console.error('Error removing delivery zone:', error);
      toast.error('Erro ao remover bairro');
    }
  };

  const updateCategoryPriority = async (id: string, priority: number) => {
    try {
      const { error } = await supabase
        .from('menu_categories')
        .update({ priority })
        .eq('id', id);
      
      if (error) throw error;
      setCategories(prev => 
        prev.map(c => c.id === id ? { ...c, priority } : c)
          .sort((a, b) => a.priority - b.priority)
      );
    } catch (error) {
      console.error('Error updating category priority:', error);
      toast.error('Erro ao atualizar prioridade');
    }
  };

  const toggleItemHighlight = async (id: string) => {
    const item = menuItems.find(i => i.id === id);
    if (!item) return;
    
    try {
      const { error } = await supabase
        .from('menu_items')
        .update({ is_highlight: !item.is_highlight })
        .eq('id', id);
      
      if (error) throw error;
      setMenuItems(prev => prev.map(i => i.id === id ? { ...i, is_highlight: !i.is_highlight } : i));
    } catch (error) {
      console.error('Error toggling highlight:', error);
      toast.error('Erro ao atualizar destaque');
    }
  };

  const addCombo = async (combo: Omit<Combo, 'id' | 'is_active'>) => {
    try {
      const { data, error } = await supabase
        .from('combos')
        .insert({ ...combo, is_active: true })
        .select()
        .single();
      
      if (error) throw error;
      if (data) setCombos(prev => [...prev, data as Combo]);
      toast.success('Combo criado!');
    } catch (error) {
      console.error('Error adding combo:', error);
      toast.error('Erro ao criar combo');
    }
  };

  const toggleComboActive = async (id: string) => {
    const combo = combos.find(c => c.id === id);
    if (!combo) return;
    
    try {
      const { error } = await supabase
        .from('combos')
        .update({ is_active: !combo.is_active })
        .eq('id', id);
      
      if (error) throw error;
      setCombos(prev => prev.map(c => c.id === id ? { ...c, is_active: !c.is_active } : c));
    } catch (error) {
      console.error('Error toggling combo:', error);
      toast.error('Erro ao atualizar combo');
    }
  };

  const removeCombo = async (id: string) => {
    try {
      const { error } = await supabase
        .from('combos')
        .delete()
        .eq('id', id);
      
      if (error) throw error;
      setCombos(prev => prev.filter(c => c.id !== id));
      toast.success('Combo removido!');
    } catch (error) {
      console.error('Error removing combo:', error);
      toast.error('Erro ao remover combo');
    }
  };

  return {
    settings,
    businessHours,
    deliveryZones,
    categories,
    menuItems,
    combos,
    loading,
    saving,
    updateSettings,
    toggleStoreStatus,
    closeStoreEmergency,
    updateBusinessHour,
    addDeliveryZone,
    removeDeliveryZone,
    updateCategoryPriority,
    toggleItemHighlight,
    addCombo,
    toggleComboActive,
    removeCombo,
    refetch: fetchAll,
  };
}
