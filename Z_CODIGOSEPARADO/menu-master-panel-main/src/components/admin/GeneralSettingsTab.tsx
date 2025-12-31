import { useState } from 'react';
import { MessageSquare, Clock, AlertTriangle, MapPin, Truck, Plus, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import type { StoreSettings, BusinessHour, DeliveryZone } from '@/hooks/useStoreSettings';

const DAY_NAMES = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

interface GeneralSettingsTabProps {
  settings: StoreSettings;
  businessHours: BusinessHour[];
  deliveryZones: DeliveryZone[];
  onUpdateSettings: (updates: Partial<StoreSettings>) => void;
  onUpdateBusinessHour: (id: string, updates: Partial<BusinessHour>) => void;
  onAddDeliveryZone: (zone: Omit<DeliveryZone, 'id' | 'is_active'>) => void;
  onRemoveDeliveryZone: (id: string) => void;
  onEmergencyClose: () => void;
}

export function GeneralSettingsTab({
  settings,
  businessHours,
  deliveryZones,
  onUpdateSettings,
  onUpdateBusinessHour,
  onAddDeliveryZone,
  onRemoveDeliveryZone,
  onEmergencyClose,
}: GeneralSettingsTabProps) {
  const [newZone, setNewZone] = useState({ neighborhood_name: '', delivery_fee: 0, radius_km: 0 });

  const handleAddZone = () => {
    if (!newZone.neighborhood_name.trim()) return;
    onAddDeliveryZone({
      neighborhood_name: newZone.neighborhood_name,
      delivery_fee: newZone.delivery_fee,
      radius_km: newZone.radius_km || null,
    });
    setNewZone({ neighborhood_name: '', delivery_fee: 0, radius_km: 0 });
  };

  return (
    <div className="space-y-6 animate-fade-in">
      {/* WhatsApp Bot Section */}
      <section className="card-elevated p-6">
        <h3 className="section-header">
          <MessageSquare className="h-5 w-5 text-primary" />
          WhatsApp Bot
        </h3>
        
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <Label htmlFor="whatsapp-toggle" className="text-sm font-medium">
              Ativar Bot do WhatsApp
            </Label>
            <Switch
              id="whatsapp-toggle"
              checked={settings.whatsapp_enabled}
              onCheckedChange={(checked) => onUpdateSettings({ whatsapp_enabled: checked })}
            />
          </div>
          
          {settings.whatsapp_enabled && (
            <div className="space-y-4 pt-4 border-t border-border animate-fade-in">
              <div>
                <Label htmlFor="whatsapp-number" className="text-sm font-medium">
                  Número com DDD
                </Label>
                <Input
                  id="whatsapp-number"
                  placeholder="(11) 99999-9999"
                  value={settings.whatsapp_number || ''}
                  onChange={(e) => onUpdateSettings({ whatsapp_number: e.target.value })}
                  className="mt-1.5"
                />
              </div>
              
              <div>
                <Label htmlFor="whatsapp-message" className="text-sm font-medium">
                  Mensagem Automática
                </Label>
                <Textarea
                  id="whatsapp-message"
                  placeholder="Olá! Obrigado por entrar em contato..."
                  value={settings.whatsapp_message || ''}
                  onChange={(e) => onUpdateSettings({ whatsapp_message: e.target.value })}
                  className="mt-1.5 min-h-[100px]"
                />
              </div>
            </div>
          )}
        </div>
      </section>

      {/* Prep Time & Emergency */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <section className="card-elevated p-6">
          <h3 className="section-header">
            <Clock className="h-5 w-5 text-primary" />
            Tempo de Preparo
          </h3>
          
          <div>
            <Label htmlFor="prep-time" className="text-sm font-medium">
              Tempo padrão (minutos)
            </Label>
            <Input
              id="prep-time"
              type="number"
              min={1}
              value={settings.default_prep_time}
              onChange={(e) => onUpdateSettings({ default_prep_time: parseInt(e.target.value) || 30 })}
              className="mt-1.5 w-32"
            />
          </div>
        </section>

        <section className="card-elevated p-6">
          <h3 className="section-header">
            <AlertTriangle className="h-5 w-5 text-destructive" />
            Emergência
          </h3>
          
          <Button
            variant="destructive"
            onClick={onEmergencyClose}
            className="w-full"
          >
            <AlertTriangle className="h-4 w-4 mr-2" />
            Fechar Loja Imediatamente
          </Button>
          <p className="text-xs text-muted-foreground mt-2">
            Use em caso de emergência para fechar a loja instantaneamente.
          </p>
        </section>
      </div>

      {/* Business Hours */}
      <section className="card-elevated p-6">
        <h3 className="section-header">
          <Clock className="h-5 w-5 text-primary" />
          Horários de Funcionamento
        </h3>
        
        <div className="space-y-3">
          {businessHours.map((hour) => (
            <div
              key={hour.id}
              className="flex flex-col sm:flex-row sm:items-center gap-3 p-3 rounded-lg bg-muted/50 border border-border"
            >
              <div className="flex items-center gap-3 min-w-[140px]">
                <Checkbox
                  id={`day-${hour.day_of_week}`}
                  checked={hour.is_open}
                  onCheckedChange={(checked) => 
                    onUpdateBusinessHour(hour.id, { is_open: checked as boolean })
                  }
                />
                <Label
                  htmlFor={`day-${hour.day_of_week}`}
                  className="text-sm font-medium cursor-pointer"
                >
                  {DAY_NAMES[hour.day_of_week]}
                </Label>
              </div>
              
              {hour.is_open && (
                <div className="flex items-center gap-2 animate-fade-in">
                  <Input
                    type="time"
                    value={hour.open_time || '09:00'}
                    onChange={(e) => onUpdateBusinessHour(hour.id, { open_time: e.target.value })}
                    className="w-32"
                  />
                  <span className="text-muted-foreground">até</span>
                  <Input
                    type="time"
                    value={hour.close_time || '22:00'}
                    onChange={(e) => onUpdateBusinessHour(hour.id, { close_time: e.target.value })}
                    className="w-32"
                  />
                </div>
              )}
              
              {!hour.is_open && (
                <span className="text-sm text-muted-foreground">Fechado</span>
              )}
            </div>
          ))}
        </div>
      </section>

      {/* Delivery & Logistics */}
      <section className="card-elevated p-6">
        <h3 className="section-header">
          <Truck className="h-5 w-5 text-primary" />
          Delivery & Logística
        </h3>
        
        <div className="space-y-6">
          <div>
            <Label htmlFor="min-delivery" className="text-sm font-medium">
              Valor mínimo para entrega
            </Label>
            <div className="flex items-center gap-2 mt-1.5">
              <span className="text-muted-foreground">R$</span>
              <Input
                id="min-delivery"
                type="number"
                min={0}
                step={0.01}
                value={settings.min_delivery_value}
                onChange={(e) => onUpdateSettings({ min_delivery_value: parseFloat(e.target.value) || 0 })}
                className="w-32"
              />
            </div>
          </div>

          <div className="border-t border-border pt-6">
            <h4 className="text-sm font-semibold mb-4 flex items-center gap-2">
              <MapPin className="h-4 w-4 text-primary" />
              Cadastrar Bairro
            </h4>
            
            <div className="grid grid-cols-1 sm:grid-cols-4 gap-3">
              <Input
                placeholder="Nome do bairro"
                value={newZone.neighborhood_name}
                onChange={(e) => setNewZone(prev => ({ ...prev, neighborhood_name: e.target.value }))}
              />
              <div className="flex items-center gap-2">
                <span className="text-muted-foreground text-sm">R$</span>
                <Input
                  type="number"
                  placeholder="Taxa"
                  min={0}
                  step={0.01}
                  value={newZone.delivery_fee || ''}
                  onChange={(e) => setNewZone(prev => ({ ...prev, delivery_fee: parseFloat(e.target.value) || 0 }))}
                />
              </div>
              <div className="flex items-center gap-2">
                <Input
                  type="number"
                  placeholder="Raio"
                  min={0}
                  step={0.1}
                  value={newZone.radius_km || ''}
                  onChange={(e) => setNewZone(prev => ({ ...prev, radius_km: parseFloat(e.target.value) || 0 }))}
                />
                <span className="text-muted-foreground text-sm">km</span>
              </div>
              <Button onClick={handleAddZone} className="gradient-primary">
                <Plus className="h-4 w-4 mr-2" />
                Adicionar
              </Button>
            </div>
          </div>

          {deliveryZones.length > 0 && (
            <div className="border-t border-border pt-6">
              <h4 className="text-sm font-semibold mb-4">Bairros Cadastrados</h4>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {deliveryZones.map((zone) => (
                  <div
                    key={zone.id}
                    className="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-border"
                  >
                    <div>
                      <p className="font-medium text-sm">{zone.neighborhood_name}</p>
                      <p className="text-xs text-muted-foreground">
                        R$ {zone.delivery_fee.toFixed(2)}
                        {zone.radius_km && ` • ${zone.radius_km}km`}
                      </p>
                    </div>
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => onRemoveDeliveryZone(zone.id)}
                      className="h-8 w-8 text-muted-foreground hover:text-destructive"
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
