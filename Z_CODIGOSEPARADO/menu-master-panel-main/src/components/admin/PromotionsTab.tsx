import { useState } from 'react';
import { Tag, Plus, Calendar, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import type { Combo } from '@/hooks/useStoreSettings';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';

interface PromotionsTabProps {
  combos: Combo[];
  onAddCombo: (combo: Omit<Combo, 'id' | 'is_active'>) => void;
  onToggleCombo: (id: string) => void;
  onRemoveCombo: (id: string) => void;
}

export function PromotionsTab({
  combos,
  onAddCombo,
  onToggleCombo,
  onRemoveCombo,
}: PromotionsTabProps) {
  const [newCombo, setNewCombo] = useState({
    name: '',
    description: '',
    price: 0,
    original_price: 0,
    valid_until: '',
  });

  const handleAddCombo = () => {
    if (!newCombo.name.trim() || newCombo.price <= 0) return;
    
    onAddCombo({
      name: newCombo.name,
      description: newCombo.description || null,
      price: newCombo.price,
      original_price: newCombo.original_price > 0 ? newCombo.original_price : null,
      valid_until: newCombo.valid_until || null,
    });
    
    setNewCombo({
      name: '',
      description: '',
      price: 0,
      original_price: 0,
      valid_until: '',
    });
  };

  const activeCombos = combos.filter(c => c.is_active);
  const inactiveCombos = combos.filter(c => !c.is_active);

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Create Combo Form */}
      <section className="card-elevated p-6">
        <h3 className="section-header">
          <Plus className="h-5 w-5 text-primary" />
          Criar Novo Combo
        </h3>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label htmlFor="combo-name" className="text-sm font-medium">
              Nome do Combo
            </Label>
            <Input
              id="combo-name"
              placeholder="Ex: Combo Família"
              value={newCombo.name}
              onChange={(e) => setNewCombo(prev => ({ ...prev, name: e.target.value }))}
              className="mt-1.5"
            />
          </div>
          
          <div className="grid grid-cols-2 gap-3">
            <div>
              <Label htmlFor="combo-price" className="text-sm font-medium">
                Preço Promocional
              </Label>
              <div className="flex items-center gap-2 mt-1.5">
                <span className="text-muted-foreground">R$</span>
                <Input
                  id="combo-price"
                  type="number"
                  min={0}
                  step={0.01}
                  value={newCombo.price || ''}
                  onChange={(e) => setNewCombo(prev => ({ ...prev, price: parseFloat(e.target.value) || 0 }))}
                />
              </div>
            </div>
            
            <div>
              <Label htmlFor="combo-original" className="text-sm font-medium">
                Preço Original
              </Label>
              <div className="flex items-center gap-2 mt-1.5">
                <span className="text-muted-foreground">R$</span>
                <Input
                  id="combo-original"
                  type="number"
                  min={0}
                  step={0.01}
                  value={newCombo.original_price || ''}
                  onChange={(e) => setNewCombo(prev => ({ ...prev, original_price: parseFloat(e.target.value) || 0 }))}
                />
              </div>
            </div>
          </div>
          
          <div className="md:col-span-2">
            <Label htmlFor="combo-description" className="text-sm font-medium">
              Descrição
            </Label>
            <Textarea
              id="combo-description"
              placeholder="Descreva o que está incluso no combo..."
              value={newCombo.description}
              onChange={(e) => setNewCombo(prev => ({ ...prev, description: e.target.value }))}
              className="mt-1.5"
            />
          </div>
          
          <div>
            <Label htmlFor="combo-valid" className="text-sm font-medium">
              Válido até
            </Label>
            <Input
              id="combo-valid"
              type="date"
              value={newCombo.valid_until}
              onChange={(e) => setNewCombo(prev => ({ ...prev, valid_until: e.target.value }))}
              className="mt-1.5"
            />
          </div>
          
          <div className="flex items-end">
            <Button onClick={handleAddCombo} className="gradient-primary w-full md:w-auto">
              <Plus className="h-4 w-4 mr-2" />
              Criar Combo
            </Button>
          </div>
        </div>
      </section>

      {/* Active Combos */}
      <section className="card-elevated p-6">
        <h3 className="section-header">
          <Tag className="h-5 w-5 text-primary" />
          Combos Ativos ({activeCombos.length})
        </h3>
        
        {activeCombos.length === 0 ? (
          <p className="text-muted-foreground text-sm py-8 text-center">
            Nenhum combo ativo no momento.
          </p>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {activeCombos.map((combo) => (
              <ComboCard
                key={combo.id}
                combo={combo}
                onToggle={() => onToggleCombo(combo.id)}
                onRemove={() => onRemoveCombo(combo.id)}
              />
            ))}
          </div>
        )}
      </section>

      {/* Inactive Combos */}
      {inactiveCombos.length > 0 && (
        <section className="card-elevated p-6">
          <h3 className="section-header text-muted-foreground">
            <Tag className="h-5 w-5" />
            Combos Inativos ({inactiveCombos.length})
          </h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {inactiveCombos.map((combo) => (
              <ComboCard
                key={combo.id}
                combo={combo}
                onToggle={() => onToggleCombo(combo.id)}
                onRemove={() => onRemoveCombo(combo.id)}
              />
            ))}
          </div>
        </section>
      )}
    </div>
  );
}

function ComboCard({
  combo,
  onToggle,
  onRemove,
}: {
  combo: Combo;
  onToggle: () => void;
  onRemove: () => void;
}) {
  const discount = combo.original_price
    ? Math.round((1 - combo.price / combo.original_price) * 100)
    : null;

  return (
    <div className={`card-elevated p-4 transition-opacity ${!combo.is_active ? 'opacity-60' : ''}`}>
      <div className="flex items-start justify-between mb-3">
        <div className="flex-1">
          <div className="flex items-center gap-2">
            <h4 className="font-semibold text-foreground">{combo.name}</h4>
            {discount && discount > 0 && (
              <span className="highlight-badge">-{discount}%</span>
            )}
          </div>
          {combo.description && (
            <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
              {combo.description}
            </p>
          )}
        </div>
        
        <Switch checked={combo.is_active} onCheckedChange={onToggle} />
      </div>
      
      <div className="flex items-center justify-between pt-3 border-t border-border">
        <div>
          <span className="text-lg font-bold text-primary">
            R$ {combo.price.toFixed(2)}
          </span>
          {combo.original_price && (
            <span className="text-sm text-muted-foreground line-through ml-2">
              R$ {combo.original_price.toFixed(2)}
            </span>
          )}
        </div>
        
        <div className="flex items-center gap-2">
          {combo.valid_until && (
            <span className="text-xs text-muted-foreground flex items-center gap-1">
              <Calendar className="h-3 w-3" />
              {format(new Date(combo.valid_until), "dd/MM", { locale: ptBR })}
            </span>
          )}
          
          <Button
            variant="ghost"
            size="icon"
            onClick={onRemove}
            className="h-8 w-8 text-muted-foreground hover:text-destructive"
          >
            <Trash2 className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  );
}
