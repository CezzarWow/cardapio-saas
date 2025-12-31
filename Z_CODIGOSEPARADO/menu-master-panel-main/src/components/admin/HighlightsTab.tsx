import { Star, Layers, Sparkles } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import type { MenuCategory, MenuItem } from '@/hooks/useStoreSettings';

interface HighlightsTabProps {
  categories: MenuCategory[];
  menuItems: MenuItem[];
  onUpdateCategoryPriority: (id: string, priority: number) => void;
  onToggleItemHighlight: (id: string) => void;
}

export function HighlightsTab({
  categories,
  menuItems,
  onUpdateCategoryPriority,
  onToggleItemHighlight,
}: HighlightsTabProps) {
  const highlightedItems = menuItems.filter(item => item.is_highlight);
  
  const getItemsByCategory = (categoryId: string) => 
    menuItems.filter(item => item.category_id === categoryId);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
      {/* Categories & Items */}
      <div className="lg:col-span-2 space-y-6">
        {/* Category Priorities */}
        <section className="card-elevated p-6">
          <h3 className="section-header">
            <Layers className="h-5 w-5 text-primary" />
            Prioridade das Categorias
          </h3>
          
          <p className="text-sm text-muted-foreground mb-4">
            Defina a ordem de exibição das categorias no cardápio (menor número = maior prioridade)
          </p>
          
          <div className="space-y-3">
            {categories.map((category) => (
              <div
                key={category.id}
                className="flex items-center justify-between p-3 rounded-lg bg-muted/50 border border-border"
              >
                <div>
                  <p className="font-medium text-sm">{category.name}</p>
                  {category.description && (
                    <p className="text-xs text-muted-foreground">{category.description}</p>
                  )}
                </div>
                
                <div className="flex items-center gap-2">
                  <Label className="text-xs text-muted-foreground">Posição:</Label>
                  <Input
                    type="number"
                    min={1}
                    value={category.priority}
                    onChange={(e) => onUpdateCategoryPriority(category.id, parseInt(e.target.value) || 1)}
                    className="w-16 h-8 text-center"
                  />
                </div>
              </div>
            ))}
          </div>
        </section>

        {/* Items by Category */}
        <section className="card-elevated p-6">
          <h3 className="section-header">
            <Star className="h-5 w-5 text-primary" />
            Marcar como Destaque
          </h3>
          
          <p className="text-sm text-muted-foreground mb-4">
            Itens marcados como destaque aparecem em evidência no cardápio
          </p>
          
          <div className="space-y-6">
            {categories.map((category) => {
              const items = getItemsByCategory(category.id);
              if (items.length === 0) return null;
              
              return (
                <div key={category.id}>
                  <h4 className="text-sm font-semibold text-muted-foreground mb-3 uppercase tracking-wide">
                    {category.name}
                  </h4>
                  
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {items.map((item) => (
                      <div
                        key={item.id}
                        className={`flex items-center justify-between p-3 rounded-lg border transition-all ${
                          item.is_highlight 
                            ? 'bg-primary/5 border-primary/30' 
                            : 'bg-muted/30 border-border'
                        }`}
                      >
                        <div className="flex items-center gap-2 flex-1 min-w-0">
                          {item.is_highlight && (
                            <Sparkles className="h-4 w-4 text-primary flex-shrink-0" />
                          )}
                          <div className="min-w-0">
                            <p className="font-medium text-sm truncate">{item.name}</p>
                            <p className="text-xs text-muted-foreground">
                              R$ {item.price.toFixed(2)}
                            </p>
                          </div>
                        </div>
                        
                        <Switch
                          checked={item.is_highlight}
                          onCheckedChange={() => onToggleItemHighlight(item.id)}
                        />
                      </div>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>
        </section>
      </div>

      {/* Menu Preview */}
      <div className="lg:col-span-1">
        <section className="card-elevated p-6 sticky top-6">
          <h3 className="section-header">
            <Sparkles className="h-5 w-5 text-primary" />
            Preview do Cardápio
          </h3>
          
          <ScrollArea className="h-[500px] pr-4">
            <div className="space-y-6">
              {/* Highlights Section */}
              {highlightedItems.length > 0 && (
                <div>
                  <h4 className="text-xs font-bold uppercase tracking-wider text-primary mb-3 flex items-center gap-1.5">
                    <Sparkles className="h-3.5 w-3.5" />
                    Destaques
                  </h4>
                  <div className="space-y-2">
                    {highlightedItems.map((item) => (
                      <div key={item.id} className="menu-preview-item">
                        <div className="flex items-start justify-between gap-2">
                          <div className="flex-1 min-w-0">
                            <p className="font-medium text-sm">{item.name}</p>
                            {item.description && (
                              <p className="text-xs text-muted-foreground line-clamp-2 mt-0.5">
                                {item.description}
                              </p>
                            )}
                          </div>
                          <span className="text-sm font-semibold text-primary whitespace-nowrap">
                            R$ {item.price.toFixed(2)}
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Categories */}
              {categories.map((category) => {
                const items = getItemsByCategory(category.id);
                if (items.length === 0) return null;
                
                return (
                  <div key={category.id}>
                    <h4 className="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">
                      {category.name}
                    </h4>
                    <div className="space-y-2">
                      {items.map((item) => (
                        <div key={item.id} className="menu-preview-item">
                          <div className="flex items-start justify-between gap-2">
                            <div className="flex-1 min-w-0">
                              <div className="flex items-center gap-1.5">
                                <p className="font-medium text-sm">{item.name}</p>
                                {item.is_highlight && (
                                  <Sparkles className="h-3 w-3 text-primary" />
                                )}
                              </div>
                              {item.description && (
                                <p className="text-xs text-muted-foreground line-clamp-2 mt-0.5">
                                  {item.description}
                                </p>
                              )}
                            </div>
                            <span className="text-sm font-semibold whitespace-nowrap">
                              R$ {item.price.toFixed(2)}
                            </span>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                );
              })}
            </div>
          </ScrollArea>
        </section>
      </div>
    </div>
  );
}
